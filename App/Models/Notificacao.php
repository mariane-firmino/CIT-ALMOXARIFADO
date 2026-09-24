<?php

class Notificacao
{
    private $db;
    private const STATUS_VALIDOS = ['Lida', 'Não lida'];

    /** Valores aceitos pelo ENUM noti_tipo */
    private const TIPOS_VALIDOS = [
        'Solicitação',
        'Aprovação',
        'Negado',
        'Estoque',
        'Usuário',
        'Produto',
        'Sistema'
    ];

    public function __construct()
    {
        $this->db = new Database;
    }

    public const FUNC_COORDENADOR = 1;
    public const FUNC_ESTAGIARIO  = 2;
    public const FUNC_SERVIDOR    = 3;

    /**
     * Usuários ativos como objetos, prontos para notificar().
     * @param int[]|null $funcoes  null = todas as funções
     */
    public function buscarUsuariosAtivos(?array $funcoes = null, ?int $excluirUsuaId = null): array
    {
        $sql = "SELECT usua_id, usua_nome, usua_email FROM usuario
            WHERE usua_status = 'Ativo' AND usua_removido = 0";
        $params = [];

        if (!empty($funcoes)) {
            $marcadores = [];
            foreach (array_values($funcoes) as $i => $func) {
                $marcadores[] = ":func{$i}";
                $params[":func{$i}"] = (int) $func;
            }
            $sql .= ' AND func_id IN (' . implode(', ', $marcadores) . ')';
        }

        if ($excluirUsuaId !== null) {
            $sql .= ' AND usua_id <> :excluir';
            $params[':excluir'] = $excluirUsuaId;
        }

        $this->db->query($sql);
        foreach ($params as $chave => $valor) {
            $this->db->bind($chave, $valor);
        }
        return $this->db->resultados();
    }

    public function buscarUsuarioPorId(int $usuarioId)
    {
        $this->db->query(
            "SELECT
            usua_id,
            usua_nome,
            usua_email
        FROM usuario
        WHERE usua_id = :usua_id
        AND usua_removido = 0"
        );

        $this->db->bind(':usua_id', $usuarioId);

        return $this->db->resultado();
    }

    // ---------- CRUD básico ----------

    public function criar($titulo, $mensagem, $tipo)
    {
        $this->db->query(
            "INSERT INTO notificacao (noti_titulo, noti_mensagem, noti_tipo)
            VALUES (:titulo, :mensagem, :tipo)"
        );
        $this->db->bind(':titulo', $titulo);
        $this->db->bind(':mensagem', $mensagem);
        $this->db->bind(':tipo', $tipo);

        return $this->db->executa() ? $this->db->ultimoIdInserido() : false;
    }

    public function vincularUsuario($notificacaoId, $usuarioId)
    {
        $this->db->query(
            "INSERT INTO notificacao_usuario (noti_id, usua_id, noti_status)
            VALUES (:notificacao, :usuario, :status)"
        );
        $this->db->bind(':notificacao', $notificacaoId);
        $this->db->bind(':usuario', $usuarioId);
        $this->db->bind(':status', 'Não lida');

        return $this->db->executa();
    }

    // ---------- Método genérico ----------

    /**
     * Cria UMA notificação e vincula a vários usuários,
     * disparando um e-mail (opcional) para cada um.
     *
     * @param string   $titulo
     * @param string   $mensagem     Texto que fica salvo no banco (aparece no sininho)
     * @param string   $tipo         Um dos valores do ENUM noti_tipo
     * @param array    $usuarios     Array de objetos/arrays de usuário (precisa de usua_id, usua_nome, usua_email)
     * @param callable|null $montarEmail  function($usuario, $dadosExtras): string  -> HTML do e-mail. Se null, não envia e-mail.
     * @param array    $dadosExtras  Dados extras pra montar o corpo do e-mail
     */
    public function notificar($titulo, $mensagem, $tipo, array $usuarios, ?callable $montarEmail = null, array $dadosExtras = [])
    {
        $notificacaoId = $this->criar($titulo, $mensagem, $tipo);

        if (!$notificacaoId) {
            return false;
        }

        foreach ($usuarios as $usuarioId) {

            $usuario = $this->buscarUsuarioPorId((int) $usuarioId);

            if (!$usuario) {
                continue;
            }

            $this->vincularUsuario(
                $notificacaoId,
                $usuario->usua_id
            );

            if ($montarEmail !== null) {

                $corpo = $montarEmail($usuario, $dadosExtras);

                Email::enviar(
                    $usuario->usua_email,
                    $usuario->usua_nome,
                    $titulo,
                    $corpo
                );
            }
        }

        return $notificacaoId;
    }

    // ---------- Busca de destinatários ----------

    public function buscarCoordenadores()
    {
        $this->db->query(
            "SELECT usua_id, usua_nome, usua_email FROM usuario WHERE func_id = :funcao AND usua_status = 'Ativo' AND usua_removido = 0"
        );
        $this->db->bind(':funcao', 1);
        return $this->db->resultados();
    }

    // ---------- Leitura pelo usuário ----------

    public function listarPorUsuario($usuarioId)
    {
        $this->db->query(
            "SELECT n.noti_id, n.noti_titulo, n.noti_mensagem, n.noti_tipo, n.noti_data,
                    nu.noti_status, nu.noti_data_leitura
            FROM notificacao n
            INNER JOIN notificacao_usuario nu ON nu.noti_id = n.noti_id
            WHERE nu.usua_id = :usuario
            ORDER BY n.noti_data DESC"
        );
        $this->db->bind(':usuario', $usuarioId);
        return $this->db->resultados();
    }

    public function contarNaoLidas($usuarioId)
    {
        $this->db->query(
            "SELECT COUNT(*) AS total
            FROM notificacao_usuario
            WHERE usua_id = :usuario AND noti_status = 'Não lida'"
        );
        $this->db->bind(':usuario', $usuarioId);
        return $this->db->resultado()->total;
    }

    public function marcarComoLida($notificacaoId, $usuarioId)
    {
        $this->db->query(
            "UPDATE notificacao_usuario
            SET noti_status = 'Lida', noti_data_leitura = NOW()
            WHERE noti_id = :noti AND usua_id = :usuario"
        );
        $this->db->bind(':noti', $notificacaoId);
        $this->db->bind(':usuario', $usuarioId);
        return $this->db->executa();
    }

    public function listarPorUsuario2(int $usuaId, array $filtros = []): array
    {
        $sql = "SELECT
                    n.noti_id,
                    n.noti_titulo,
                    n.noti_mensagem,
                    n.noti_tipo,
                    n.noti_data,
                    nu.noti_status,
                    nu.noti_data_leitura
                FROM notificacao n
                INNER JOIN notificacao_usuario nu
                    ON nu.noti_id = n.noti_id
                WHERE nu.usua_id = :usua_id";

        $params = [':usua_id' => $usuaId];

        // Filtro por status (validado contra o ENUM)
        $status = trim((string) ($filtros['status'] ?? ''));
        if (in_array($status, self::STATUS_VALIDOS, true)) {
            $sql .= " AND nu.noti_status = :status";
            $params[':status'] = $status;
        }

        // Filtro por texto no título ou na mensagem
        $pesquisa = trim((string) ($filtros['pesquisa'] ?? ''));
        if ($pesquisa !== '') {
            $sql .= " AND (n.noti_titulo LIKE :pesquisa OR n.noti_mensagem LIKE :pesquisa)";
            $params[':pesquisa'] = '%' . $this->escaparLike($pesquisa) . '%';
        }

        // Filtro por dia exato
        $data = trim((string) ($filtros['data'] ?? ''));
        if ($this->dataValida($data)) {
            $sql .= " AND n.noti_data >= :data_ini AND n.noti_data < :data_fim";
            $params[':data_ini'] = $data . ' 00:00:00';
            $params[':data_fim'] = date('Y-m-d', strtotime($data . ' +1 day')) . ' 00:00:00';
        }

        // Não lidas primeiro, depois da mais recente para a mais antiga
        $sql .= " ORDER BY (nu.noti_status = 'Não lida') DESC, n.noti_data DESC, n.noti_id DESC";

        $this->db->query($sql);

        foreach ($params as $chave => $valor) {
            $this->db->bind($chave, $valor);
        }

        return $this->db->resultados();
    }

    /**
     * Busca uma notificação específica do usuário (útil para validar permissão).
     */
    public function buscarDoUsuario(int $notiId, int $usuaId)
    {
        $this->db->query(
            "SELECT
                n.noti_id,
                n.noti_titulo,
                n.noti_mensagem,
                n.noti_tipo,
                n.noti_data,
                nu.noti_status
             FROM notificacao n
             INNER JOIN notificacao_usuario nu
                ON nu.noti_id = n.noti_id
             WHERE n.noti_id = :noti_id
               AND nu.usua_id = :usua_id"
        );

        $this->db->bind(':noti_id', $notiId);
        $this->db->bind(':usua_id', $usuaId);

        return $this->db->resultado();
    }

    /**
     * Total de notificações não lidas (para o badge do menu).
     */
    public function contarNaoLidas2(int $usuaId): int
    {
        $this->db->query(
            "SELECT COUNT(*) AS total
             FROM notificacao_usuario
             WHERE usua_id = :usua_id
               AND noti_status = 'Não lida'"
        );

        $this->db->bind(':usua_id', $usuaId);

        $linha = $this->db->resultado();

        return $linha ? (int) $linha->total : 0;
    }

    /**
     * Marca uma notificação como lida para o usuário informado.
     * O WHERE com usua_id impede que alguém altere a notificação de outro.
     */
    public function marcarComoLida2(int $notiId, int $usuaId): bool
    {
        $this->db->query(
            "UPDATE notificacao_usuario
             SET noti_status = 'Lida',
                 noti_data_leitura = NOW()
             WHERE noti_id = :noti_id
               AND usua_id = :usua_id
               AND noti_status = 'Não lida'"
        );

        $this->db->bind(':noti_id', $notiId);
        $this->db->bind(':usua_id', $usuaId);

        return $this->db->executa();
    }

    /**
     * Marca todas as notificações pendentes do usuário como lidas.
     */
    public function marcarTodasComoLidas(int $usuaId): bool
    {
        $this->db->query(
            "UPDATE notificacao_usuario
             SET noti_status = 'Lida',
                 noti_data_leitura = NOW()
             WHERE usua_id = :usua_id
               AND noti_status = 'Não lida'"
        );

        $this->db->bind(':usua_id', $usuaId);

        return $this->db->executa();
    }

    /**
     * Remove a notificação da caixa do usuário.
     * Apaga apenas o vínculo — os outros destinatários continuam vendo.
     */
    public function excluirDoUsuario(int $notiId, int $usuaId): bool
    {
        $this->db->query(
            "DELETE FROM notificacao_usuario
             WHERE noti_id = :noti_id
               AND usua_id = :usua_id"
        );

        $this->db->bind(':noti_id', $notiId);
        $this->db->bind(':usua_id', $usuaId);

        return $this->db->executa();
    }

    /**
     * Cria uma notificação e a entrega aos destinatários informados.
     * Use em qualquer parte do sistema (cadastro de usuário, estoque baixo,
     * aprovação de solicitação) em vez de repetir INSERTs pelos controllers.
     *
     * @param array $destinatarios IDs de usuário
     * @return int  ID da notificação criada (0 em caso de falha)
     */


    /**
     * IDs dos usuários ativos, opcionalmente filtrados por função
     * (1 = Coordenador, 2 = Estagiário, 3 = Servidor).
     * Útil para montar a lista de destinatários em criar().
     */
    public function destinatarios(?int $funcId = null, ?int $excluirUsuaId = null): array
    {
        $sql = "SELECT usua_id
                FROM usuario
                WHERE usua_status = 'Ativo'
                AND usua_removido = 0";

        $params = [];

        if ($funcId !== null) {
            $sql .= " AND func_id = :func_id";
            $params[':func_id'] = $funcId;
        }

        if ($excluirUsuaId !== null) {
            $sql .= " AND usua_id <> :excluir";
            $params[':excluir'] = $excluirUsuaId;
        }

        $this->db->query($sql);

        foreach ($params as $chave => $valor) {
            $this->db->bind($chave, $valor);
        }

        return array_map(
            static fn($linha) => (int) $linha->usua_id,
            $this->db->resultados()
        );
    }

    /** Escapa os curingas do LIKE para que sejam buscados como texto literal */
    private function escaparLike(string $texto): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $texto);
    }

    /** Valida uma data no formato Y-m-d */
    private function dataValida(string $data): bool
    {
        if ($data === '') {
            return false;
        }

        $d = DateTime::createFromFormat('Y-m-d', $data);

        return $d && $d->format('Y-m-d') === $data;
    }
}
