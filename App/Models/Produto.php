<?php

/**
 * Model Produto  (esquema: bd_cit_almoxarifado)
 * Tabelas: produto, categoria, localizacao, item_solicitacao.
 *
 * Assume a classe Database: query() | bind() | executa() | resultado() | resultados()
 *                            | beginTransaction() | commit() | rollBack() | inTransaction()
 *                            | ultimoIdInserido()
 */
class Produto
{
    public const STATUS_VALIDOS = ['Disponível', 'Estoque baixo', 'Esgotado'];

    /**
     * Quantidade já comprometida com solicitações Pendentes (ainda não analisadas).
     * Enquanto uma solicitação estiver pendente, essas unidades não podem ser
     * pedidas por ninguém mais. Se ela for negada, a soma cai sozinha (o SELECT
     * conta só o que está 'Pendente' agora); se for aprovada, o gatilho do banco
     * desconta de prod_quantidade e ela também some da reserva.
     */
    private const RESERVADO_SQL = "(SELECT COALESCE(SUM(i.item_quantidade), 0)
                                       FROM item_solicitacao i
                                 INNER JOIN solicitacao s ON s.soli_id = i.soli_id
                                      WHERE i.prod_id = p.prod_id
                                        AND s.soli_status = 'Pendente')";

    /** O que sobra para ser solicitado agora: estoque total menos o já reservado. */
    private const DISPONIVEL_SQL = 'GREATEST(p.prod_quantidade - ' . self::RESERVADO_SQL . ', 0)';

    /**
     * Status "ao vivo", calculado a partir do DISPONÍVEL (não da quantidade total):
     * um produto com tudo reservado aparece como esgotado para quem for solicitar,
     * mesmo com prod_quantidade > 0. Usado para listar/filtrar produtos e checar
     * solicitações. p.prod_status (a coluna gravada) reflete só o estoque físico
     * — quem grava esse valor é calcularStatus(), abaixo — e é o que a tela
     * Controlar Estoque mostra, sem entrar em reservas.
     */
    private const STATUS_SQL = "CASE
            WHEN " . self::DISPONIVEL_SQL . " = 0 THEN 'Esgotado'
            WHEN p.prod_status = 'Estoque baixo' THEN 'Estoque baixo'
            ELSE 'Disponível'
        END";

    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /* ==========================================================
       Listagens
       ========================================================== */

    public function listarCategorias(): array
    {
        $this->db->query('SELECT cate_id, cate_nome FROM categoria ORDER BY cate_nome');

        return $this->db->resultados();
    }

    /**
     * Lista para a tela Controlar Produto (cards). prod_status aqui já vem
     * "ao vivo" (reservas descontadas), não é a coluna gravada.
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        [$where, $parametros] = $this->montarFiltros($filtros);

        // LIMIT/OFFSET recebem inteiros já convertidos, por isso podem ir direto na query
        $limite = max(1, $limite);
        $offset = max(0, $offset);

        $this->db->query(
            'SELECT p.prod_id, p.prod_nome, p.prod_foto, p.prod_quantidade,
                    ' . self::RESERVADO_SQL . ' AS reservado,
                    ' . self::DISPONIVEL_SQL . ' AS disponivel,
                    ' . self::STATUS_SQL . " AS prod_status,
                    c.cate_id, c.cate_nome
               FROM produto p
          LEFT JOIN categoria c ON c.cate_id = p.cate_id
               {$where}
           ORDER BY p.prod_nome ASC
              LIMIT {$limite} OFFSET {$offset}"
        );
        $this->vincular($parametros);

        return $this->db->resultados();
    }

    /**
     * Lista para a tabela da tela Controlar Estoque: reflete o estoque físico
     * (prod_status gravado), sem descontar reservas — é a visão do coordenador
     * sobre o que existe fisicamente, não sobre o que pode ser pedido agora.
     */
    public function listarEstoque(array $filtros, int $limite, int $offset): array
    {
        return $this->consultarLista(
            'p.prod_id, p.prod_nome, p.prod_quantidade, p.prod_estoque_minimo, p.prod_status, c.cate_nome',
            $filtros,
            $limite,
            $offset
        );
    }

    /**
     * Todos os produtos (ou de uma categoria) para o relatório, sem paginação.
     * Ordenados por categoria e nome, para permitir o agrupamento na view.
     */
    public function listarRelatorio(?int $categoria = null): array
    {
        $where = $categoria ? 'WHERE p.cate_id = :categoria' : '';

        $this->db->query(
            "SELECT p.prod_id, p.prod_nome, p.prod_quantidade, p.prod_estoque_minimo,
                    p.prod_status, c.cate_nome, l.loca_nome
               FROM produto p
         INNER JOIN categoria c ON c.cate_id = p.cate_id
         INNER JOIN localizacao l ON l.loca_id = p.loca_id
               {$where}
           ORDER BY c.cate_nome ASC, p.prod_nome ASC"
        );

        if ($categoria) {
            $this->db->bind(':categoria', $categoria);
        }

        return $this->db->resultados();
    }

    /**
     * Conta o total de produtos com os mesmos filtros (usado na paginação de
     * listar()/listarEstoque(); as três consultas sempre batem).
     */
    public function contar(array $filtros): int
    {
        [$where, $parametros] = $this->montarFiltros($filtros);

        $this->db->query("SELECT COUNT(*) AS total FROM produto p {$where}");
        $this->vincular($parametros);

        return (int) $this->db->resultado()->total;
    }

    /**
     * Detalhe completo de um produto (tela de editar/detalhar), com localização
     * e se já tem QR Code gerado.
     */
    public function buscarPorId(int $id)
    {
        $this->db->query("SELECT
                p.prod_id, p.prod_nome, p.prod_descricao, p.prod_foto,
                p.prod_quantidade, p.prod_estoque_minimo, p.prod_status,
                c.cate_id, c.cate_nome,
                l.loca_id, l.loca_nome,
                CASE
                    WHEN p.prod_qrcode IS NOT NULL AND OCTET_LENGTH(p.prod_qrcode) > 0 THEN 1
                    ELSE 0
                END AS tem_qrcode
            FROM produto p
       INNER JOIN categoria c ON c.cate_id = p.cate_id
       INNER JOIN localizacao l ON l.loca_id = p.loca_id
           WHERE p.prod_id = :id");
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    /**
     * Usado na tela de solicitação: traz os produtos pedidos com a quantidade
     * DISPONÍVEL (descontando o que está reservado em pendentes) e o status
     * calculado em cima dela. É uma leitura "otimista": serve para mostrar
     * limites na tela, mas quem garante a reserva de verdade, sem condição de
     * corrida, é o Solicitacao::criar() com SELECT ... FOR UPDATE.
     */
    public function buscarPorIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        // Marcadores nomeados (:id0, :id1...) em vez de "?": mais portável entre
        // implementações de Database, que às vezes só aceitam bind nomeado.
        $marcadores = [];
        $parametros = [];
        foreach ($ids as $indice => $id) {
            $marcadores[]              = ":id{$indice}";
            $parametros[":id{$indice}"] = $id;
        }

        $this->db->query(
            'SELECT p.prod_id, p.prod_nome, p.prod_descricao, p.prod_foto, p.prod_quantidade,
                    ' . self::RESERVADO_SQL . ' AS reservado,
                    ' . self::DISPONIVEL_SQL . ' AS disponivel,
                    ' . self::STATUS_SQL . " AS prod_status,
                    c.cate_nome
               FROM produto p
          INNER JOIN categoria c ON c.cate_id = p.cate_id
              WHERE p.prod_id IN (" . implode(', ', $marcadores) . ')
           ORDER BY p.prod_nome'
        );
        $this->vincular($parametros);

        return $this->db->resultados();
    }

    /** O produto já aparece em alguma solicitação? (a FK impede a exclusão nesse caso) */
    public function possuiSolicitacoes(int $id): bool
    {
        $this->db->query('SELECT item_id FROM item_solicitacao WHERE prod_id = :id LIMIT 1');
        $this->db->bind(':id', $id);

        return (bool) $this->db->resultado();
    }

    /**
     * Totais dos cards-resumo do estoque, em uma única consulta.
     * Retorna objeto: total, disponiveis, baixo, esgotados.
     */
    public function resumoEstoque()
    {
        $this->db->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(CASE WHEN prod_status = 'Disponível'    THEN 1 ELSE 0 END), 0) AS disponiveis,
                    COALESCE(SUM(CASE WHEN prod_status = 'Estoque baixo' THEN 1 ELSE 0 END), 0) AS baixo,
                    COALESCE(SUM(CASE WHEN prod_status = 'Esgotado'      THEN 1 ELSE 0 END), 0) AS esgotados
             FROM produto"
        );

        return $this->db->resultado();
    }

    /** Busca o QR Code de um produto (BLOB). */
    public function buscarQrCode(int $id): ?string
    {
        $sql = "
        SELECT prod_qrcode
        FROM produto
        WHERE prod_id = :id
        LIMIT 1
        ";

        $this->db->query($sql);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        $resultado = $this->db->resultado();

        if (!$resultado) {
            return null;
        }

        // O Database::resultado() retorna um stdClass
        $qr = $resultado->prod_qrcode ?? null;

        if ($qr === null) {
            return null;
        }

        // Caso o PDO entregue o BLOB como recurso
        if (is_resource($qr)) {
            $qr = stream_get_contents($qr);
        }

        if (!is_string($qr) || $qr === '') {
            return null;
        }

        return $qr;
    }

    /* ==========================================================
       Cadastro, edição e exclusão
       ========================================================== */

    public function cadastrar(array $d, callable $gerarQr): int
    {
        $this->db->beginTransaction();

        try {
            $sql = 'INSERT INTO produto
            (prod_nome, prod_descricao, prod_foto, prod_quantidade,
            prod_estoque_minimo, prod_status, loca_id, cate_id)
        VALUES
            (:nome, :descricao, :foto, :quantidade,
            :minimo, :status, :localizacao, :categoria)';

            $this->db->query($sql);

            $this->db->bind(':nome', $d['nome']);
            $this->db->bind(':descricao', $d['descricao']);
            $this->db->bind(':foto', $d['foto']);
            $this->db->bind(':quantidade', $d['quantidade']);
            $this->db->bind(':minimo', $d['estoque_minimo']);

            $this->db->bind(
                ':status',
                self::calcularStatus(
                    $d['quantidade'],
                    $d['estoque_minimo']
                )
            );

            $this->db->bind(':localizacao', $d['localizacao']);
            $this->db->bind(':categoria', $d['categoria']);

            $this->db->executa();

            $id  = (int) $this->db->ultimoIdInserido();
            $png = $gerarQr($id);

            $upd = 'UPDATE produto
            SET prod_qrcode = :qr
            WHERE prod_id = :id';

            $this->db->query($upd);

            $this->db->bind(':qr', $png, PDO::PARAM_LOB);
            $this->db->bind(':id', $id, PDO::PARAM_INT);

            $this->db->executa();

            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function atualizar(int $id, array $d, ?string $novaFoto = null): void
    {
        $campos = 'prod_descricao = :descricao,
                   prod_quantidade = :quantidade,
                   prod_estoque_minimo = :minimo,
                   prod_status = :status,
                   loca_id = :localizacao';

        $params = [
            ':descricao'   => $d['descricao'],
            ':quantidade'  => $d['quantidade'],
            ':minimo'      => $d['estoque_minimo'],
            ':status'      => self::calcularStatus($d['quantidade'], $d['estoque_minimo']),
            ':localizacao' => $d['localizacao'],
            ':id'          => $id,
        ];

        if ($novaFoto !== null) {
            $campos .= ', prod_foto = :foto';
            $params[':foto'] = $novaFoto;
        }

        $this->db->query("UPDATE produto SET $campos WHERE prod_id = :id");

        foreach ($params as $parametro => $valor) {
            $this->db->bind($parametro, $valor);
        }

        $this->db->executa();
    }

    public function excluir(int $id): bool
    {
        try {
            $this->db->query('DELETE FROM produto WHERE prod_id = :id');
            $this->db->bind(':id', $id);

            return (bool) $this->db->executa();
        } catch (PDOException $erro) {
            // Ex.: violação de chave estrangeira (produto com movimentações)
            return false;
        }
    }

    /** Status gravado a partir do estoque físico (sem considerar reservas). */
    public static function calcularStatus(int $quantidade, int $minimo): string
    {
        if ($quantidade <= 0) {
            return 'Esgotado';
        }

        return $quantidade <= $minimo ? 'Estoque baixo' : 'Disponível';
    }

    /* ==========================================================
       Internos
       ========================================================== */

    private function consultarLista(string $colunas, array $filtros, int $limite, int $offset): array
    {
        [$where, $parametros] = $this->montarFiltros($filtros);

        // LIMIT/OFFSET recebem inteiros já convertidos, por isso podem ir direto na query
        $sql = "SELECT {$colunas}
                FROM produto p
                INNER JOIN categoria c ON c.cate_id = p.cate_id
                {$where}
                ORDER BY p.prod_nome ASC
                LIMIT " . (int) $limite . ' OFFSET ' . (int) $offset;

        $this->db->query($sql);
        $this->vincular($parametros);

        return $this->db->resultados();
    }

    /**
     * Monta o WHERE e os parâmetros a partir dos filtros.
     * Compartilhada por listar(), listarEstoque() e contar(): as consultas
     * sempre batem. $filtros: ['pesquisa' => string, 'categoria' => int (0 =
     * todas), 'status' => string]
     */
    private function montarFiltros(array $filtros): array
    {
        $condicoes  = [];
        $parametros = [];

        if ($filtros['pesquisa'] !== '') {
            // Escapa % e _ para o usuário não usar curingas do LIKE sem querer
            $termo = '%' . addcslashes($filtros['pesquisa'], '%_\\') . '%';
            $condicoes[] = '(p.prod_nome LIKE :pesquisa_nome OR p.prod_descricao LIKE :pesquisa_descricao)';
            $parametros[':pesquisa_nome']      = $termo;
            $parametros[':pesquisa_descricao'] = $termo;
        }

        if ($filtros['categoria'] > 0) {
            $condicoes[] = 'p.cate_id = :categoria';
            $parametros[':categoria'] = $filtros['categoria'];
        }

        if ($filtros['status'] !== '') {
            $condicoes[] = self::STATUS_SQL . ' = :status';
            $parametros[':status'] = $filtros['status'];
        }

        $where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

        return [$where, $parametros];
    }

    private function vincular(array $parametros): void
    {
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }
    }
}
