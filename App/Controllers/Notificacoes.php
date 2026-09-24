<?php

class Notificacoes extends Controller
{
    private $notificacaoModel;
    private $usuaId;

    /** Chaves de filtro aceitas na query string */
    private const FILTROS = ['status', 'pesquisa', 'data'];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Só usuário autenticado acessa notificações
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . URL . '/login');
            exit;
        }

        $this->usuaId = (int) $_SESSION['usuario_id'];
        $this->notificacaoModel = $this->model('Notificacao');
    }

    /**
     * Lista as notificações do usuário logado, aplicando os filtros da toolbar.
     */
    public function notificacao(): void
    {
        $filtros = [
            'status'   => trim((string) ($_GET['status']   ?? '')),
            'pesquisa' => trim((string) ($_GET['pesquisa'] ?? '')),
            'data'     => trim((string) ($_GET['data']     ?? '')),
        ];

        $dados = [
            'tituloPagina'  => 'Notificações',
            'notificacoes'  => $this->notificacaoModel->listarPorUsuario2($this->usuaId, $filtros),
            'totalNaoLidas' => $this->notificacaoModel->contarNaoLidas2($this->usuaId),
            'filtros'       => $filtros,
            'mensagem'      => $this->consumirMensagem(),
        ];

        $this->view('notificacoes/notificacao', $dados);
    }

    /**
     * Marca uma notificação como lida.
     */
    public function lida($id = 0): void
    {
        $notiId = (int) $id;

        if ($notiId <= 0) {
            $this->redirecionar('Notificação inválida.', 'erro');
        }

        // Confere se a notificação realmente pertence ao usuário logado
        if (!$this->notificacaoModel->buscarDoUsuario($notiId, $this->usuaId)) {
            $this->redirecionar('Notificação não encontrada.', 'erro');
        }

        $this->notificacaoModel->marcarComoLida2($notiId, $this->usuaId);

        $this->redirecionar('Notificação marcada como lida.');
    }

    /**
     * Marca todas as notificações pendentes como lidas.
     */
    public function lerTodas(): void
    {
        $this->notificacaoModel->marcarTodasComoLidas($this->usuaId);

        $this->redirecionar('Todas as notificações foram marcadas como lidas.');
    }

    /**
     * Remove a notificação da caixa do usuário logado.
     */
    public function excluir($id = 0): void
    {
        $notiId = (int) $id;

        if ($notiId <= 0) {
            $this->redirecionar('Notificação inválida.', 'erro');
        }

        if (!$this->notificacaoModel->buscarDoUsuario($notiId, $this->usuaId)) {
            $this->redirecionar('Notificação não encontrada.', 'erro');
        }

        $this->notificacaoModel->excluirDoUsuario($notiId, $this->usuaId);

        $this->redirecionar('Notificação excluída.');
    }

    /**
     * Volta para a listagem preservando os filtros que estavam na tela
     * e guardando uma mensagem de feedback na sessão.
     */
    private function redirecionar(string $mensagem = '', string $tipo = 'sucesso'): void
    {
        if ($mensagem !== '') {
            $_SESSION['flash_notificacoes'] = [
                'texto' => $mensagem,
                'tipo'  => $tipo,
            ];
        }

        // Só reaproveita as chaves conhecidas, nunca a query string inteira
        $filtros = [];
        foreach (self::FILTROS as $chave) {
            $valor = trim((string) ($_GET[$chave] ?? ''));
            if ($valor !== '') {
                $filtros[$chave] = $valor;
            }
        }

        $query = $filtros ? '?' . http_build_query($filtros) : '';

        header('Location: ' . URL . '/notificacoes/notificacao' . $query);
        exit;
    }

    /** Lê e apaga a mensagem de feedback da sessão */
    private function consumirMensagem(): ?array
    {
        if (empty($_SESSION['flash_notificacoes'])) {
            return null;
        }

        $mensagem = $_SESSION['flash_notificacoes'];
        unset($_SESSION['flash_notificacoes']);

        return $mensagem;
    }
}