<?php

/**
 * Mesma classe Database usada no Model Produto:
 *   query($sql), bind($param, $valor), executa(), resultado(), resultados(),
 *   iniciarTransacao(), confirmarTransacao(), desfazerTransacao(), ultimoIdInserido().
 *
 * Tabelas (esquema real, ver bd_cit_almoxarifado.sql):
 *   solicitacao(soli_id, soli_dth_retirada, soli_dth_devolucao, soli_observacao,
 *               soli_status, usua_id_solicitante, usua_id_coord, soli_data_solicitacao)
 *   item_solicitacao(item_id, soli_id, prod_id, item_quantidade)
 *   usuario(usua_id, usua_nome, usua_email)
 */
class Solicitacao
{
    private $db;
    public const STATUS_VALIDOS = ['Pendente', 'Aprovada', 'Negada', 'Em devolução', 'Devolvido', 'Cancelada'];

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Solicitação com o nome do solicitante. `codigo` é o alias usado na view.
     */
    public function buscarPorId(int $id)
    {
        $this->db->query(
            "SELECT
            s.soli_id,
            s.soli_id AS codigo,
            s.soli_status,
            s.soli_data_solicitacao,
            s.soli_dth_retirada,
            s.soli_dth_devolucao,
            s.soli_observacao,
            s.usua_id_solicitante,
            u.usua_id,
            u.usua_nome,
            u.usua_email
        FROM solicitacao AS s
        INNER JOIN usuario AS u
            ON u.usua_id = s.usua_id_solicitante
        WHERE s.soli_id = :id"
        );

        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /** Servidor informa que devolveu: Aprovada → Em devolução. */
    public function marcarDevolvido(int $id, int $usuarioId): bool
    {
        $this->db->query(
            "UPDATE solicitacao
            SET soli_status = 'Em devolução'
            WHERE soli_id = :id
            AND usua_id_solicitante = :usuario
            AND soli_status = 'Aprovada'"
        );
        $this->db->bind(':id', $id);
        $this->db->bind(':usuario', $usuarioId);

        return (bool) $this->db->executa();
    }

    /** Coordenador confirma o recebimento: Em devolução → Devolvido. */
    public function confirmarDevolucao(int $id): bool
    {
        $this->db->query(
            "UPDATE solicitacao
            SET soli_status = 'Devolvido'
            WHERE soli_id = :id
            AND soli_status = 'Em devolução'"
        );
        $this->db->bind(':id', $id);

        return (bool) $this->db->executa();
    }

    // contarPorStatus aceita o usuário (null = todos, usado pelo coordenador)
    public function contarPorStatus(?int $usuarioId = null): array
    {
        $sql = 'SELECT soli_status, COUNT(*) AS total FROM solicitacao';
        if ($usuarioId !== null) {
            $sql .= ' WHERE usua_id_solicitante = :usuario';
        }
        $sql .= ' GROUP BY soli_status';

        $this->db->query($sql);
        if ($usuarioId !== null) {
            $this->db->bind(':usuario', $usuarioId);
        }

        $contagem = [];
        foreach ($this->db->resultados() as $linha) {
            $contagem[$linha->soli_status] = (int) $linha->total;
        }

        return $contagem;
    }

    /**
     * Cria a solicitação, mas só depois de confirmar — com as linhas travadas
     * (FOR UPDATE) — que sobra unidade disponível de cada produto.
     *
     * "Disponível" aqui já desconta o que outras solicitações Pendentes
     * reservaram (prod_quantidade - soma dos itens de solicitações 'Pendente').
     * Sem o lock, duas pessoas pedindo ao mesmo tempo a última unidade
     * conseguiriam passar as duas pela validação antes de qualquer uma gravar;
     * com o FOR UPDATE, a segunda espera a primeira transação terminar e
     * enxerga a reserva já feita por ela.
     *
     * Retorna ['ok' => bool, 'id' => int|null, 'erro' => string|null].
     * $itens: [prod_id => quantidade]
     */
    public function criar(int $usuarioId, array $itens): array
    {
        $this->db->iniciarTransacao();

        try {
            // Ordem fixa (sempre crescente) ao travar as linhas: evita deadlock
            // quando duas solicitações concorrentes pedem os mesmos produtos
            // em ordens diferentes.
            $produtoIds = array_map('intval', array_keys($itens));
            sort($produtoIds);

            foreach ($produtoIds as $produtoId) {
                $this->db->query(
                    "SELECT p.prod_id, p.prod_nome, p.prod_quantidade,
                            COALESCE((SELECT SUM(i.item_quantidade)
                                        FROM item_solicitacao i
                                        INNER JOIN solicitacao s ON s.soli_id = i.soli_id
                                       WHERE i.prod_id = p.prod_id
                                         AND s.soli_status = 'Pendente'), 0) AS reservado
                       FROM produto p
                      WHERE p.prod_id = :id
                      FOR UPDATE"
                );
                $this->db->bind(':id', $produtoId);
                $produto = $this->db->resultado();

                if (!$produto) {
                    $this->db->desfazerTransacao();
                    return ['ok' => false, 'id' => null, 'erro' => 'Um dos produtos selecionados não existe mais.'];
                }

                $disponivel = max(0, (int) $produto->prod_quantidade - (int) $produto->reservado);
                $solicitado = (int) $itens[$produtoId];

                if ($solicitado > $disponivel) {
                    // Aborta antes de inserir qualquer linha: a solicitação não é criada pela metade.
                    $this->db->desfazerTransacao();
                    return [
                        'ok' => false,
                        'id' => null,
                        'erro' => $disponivel > 0
                            ? "Restam apenas {$disponivel} unidade(s) de \"{$produto->prod_nome}\" disponíveis (o restante já está reservado em outras solicitações pendentes)."
                            : "\"{$produto->prod_nome}\" não tem mais unidades disponíveis: todas já estão reservadas em solicitações pendentes.",
                    ];
                }
            }

            // Cria a solicitação
            $this->db->query(
                "INSERT INTO solicitacao (
                soli_dth_retirada,
                soli_dth_devolucao,
                soli_observacao,
                soli_status,
                usua_id_solicitante,
                usua_id_coord,
                soli_data_solicitacao
            ) VALUES (
                NULL,
                NULL,
                '',
                'Pendente',
                :usuario,
                NULL,
                NOW()
            )"
            );

            $this->db->bind(':usuario', $usuarioId);

            $this->executarOuFalhar();

            // Recupera o ID da solicitação criada
            $solicitacaoId = (int) $this->db->ultimoIdInserido();

            // Insere os produtos da solicitação (é essa linha que passa a "reservar" o item)
            foreach ($itens as $produtoId => $quantidade) {

                $this->db->query(
                    "INSERT INTO item_solicitacao (
                    soli_id,
                    prod_id,
                    item_quantidade
                ) VALUES (
                    :solicitacao,
                    :produto,
                    :quantidade
                )"
                );

                $this->db->bind(':solicitacao', $solicitacaoId);
                $this->db->bind(':produto', (int) $produtoId);
                $this->db->bind(':quantidade', (int) $quantidade);

                $this->executarOuFalhar();
            }

            $this->db->confirmarTransacao();

            return ['ok' => true, 'id' => $solicitacaoId, 'erro' => null];
        } catch (Throwable $erro) {

            $this->db->desfazerTransacao();

            error_log('[Solicitacao::criar] ' . $erro->getMessage());

            return ['ok' => false, 'id' => null, 'erro' => 'Não foi possível enviar a solicitação. Tente novamente.'];
        }
    }

    private function executarOuFalhar(): void
    {
        if (!$this->db->executa()) {
            throw new RuntimeException('Falha ao executar a consulta.');
        }
    }

    public function registrarAnalise(
        int $id,
        string $status,
        ?string $dataRetirada,
        ?string $dataDevolucao,
        ?string $observacao,
        int $analistaId
    ): bool {

        $this->db->query(
            "UPDATE solicitacao
         SET soli_status = :status,
             soli_dth_retirada = :retirada,
             soli_dth_devolucao = :devolucao,
             soli_observacao = :observacao,
             usua_id_coord = :analista
         WHERE soli_id = :id
           AND soli_status = 'Pendente'"
        );

        $this->db->bind(':status', $status);
        $this->db->bind(':retirada', $dataRetirada);
        $this->db->bind(':devolucao', $dataDevolucao);
        $this->db->bind(':observacao', $observacao);
        $this->db->bind(':analista', $analistaId);
        $this->db->bind(':id', $id);

        return (bool) $this->db->executa();
    }

    public function buscarItens(int $solicitacaoId): array
    {
        $this->db->query(
            "SELECT
            p.prod_nome,
            i.item_quantidade
        FROM item_solicitacao AS i
        INNER JOIN produto AS p
            ON p.prod_id = i.prod_id
        WHERE i.soli_id = :id
        ORDER BY p.prod_nome ASC"
        );

        $this->db->bind(':id', $solicitacaoId);

        return $this->db->resultados();
    }

    public function definirStatus(int $id, string $novoStatus, ?int $idCoordenador): array
    {
        try {
            $this->db->query(
                "UPDATE solicitacao
                    SET soli_status = :status, usua_id_coord = :coordenador
                  WHERE soli_id = :id AND soli_status = 'Pendente'"
            );
            $this->db->bind(':status', $novoStatus);
            $this->db->bind(':coordenador', $idCoordenador);
            $this->db->bind(':id', $id);

            return ['ok' => (bool) $this->db->executa(), 'erro' => null];
        } catch (PDOException $erro) {
            // 1644 = SIGNAL do trigger (ex.: estoque insuficiente): a mensagem já é amigável
            $mensagem = ((int) ($erro->errorInfo[1] ?? 0) === 1644)
                ? $erro->errorInfo[2]
                : 'Não foi possível atualizar a solicitação.';

            return ['ok' => false, 'erro' => $mensagem];
        }
    }

    public function contar(array $filtros): int
    {
        [$where, $parametros] = $this->montarFiltros($filtros);

        $sql = "SELECT COUNT(*) AS total
                FROM solicitacao s
                INNER JOIN usuario u ON u.usua_id = s.usua_id_solicitante
                {$where}";

        $this->db->query($sql);
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }

        return (int) $this->db->resultado()->total;
    }

    public function resumo()
    {
        $this->db->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(CASE WHEN soli_status = 'Em devolução' THEN 1 ELSE 0 END), 0) AS devolucao,
                    COALESCE(SUM(CASE WHEN soli_status = 'Pendente'     THEN 1 ELSE 0 END), 0) AS pendentes,
                    COALESCE(SUM(CASE WHEN soli_status = 'Aprovada'     THEN 1 ELSE 0 END), 0) AS aprovadas,
                    COALESCE(SUM(CASE WHEN soli_status = 'Negada'       THEN 1 ELSE 0 END), 0) AS negadas
            FROM solicitacao"
        );

        return $this->db->resultado();
    }

    public function listar(array $filtros, int $limite, int $offset): array
    {
        [$where, $parametros] = $this->montarFiltros($filtros);

        // LIMIT/OFFSET recebem inteiros já convertidos, por isso podem ir direto na query
        $sql = "SELECT s.soli_id, s.soli_status, s.soli_data_solicitacao,
                    u.usua_nome, u.usua_email,
                    GROUP_CONCAT(p.prod_nome ORDER BY p.prod_nome SEPARATOR ', ') AS produtos,
                    COALESCE(SUM(i.item_quantidade), 0) AS quantidade_total
                FROM solicitacao s
                INNER JOIN usuario u ON u.usua_id = s.usua_id_solicitante
                LEFT JOIN item_solicitacao i ON i.soli_id = s.soli_id
                LEFT JOIN produto p ON p.prod_id = i.prod_id
                {$where}
                GROUP BY s.soli_id, s.soli_status, s.soli_data_solicitacao, u.usua_nome, u.usua_email
                ORDER BY s.soli_data_solicitacao DESC, s.soli_id DESC
                LIMIT " . (int) $limite . " OFFSET " . (int) $offset;

        $this->db->query($sql);
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }

        return $this->db->resultados();
    }

    /**
     * Monta o WHERE e os parâmetros a partir dos filtros (pesquisa, status, data).
     * Compartilhada por listar() e contar(): as duas consultas sempre batem.
     */
    private function montarFiltros(array $filtros): array
    {
        $condicoes  = [];
        $parametros = [];

        if (!empty($filtros['pesquisa'])) {
            // Usuário, e-mail, nº da solicitação ou nome de qualquer produto do pedido
            $condicoes[] = "(u.usua_nome LIKE :pesq_nome
                          OR u.usua_email LIKE :pesq_email
                          OR CAST(s.soli_id AS CHAR) = :pesq_codigo
                          OR EXISTS (SELECT 1
                                FROM item_solicitacao ix
                                INNER JOIN produto px ON px.prod_id = ix.prod_id
                                WHERE ix.soli_id = s.soli_id
                                AND px.prod_nome LIKE :pesq_produto))";
            $like = '%' . $filtros['pesquisa'] . '%';
            $parametros[':pesq_nome']    = $like;
            $parametros[':pesq_email']   = $like;
            $parametros[':pesq_codigo']  = ltrim($filtros['pesquisa'], '#');
            $parametros[':pesq_produto'] = $like;
        }

        if (!empty($filtros['status'])) {
            $condicoes[] = 's.soli_status = :status';
            $parametros[':status'] = $filtros['status'];
        }

        if (!empty($filtros['data'])) {
            // Intervalo em vez de DATE(coluna): permite usar índice
            $inicio = new DateTimeImmutable($filtros['data'] . ' 00:00:00');
            $condicoes[] = 's.soli_data_solicitacao >= :data_inicio AND s.soli_data_solicitacao < :data_fim';
            $parametros[':data_inicio'] = $inicio->format('Y-m-d H:i:s');
            $parametros[':data_fim']    = $inicio->modify('+1 day')->format('Y-m-d H:i:s');
        }

        if (!empty($filtros['usuario'])) {
            $condicoes[] = 's.usua_id_solicitante = :usuario';
            $parametros[':usuario'] = (int) $filtros['usuario'];
        }

        $where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

        return [$where, $parametros];
    }

    public function cancelar(int $id, int $usuarioId): bool
    {
        $this->db->query(
            "UPDATE solicitacao
             SET soli_status = 'Cancelada'
             WHERE soli_id = :id
               AND usua_id_solicitante = :usuario
               AND soli_status = 'Pendente'"
        );
        $this->db->bind(':id', $id);
        $this->db->bind(':usuario', $usuarioId);

        return (bool) $this->db->executa();
    }
}
