<?php

class Users extends Controller
{
    private $userModel;
    private $notifyModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
        $this->notifyModel = $this->model('Notificacao');
    }

    public function tipoUsuario()
    {
        $this->view('user/usuario');
    }

    public function loginUser()
    {
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        if (isset($formulario)) :
            $dados = [
                'email' => trim($formulario['email']),
                'senha' => trim($formulario['senha']),
            ];

            if (in_array("", $formulario)) :

                if (empty($formulario['email'])) :
                    $dados['email_erro'] = 'Preencha o campo e-mail';
                endif;

                if (empty($formulario['senha'])) :
                    $dados['senha_erro'] = 'Preencha o campo senha';
                endif;

            else :
                if (Checa::checarEmail($formulario['email'])) :
                    $dados['email_erro'] = 'O e-mail informado é invalido';
                    echo "email invalido";
                else :
                    $usuario = $this->userModel->checarLogin(
                        $formulario['email'],
                        $formulario['senha']
                    );

                    if ($usuario):

                        // Verifica se o usuário está inativo
                        if ($usuario->usua_status !== 'Ativo'):

                            Sessao::mensagem(
                                'user',
                                'Usuário inativo. Aguarde o coordenador autorizar seu cadastro no sistema.',
                                'alert alert-danger'
                            );

                        else:

                            // Atualiza o último acesso somente de usuários ativos
                            $this->userModel->atualizarUltimoLogin(
                                $usuario->usua_id
                            );

                            // Cria a sessão somente se estiver ativo
                            $this->criarSessaoUsuario($usuario);

                        endif;

                    else:

                        Sessao::mensagem(
                            'user',
                            'Usuário ou senha inválidos',
                            'alert alert-danger'
                        );

                    endif;
                endif;

            endif;
        else :
            $dados = [
                'email' => '',
                'senha' => '',
                'email_erro' => '',
                'senha_erro' => ''
            ];

        endif;

        $this->view('user/login', $dados);
    }

    private function criarSessaoUsuario($usuario)
    {
        $_SESSION['usuario_id'] = $usuario->usua_id;
        $_SESSION['usuario_nome'] = $usuario->usua_nome;
        $_SESSION['usuario_email'] = $usuario->usua_email;
        $_SESSION['usuario_telefone'] = $usuario->tele_numero;
        $_SESSION['usuario_siap'] = $usuario->usua_siap;
        $_SESSION['usuario_matricula'] = $usuario->usua_matricula;
        $_SESSION['usuario_ano'] = $usuario->turm_ano;
        $_SESSION['usuario_curso'] = $usuario->turm_curso;
        $_SESSION['usuario_funcao'] = $usuario->func_id;
        $_SESSION['usuario_setor'] = $usuario->seto_nome;
        $_SESSION['usuario_foto'] = $usuario->usua_foto;


        URL::redirecionar('paginas/home');
        exit;
    }

    public function cadCoordenador()
    {
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        if (isset($formulario)) :
            $dados = [
                'nome' => trim($formulario['nome']),
                'email' => trim($formulario['email']),
                'siap' => trim($formulario['siap']),
                'matricula' => NULL,
                'senha' => trim($formulario['senha']),
                'confirma_senha' => trim($formulario['confirma_senha']),
                'funcao' => 1,
                'setor' => trim($formulario['setor']),
                'turma' => NULL,
                'celular' => trim($formulario['celular'])

            ];


            if (in_array("", $formulario)) :

                if (empty($formulario['nome'])) :
                    $dados['nome_erro'] = 'Preencha o campo nome';
                endif;

                if (empty($formulario['email'])) :
                    $dados['email_erro'] = 'Preencha o campo e-mail';
                endif;

                if (empty($formulario['siap'])) :
                    $dados['siap_erro'] = 'Preencha o campo SIAP';
                endif;
                if (empty($formulario['setor'])) :
                    $dados['setor_erro'] = 'Preencha o campo setor';
                endif;

                if (empty($formulario['senha'])) :
                    $dados['senha_erro'] = 'Preencha o campo senha';
                endif;

                if (empty($formulario['confirma_senha'])) :
                    $dados['confirma_senha_erro'] = 'Confirme a Senha';
                endif;

            else :
                if (Checa::checarNome($formulario['nome'])) :
                    $dados['nome_erro'] = 'O nome informado é invalido';

                elseif ($this->userModel->checarEmail($formulario['email'])) :
                    $dados['email_erro'] = 'O e-mail informado já está cadastrado';

                elseif ($this->userModel->checarSiap($formulario['siap'])) :
                    $dados['siap_erro'] = 'O SIAP informado já está cadastrado';

                elseif (strlen($formulario['senha']) < 6) :
                    $dados['senha_erro'] = 'A senha deve ter no minimo 6 caracteres';

                elseif ($formulario['senha'] != $formulario['confirma_senha']) :
                    $dados['confirma_senha_erro'] = 'As senhas são diferentes';
                else :


                    $dados['senha'] = password_hash($formulario['senha'], PASSWORD_DEFAULT);

                    if ($this->userModel->armazenar($dados)) :

                        $dadosNotificacao = [
                            'nome' => $dados['nome'],
                            'email' => $dados['email'],
                            'siap' => $dados['siap'],
                            'setor' => $dados['setor'],
                            'celular' => $dados['celular']
                        ];

                        $notificacaoModel = $this->model('Notificacao');

                        // Cria a notificação interna para todos os coordenadores
                        $notificacaoModel->notificar(
                            'Novo cadastro',
                            "Novo usuário cadastrado: {$dados['nome']}",
                            'Usuário',
                            $notificacaoModel->buscarCoordenadores(),
                            function ($coordenador, $extra) {
                                $corpo = "<p>Um novo usuário foi cadastrado no sistema.</p>
                            <p><strong>Nome:</strong> {$extra['nome']}<br>
                            <strong>E-mail:</strong> {$extra['email']}<br>
                            <strong>SIAPE:</strong> {$extra['siap']}</p>";
                                return EmailTemplate::padrao('Novo cadastro', $coordenador->usua_nome, $corpo);
                            },
                            ['nome' => $dados['nome'], 'email' => $dados['email'], 'siap' => $dados['siap']]
                        );

                        $notificacaoModel->notificar(
                            'Novo cadastro aguardando ativação',
                            "O usuário {$dados['nome']} realizou um novo cadastro e aguarda ativação.",
                            'Usuário',
                            $notificacaoModel->buscarCoordenadores(),

                            function ($coordenador, $extra) {

                                $corpo = "
                                        <p>Um novo usuário realizou um cadastro no sistema e está aguardando ativação.</p>

                                        <p>
                                            <strong>Nome:</strong> {$extra['nome']}<br>
                                            <strong>E-mail:</strong> {$extra['email']}<br>
                                            <strong>SIAP:</strong> {$extra['siap']}<br>
                                            <strong>Setor:</strong> {$extra['setor']}
                                        </p>

                                        <p>
                                            Acesse o sistema para verificar o cadastro e autorizar o acesso do usuário.
                                        </p>
                                    ";

                                return EmailTemplate::padrao(
                                    'Novo cadastro aguardando ativação',
                                    $coordenador->usua_nome,
                                    $corpo
                                );
                            },

                            [
                                'nome' => $dados['nome'],
                                'email' => $dados['email'],
                                'siap' => $dados['siap'],
                                'setor' => $dados['setor']
                            ]
                        );

                        Sessao::mensagem(
                            'user',
                            'Cadastro realizado com sucesso'
                        );

                        URL::redirecionar('users/loginUser');

                    else :

                        die("Erro ao armazenar usuário no banco de dados");

                    endif;

                endif;

            endif;
        else :
            $dados = [
                'nome' => '',
                'email' => '',
                'siap' => '',
                'setor' => '',
                'senha' => '',
                'confirma_senha' => '',
                'nome_erro' => '',
                'email_erro' => '',
                'siap_erro' => '',
                'setor_erro' => '',
                'senha_erro' => '',
                'confirma_senha_erro' => '',

            ];

        endif;
        $this->view('user/cadCoor', $dados);
    } // fim do método cadCoordenador

    public function cadServidor()
    {
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        if (isset($formulario)) :
            $dados = [
                'nome' => trim($formulario['nome']),
                'email' => trim($formulario['email']),
                'siap' => trim($formulario['siap']),
                'matricula' => NULL,
                'senha' => trim($formulario['senha']),
                'confirma_senha' => trim($formulario['confirma_senha']),
                'funcao' => 3,
                'setor' => trim($formulario['setor']),
                'turma' => NULL,
                'celular' => trim($formulario['celular'])

            ];

            if (in_array("", $formulario)) :

                if (empty($formulario['nome'])) :
                    $dados['nome_erro'] = 'Preencha o campo nome';
                endif;

                if (empty($formulario['email'])) :
                    $dados['email_erro'] = 'Preencha o campo e-mail';
                endif;

                if (empty($formulario['siap'])) :
                    $dados['siap_erro'] = 'Preencha o campo SIAP';
                endif;
                if (empty($formulario['setor'])) :
                    $dados['setor_erro'] = 'Preencha o campo setor';
                endif;

                if (empty($formulario['senha'])) :
                    $dados['senha_erro'] = 'Preencha o campo senha';
                endif;

                if (empty($formulario['confirma_senha'])) :
                    $dados['confirma_senha_erro'] = 'Confirme a Senha';
                endif;
                if (empty($formulario['funcao'])) :
                    $dados['funcao_erro'] = 'Preencha o campo função';
                endif;

            else :
                if (Checa::checarNome($formulario['nome'])) :
                    $dados['nome_erro'] = 'O nome informado é invalido';
                elseif ($this->userModel->checarEmail($formulario['email'])) :
                    $dados['email_erro'] = 'O e-mail informado já está cadastrado';

                elseif ($this->userModel->checarSiap($formulario['siap'])) :
                    $dados['siap_erro'] = 'O SIAP informado já está cadastrado';

                elseif (strlen($formulario['senha']) < 6) :
                    $dados['senha_erro'] = 'A senha deve ter no minimo 6 caracteres';

                elseif ($formulario['senha'] != $formulario['confirma_senha']) :
                    $dados['confirma_senha_erro'] = 'As senhas são diferentes';

                else :


                    $dados['senha'] = password_hash($formulario['senha'], PASSWORD_DEFAULT);

                    if ($this->userModel->armazenar($dados)) :

                        $notificacaoModel = $this->model('Notificacao');

                        // Cria a notificação interna para todos os coordenadores
                        $notificacaoModel->notificar(
                            'Novo cadastro',
                            "Novo usuário cadastrado: {$dados['nome']}",
                            'Usuário',
                            $notificacaoModel->buscarCoordenadores(),
                            function ($coordenador, $extra) {
                                $corpo = "<p>Um novo usuário foi cadastrado no sistema.</p>
                            <p><strong>Nome:</strong> {$extra['nome']}<br>
                            <strong>E-mail:</strong> {$extra['email']}<br>
                            <strong>SIAPE:</strong> {$extra['siap']}</p>";
                                return EmailTemplate::padrao('Novo cadastro', $coordenador->usua_nome, $corpo);
                            },
                            ['nome' => $dados['nome'], 'email' => $dados['email'], 'siap' => $dados['siap']]
                        );

                        $notificacaoModel->notificar(
                            'Novo cadastro aguardando ativação',
                            "O usuário {$dados['nome']} realizou um novo cadastro e aguarda ativação.",
                            'Usuário',
                            $notificacaoModel->buscarCoordenadores(),

                            function ($coordenador, $extra) {

                                $corpo = "
                                        <p>Um novo usuário realizou um cadastro no sistema e está aguardando ativação.</p>

                                        <p>
                                            <strong>Nome:</strong> {$extra['nome']}<br>
                                            <strong>E-mail:</strong> {$extra['email']}<br>
                                            <strong>SIAP:</strong> {$extra['siap']}<br>
                                            <strong>Setor:</strong> {$extra['setor']}
                                        </p>

                                        <p>
                                            Acesse o sistema para verificar o cadastro e autorizar o acesso do usuário.
                                        </p>
                                    ";

                                return EmailTemplate::padrao(
                                    'Novo cadastro aguardando ativação',
                                    $coordenador->usua_nome,
                                    $corpo
                                );
                            },

                            [
                                'nome' => $dados['nome'],
                                'email' => $dados['email'],
                                'siap' => $dados['siap'],
                                'setor' => $dados['setor']
                            ]
                        );

                        Sessao::mensagem('usuario', 'Cadastro realizado com sucesso');
                        URL::redirecionar('users/loginUser');

                    else :
                        die("Erro ao armazenar usuario no banco de dados");
                    endif;

                endif;

            endif;
        else :
            $dados = [
                'nome' => '',
                'email' => '',
                'siap' => '',
                'setor' => '',
                'senha' => '',
                'confirma_senha' => '',
                'funcao' => '',
                'nome_erro' => '',
                'email_erro' => '',
                'siap_erro' => '',
                'setor_erro' => '',
                'senha_erro' => '',
                'confirma_senha_erro' => '',
                'funcao_erro' => '',

            ];

        endif;
        $this->view('user/cadServ', $dados);
    } // fim do método cadServidor

    public function cadEstagiario()
    {
        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        if (isset($formulario)) :
            $dados = [
                'nome' => trim($formulario['nome']),
                'email' => trim($formulario['email']),
                'siap' => NULL,
                'setor' => NULL,
                'senha' => trim($formulario['senha']),
                'confirma_senha' => trim($formulario['confirma_senha']),
                'funcao' => 2,
                'curso' => trim($formulario['curso']),
                'ano' => trim($formulario['ano']),
                'celular' => trim($formulario['celular']),
                'matricula' => trim($formulario['matricula']),
                'turma' => null,
            ];

            if ($dados['curso'] == 'Técnico em Informática' && $dados['ano'] == 1) {
                $dados['turma'] = 1;
            } elseif ($dados['curso'] == 'Técnico em Informática' && $dados['ano'] == 2) {
                $dados['turma'] = 2;
            } elseif ($dados['curso'] == 'Técnico em Informática' && $dados['ano'] == 3) {
                $dados['turma'] = 3;
            } elseif ($dados['curso'] == 'Técnico em Biotecnologia' && $dados['ano'] == 1) {
                $dados['turma'] = 4;
            } elseif ($dados['curso'] == 'Técnico em Biotecnologia' && $dados['ano'] == 2) {
                $dados['turma'] = 5;
            } elseif ($dados['curso'] == 'Técnico em Biotecnologia' && $dados['ano'] == 3) {
                $dados['turma'] = 6;
            } else {
                $dados['turma'] = null;
            }

            if (in_array("", $formulario)) :

                if (empty($formulario['nome'])) :
                    $dados['nome_erro'] = 'Preencha o campo nome';
                endif;

                if (empty($formulario['email'])) :
                    $dados['email_erro'] = 'Preencha o campo e-mail';
                endif;

                if (empty($formulario['matricula'])) :
                    $dados['matricula_erro'] = 'Preencha o campo Matrícula';
                endif;
                if (empty($formulario['curso'])) :
                    $dados['curso_erro'] = 'Preencha o campo curso';
                endif;
                if (empty($formulario['ano'])) :
                    $dados['ano_erro'] = 'Preencha o campo ano';
                endif;

                if (empty($formulario['senha'])) :
                    $dados['senha_erro'] = 'Preencha o campo senha';
                endif;

                if (empty($formulario['confirma_senha'])) :
                    $dados['confirma_senha_erro'] = 'Confirme a Senha';
                endif;
                if (empty($formulario['funcao'])) :
                    $dados['funcao_erro'] = 'Preencha o campo função';
                endif;

            else :
                if (Checa::checarNome($formulario['nome'])) :
                    $dados['nome_erro'] = 'O nome informado é invalido';
                elseif (Checa::checarEmail($formulario['email'])) :
                    $dados['email_erro'] = 'O e-mail informado é invalido';

                elseif ($this->userModel->checarEmail($formulario['email'])) :
                    $dados['email_erro'] = 'O e-mail informado já está cadastrado';
                elseif (strlen($formulario['senha']) < 6) :
                    $dados['senha_erro'] = 'A senha deve ter no minimo 6 caracteres';
                elseif ($formulario['senha'] != $formulario['confirma_senha']) :
                    $dados['confirma_senha_erro'] = 'As senhas são diferentes';
                else :
                    $dados['senha'] = password_hash($formulario['senha'], PASSWORD_DEFAULT);

                    if ($this->userModel->armazenar($dados)) :

                        $notificacaoModel = $this->model('Notificacao');

                        // Cria a notificação interna para todos os coordenadores
                        $notificacaoModel->notificar(
                            'Novo cadastro',
                            "Novo usuário cadastrado: {$dados['nome']}",
                            'Usuário',
                            $notificacaoModel->buscarCoordenadores(),
                            function ($coordenador, $extra) {
                                $corpo = "<p>Um novo usuário foi cadastrado no sistema.</p>
                            <p><strong>Nome:</strong> {$extra['nome']}<br>
                            <strong>E-mail:</strong> {$extra['email']}<br>
                            <strong>SIAPE:</strong> {$extra['matricula']}</p>";
                                return EmailTemplate::padrao('Novo cadastro', $coordenador->usua_nome, $corpo);
                            },
                            ['nome' => $dados['nome'], 'email' => $dados['email'], 'matricula' => $dados['matricula']]
                        );
                        $notificacaoModel->notificar(
                            'Novo cadastro aguardando ativação',
                            "O usuário {$dados['nome']} realizou um novo cadastro e aguarda ativação.",
                            'Usuário',
                            $notificacaoModel->buscarCoordenadores(),

                            function ($coordenador, $extra) {

                                $corpo = "
                                        <p>Um novo usuário realizou um cadastro no sistema e está aguardando ativação.</p>

                                        <p>
                                            <strong>Nome:</strong> {$extra['nome']}<br>
                                            <strong>E-mail:</strong> {$extra['email']}<br>
                                            <strong>SIAP:</strong> {$extra['siap']}<br>
                                            <strong>Setor:</strong> {$extra['setor']}
                                        </p>

                                        <p>
                                            Acesse o sistema para verificar o cadastro e autorizar o acesso do usuário.
                                        </p>
                                    ";

                                return EmailTemplate::padrao(
                                    'Novo cadastro aguardando ativação',
                                    $coordenador->usua_nome,
                                    $corpo
                                );
                            },

                            [
                                'nome' => $dados['nome'],
                                'email' => $dados['email'],
                                'siap' => $dados['siap'],
                                'setor' => $dados['setor']
                            ]
                        );

                        Sessao::mensagem('usuario', 'Cadastro realizado com sucesso');
                        URL::redirecionar('users/loginUser');

                    else :
                        die("Erro ao armazenar usuario no banco de dados");
                    endif;

                endif;

            endif;
        else :
            $dados = [
                'nome' => '',
                'email' => '',
                'siap' => '',
                'curso' => '',
                'ano' => '',
                'senha' => '',
                'confirma_senha' => '',
                'funcao' => '',
                'nome_erro' => '',
                'email_erro' => '',
                'siap_erro' => '',
                'curso_erro' => '',
                'ano_erro' => '',
                'senha_erro' => '',
                'confirma_senha_erro' => '',
                'funcao_erro' => '',

            ];

        endif;
        $this->view('user/cadEst', $dados);
    } // fim do método cadEstagiario

    public function esqueciSenha()
    {

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        if (isset($formulario)) :
            $dados = [
                'email' => trim($formulario['email']),
                'email_erro' => ''
            ];

            if (empty($formulario['email'])) :
                $dados['email_erro'] = 'Preencha o campo e-mail';
            elseif (Checa::checarEmail($formulario['email'])) :
                $dados['email_erro'] = 'O e-mail informado é invalido';
            else :
                $usuario = $this->userModel->buscarPorEmail($dados['email']);

                if ($usuario) :
                    $token = bin2hex(random_bytes(32));
                    $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    $this->userModel->salvarTokenSenha($usuario->usua_id, $token, $expira);

                    $link = URL . '/users/redefinirSenha/' . $token;

                    $corpo = "<p>Recebemos uma solicitação para redefinir sua senha no <strong>CIT Almoxarifado</strong>.</p>
                <p>Clique no botão abaixo para criar uma nova senha. Este link expira em <strong>1 hora</strong>.</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$link}' style='background-color: #075248; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                        Redefinir senha
                    </a>
                </p>
                <p style='font-size: 13px; color: #666;'>Se você não solicitou isso, pode ignorar este e-mail com segurança — sua senha continuará a mesma.</p>";

                    Email::enviar(
                        $usuario->usua_email,
                        $usuario->usua_nome,
                        'Redefinição de senha - CIT Almoxarifado',
                        EmailTemplate::padrao('Redefinição de senha', $usuario->usua_nome, $corpo)
                    );
                endif;

                // Mensagem igual, exista ou não o e-mail — evita confirmar pra quem tenta adivinhar e-mails cadastrados
                Sessao::mensagem('usuario', 'Se o e-mail estiver cadastrado, um link de redefinição de senha será enviado.', 'alert alert-success');
                URL::redirecionar('users/loginUser');
            endif;
        else :
            $dados = [
                'email' => '',
                'email_erro' => ''
            ];
        endif;

        $this->view('user/EsqueciSenha', $dados);
    }

    public function redefinirSenha($token = null)
    {
        if (empty($token)) :
            Sessao::mensagem('usuario', 'Link de redefinição inválido.', 'alert alert-danger');
            URL::redirecionar('users/loginUser');
        endif;

        $tokenValido = $this->userModel->buscarTokenValido($token);

        if (!$tokenValido) :
            Sessao::mensagem('usuario', 'Este link expirou ou já foi utilizado. Solicite novamente.', 'alert alert-danger');
            URL::redirecionar('users/esqueciSenha');
        endif;

        $formulario = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $dados = [
            'token' => $token,
            'senha' => '',
            'confirma_senha' => '',
            'senha_erro' => '',
            'confirma_senha_erro' => ''
        ];

        if (isset($formulario)) :
            $dados['senha'] = trim($formulario['senha']);
            $dados['confirma_senha'] = trim($formulario['confirma_senha']);

            if (empty($formulario['senha'])) :
                $dados['senha_erro'] = 'Preencha o campo senha';
            elseif (strlen($formulario['senha']) < 6) :
                $dados['senha_erro'] = 'A senha deve ter no mínimo 6 caracteres';
            elseif ($formulario['senha'] != $formulario['confirma_senha']) :
                $dados['confirma_senha_erro'] = 'As senhas são diferentes';
            else :
                $senhaHash = password_hash($formulario['senha'], PASSWORD_DEFAULT);

                $this->userModel->atualizarSenha($tokenValido->usua_id, $senhaHash);
                $this->userModel->marcarTokenUsado($tokenValido->toke_id);

                Sessao::mensagem('usuario', 'Senha redefinida com sucesso! Faça login com a nova senha.', 'alert alert-success');
                URL::redirecionar('users/loginUser');
            endif;
        endif;

        $this->view('user/redefinirSenha', $dados);
    }

    public function logoutUser()
    {
        unset($_SESSION['usuario_id']);
        unset($_SESSION['usuario_nome']);
        unset($_SESSION['usuario_email']);
        unset($_SESSION['usuario_telefone']);
        unset($_SESSION['usuario_siap']);
        unset($_SESSION['usuario_matricula']);
        unset($_SESSION['usuario_ano']);
        unset($_SESSION['usuario_curso']);
        unset($_SESSION['usuario_funcao']);
        unset($_SESSION['usuario_setor']);
        unset($_SESSION['usuario_foto']);

        session_destroy();
        URL::redirecionar('users/loginUser');
    }
}
