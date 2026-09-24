<?php

/**
 * Controller Inicio: página inicial (dashboard) de cada tipo de usuário.
 *
 * Funções (tabela funcao): 1 = Coordenador, 2 = Estagiário, 3 = Servidor.
 * Rota: /inicio  (renomeie a classe se a sua página inicial tiver outro nome)
 */
class Paginas extends Controller
{
    private $painel;

    public function __construct()
    {
        $this->painel = $this->model('Painel');
    }

    public function index(){
        URL::redirecionar('users/loginUser');
    }

    public function home()
    {
        // O redirecionamento acontece ANTES de qualquer HTML ser enviado
        // (na view original ele vinha depois do menu, e o header() falharia).
        if (empty($_SESSION['usuario_id'])) {
            URL::redirecionar('users/loginUser');
            exit;
        }

        $id     = (int) $_SESSION['usuario_id'];
        $funcao = (int) ($_SESSION['usuario_funcao'] ?? 0);

        $dados = [
            'notificacoesNaoLidas' => $this->painel->contarNotificacoesNaoLidas($id),
        ];

        if ($funcao === 1) {
            // Coordenador: visão geral do sistema
            $dados['resumo']     = $this->painel->resumoSolicitacoes();
            $dados['atividades'] = $this->painel->resumoAtividades();
            $dados['perfis']     = $this->painel->resumoPerfis();
            $dados['produtos']   = $this->painel->resumoProdutos();
            $dados['estoque']    = $this->painel->resumoEstoque();
        } elseif ($funcao === 2 || $funcao === 3) {
            // Estagiário / Servidor: apenas as próprias solicitações
            $dados['resumo']            = $this->painel->resumoSolicitacoes($id);
            $dados['ultimaSolicitacao'] = $this->painel->ultimaSolicitacao($id);
        } else {
            URL::redirecionar('users/loginUser');
            exit;
        }

        $this->view('paginas/home', $dados);
    }

    public function sobre () {
        $this->view('paginas/sobre');
    }
}