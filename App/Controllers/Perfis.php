<?php

class Perfis extends Controller
{
    private const POR_PAGINA = 10;
    private const FUNCAO_ADMINISTRADOR = 1;
    private const PERFIL_PADRAO = 'servidor';
    private const PASTA_FOTOS = __DIR__ . '/../../public/img/usuarios/';
    private const FOTO_PADRAO = 'avatar-padrao.png';
    private const TAMANHO_MAX_FOTO = 2 * 1024 * 1024; // 2 MB
    private const TIPOS_FOTO = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private $perfilModel;
    private $notificacaoModel;

    public function __construct()
    {
        $this->perfilModel = $this->model('Perfil');
        $this->notificacaoModel = $this->model('Notificacao');
    }

    public function perfil()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . URL . '/login');
            exit;
        }

        $dados = [
            'tituloPagina' => 'Meu Perfil',
            'mensagem'     => $this->pegarMensagem(),
        ];

        $this->view('perfis/perfil', $dados);
    }

    /**
     * GET /users/gerenciarPerfis
     * Filtros aceitos: ?pesquisa=  ?funcao=  ?status=  ?pagina=
     */
    public function gerenciarPerfis()
    {
        $this->exigirAdministrador();

        $filtros = $this->lerFiltros($_GET);

        $totalUsuarios = $this->perfilModel->contarPerfis($filtros);
        $totalPaginas  = max(1, (int) ceil($totalUsuarios / self::POR_PAGINA));
        $paginaAtual   = min($totalPaginas, max(1, (int) ($_GET['pagina'] ?? 1)));
        $offset        = ($paginaAtual - 1) * self::POR_PAGINA;

        $resumo = $this->perfilModel->resumoPerfis();

        $dados = [
            'total'         => (int) $resumo->total,
            'ativos'        => (int) $resumo->ativos,
            'inativos'      => (int) $resumo->inativos,
            'removidos'     => (int) $resumo->removidos,
            'funcoes'       => $this->perfilModel->listarFuncoes(),
            'usuarios'      => $this->perfilModel->listarPerfis($filtros, self::POR_PAGINA, $offset),
            'totalUsuarios' => $totalUsuarios,
            'paginaAtual'   => $paginaAtual,
            'totalPaginas'  => $totalPaginas,
            'csrf'          => $this->tokenCsrf(),
            'mensagem'      => $this->pegarMensagem(),
        ];

        $this->view('perfis/gerenciarPerfil', $dados);
    }

    /**
     * POST /users/alterarStatus/{id}
     * Campos: csrf, novo_status ('Ativo' | 'Inativo'), retorno (filtros da lista)
     */
    public function alterarStatus($id = 0)
    {
        $this->exigirAdministrador();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar('perfis/gerenciarPerfis');
        }

        $filtrosDeRetorno = $this->lerFiltrosDeRetorno($_POST['retorno'] ?? '');

        if (!$this->csrfValido($_POST['csrf'] ?? '')) {
            $this->guardarMensagem('erro', 'Sessão expirada. Tente novamente.');
            $this->redirecionar('perfis/gerenciarPerfis', $filtrosDeRetorno);
        }

        $id         = (int) $id;
        $novoStatus = $_POST['novo_status'] ?? '';

        if (!in_array($novoStatus, ['Ativo', 'Inativo'], true)) {
            $this->guardarMensagem('erro', 'Ação inválida.');
            $this->redirecionar('perfis/gerenciarPerfis', $filtrosDeRetorno);
        }

        // o administrador não pode inativar a própria conta
        if ($novoStatus === 'Inativo' && $id === (int) ($_SESSION['usuario_id'] ?? 0)) {
            $this->guardarMensagem('erro', 'Você não pode inativar o seu próprio perfil.');
            $this->redirecionar('perfis/gerenciarPerfis', $filtrosDeRetorno);
        }

        $usuario = $this->perfilModel->buscarPorId($id);

        if (!$usuario || $usuario->status === 'Removido') {
            $this->guardarMensagem('erro', 'Usuário não encontrado.');
            $this->redirecionar('perfis/gerenciarPerfis', $filtrosDeRetorno);
        }

        if ($this->perfilModel->alterarStatus($id, $novoStatus)) {
            $verbo   = $novoStatus === 'Ativo' ? 'ativado' : 'inativado';
            $adminId = (int) ($_SESSION['usuario_id'] ?? 0);
            $h       = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

            // Coordenadores ativos, sem quem fez a alteração e sem o próprio usuário afetado
            $coordenadores = array_values(array_filter(
                $this->notificacaoModel->buscarUsuariosAtivos([Notificacao::FUNC_COORDENADOR], $adminId ?: null),
                fn($c) => (int) $c->usua_id !== $id
            ));

            // Falha em notificação não pode derrubar uma alteração que já foi salva
            try {
                if ($coordenadores) {
                    $this->notificacaoModel->notificar(
                        'Usuário alterado',
                        "Status do usuário alterado: {$usuario->usua_nome} ({$usuario->usua_email}) agora está {$novoStatus}.",
                        'Usuário',
                        $coordenadores,
                        function ($coordenador, $extra) {
                            $corpo = "
                        <p>Um usuário foi alterado no sistema.</p>
                        <p>
                            <strong>Nome:</strong> {$extra['nome']}<br>
                            <strong>E-mail:</strong> {$extra['email']}<br>
                            <strong>SIAPE:</strong> {$extra['siap']}<br>
                            <strong>Novo status:</strong> {$extra['status']}
                        </p>
                    ";

                            return EmailTemplate::padrao(
                                'Usuário alterado',
                                htmlspecialchars($coordenador->usua_nome, ENT_QUOTES, 'UTF-8'),
                                $corpo
                            );
                        },
                        [
                            'nome'   => $h($usuario->usua_nome),
                            'email'  => $h($usuario->usua_email),
                            'siap'   => $h($usuario->usua_siap),
                            'status' => $h($novoStatus),
                        ]
                    );
                }

                $this->notificacaoModel->notificar(
                    'Seu status foi alterado',
                    "Seu status no sistema foi alterado para {$novoStatus}.",
                    'Usuário',
                    [$usuario],
                    function ($destinatario, $extra) {
                        $nome  = htmlspecialchars($destinatario->usua_nome, ENT_QUOTES, 'UTF-8');
                        $corpo = "
                    <p>Olá, {$nome}!</p>
                    <p>Seu status no sistema foi alterado.</p>
                    <p><strong>Novo status:</strong> {$extra['status']}</p>
                    <p>
                        Caso você tenha dúvidas sobre essa alteração,
                        entre em contato com a coordenação.
                    </p>
                ";

                        return EmailTemplate::padrao('Seu status foi alterado', $nome, $corpo);
                    },
                    ['status' => $h($novoStatus)]
                );
            } catch (Throwable $ex) {
                error_log('[alterarStatus] notificação falhou: ' . $ex->getMessage());
            }

            $this->guardarMensagem('sucesso', "Usuário {$verbo} com sucesso.");
        } else {
            $this->guardarMensagem('erro', 'Não foi possível alterar o status. Tente novamente.');
        }

        $this->redirecionar('perfis/gerenciarPerfis', $filtrosDeRetorno);
    }
 
    /* ================= auxiliares ================= */

    /** Só administrador acessa; os demais voltam para o início. */
    private function exigirAdministrador(): void
    {
        if ((int) ($_SESSION['usuario_funcao'] ?? 0) !== self::FUNCAO_ADMINISTRADOR) {
            $this->redirecionar('pagina/home');
        }
    }

    public function cadastrar(): void
    {
        $this->exigirCoordenador();

        $perfil = $_GET['perfil'] ?? '';

        // Redireciona somente se o perfil NÃO for válido
        if (!Perfil::perfilValido($perfil)) {
            $this->redirecionar(
                '/perfis/cadastrar?perfil=' . self::PERFIL_PADRAO
            );
        }

        $modelo = $this->model('Perfil');
        $erros = [];
        $antigos = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $token = (string) ($_POST['csrf'] ?? '');

            if (!hash_equals($this->tokenCsrf(), $token)) {
                http_response_code(419);
                exit('Sessão expirada. Recarregue a página e tente novamente.');
            }

            $dados = $modelo->normalizar($perfil, $_POST);

            $erros = $modelo->validar($perfil, $dados);

            if (!$erros) {
                try {

                    $idAutor = (int) $_SESSION['usuario_id'];

                    $idNovoUsuario = $modelo->criar(
                        $perfil,
                        $dados,
                        $idAutor
                    );

                    // Cria a notificação somente depois que o usuário foi cadastrado
                    $this->notificarNovoCadastro(
                        $idNovoUsuario,
                        $dados,
                        $perfil,
                        $idAutor
                    );

                    $_SESSION['flash_sucesso'] = 'Cadastro realizado com sucesso.';

                    $this->redirecionar(
                        '/perfis/cadastrar?perfil=' . $perfil
                    );
                } catch (PDOException $ex) {

                    $erros['_geral'] = $ex->getCode() === '23000'
                        ? 'Já existe um usuário com esses dados.'
                        : 'Não foi possível salvar. Tente novamente.';

                    error_log($ex->getMessage());
                }
            }

            // Mantém os dados digitados, mas nunca a senha
            $antigos = $dados;

            unset(
                $antigos['senha'],
                $antigos['confirmar_senha']
            );
        }

        $sucesso = $_SESSION['flash_sucesso'] ?? '';
        unset($_SESSION['flash_sucesso']);

        $this->render('perfis/cadastrar', [

            'tituloPagina' =>
            'Cadastrar ' . Perfil::PERFIS[$perfil]['rotulo'],

            'cssPagina' => ['cit-cadastro'],

            'usuarioLogado' => [
                'nome' => $_SESSION['usuario']['usua_nome'] ?? ''
            ],

            'perfil' => $perfil,

            'tiposDePerfil' =>
            Perfil::tiposDePerfil(),

            'campos' =>
            $modelo->camposDoPerfil($perfil),

            'antigos' => $antigos,

            'erros' => $erros,

            'sucesso' => $sucesso,

            'csrf' => $this->tokenCsrf(),
        ]);
    }

    public function validar(string $perfil, array $dados): array
    {
        $erros = [];

        // =========================
        // VALIDAÇÕES BÁSICAS
        // =========================

        if (empty(trim($dados['nome'] ?? ''))) {
            $erros['nome'] = 'Preencha o nome.';
        }

        if (empty(trim($dados['email'] ?? ''))) {
            $erros['email'] = 'Preencha o e-mail.';
        } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido.';
        }

        if (empty(trim($dados['siap'] ?? ''))) {
            $erros['siap'] = 'Preencha o SIAP.';
        }

        // =========================
        // VERIFICA DUPLICIDADE
        // =========================

        if (
            empty($erros['email'])
            && $this->perfilModel->emailExiste($dados['email'])
        ) {
            $erros['email'] = 'O e-mail informado já está cadastrado.';
        }

        if (
            empty($erros['siap'])
            && $this->perfilModel->siapExiste($dados['siap'])
        ) {
            $erros['siap'] = 'O SIAP informado já está cadastrado.';
        }

        // =========================
        // SENHA
        // =========================

        if (empty($dados['senha'] ?? '')) {
            $erros['senha'] = 'Preencha a senha.';
        } elseif (strlen($dados['senha']) < 6) {
            $erros['senha'] = 'A senha deve ter no mínimo 6 caracteres.';
        }

        if (
            empty($erros['confirmar_senha'])
            && ($dados['senha'] ?? '') !== ($dados['confirmar_senha'] ?? '')
        ) {
            $erros['confirmar_senha'] = 'As senhas são diferentes.';
        }

        return $erros;
    }

    /* ------------------------------------------------------------------ */

    private function exigirCoordenador(): void
    {
        if ((int) ($_SESSION['usuario_funcao'] ?? 0) !== Perfil::FUNC_COORDENADOR) {
            http_response_code(403);
            exit('Acesso restrito a coordenadores.');
        }
    }

    private function render(string $view, array $dados): void
    {
        $e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        extract($dados, EXTR_SKIP);

        require dirname(__DIR__) . '/Views/' . $view . '.php';
    }

    /** Limpa e valida os filtros vindos da URL. */
    private function lerFiltros(array $origem): array
    {
        $status = $origem['status'] ?? '';

        return [
            'pesquisa' => trim((string) ($origem['pesquisa'] ?? '')),
            'funcao'   => (int) ($origem['funcao'] ?? 0),
            'status'   => in_array($status, ['Ativo', 'Inativo'], true) ? $status : '',
        ];
    }

    /**
     * Converte a query string enviada no formulário em um array seguro.
     * Só aceita as chaves conhecidas, evitando redirecionamento para qualquer lugar.
     */
    private function lerFiltrosDeRetorno(string $retorno): array
    {
        parse_str($retorno, $parametros);

        $permitidos = ['pesquisa', 'funcao', 'status', 'pagina'];
        $parametros = array_intersect_key($parametros, array_flip($permitidos));

        return array_filter($parametros, 'is_scalar');
    }

    private function redirecionar(string $rota, array $query = []): void
    {
        $destino = URL . '/' . $rota;

        if ($query) {
            $destino .= '?' . http_build_query($query);
        }

        header('Location: ' . $destino);
        exit;
    }

    /* ---- mensagem de retorno (aparece uma vez e some) ---- */

    private function guardarMensagem(string $tipo, string $texto): void
    {
        $_SESSION['perfis_mensagem'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    private function pegarMensagem(): ?array
    {
        $mensagem = $_SESSION['perfis_mensagem'] ?? null;
        unset($_SESSION['perfis_mensagem']);

        return $mensagem;
    }

    /* ---- proteção CSRF ---- */

    private function tokenCsrf(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    private function csrfValido(string $token): bool
    {
        return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }

    public function editarPerfil()
    {
        $this->exigirLogin();

        $usuario = $this->perfilModel->buscarPerfil(
            (int) $_SESSION['usuario_id'],
            (int) $_SESSION['usuario_funcao']
        );

        if (!$usuario) {
            $this->redirecionar('/login');
        }

        $this->view('perfis/editarPerfil', [
            'usuario' => $usuario,
            'csrf'    => $this->tokenCsrf(),
            'erros'   => $this->lerFlash('perfil_erros', []),
            'sucesso' => $this->lerFlash('perfil_sucesso', ''),
        ]);
    }

    /* ======================================================================
       POST /usuarios/salvarAlteracoes  ->  valida e grava
       ====================================================================== */
    public function salvarAlteracoes()
    {
        $this->exigirLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar('/perfis/editarPerfil');
        }

        if (!$this->csrfValido($_POST['csrf'] ?? '')) {
            $this->erroEVolta(['Sessão expirada. Tente novamente.']);
        }

        $id      = (int) $_SESSION['usuario_id'];
        $funcao  = (int) $_SESSION['usuario_funcao'];
        $atual   = $this->perfilModel->buscarPerfil($id, $funcao);

        if (!$atual) {
            $this->redirecionar('/login');
        }

        // ---- Entrada (só campos editáveis; SIAPE/setor/função etc. são ignorados) ----
        $nome     = trim($_POST['nome'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? ''); // só dígitos

        // ---- Validação ----
        $erros = [];

        if ($nome === '' || mb_strlen($nome) > 100) {
            $erros[] = 'Informe um nome de até 100 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um e-mail válido.';
        } elseif ($this->perfilModel->emailEmUso($email, $id)) {
            $erros[] = 'Este e-mail já está em uso por outro usuário.';
        }
        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            $erros[] = 'Informe o telefone com DDD (10 ou 11 dígitos).';
        }

        // ---- Foto (opcional) ----
        [$novaFoto, $erroFoto] = $this->processarFoto($_FILES['foto'] ?? null, $id);
        if ($erroFoto) {
            $erros[] = $erroFoto;
        }

        if (!empty($erros)) {
            if ($novaFoto) {
                $this->apagarFoto($novaFoto); // não deixa arquivo órfão
            }
            $this->erroEVolta($erros);
        }

        // ---- Gravação ----
        $gravou = $this->perfilModel->atualizarPerfil($id, [
            'nome'     => $nome,
            'email'    => $email,
            'telefone' => $telefone,
            'foto'     => $novaFoto,
        ]);

        if (!$gravou) {
            if ($novaFoto) {
                $this->apagarFoto($novaFoto);
            }
            $this->erroEVolta(['Não foi possível salvar. Tente novamente.']);
        }

        if ($novaFoto) {
            $this->apagarFoto($atual->usua_foto ?? null); // remove a foto antiga
        }

        // Atualiza a sessão para o menu e outras telas refletirem a edição
        $_SESSION['usuario_nome']  = $nome;
        $_SESSION['usuario_email'] = $email;
        if ($novaFoto) {
            $_SESSION['usuario_foto'] = $novaFoto;
        }

        // Se você guarda o usuário inteiro em $_SESSION['usuario'] (array)
        if (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
            $_SESSION['usuario']['usua_nome']  = $nome;
            $_SESSION['usuario']['usua_email'] = $email;
            if ($novaFoto) {
                $_SESSION['usuario']['usua_foto'] = $novaFoto;
            }
        }

        $_SESSION['perfil_sucesso'] = 'Perfil atualizado com sucesso.';
        $this->redirecionar('/perfis/editarPerfil');
    }

    /* ======================================================================
       Métodos auxiliares (privados)
       ====================================================================== */

    /**
     * Valida e move o upload. Retorna [nomeDoArquivo|null, mensagemDeErro|null].
     */
    private function processarFoto(?array $arquivo, int $idUsuario): array
    {
        if ($arquivo === null || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
            return [null, null]; // usuário não escolheu foto: tudo bem
        }
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Não foi possível enviar a foto.'];
        }
        if ($arquivo['size'] > self::TAMANHO_MAX_FOTO) {
            return [null, 'A foto deve ter no máximo 2 MB.'];
        }

        // Confere o tipo real do arquivo (não confia na extensão nem no $_FILES['type'])
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
        if (!isset(self::TIPOS_FOTO[$mime])) {
            return [null, 'A foto deve ser JPG, PNG ou WEBP.'];
        }

        if (!is_dir(self::PASTA_FOTOS) && !mkdir(self::PASTA_FOTOS, 0755, true)) {
            return [null, 'Não foi possível salvar a foto no servidor.'];
        }

        // Nome único e imprevisível: evita sobrescrever e adivinhar URLs
        $nome = 'u' . $idUsuario . '_' . bin2hex(random_bytes(8)) . '.' . self::TIPOS_FOTO[$mime];

        if (!move_uploaded_file($arquivo['tmp_name'], self::PASTA_FOTOS . $nome)) {
            return [null, 'Não foi possível salvar a foto no servidor.'];
        }

        return [$nome, null];
    }

    private function apagarFoto(?string $nomeArquivo): void
    {
        if (empty($nomeArquivo) || $nomeArquivo === self::FOTO_PADRAO) {
            return;
        }

        $caminho = self::PASTA_FOTOS . basename($nomeArquivo); // basename evita path traversal
        if (is_file($caminho)) {
            unlink($caminho);
        }
    }

    private function exigirLogin(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            $this->redirecionar('/login'); // AJUSTE para a rota do seu login
        }
    }

    private function erroEVolta(array $erros): void
    {
        $_SESSION['perfil_erros'] = $erros;
        $this->redirecionar('/usuarios/editarPerfil');
    }

    /** Lê uma mensagem "flash" da sessão e a remove (aparece só uma vez). */
    private function lerFlash(string $chave, $padrao)
    {
        $valor = $_SESSION[$chave] ?? $padrao;
        unset($_SESSION[$chave]);
        return $valor;
    }

    public function alterarSenha()
    {
        $this->exigirLogin();

        $this->view('perfis/alterarSenha', [
            'csrf'    => $this->tokenCsrf(),
            'erros'   => $this->lerFlash('senha_erros', []),
            'sucesso' => $this->lerFlash('senha_sucesso', ''),
        ]);
    }

    /* ======================================================================
       POST /usuarios/salvarSenha  ->  valida e grava a nova senha
       ====================================================================== */
    public function salvarSenha()
    {
        $this->exigirLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar('/perfis/alterarSenha');
        }

        if (!$this->csrfValido($_POST['csrf'] ?? '')) {
            $this->erroEVolta2(['Sessão expirada. Tente novamente.'], 'senha_erros', '/perfis/alterarSenha');
        }

        $id        = (int) $_SESSION['usuario_id'];
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha  = $_POST['nova_senha'] ?? '';
        $confirmar  = $_POST['confirmar_senha'] ?? '';

        // Senhas NÃO recebem trim(): espaços podem fazer parte da senha.
        $erros = [];

        $hashAtual = $this->perfilModel->buscarHashSenha($id);
        if (!$hashAtual || !password_verify($senhaAtual, $hashAtual)) {
            $erros[] = 'A senha atual está incorreta.';
        }
        if (strlen($novaSenha) < 8) {
            $erros[] = 'A nova senha deve ter pelo menos 8 caracteres.';
        }
        if ($novaSenha !== $confirmar) {
            $erros[] = 'A confirmação não é igual à nova senha.';
        }
        if ($novaSenha !== '' && $novaSenha === $senhaAtual) {
            $erros[] = 'A nova senha deve ser diferente da atual.';
        }

        if (!empty($erros)) {
            $this->erroEVolta2($erros, 'senha_erros', '/perfis/alterarSenha');
        }

        $gravou = $this->perfilModel->atualizarSenha($id, password_hash($novaSenha, PASSWORD_DEFAULT));

        if (!$gravou) {
            $this->erroEVolta2(['Não foi possível alterar a senha. Tente novamente.'], 'senha_erros', '/perfis/alterarSenha');
        }

        session_regenerate_id(true); // boa prática após trocar credenciais

        $_SESSION['perfil_sucesso'] = 'Senha alterada com sucesso.';
        $this->redirecionar('/perfis/editarPerfil');
    }

    private function erroEVolta2(
        array $erros,
        string $chave = 'perfil_erros',
        string $rota = '/perfis/editarPerfil'
    ): void {
        $_SESSION[$chave] = $erros;
        $this->redirecionar($rota);
    }

    private function notificarNovoCadastro(
        int $idNovoUsuario,
        array $dados,
        string $perfil,
        int $idAutor
    ): void {
        try {
            $coordenadores = $this->notificacaoModel->buscarUsuariosAtivos(
                [Notificacao::FUNC_COORDENADOR],
                $idAutor
            );

            if (empty($coordenadores)) {
                return;
            }

            $rotuloPerfil = Perfil::PERFIS[$perfil]['rotulo'] ?? $perfil;

            $nome = htmlspecialchars(
                $dados['nome'] ?? '',
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $email = htmlspecialchars(
                $dados['email'] ?? '',
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $siape = htmlspecialchars(
                (string) ($dados['siape'] ?? ''),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $perfilSeguro = htmlspecialchars(
                $rotuloPerfil,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $this->notificacaoModel->notificar(
                'Novo usuário cadastrado',

                "Um novo usuário foi cadastrado no sistema: {$dados['nome']} ({$rotuloPerfil}).",

                'Usuário',

                $coordenadores,

                function ($coordenador, $extra) {

                    $nomeCoordenador = htmlspecialchars(
                        $coordenador->usua_nome,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    );

                    $corpo = "
                    <p>Olá, {$nomeCoordenador}!</p>

                    <p>
                        Um novo usuário foi cadastrado no sistema.
                    </p>

                    <p>
                        <strong>Nome:</strong> {$extra['nome']}<br>
                        <strong>E-mail:</strong> {$extra['email']}<br>
                        <strong>SIAPE:</strong> {$extra['siape']}<br>
                        <strong>Perfil:</strong> {$extra['perfil']}
                    </p>

                    <p>
                        O cadastro já está disponível para consulta no sistema.
                    </p>
                ";

                    return EmailTemplate::padrao(
                        'Novo usuário cadastrado',
                        $nomeCoordenador,
                        $corpo
                    );
                },

                [
                    'nome'   => $nome,
                    'email'  => $email,
                    'siape'  => $siape,
                    'perfil' => $perfilSeguro,
                ]
            );
        } catch (Throwable $ex) {
            error_log(
                '[notificarNovoCadastro] Falha: ' . $ex->getMessage()
            );
        }
    }
}
