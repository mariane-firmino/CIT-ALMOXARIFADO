<?php

class Solicitacoes extends Controller
{
    private const PRODUTOS_POR_PAGINA = 12;
    private const POR_PAGINA = 10;

    private const STATUS_APROVADA     = 'Aprovada';
    private const STATUS_EM_DEVOLUCAO = 'Em devolução';

    private const STATUS_PENDENTE = 'Pendente';
    private const ACOES_PERMITIDAS = ['Aprovada', 'Negada'];
    private const SOLICITACOES_POR_PAGINA = 10;

    // Status disponíveis em "Minhas solicitações"
    private const STATUS_USUARIO = ['Pendente', 'Aprovada', 'Negada', 'Em devolução', 'Devolvido', 'Cancelada'];

    // valor gravado no banco => rótulo da aba (a ordem é a das abas)
    private const OPCOES_STATUS = [
        'Pendente'     => 'Pendentes',
        'Aprovada'     => 'Aprovadas',
        'Negada'       => 'Negadas',
        'Em devolução' => 'Em devolução',
        'Devolvido'    => 'Devolvidas', // novo: senão a solicitação some das abas depois de confirmada
    ];
    private const ROTA_LISTA    = '/solicitacoes/analisarSolicitacao';
    private const ROTA_PRODUTOS = '/solicitacoes/consultarProduto';
    private const ROTA_MINHAS   = '/solicitacoes/solicitacaoServidor';
    private const ROTA_LOGIN    = '/login'; // ajuste para a sua rota de login

    private $produtoModel;
    private $categoriaModel;
    private $solicitacaoModel;

    public function __construct()
    {
        $this->produtoModel   = $this->model('Produto');
        $this->categoriaModel = $this->model('Categoria');
        $this->solicitacaoModel = $this->model('Solicitacao');
    }

    /**
     * GET /produtos/consultarProduto
     * Aceita: pesquisa, categoria, status, pagina
     */
    public function consultarProduto(): void
    {
        $filtros = $this->lerFiltrosConsulta();
        $limite  = self::PRODUTOS_POR_PAGINA;

        $total        = $this->produtoModel->contar($filtros);
        $totalPaginas = max(1, (int) ceil($total / $limite));
        $paginaAtual  = min($totalPaginas, max(1, (int) ($_GET['pagina'] ?? 1)));
        $offset       = ($paginaAtual - 1) * $limite;

        $dados = [
            'produtos'     => $this->produtoModel->listar($filtros, $limite, $offset),
            'categorias'   => $this->categoriaModel->listar(),
            'opcoesStatus' => self::OPCOES_STATUS,
            'filtros'      => $filtros,
            'paginacao'    => [
                'total'        => $total,
                'paginaAtual'  => $paginaAtual,
                'totalPaginas' => $totalPaginas,
                'inicio'       => $total > 0 ? $offset + 1 : 0,
                'fim'          => min($offset + $limite, $total),
            ],
            'sucesso'      => $this->pegarMensagem('sucesso'),
            'erro'         => $this->pegarMensagem('erro'),
        ];

        $this->view('solicitacoes/consultarProduto', $dados);
    }

    public function nova(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar(self::ROTA_PRODUTOS);
        }

        $produtos = $this->carregarProdutosSolicitaveis();

        if (empty($produtos)) {
            $_SESSION['erro'] = 'Selecione pelo menos um produto disponível.';
            $this->redirecionar(self::ROTA_PRODUTOS);
        }

        $this->view('solicitacoes/realizarSolicitacao', [
            'produtos'    => $produtos,
            'quantidades' => [],
            'erro'        => null,
        ]);
    }

    /**
     * Traz os produtos marcados na tela, mas só os que ainda têm alguma
     * unidade DISPONÍVEL para reserva agora (descontando o que já está
     * reservado em solicitações Pendentes de outras pessoas).
     *
     * Isto é só a validação "de tela" — mostra o limite certo ao usuário
     * antes de ele enviar o formulário. A validação que realmente decide
     * (com o banco travado) acontece de novo em Solicitacao::criar().
     */
    private function carregarProdutosSolicitaveis(): array
    {
        // Só inteiros positivos, sem repetição.
        $ids = array_values(array_unique(array_filter(
            array_map('intval', (array) ($_POST['produtos'] ?? [])),
            static fn(int $id) => $id > 0
        )));

        if (empty($ids)) {
            return [];
        }

        return array_values(array_filter(
            $this->produtoModel->buscarPorIds($ids),
            static fn($produto) => (int) $produto->disponivel > 0
        ));
    }

    public function cancelar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar(self::ROTA_MINHAS);
        }

        $usuarioId   = $this->exigirUsuarioLogado();
        $id          = (int) ($_POST['soli_id'] ?? 0);
        $solicitacao = $id > 0 ? $this->solicitacaoModel->buscarPorId($id) : null;

        if (!$solicitacao || (int) $solicitacao->usua_id_solicitante !== $usuarioId) {
            $_SESSION['erro'] = 'Solicitação não encontrada.';
            $this->redirecionar(self::ROTA_MINHAS);
        }

        if ($solicitacao->soli_status !== self::STATUS_PENDENTE) {
            $_SESSION['erro'] = 'Só é possível cancelar solicitações pendentes.';
            $this->redirecionar(self::ROTA_MINHAS);
        }

        // Cancelar uma solicitação Pendente também "devolve" a reserva:
        // Solicitacao::criar() só soma como reservado o que estiver com
        // soli_status = 'Pendente', então basta o status deixar de ser esse.
        if ($this->solicitacaoModel->cancelar($id, $usuarioId)) {
            $_SESSION['sucesso'] = 'Solicitação #' . $id . ' cancelada. As unidades reservadas foram liberadas.';
        } else {
            $_SESSION['erro'] = 'Não foi possível cancelar a solicitação. Tente novamente.';
        }

        $this->redirecionar(self::ROTA_MINHAS);
    }

    public function realizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar(self::ROTA_PRODUTOS);
        }

        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

        if ($usuarioId <= 0) {
            $this->redirecionar(self::ROTA_LOGIN);
        }

        $produtos = $this->carregarProdutosSolicitaveis();

        if (empty($produtos)) {
            $_SESSION['erro'] = 'Nenhum dos produtos selecionados está disponível (tudo já reservado em outras solicitações pendentes).';
            $this->redirecionar(self::ROTA_PRODUTOS);
        }

        // Valida as quantidades contra o DISPONÍVEL (não contra o estoque total)
        $enviadas    = (array) ($_POST['quantidade'] ?? []);
        $quantidades = [];
        $itens       = [];
        $erro        = null;

        foreach ($produtos as $produto) {

            $id = (int) $produto->prod_id;

            $quantidade = (int) ($enviadas[$id] ?? 0);

            $quantidades[$id] = $quantidade;
            $itens[$id] = $quantidade;

            if (
                $erro === null &&
                ($quantidade < 1 || $quantidade > (int) $produto->disponivel)
            ) {
                $erro = 'A quantidade de "' . $produto->prod_nome .
                    '" deve ficar entre 1 e ' .
                    (int) $produto->disponivel . ' (unidades já reservadas por outras solicitações não contam).';
            }
        }

        // Cria a solicitação. A checagem definitiva (com o banco travado) acontece
        // dentro do Model; mesmo que a tela acima tenha deixado passar, o Model
        // recusa se, entre a exibição da tela e o envio, alguém reservou primeiro.
        $resultado = $erro === null
            ? $this->solicitacaoModel->criar($usuarioId, $itens)
            : ['ok' => false, 'id' => null, 'erro' => null];

        if ($erro !== null || !$resultado['ok']) {

            $this->view('solicitacoes/realizarSolicitacao', [
                'produtos'    => $produtos,
                'quantidades' => $quantidades,
                'erro'        => $erro ?? ($resultado['erro'] ?? 'Não foi possível enviar a solicitação. Tente novamente.'),
            ]);

            return;
        }

        $codigo = $resultado['id'];


        /*
     * ==========================================================
     * NOTIFICAÇÃO PARA OS COORDENADORES
     * ==========================================================
     */

        try {

            $notificacaoModel = $this->model('Notificacao');

            // Nome do usuário logado.
            // Caso o nome não esteja salvo na sessão, usa o ID como fallback.
            $nomeUsuario = $_SESSION['usuario_nome']
                ?? 'Usuário #' . $usuarioId;


            // Monta a lista de produtos solicitados
            $listaProdutos = '';

            foreach ($produtos as $produto) {

                $produtoId = (int) $produto->prod_id;

                $quantidade = (int) ($quantidades[$produtoId] ?? 0);

                if ($quantidade > 0) {
                    $listaProdutos .=
                        '<li>' .
                        htmlspecialchars($produto->prod_nome) .
                        ' — Quantidade: ' . $quantidade .
                        '</li>';
                }
            }


            // Cria a notificação para todos os coordenadores
            $notificacaoModel->notificar(

                'Nova solicitação',

                "Nova solicitação #{$codigo} realizada por {$nomeUsuario}.",

                'Solicitação',

                $notificacaoModel->buscarCoordenadores(),

                function ($coordenador, $extra) {

                    $corpo = "
                    <p>Uma nova solicitação foi realizada no sistema.</p>

                    <p>
                        <strong>Número da solicitação:</strong>
                        #{$extra['codigo']}
                    </p>

                    <p>
                        <strong>Solicitante:</strong>
                        {$extra['usuario']}
                    </p>

                    <p>
                        <strong>Itens solicitados:</strong>
                    </p>

                    <ul>
                        {$extra['produtos']}
                    </ul>

                    <p>
                        Acesse o sistema para analisar a solicitação.
                    </p>
                ";

                    return EmailTemplate::padrao(
                        'Nova solicitação',
                        $coordenador->usua_nome,
                        $corpo
                    );
                },

                [
                    'codigo'   => $codigo,
                    'usuario'  => $nomeUsuario,
                    'produtos' => $listaProdutos
                ]
            );
        } catch (Throwable $erroNotificacao) {

            // A solicitação já foi criada.
            // Se a notificação falhar, não impede o usuário de concluir a solicitação.
            error_log(
                'Erro ao criar notificação da solicitação #' .
                    $codigo . ': ' .
                    $erroNotificacao->getMessage()
            );
        }


        // Mensagem para o usuário
        $_SESSION['sucesso'] =
            'Solicitação #' . $codigo .
            ' enviada! As unidades pedidas ficam reservadas até a análise do coordenador.';

        $this->redirecionar(self::ROTA_PRODUTOS);
    }

    public function processarSolicitacao(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar(self::ROTA_LISTA);
        }

        $id         = (int) ($_POST['soli_id'] ?? 0);
        $acao       = (string) ($_POST['acao'] ?? '');
        $retirada   = trim((string) ($_POST['data_retirada'] ?? ''));
        $devolucao  = trim((string) ($_POST['data_devolucao'] ?? ''));
        $observacao = mb_substr(trim((string) ($_POST['observacao'] ?? '')), 0, 500);

        $solicitacao = $id > 0 ? $this->solicitacaoModel->buscarPorId($id) : null;

        if (!$solicitacao) {
            $_SESSION['erro'] = 'Solicitação não encontrada.';
            $this->redirecionar(self::ROTA_LISTA);
        }

        $voltar = '/solicitacoes/verSolicitacao/' . $id;

        if ($solicitacao->soli_status !== self::STATUS_PENDENTE) {
            $_SESSION['erro'] = 'Esta solicitação já foi analisada.';
            $this->redirecionar($voltar);
        }

        if (!in_array($acao, self::ACOES_PERMITIDAS, true)) {
            $_SESSION['erro'] = 'Ação inválida.';
            $this->redirecionar($voltar);
        }

        if ($acao === 'Negada') {
            if ($observacao === '') {
                $_SESSION['erro'] = 'Informe o motivo da negação na observação.';
                $this->redirecionar($voltar);
            }

            // Negada não tem datas de retirada/devolução.
            $retirada  = null;
            $devolucao = null;
        } else {
            $erro = $this->validarDatas($retirada, $devolucao);

            if ($erro !== null) {
                $_SESSION['erro'] = $erro;
                $this->redirecionar($voltar);
            }
        }

        // Ao sair de 'Pendente' (para 'Aprovada' ou 'Negada'), a reserva desta
        // solicitação deixa de contar automaticamente: Produto::RESERVADO_SQL só
        // soma itens de solicitações com soli_status = 'Pendente'. Se for
        // aprovada, é o gatilho do banco que desconta produto.prod_quantidade
        // de verdade (e barra a aprovação se o estoque real não for suficiente).
        $salvou = $this->solicitacaoModel->registrarAnalise(
            $id,
            $acao,
            $retirada,
            $devolucao,
            $observacao,
            (int) ($_SESSION['usuario_id'] ?? 0)
        );

        if ($salvou) {
            /*
         * ============================================================
         * NOTIFICAÇÃO PARA O USUÁRIO QUE FEZ A SOLICITAÇÃO
         * ============================================================
         */

            $notificacaoModel = $this->model('Notificacao');

            $titulo = $acao === 'Aprovada'
                ? 'Solicitação aprovada'
                : 'Solicitação negada';

            $mensagem = $acao === 'Aprovada'
                ? "Sua solicitação #{$id} foi aprovada."
                : "Sua solicitação #{$id} foi negada.";

            $notificacaoModel->notificar(
                $titulo,
                $mensagem,
                $acao === 'Aprovada' ? 'Aprovação' : 'Negado',
                [$solicitacao->usua_id_solicitante],
                function ($usuario, $extra) {

                    if ($extra['status'] === 'Aprovada') {

                        $corpo = "
                        <p>Sua solicitação foi <strong>aprovada</strong>.</p>

                        <p>
                            <strong>Solicitação:</strong> #{$extra['id']}<br>
                            <strong>Status:</strong> {$extra['status']}
                        </p>
                    ";
                    } else {

                        $corpo = "
                        <p>Sua solicitação foi <strong>negada</strong>.</p>

                        <p>
                            <strong>Solicitação:</strong> #{$extra['id']}<br>
                            <strong>Status:</strong> {$extra['status']}
                        </p>

                        <p>
                            <strong>Observação:</strong><br>
                            {$extra['observacao']}
                        </p>
                    ";
                    }

                    return EmailTemplate::padrao(
                        $extra['titulo'],
                        $usuario->usua_nome,
                        $corpo
                    );
                },
                [
                    'id'         => $id,
                    'status'     => $acao,
                    'observacao' => $observacao,
                    'titulo'     => $titulo
                ]
            );
            $_SESSION['sucesso'] = 'Solicitação #' . $id . ' ' . mb_strtolower($acao) . ' com sucesso.';
            $this->redirecionar(self::ROTA_LISTA);
        }

        $_SESSION['erro'] = 'Não foi possível salvar a análise. Tente novamente.';
        $this->redirecionar($voltar);
    }

    /**
     * Devolve o id do usuário logado ou manda para o login.
     */
    private function exigirUsuarioLogado(): int
    {
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

        if ($usuarioId <= 0) {
            $this->redirecionar(self::ROTA_LOGIN);
        }

        return $usuarioId;
    }

    public function solicitacaoServidor(): void
    {
        $usuarioId = $this->exigirUsuarioLogado();

        $filtros = $this->lerFiltros(self::STATUS_USUARIO);
        $limite  = self::SOLICITACOES_POR_PAGINA;

        // O id do usuário vai só para o Model (não aparece nos links da tela).
        $filtrosModel = $filtros + ['usuario' => $usuarioId];

        $total        = $this->solicitacaoModel->contar($filtrosModel);
        $totalPaginas = max(1, (int) ceil($total / $limite));
        $paginaAtual  = min($totalPaginas, max(1, (int) ($_GET['pagina'] ?? 1)));
        $offset       = ($paginaAtual - 1) * $limite;

        $contagem = $this->solicitacaoModel->contarPorStatus($usuarioId);
        $resumo   = [];

        foreach (self::STATUS_USUARIO as $status) {
            $resumo[$status] = $contagem[$status] ?? 0;
        }

        $this->view('solicitacoes/minhasSolicitacoes', [
            'solicitacoes' => $this->solicitacaoModel->listar($filtrosModel, $limite, $offset),
            'resumo'       => $resumo,
            'opcoesStatus' => self::STATUS_USUARIO,
            'filtros'      => $filtros,
            'paginacao'    => [
                'total'        => $total,
                'paginaAtual'  => $paginaAtual,
                'totalPaginas' => $totalPaginas,
                'inicio'       => $total > 0 ? $offset + 1 : 0,
                'fim'          => min($offset + $limite, $total),
            ],
            'sucesso'      => $this->pegarMensagem('sucesso'),
            'erro'         => $this->pegarMensagem('erro'),
        ]);
    }

    public function verSolicitacao($id = 0): void
    {
        $id          = (int) $id;
        $solicitacao = $id > 0 ? $this->solicitacaoModel->buscarPorId($id) : null;

        if (!$solicitacao) {
            $_SESSION['erro'] = 'Solicitação não encontrada.';
            $this->redirecionar(self::ROTA_LISTA);
        }

        $this->view('solicitacoes/verSolicitacao', [
            'solicitacao'  => $solicitacao,
            'podeConfirmarDevolucao' => $solicitacao->soli_status === self::STATUS_EM_DEVOLUCAO,
            'itens'        => $this->solicitacaoModel->buscarItens($id),
            'podeAnalisar' => $solicitacao->soli_status === self::STATUS_PENDENTE,
            'erro'         => $this->pegarMensagem('erro'),
        ]);
    }

    /**
     * POST /solicitacoes/realizarSolicitacao
     * Recebe produtos[] com os IDs marcados na tela.
     */
    public function realizarSolicitacao(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar('/solicitacoes/consultarProduto');
        }

        // Nunca confie no que veio do navegador: só inteiros positivos, sem repetição.
        $ids = array_values(array_unique(array_filter(
            array_map('intval', (array) ($_POST['produtos'] ?? [])),
            static fn(int $id) => $id > 0
        )));

        if (empty($ids)) {
            $_SESSION['erro'] = 'Selecione pelo menos um produto.';
            $this->redirecionar('/solicitacoes/consultarProduto');
        }

        // Confere no banco: o produto existe e ainda tem unidade disponível
        // (não reservada por outra solicitação pendente)?
        $produtos = $this->produtoModel->buscarPorIds($ids);
        $validos  = array_filter($produtos, static fn($p) => (int) $p->disponivel > 0);

        if (empty($validos)) {
            $_SESSION['erro'] = 'Os produtos selecionados não estão disponíveis no momento.';
            $this->redirecionar('/solicitacoes/consultarProduto');
        }

        $_SESSION['sucesso'] = 'Solicitação registrada com sucesso.';
        $this->redirecionar('/solicitacoes/consultarProduto');
    }

    private function lerFiltrosConsulta(): array
    {
        $pesquisa  = mb_substr(trim((string) ($_GET['pesquisa'] ?? '')), 0, 100);
        $categoria = filter_var($_GET['categoria'] ?? '', FILTER_VALIDATE_INT);
        $status    = (string) ($_GET['status'] ?? '');

        return [
            'pesquisa'  => $pesquisa,
            'categoria' => ($categoria !== false && $categoria > 0) ? $categoria : '',
            'status'    => array_key_exists($status, self::OPCOES_STATUS) ? $status : '',
        ];
    }

    /**
     * Lê e valida os filtros da URL. Valores inválidos viram '' (sem filtro).
     */

    private function lerFiltros(?array $statusPermitidos = null): array
    {
        $statusPermitidos ??= array_keys(self::OPCOES_STATUS);

        $pesquisa = mb_substr(trim((string) ($_GET['pesquisa'] ?? '')), 0, 100);
        $status   = (string) ($_GET['status'] ?? '');
        $data     = trim((string) ($_GET['data'] ?? ''));

        return [
            'pesquisa' => $pesquisa,
            'status'   => in_array($status, $statusPermitidos, true) ? $status : '',
            'data'     => $this->dataValida($data) ? $data : '',
        ];
    }

    private function redirecionar(string $caminho): void
    {
        header('Location: ' . URL . $caminho);
        exit;
    }

    public function analisarSolicitacao(): void
    {
        $filtros = $this->lerFiltros();
        $limite  = self::SOLICITACOES_POR_PAGINA;

        $total        = $this->solicitacaoModel->contar($filtros);
        $totalPaginas = max(1, (int) ceil($total / $limite));
        $paginaAtual  = min($totalPaginas, max(1, (int) ($_GET['pagina'] ?? 1)));
        $offset       = ($paginaAtual - 1) * $limite;

        // Os cartões de resumo ignoram os filtros: mostram o panorama geral.
        $contagem = $this->solicitacaoModel->contarPorStatus();
        $resumo   = ['total' => array_sum($contagem)];

        foreach (array_keys(self::OPCOES_STATUS) as $status) {
            $resumo[$status] = $contagem[$status] ?? 0;
        }

        $this->view('solicitacoes/analisarSolicitacao', [
            'solicitacoes' => $this->solicitacaoModel->listar($filtros, $limite, $offset),
            'resumo'       => $resumo,
            'opcoesStatus' => self::OPCOES_STATUS,
            'filtros'      => $filtros,
            'paginacao'    => [
                'total'        => $total,
                'paginaAtual'  => $paginaAtual,
                'totalPaginas' => $totalPaginas,
                'inicio'       => $total > 0 ? $offset + 1 : 0,
                'fim'          => min($offset + $limite, $total),
            ],
            'sucesso'      => $this->pegarMensagem('sucesso'),
            'erro'         => $this->pegarMensagem('erro'),
        ]);
    }

    /* ==========================================================
       Ações (somente POST + CSRF)
       ========================================================== */
    public function aprovar($id = null)
    {
        $this->decidir($id, 'Aprovada');
    }

    public function negar($id = null)
    {
        $this->decidir($id, 'Negada');
    }

    private function decidir($id, string $novoStatus): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::valido()) {
            $this->avisar('erro', 'Requisição inválida.');
            $this->voltarParaLista();
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        $solicitacao = $id ? $this->solicitacaoModel->buscarPorId($id) : null;

        if (!$solicitacao) {
            $this->avisar('erro', 'Solicitação não encontrada.');
            $this->voltarParaLista();
        }

        if ($solicitacao->soli_status !== 'Pendente') {
            $this->avisar('erro', 'Só é possível analisar solicitações pendentes.');
            $this->voltarParaLista();
        }

        $resultado = $this->solicitacaoModel->definirStatus($id, $novoStatus, $this->idUsuarioLogado());

        if ($resultado['ok']) {
            $texto = $novoStatus === 'Aprovada'
                ? "Solicitação #{$id} aprovada. O estoque foi atualizado."
                : "Solicitação #{$id} negada. As unidades reservadas foram liberadas.";
            $this->avisar('sucesso', $texto);
        } else {
            $this->avisar('erro', $resultado['erro'] ?? 'Não foi possível concluir a ação.');
        }

        $this->voltarParaLista();
    }

    /* ==========================================================
    Auxiliares
       ========================================================== */

    /**
     * Guarda a mensagem para a próxima carga da tela.
     */
    private function avisar(string $tipo, string $texto): void
    {
        $_SESSION['solicitacao'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    /**
     * Volta para a lista mantendo filtros e página de onde o usuário clicou.
     * O formulário manda esses valores no campo oculto "retorno".
     */
    private function voltarParaLista(): void
    {
        $retorno = $_POST['retorno'] ?? '';
        parse_str(is_string($retorno) ? $retorno : '', $entrada);
        $query = $this->lerFiltros($entrada);

        $pagina = (int) ($entrada['pagina'] ?? 1);
        if ($pagina > 1) {
            $query['pagina'] = $pagina;
        }

        $query = array_filter($query, fn($valor) => $valor !== '' && $valor !== null);
        $url   = URL . '/solicitacoes/analisarSolicitacao' . ($query ? '?' . http_build_query($query) : '');

        header('Location: ' . $url);
        exit;
    }

    /**
     * ID do coordenador logado, gravado em usua_id_coord.
     * AJUSTE a chave da sessão para a que o seu login realmente usa.
     */
    private function idUsuarioLogado(): ?int
    {
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    }

    /**
     * Retorna a mensagem de erro ou null se as datas forem válidas.
     */
    private function validarDatas(string $retirada, string $devolucao): ?string
    {
        if (!$this->dataValida($retirada) || !$this->dataValida($devolucao)) {
            return 'Informe as datas de retirada e devolução.';
        }

        if ($retirada < date('Y-m-d')) {
            return 'A data de retirada não pode estar no passado.';
        }

        if ($devolucao < $retirada) {
            return 'A devolução não pode ser anterior à retirada.';
        }

        return null;
    }

    private function dataValida(string $data): bool
    {
        $objeto = DateTime::createFromFormat('Y-m-d', $data);

        return $objeto !== false && $objeto->format('Y-m-d') === $data;
    }

    /**
     * Lê uma mensagem "flash" da sessão e a remove (aparece uma única vez).
     */
    private function pegarMensagem(string $chave): ?string
    {
        $mensagem = $_SESSION[$chave] ?? null;
        unset($_SESSION[$chave]);

        return $mensagem;
    }

    private function buscarDoUsuario(int $id, int $usuarioId)
    {
        $solicitacao = $id > 0 ? $this->solicitacaoModel->buscarPorId($id) : null;

        return ($solicitacao && (int) $solicitacao->usua_id_solicitante === $usuarioId)
            ? $solicitacao
            : null;
    }

    /** GET /solicitacoes/detalharSolicitacao/{id}: página "Ver" do servidor */
    public function detalharSolicitacao($id = 0): void
    {
        $usuarioId   = $this->exigirUsuarioLogado();
        $solicitacao = $this->buscarDoUsuario((int) $id, $usuarioId);

        if (!$solicitacao) {
            $_SESSION['erro'] = 'Solicitação não encontrada.';
            $this->redirecionar(self::ROTA_MINHAS);
        }

        $this->view('solicitacoes/detalharSolicitacao', [
            'solicitacao'  => $solicitacao,
            'itens'        => $this->solicitacaoModel->buscarItens((int) $solicitacao->soli_id),
            'podeDevolver' => $solicitacao->soli_status === self::STATUS_APROVADA,
            'sucesso'      => $this->pegarMensagem('sucesso'),
            'erro'         => $this->pegarMensagem('erro'),
        ]);
    }

    /** POST /solicitacoes/devolver: servidor informa a devolução */
    public function devolver(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar(self::ROTA_MINHAS);
        }

        $usuarioId   = $this->exigirUsuarioLogado();
        $id          = (int) ($_POST['soli_id'] ?? 0);
        $solicitacao = $this->buscarDoUsuario($id, $usuarioId);

        if (!$solicitacao) {
            $_SESSION['erro'] = 'Solicitação não encontrada.';
            $this->redirecionar(self::ROTA_MINHAS);
        }

        $voltar = '/solicitacoes/detalharSolicitacao/' . $id;

        if ($solicitacao->soli_status !== self::STATUS_APROVADA) {
            $_SESSION['erro'] = 'Só é possível informar a devolução de solicitações aprovadas.';
            $this->redirecionar($voltar);
        }

        if (!$this->solicitacaoModel->marcarDevolvido($id, $usuarioId)) {
            $_SESSION['erro'] = 'Não foi possível registrar a devolução. Tente novamente.';
            $this->redirecionar($voltar);
        }

        // Notifica os coordenadores (mesmo padrão do realizar())
        try {
            $notificacaoModel = $this->model('Notificacao');
            $nomeUsuario      = $_SESSION['usuario_nome'] ?? 'Usuário #' . $usuarioId;

            $notificacaoModel->notificar(
                'Devolução informada',
                "{$nomeUsuario} informou a devolução da solicitação #{$id}.",
                'Solicitação',
                $notificacaoModel->buscarCoordenadores(),
                function ($coordenador, $extra) {
                    $corpo = "
                    <p><strong>{$extra['usuario']}</strong> informou a devolução dos itens
                    da solicitação <strong>#{$extra['id']}</strong>.</p>
                    <p>Acesse o sistema para confirmar o recebimento.</p>";

                    return EmailTemplate::padrao('Devolução informada', $coordenador->usua_nome, $corpo);
                },
                ['id' => $id, 'usuario' => htmlspecialchars($nomeUsuario)]
            );
        } catch (Throwable $erroNotificacao) {
            error_log('Erro ao notificar devolução #' . $id . ': ' . $erroNotificacao->getMessage());
        }

        $_SESSION['sucesso'] = 'Devolução informada! Aguarde a confirmação do coordenador.';
        $this->redirecionar($voltar);
    }

    /** POST /solicitacoes/confirmarDevolucao: coordenador confirma */
    public function confirmarDevolucao(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar(self::ROTA_LISTA);
        }

        // TODO: exigirCoordenador()

        $id          = (int) ($_POST['soli_id'] ?? 0);
        $solicitacao = $id > 0 ? $this->solicitacaoModel->buscarPorId($id) : null;

        if (!$solicitacao) {
            $_SESSION['erro'] = 'Solicitação não encontrada.';
            $this->redirecionar(self::ROTA_LISTA);
        }

        $voltar = '/solicitacoes/verSolicitacao/' . $id;

        if ($solicitacao->soli_status !== self::STATUS_EM_DEVOLUCAO) {
            $_SESSION['erro'] = 'Esta solicitação não está aguardando confirmação de devolução.';
            $this->redirecionar($voltar);
        }

        if (!$this->solicitacaoModel->confirmarDevolucao($id)) {
            $_SESSION['erro'] = 'Não foi possível confirmar a devolução. Tente novamente.';
            $this->redirecionar($voltar);
        }

        try {
            $notificacaoModel = $this->model('Notificacao');

            $notificacaoModel->notificar(
                'Devolução confirmada',
                "A devolução da sua solicitação #{$id} foi confirmada.",
                'Aprovação',
                [$solicitacao->usua_id_solicitante],
                function ($usuario, $extra) {
                    $corpo = "<p>A devolução dos itens da solicitação <strong>#{$extra['id']}</strong>
                        foi confirmada pelo coordenador.</p>";

                    return EmailTemplate::padrao('Devolução confirmada', $usuario->usua_nome, $corpo);
                },
                ['id' => $id]
            );
        } catch (Throwable $erroNotificacao) {
            error_log('Erro ao notificar confirmação #' . $id . ': ' . $erroNotificacao->getMessage());
        }

        $_SESSION['sucesso'] = 'Devolução da solicitação #' . $id . ' confirmada.';
        $this->redirecionar(self::ROTA_LISTA);
    }
}
