<?php

/**
 * Model Painel: consultas da página inicial (dashboard).
 * Todos os métodos retornam arrays simples com inteiros já convertidos.
 *
 * Assume a classe Database: query() | bind() | resultado()
 */
class Painel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /** Notificações ainda não lidas de um usuário. */
    public function contarNotificacoesNaoLidas(int $usuarioId): int
    {
        $this->db->query("SELECT COUNT(*) AS total
                            FROM notificacao_usuario
                           WHERE usua_id = :id AND noti_status = 'Não lida'");
        $this->db->bind(':id', $usuarioId);

        return (int) $this->db->resultado()->total;
    }

    /**
     * Aprovadas / pendentes / negadas.
     * Sem $usuarioId = todas as solicitações (coordenador);
     * com $usuarioId = só as do solicitante.
     */
    public function resumoSolicitacoes(?int $usuarioId = null): array
    {
        $sql = "SELECT COALESCE(SUM(soli_status = 'Aprovada'), 0) AS aprovadas,
                       COALESCE(SUM(soli_status = 'Pendente'), 0) AS pendentes,
                       COALESCE(SUM(soli_status = 'Negada'),   0) AS negadas
                  FROM solicitacao";

        if ($usuarioId !== null) {
            $sql .= ' WHERE usua_id_solicitante = :id';
        }

        $this->db->query($sql);
        if ($usuarioId !== null) {
            $this->db->bind(':id', $usuarioId);
        }
        $r = $this->db->resultado();

        return [
            'aprovadas' => (int) $r->aprovadas,
            'pendentes' => (int) $r->pendentes,
            'negadas'   => (int) $r->negadas,
        ];
    }

    /**
     * Resumo das atividades.
     * ATENÇÃO: o banco não tem um status de "devolvida/concluída". Por enquanto:
     *   - em andamento       = Aprovada com devolução ainda no prazo (ou sem data)
     *   - pendência devolução = Aprovada com devolução já vencida
     *   - concluídas         = status 'Concluída' (ainda não existe; ficará 0)
     * Veja a sugestão de novo status na explicação.
     */
    public function resumoAtividades(): array
    {
        $this->db->query("SELECT
                COALESCE(SUM(soli_status = 'Aprovada'
                             AND (soli_dth_devolucao IS NULL OR soli_dth_devolucao >= NOW())), 0) AS em_andamento,
                COALESCE(SUM(soli_status = 'Aprovada'
                             AND soli_dth_devolucao < NOW()), 0) AS pendencias_devolucao,
                COALESCE(SUM(soli_status = 'Concluída'), 0) AS concluidas
              FROM solicitacao");
        $r = $this->db->resultado();

        return [
            'emAndamento'         => (int) $r->em_andamento,
            'pendenciasDevolucao' => (int) $r->pendencias_devolucao,
            'concluidas'          => (int) $r->concluidas,
        ];
    }

    /** Perfis ativos e removidos (exclusão lógica em usua_removido). */
    public function resumoPerfis(): array
    {
        $this->db->query('SELECT COALESCE(SUM(IFNULL(usua_removido, 0) = 0), 0) AS ativos,
                                 COALESCE(SUM(IFNULL(usua_removido, 0) = 1), 0) AS removidos
                            FROM usuario');
        $r = $this->db->resultado();

        return ['ativos' => (int) $r->ativos, 'removidos' => (int) $r->removidos];
    }

    /** Quantidade de produtos cadastrados e soma das unidades em estoque. */
    public function resumoProdutos(): array
    {
        $this->db->query('SELECT COUNT(*) AS total,
                                 COALESCE(SUM(prod_quantidade), 0) AS unidades
                            FROM produto');
        $r = $this->db->resultado();

        return ['total' => (int) $r->total, 'unidades' => (int) $r->unidades];
    }

    /** Produtos com estoque baixo (mas ainda disponíveis) e produtos em falta (quantidade 0). */
    public function resumoEstoque(): array
    {
        $this->db->query("SELECT
                COALESCE(SUM(prod_quantidade > 0 AND prod_status = 'Estoque baixo'), 0) AS baixo,
                COALESCE(SUM(prod_quantidade = 0), 0) AS falta
              FROM produto");
        $r = $this->db->resultado();

        return ['baixo' => (int) $r->baixo, 'falta' => (int) $r->falta];
    }

    /**
     * Última solicitação do usuário (produtos agrupados em uma linha).
     * Retorna array ou null se ele nunca solicitou nada.
     */
    public function ultimaSolicitacao(int $usuarioId): ?array
    {
        $this->db->query("SELECT s.soli_status,
                                 s.soli_data_solicitacao,
                                 GROUP_CONCAT(p.prod_nome ORDER BY p.prod_nome SEPARATOR ', ') AS produtos,
                                 COALESCE(SUM(i.item_quantidade), 0) AS quantidade
                            FROM solicitacao s
                       LEFT JOIN item_solicitacao i ON i.soli_id = s.soli_id
                       LEFT JOIN produto p          ON p.prod_id = i.prod_id
                           WHERE s.usua_id_solicitante = :id
                        GROUP BY s.soli_id, s.soli_status, s.soli_data_solicitacao
                        ORDER BY s.soli_data_solicitacao DESC, s.soli_id DESC
                           LIMIT 1");
        $this->db->bind(':id', $usuarioId);
        $r = $this->db->resultado();

        if (!$r) {
            return null;
        }

        return [
            'produtos'   => $r->produtos ?? '',
            'quantidade' => (int) $r->quantidade,
            'status'     => $r->soli_status,
            'data'       => $r->soli_data_solicitacao,
        ];
    }
}
