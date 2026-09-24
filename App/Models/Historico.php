<?php

/**
 * Model: Historico  (exclusivo da tela Consultar Histórico e do relatório mensal)
 *
 * Esquema (bd_cit_almoxarifado):
 *  - solicitacao:      soli_id, soli_status, soli_data_solicitacao, soli_dth_retirada,
 *                      soli_dth_devolucao, usua_id_solicitante, usua_id_coord
 *  - item_solicitacao: soli_id, prod_id, item_quantidade
 *  - usuario / funcao: usua_nome, usua_email, func_id / func_nome
 *  - produto:          prod_nome, prod_quantidade, prod_estoque_minimo, prod_status,
 *                      cate_id, loca_id, prod_data_cadastro  (coluna NOVA: migracao_historicos.sql)
 *  - notificacao:      noti_data, noti_tipo, noti_titulo, noti_mensagem
 *
 * O histórico é somente leitura: nada aqui altera ou apaga registros.
 * Premissa: Database com query($sql), bind(':param', $v), executa(), resultado(), resultados().
 */
class Historico
{
    public const STATUS_VALIDOS = ['Pendente', 'Aprovada', 'Negada', 'Em devolução'];

    /** Tipos de notificação que entram no "Registro de eventos" do relatório completo
     *  (Solicitação/Aprovação/Negado já aparecem na seção de solicitações). */
    private const TIPOS_EVENTOS = ['Usuário', 'Produto', 'Estoque', 'Sistema'];

    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /* ==========================================================
       Tela: listagem
       ========================================================== */

    /**
     * Uma linha por solicitação, com usuário, função, produtos e quantidade agregados.
     * $usuarioId != null restringe ao histórico daquele usuário (visão pessoal).
     */
    public function listar(array $filtros, ?int $usuarioId, int $limite, int $offset): array
    {
        [$where, $parametros] = $this->montarFiltros($filtros, $usuarioId);

        // LIMIT/OFFSET recebem inteiros já convertidos, por isso podem ir direto na query
        $sql = "SELECT s.soli_id, s.soli_status, s.soli_data_solicitacao,
                       u.usua_nome, u.usua_email, f.func_nome,
                       GROUP_CONCAT(p.prod_nome ORDER BY p.prod_nome SEPARATOR ', ') AS produtos,
                       COALESCE(SUM(i.item_quantidade), 0) AS quantidade_total
                FROM solicitacao s
                INNER JOIN usuario u ON u.usua_id = s.usua_id_solicitante
                LEFT JOIN funcao f ON f.func_id = u.func_id
                LEFT JOIN item_solicitacao i ON i.soli_id = s.soli_id
                LEFT JOIN produto p ON p.prod_id = i.prod_id
                {$where}
                GROUP BY s.soli_id, s.soli_status, s.soli_data_solicitacao,
                         u.usua_nome, u.usua_email, f.func_nome
                ORDER BY s.soli_data_solicitacao DESC, s.soli_id DESC
                LIMIT " . (int) $limite . " OFFSET " . (int) $offset;

        $this->db->query($sql);
        $this->vincular($parametros);

        return $this->db->resultados();
    }

    public function contar(array $filtros, ?int $usuarioId): int
    {
        [$where, $parametros] = $this->montarFiltros($filtros, $usuarioId);

        $this->db->query(
            "SELECT COUNT(*) AS total
               FROM solicitacao s
               INNER JOIN usuario u ON u.usua_id = s.usua_id_solicitante
               {$where}"
        );
        $this->vincular($parametros);

        return (int) $this->db->resultado()->total;
    }

    /**
     * Cards da visão do coordenador (respeitam os filtros aplicados).
     * Retorna objeto: registros, usuarios, produtos, primeira, ultima.
     */
    public function resumoGeral(array $filtros)
    {
        [$where, $parametros] = $this->montarFiltros($filtros, null);

        $this->db->query(
            "SELECT COUNT(DISTINCT s.soli_id) AS registros,
                    COUNT(DISTINCT s.usua_id_solicitante) AS usuarios,
                    COUNT(DISTINCT i.prod_id) AS produtos,
                    MIN(s.soli_data_solicitacao) AS primeira,
                    MAX(s.soli_data_solicitacao) AS ultima
               FROM solicitacao s
               INNER JOIN usuario u ON u.usua_id = s.usua_id_solicitante
               LEFT JOIN item_solicitacao i ON i.soli_id = s.soli_id
               {$where}"
        );
        $this->vincular($parametros);

        return $this->db->resultado();
    }

    /**
     * Cards da visão pessoal (todo o histórico do usuário, sem filtros).
     * Retorna objeto: total, aprovadas, negadas, devolucao.
     */
    public function resumoPessoal(int $usuarioId)
    {
        $this->db->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(CASE WHEN soli_status = 'Aprovada'     THEN 1 ELSE 0 END), 0) AS aprovadas,
                    COALESCE(SUM(CASE WHEN soli_status = 'Negada'       THEN 1 ELSE 0 END), 0) AS negadas,
                    COALESCE(SUM(CASE WHEN soli_status = 'Em devolução' THEN 1 ELSE 0 END), 0) AS devolucao
               FROM solicitacao
              WHERE usua_id_solicitante = :usuario"
        );
        $this->db->bind(':usuario', $usuarioId);

        return $this->db->resultado();
    }

    /** Opções do filtro "Tipo de usuário". */
    public function listarFuncoes(): array
    {
        $this->db->query('SELECT func_id, func_nome FROM funcao ORDER BY func_nome ASC');

        return $this->db->resultados();
    }

    /** Primeiro ano com registros (para a lista de anos do relatório). Null se não houver. */
    public function primeiroAno(): ?int
    {
        $this->db->query(
            "SELECT MIN(ano) AS ano FROM (
                SELECT YEAR(MIN(soli_data_solicitacao)) AS ano FROM solicitacao
                UNION ALL
                SELECT YEAR(MIN(prod_data_cadastro)) FROM produto
             ) AS anos"
        );
        $linha = $this->db->resultado();

        return ($linha && $linha->ano !== null) ? (int) $linha->ano : null;
    }

    /** Nome e função de quem está emitindo o relatório. */
    public function buscarEmissor(int $usuarioId)
    {
        $this->db->query(
            "SELECT u.usua_nome, f.func_nome
               FROM usuario u
               LEFT JOIN funcao f ON f.func_id = u.func_id
              WHERE u.usua_id = :id"
        );
        $this->db->bind(':id', $usuarioId);

        return $this->db->resultado();
    }

    /* ==========================================================
       Relatório mensal ($inicio inclusive, $fim exclusivo: 'Y-m-d H:i:s')
       ========================================================== */

    /**
     * Indicadores das solicitações do período.
     * Retorna objeto: total, aprovadas, negadas, pendentes, em_devolucao, solicitantes, itens.
     */
    public function resumoSolicitacoes(string $inicio, string $fim)
    {
        $this->db->query(
            "SELECT COUNT(DISTINCT s.soli_id) AS total,
                    COUNT(DISTINCT CASE WHEN s.soli_status = 'Aprovada'     THEN s.soli_id END) AS aprovadas,
                    COUNT(DISTINCT CASE WHEN s.soli_status = 'Negada'       THEN s.soli_id END) AS negadas,
                    COUNT(DISTINCT CASE WHEN s.soli_status = 'Pendente'     THEN s.soli_id END) AS pendentes,
                    COUNT(DISTINCT CASE WHEN s.soli_status = 'Em devolução' THEN s.soli_id END) AS em_devolucao,
                    COUNT(DISTINCT s.usua_id_solicitante) AS solicitantes,
                    COALESCE(SUM(i.item_quantidade), 0) AS itens
               FROM solicitacao s
               LEFT JOIN item_solicitacao i ON i.soli_id = s.soli_id
              WHERE s.soli_data_solicitacao >= :inicio
                AND s.soli_data_solicitacao <  :fim"
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);

        return $this->db->resultado();
    }

    /** Todas as solicitações do período, da mais antiga para a mais nova. */
    public function solicitacoesDoPeriodo(string $inicio, string $fim): array
    {
        // Listas grandes de itens não podem ser cortadas pelo limite padrão do GROUP_CONCAT
        $this->db->query('SET SESSION group_concat_max_len = 8192');
        $this->db->executa();

        $this->db->query(
            "SELECT s.soli_id, s.soli_data_solicitacao, s.soli_status,
                    u.usua_nome, f.func_nome,
                    c.usua_nome AS analisado_por,
                    GROUP_CONCAT(CONCAT(p.prod_nome, ' (', i.item_quantidade, ')')
                                 ORDER BY p.prod_nome SEPARATOR '; ') AS itens,
                    COALESCE(SUM(i.item_quantidade), 0) AS quantidade_total
               FROM solicitacao s
               INNER JOIN usuario u ON u.usua_id = s.usua_id_solicitante
               LEFT JOIN funcao f ON f.func_id = u.func_id
               LEFT JOIN usuario c ON c.usua_id = s.usua_id_coord
               LEFT JOIN item_solicitacao i ON i.soli_id = s.soli_id
               LEFT JOIN produto p ON p.prod_id = i.prod_id
              WHERE s.soli_data_solicitacao >= :inicio
                AND s.soli_data_solicitacao <  :fim
              GROUP BY s.soli_id, s.soli_data_solicitacao, s.soli_status,
                       u.usua_nome, f.func_nome, c.usua_nome
              ORDER BY s.soli_data_solicitacao ASC, s.soli_id ASC"
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);

        return $this->db->resultados();
    }

    /** Produtos cadastrados no período. */
    public function produtosCadastradosNoPeriodo(string $inicio, string $fim): array
    {
        $this->db->query(
            "SELECT p.prod_id, p.prod_nome, p.prod_data_cadastro, p.prod_quantidade,
                    p.prod_estoque_minimo, p.prod_status,
                    c.cate_nome, l.loca_nome
               FROM produto p
               LEFT JOIN categoria c ON c.cate_id = p.cate_id
               LEFT JOIN localizacao l ON l.loca_id = p.loca_id
              WHERE p.prod_data_cadastro >= :inicio
                AND p.prod_data_cadastro <  :fim
              ORDER BY p.prod_data_cadastro ASC, p.prod_nome ASC"
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);

        return $this->db->resultados();
    }

    /**
     * Eventos administrativos do período (usuários, produtos, estoque, sistema).
     * Não existe tabela de log: usa as notificações, que registram cada evento uma vez.
     */
    public function eventosDoPeriodo(string $inicio, string $fim): array
    {
        $tipos = "'" . implode("','", self::TIPOS_EVENTOS) . "'"; // lista fixa da constante acima

        $this->db->query(
            "SELECT noti_data, noti_tipo, noti_titulo, noti_mensagem
               FROM notificacao
              WHERE noti_data >= :inicio
                AND noti_data <  :fim
                AND noti_tipo IN ({$tipos})
              ORDER BY noti_data ASC, noti_id ASC"
        );
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fim', $fim);

        return $this->db->resultados();
    }

    /* ==========================================================
       Internos
       ========================================================== */

    private function vincular(array $parametros): void
    {
        foreach ($parametros as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }
    }

    /**
     * Monta o WHERE e os parâmetros (pesquisa, função, status, período).
     * Usa só os aliases s e u; produtos entram por EXISTS, então não duplicam linhas.
     * Cada parâmetro tem nome único (prepares nativos não aceitam o mesmo :nome repetido).
     */
    private function montarFiltros(array $filtros, ?int $usuarioId): array
    {
        $condicoes  = [];
        $parametros = [];

        if ($usuarioId !== null) {
            $condicoes[] = 's.usua_id_solicitante = :usuario';
            $parametros[':usuario'] = $usuarioId;
        }

        if (!empty($filtros['pesquisa'])) {
            $condicoes[] = "(u.usua_nome LIKE :pesq_nome
                          OR u.usua_email LIKE :pesq_email
                          OR CAST(s.soli_id AS CHAR) = :pesq_codigo
                          OR EXISTS (SELECT 1
                                     FROM item_solicitacao ix
                                     INNER JOIN produto px ON px.prod_id = ix.prod_id
                                     WHERE ix.soli_id = s.soli_id
                                       AND px.prod_nome LIKE :pesq_produto))";
            $like = '%' . addcslashes($filtros['pesquisa'], '%_\\') . '%';
            $parametros[':pesq_nome']    = $like;
            $parametros[':pesq_email']   = $like;
            $parametros[':pesq_codigo']  = ltrim($filtros['pesquisa'], '#');
            $parametros[':pesq_produto'] = $like;
        }

        if (!empty($filtros['funcao'])) {
            $condicoes[] = 'u.func_id = :funcao';
            $parametros[':funcao'] = (int) $filtros['funcao'];
        }

        if (!empty($filtros['status']) && in_array($filtros['status'], self::STATUS_VALIDOS, true)) {
            $condicoes[] = 's.soli_status = :status';
            $parametros[':status'] = $filtros['status'];
        }

        // Intervalo em vez de DATE(coluna): permite usar o índice
        if (!empty($filtros['de'])) {
            $condicoes[] = 's.soli_data_solicitacao >= :data_de';
            $parametros[':data_de'] = $filtros['de'] . ' 00:00:00';
        }

        if (!empty($filtros['ate'])) {
            $fimDoDia = (new DateTimeImmutable($filtros['ate'] . ' 00:00:00'))->modify('+1 day');
            $condicoes[] = 's.soli_data_solicitacao < :data_ate';
            $parametros[':data_ate'] = $fimDoDia->format('Y-m-d H:i:s');
        }

        $where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

        return [$where, $parametros];
    }
}