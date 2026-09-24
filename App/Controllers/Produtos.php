<?php

/**
 * Controller: Produtos
 * Rotas usadas por esta tela:
 *   GET  /produtos/controlarProduto   -> lista com busca, filtros e paginação
 *   POST /produtos/excluirProduto/ID  -> exclui (exige token CSRF)
 *
 * Premissa: a classe base Controller oferece $this->model('Nome') e
 * $this->view('caminho', $dados), e a sessão já foi iniciada (session_start).
 */

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Dompdf\Dompdf;
use Dompdf\Options;

class Produtos extends Controller
{
    private const POR_PAGINA = 12;
    private const POR_PAGINA_CARDS   = 12;
    private const POR_PAGINA_ESTOQUE = 10;
    private const STATUS_VALIDOS = ['Disponível', 'Estoque baixo', 'Esgotado'];
    private const FUNCOES_PERMITIDAS = [1];

    private const TAMANHO_MAX_IMAGEM = 2 * 1024 * 1024; // 2 MB
    private const TIPOS_IMAGEM = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private $produtoModel;
    private $categoriaModel;
    private $localizacaoModel;
    private $notificacaoModel;

    public function __construct()
    {
        $this->produtoModel   = $this->model('Produto');
        $this->categoriaModel = $this->model('Categoria');
        $this->localizacaoModel = $this->model('Localizacao');
        $this->notificacaoModel = $this->model('Notificacao');
    }

    public function controlarProduto()
    {
        // 1) Filtros vindos da URL, já saneados
        $status = $_GET['status'] ?? '';
        if (!in_array($status, Produto::STATUS_VALIDOS, true)) {
            $status = '';
        }

        $filtros = [
            'pesquisa'  => trim($_GET['pesquisa'] ?? ''),
            'categoria' => filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT) ?: null,
            'status'    => $status,
        ];

        // 2) Paginação
        $total        = $this->produtoModel->contar($filtros);
        $totalPaginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $paginaAtual  = (int) ($_GET['pagina'] ?? 1);
        $paginaAtual  = min(max(1, $paginaAtual), $totalPaginas);
        $offset       = ($paginaAtual - 1) * self::POR_PAGINA;

        // 3) Token CSRF para os formulários de exclusão
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        // 4) Mensagem de uma ação anterior (exibida uma única vez)
        $mensagem = $_SESSION['mensagem_produto'] ?? null;
        unset($_SESSION['mensagem_produto']);

        $dados = [
            'produtos'     => $this->produtoModel->listar($filtros, self::POR_PAGINA, $offset),
            'categorias'   => $this->categoriaModel->listar(),
            'filtros'      => $filtros,
            'total'        => $total,
            'paginaAtual'  => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'limite'       => self::POR_PAGINA,
            'csrf'         => $_SESSION['csrf'],
            'mensagem'     => $mensagem,
        ];

        $this->view('produtos/controlarProduto', $dados);
    }

    public function relatorio(): void
    {
        $categoriaId = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT) ?: null;
        $baixar      = ($_GET['acao'] ?? '') === 'baixar';

        // Descobre o nome da categoria (e garante que ela existe)
        $categoriaNome = null;
        if ($categoriaId !== null) {
            foreach ($this->categoriaModel->listar() as $categoria) {
                if ((int) $categoria->cate_id === $categoriaId) {
                    $categoriaNome = $categoria->cate_nome;
                    break;
                }
            }

            if ($categoriaNome === null) {
                $_SESSION['flash_erro'] = 'Categoria não encontrada.';
                header('Location: ' . URL . '/produtos/estoque');
                exit;
            }
        }

        $produtos = $this->produtoModel->listarRelatorio($categoriaId);

        // Totais calculados a partir da própria lista (sempre batem com a tabela)
        $resumo = (object) ['total' => count($produtos), 'disponiveis' => 0, 'baixo' => 0, 'esgotados' => 0, 'unidades' => 0];
        foreach ($produtos as $produto) {
            $resumo->unidades += (int) $produto->prod_quantidade;

            if ($produto->prod_status === 'Disponível') {
                $resumo->disponiveis++;
            } elseif ($produto->prod_status === 'Estoque baixo') {
                $resumo->baixo++;
            } elseif ($produto->prod_status === 'Esgotado') {
                $resumo->esgotados++;
            }
        }

        $dados = [
            'categoriaNome' => $categoriaNome,
            'produtos'      => $produtos,
            'resumo'        => $resumo,
            'emitidoEm'     => date('d/m/Y H:i'),
            'emissor'       => $this->emissorRelatorio(),
            'instituicao'   => 'Instituto Federal de Rondônia', // use o mesmo texto do relatório do histórico
            'sistema'       => 'SACIT',                          // idem
            'logo'          => $this->logoBase64(),
        ];

        try {
            // Captura o HTML da view em string (igual ao que você faz no histórico)
            ob_start();
            $this->view('produtos/relatorioProdutos', $dados);
            $html = ob_get_clean();

            $opcoes = new Options();
            $opcoes->set('defaultFont', 'DejaVu Sans');
            $opcoes->set('isRemoteEnabled', false); // a logo vai embutida em base64

            $dompdf = new Dompdf($opcoes);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Numeração "Página X de Y" no rodapé
            $canvas = $dompdf->getCanvas();
            $fonte  = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
            $canvas->page_text(
                $canvas->get_width() - 100,
                $canvas->get_height() - 30,
                'Página {PAGE_NUM} de {PAGE_COUNT}',
                $fonte,
                7.5,
                [0.33, 0.33, 0.33]
            );

            // Nada pode ser impresso antes do PDF, senão ele corrompe
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $dompdf->stream('relatorio-produtos-' . date('Y-m-d') . '.pdf', ['Attachment' => $baixar]);
            exit;
        } catch (Throwable $ex) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            error_log('[Produtos::relatorio] ' . $ex->getMessage());
            $_SESSION['flash_erro'] = 'Não foi possível gerar o relatório. Tente novamente.';
            header('Location: ' . URL . '/produtos/estoque');
            exit;
        }
    }

    /** Quem está emitindo o relatório. AJUSTE às chaves de sessão do seu login. */
    private function emissorRelatorio(): object
    {
        return (object) [
            'usua_nome' => $_SESSION['usua_nome'] ?? '—',
            'func_nome' => $_SESSION['func_nome'] ?? '',
        ];
    }

    /** Logo embutida (data URI), para o Dompdf não precisar acessar URL remota. */
    private function logoBase64(): string
    {
        $arquivo = dirname(__DIR__, 2) . '/public/img/logo-sacit.png';

        return is_file($arquivo)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($arquivo))
            : '';
    }

    public function estoque()
    {
        $filtros = $this->lerFiltros();

        $total = $this->produtoModel->contar($filtros);
        [$paginaAtual, $totalPaginas, $offset] = $this->calcularPaginacao($total, self::POR_PAGINA_ESTOQUE);

        $dados = [
            'resumo'       => $this->produtoModel->resumoEstoque(),
            'produtos'     => $this->produtoModel->listarEstoque($filtros, self::POR_PAGINA_ESTOQUE, $offset),
            'categorias'   => $this->categoriaModel->listar(),
            'filtros'      => $filtros,
            'total'        => $total,
            'paginaAtual'  => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'limite'       => self::POR_PAGINA_ESTOQUE,
        ];

        $this->view('produtos/estoque', $dados);
    }

    public function cadastrarProduto(): void
    {
        $this->renderizarFormulario($this->formVazio(), []);
    }

    public function salvarProduto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/produtos/cadastrarProduto');
            exit;
        }

        $form = [
            'nome'           => trim((string) ($_POST['nome'] ?? '')),
            'categoria'      => trim((string) ($_POST['categoria'] ?? '')),
            'localizacao'    => trim((string) ($_POST['localizacao'] ?? '')),
            'quantidade'     => trim((string) ($_POST['quantidade'] ?? '')),
            'estoque_minimo' => trim((string) ($_POST['estoque_minimo'] ?? '')),
            'descricao'      => trim((string) ($_POST['descricao'] ?? '')),
        ];

        $erros = [];

        if (!$this->csrfValido()) {
            $erros[] = 'Sessão expirada. Recarregue a página e tente novamente.';
        }

        $erros = array_merge($erros, $this->validarCampos($form));

        [$extensao, $erroImagem] = $this->validarImagem($_FILES['imagem'] ?? null);
        if ($erroImagem !== null) {
            $erros[] = $erroImagem;
        }

        if ($erros) {
            $this->renderizarFormulario($form, $erros);
            return;
        }

        // Salva a imagem com nome aleatório (nunca confie no nome enviado pelo usuário)
        $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensao;
        $pasta       = $this->pastaImagens();

        if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
            $this->renderizarFormulario($form, ['Não foi possível preparar a pasta de imagens.']);
            return;
        }

        $destino = $pasta . $nomeArquivo;
        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
            $this->renderizarFormulario($form, ['Não foi possível salvar a imagem.']);
            return;
        }

        // Cadastra + gera o QR (tudo em transação no Model)
        try {
            $prodId = $this->produtoModel->cadastrar([
                'nome'           => $form['nome'],
                'descricao'      => $form['descricao'],
                'foto'           => $nomeArquivo,
                'quantidade'     => (int) $form['quantidade'],
                'estoque_minimo' => (int) $form['estoque_minimo'],
                'localizacao'    => (int) $form['localizacao'],
                'categoria'      => (int) $form['categoria'],
            ], fn(int $id) => $this->gerarQrCode($id));
        } catch (Throwable $ex) {
            @unlink($destino); // não deixa imagem órfã se o banco falhar
            error_log('[Produtos::salvarProduto] ' . $ex->getMessage());
            $this->renderizarFormulario($form, ['Não foi possível cadastrar o produto. Tente novamente.']);
            return;
        }

        $this->notificarNovoProduto($prodId, $form['nome']);
        unset($_SESSION['csrf']); // renova o token
        $_SESSION['flash_sucesso'] = 'Produto cadastrado com sucesso!';
        header('Location: ' . URL . '/produtos/estoque');
        exit;
    }

    public function editarProduto($id = 0): void
    {
        $produto = $this->carregarProdutoOuSair($id);

        $this->renderizarEdicao($produto, [
            'localizacao'    => (string) $produto->loca_id,
            'quantidade'     => (string) $produto->prod_quantidade,
            'estoque_minimo' => (string) $produto->prod_estoque_minimo,
            'descricao'      => (string) $produto->prod_descricao,
        ], []);
    }

    public function detalhes($id = 0): void
    {
        $produto = $this->carregarProdutoOuSair($id);
        $this->view('produtos/detalheProduto', ['produto' => $produto]);
    }

    public function etiqueta($id = 0): void
    {
        $produto = $this->carregarProdutoOuSair($id);

        if (empty($produto->tem_qrcode)) {
            $_SESSION['flash_erro'] = 'Este produto ainda não tem QR Code.';
            header('Location: ' . URL . '/produtos/detalheProduto/' . (int) $produto->prod_id);
            exit;
        }

        $this->view('produtos/etiquetaProduto', ['produto' => $produto]);
    }

    private function carregarProdutoOuSair($id): object
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $produto = ($id === false) ? null : $this->produtoModel->buscarPorId($id);

        if ($produto === null) {
            $_SESSION['flash_erro'] = 'Produto não encontrado.';
            header('Location: ' . URL . '/produtos/estoque');
            exit;
        }

        return $produto;
    }

    public function atualizarProduto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/produtos/estoque');
            exit;
        }

        // Nome e categoria não são editáveis: o produto vem do banco, não do POST
        $produto = $this->carregarProdutoOuSair($_POST['id'] ?? 0);

        $form = [
            'localizacao'    => trim((string) ($_POST['localizacao'] ?? '')),
            'quantidade'     => trim((string) ($_POST['quantidade'] ?? '')),
            'estoque_minimo' => trim((string) ($_POST['estoque_minimo'] ?? '')),
            'descricao'      => trim((string) ($_POST['descricao'] ?? '')),
        ];

        $erros = [];

        if (!$this->csrfValido()) {
            $erros[] = 'Sessão expirada. Recarregue a página e tente novamente.';
        }

        $erros = array_merge($erros, $this->validarCamposEdicao($form));

        // A imagem é opcional na edição: só valida se o usuário escolheu uma nova
        $extensao     = null;
        $enviouImagem = isset($_FILES['imagem'])
            && ($_FILES['imagem']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if ($enviouImagem) {
            [$extensao, $erroImagem] = $this->validarImagem($_FILES['imagem']);
            if ($erroImagem !== null) {
                $erros[] = $erroImagem;
            }
        }

        if ($erros) {
            $this->renderizarEdicao($produto, $form, $erros);
            return;
        }

        $novaFoto = null;
        $destino  = null;

        if ($enviouImagem) {
            $pasta = $this->pastaImagens();

            if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
                $this->renderizarEdicao($produto, $form, ['Não foi possível preparar a pasta de imagens.']);
                return;
            }

            $novaFoto = bin2hex(random_bytes(16)) . '.' . $extensao;
            $destino  = $pasta . $novaFoto;

            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                $this->renderizarEdicao($produto, $form, ['Não foi possível salvar a imagem.']);
                return;
            }
        }

        try {
            $this->produtoModel->atualizar((int) $produto->prod_id, [
                'descricao'      => $form['descricao'],
                'quantidade'     => (int) $form['quantidade'],
                'estoque_minimo' => (int) $form['estoque_minimo'],
                'localizacao'    => (int) $form['localizacao'],
            ], $novaFoto);
        } catch (Throwable $ex) {
            if ($destino !== null) {
                @unlink($destino); // não deixa imagem órfã se o banco falhar
            }
            error_log('[Produtos::atualizarProduto] ' . $ex->getMessage());
            $this->renderizarEdicao($produto, $form, ['Não foi possível salvar as alterações. Tente novamente.']);
            return;
        }

        // Só apaga a foto antiga depois que o banco já apontou para a nova
        if ($novaFoto !== null && !empty($produto->prod_foto)) {
            $antiga = $this->pastaImagens() . basename((string) $produto->prod_foto);
            if (is_file($antiga)) {
                @unlink($antiga);
            }
        }

        unset($_SESSION['csrf']); // renova o token
        $_SESSION['flash_sucesso'] = 'Produto atualizado com sucesso!';
        header('Location: ' . URL . '/produtos/estoque');
        exit;
    }

    private function validarCamposEdicao(array $f): array
    {
        $erros = [];

        $localizacao = filter_var($f['localizacao'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($localizacao === false || !$this->localizacaoModel->localizacaoExiste($localizacao)) {
            $erros[] = 'Selecione uma localização válida.';
        }

        $opcoesInt = ['options' => ['min_range' => 0, 'max_range' => 99999]];

        if (filter_var($f['quantidade'], FILTER_VALIDATE_INT, $opcoesInt) === false) {
            $erros[] = 'A quantidade deve ser um número inteiro maior ou igual a 0.';
        }

        if (filter_var($f['estoque_minimo'], FILTER_VALIDATE_INT, $opcoesInt) === false) {
            $erros[] = 'O estoque mínimo deve ser um número inteiro maior ou igual a 0.';
        }

        if ($f['descricao'] === '') {
            $erros[] = 'Informe a descrição do produto.';
        } elseif (mb_strlen($f['descricao']) > 1000) {
            $erros[] = 'A descrição deve ter no máximo 1000 caracteres.';
        }

        return $erros;
    }

    private function renderizarEdicao(object $produto, array $form, array $erros): void
    {
        $this->view('produtos/editarProduto', [
            'produto'      => $produto,
            'localizacoes' => $this->localizacaoModel->listar(),
            'form'         => $form,
            'erros'        => $erros,
            'csrf'         => $this->tokenCsrf(),
        ]);
    }

    public function qrcode($id = 0): void
    {
        $id = (int) $id;

        if ($id <= 0) {
            http_response_code(404);
            exit('Produto inválido.');
        }

        $qr = $this->produtoModel->buscarQrCode($id);

        if ($qr === null || $qr === '') {
            http_response_code(404);
            exit('QR Code não encontrado.');
        }

        // Remove qualquer saída anterior que possa corromper o PNG
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Informa ao navegador que a resposta é uma imagem PNG
        header('Content-Type: image/png');
        header('Content-Length: ' . strlen($qr));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $qr;
        exit;
    }

    private function gerarQrCode(int $prodId): string
    {
        // O que o QR abre ao ser escaneado. Ajuste para a rota de detalhes que você tiver.
        $conteudo = URL . '/produtos/detalhes/' . $prodId;

        $resultado = (new Builder(
            writer: new PngWriter(),
            data: $conteudo,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        return $resultado->getString(); // PNG em binário -> vai para o BLOB prod_qrcode
    }

    private function validarCampos(array $f): array
    {
        $erros = [];

        if ($f['nome'] === '') {
            $erros[] = 'Informe o nome do produto.';
        } elseif (mb_strlen($f['nome']) > 100) {
            $erros[] = 'O nome deve ter no máximo 100 caracteres.';
        }

        $categoria = filter_var($f['categoria'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($categoria === false || !$this->categoriaModel->categoriaExiste($categoria)) {
            $erros[] = 'Selecione uma categoria válida.';
        }

        $localizacao = filter_var($f['localizacao'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($localizacao === false || !$this->localizacaoModel->localizacaoExiste($localizacao)) {
            $erros[] = 'Selecione uma localização válida.';
        }

        $opcoesInt = ['options' => ['min_range' => 0, 'max_range' => 99999]];

        if (filter_var($f['quantidade'], FILTER_VALIDATE_INT, $opcoesInt) === false) {
            $erros[] = 'A quantidade deve ser um número inteiro maior ou igual a 0.';
        }

        if (filter_var($f['estoque_minimo'], FILTER_VALIDATE_INT, $opcoesInt) === false) {
            $erros[] = 'O estoque mínimo deve ser um número inteiro maior ou igual a 0.';
        }

        if ($f['descricao'] === '') {
            $erros[] = 'Informe a descrição do produto.';
        } elseif (mb_strlen($f['descricao']) > 1000) {
            $erros[] = 'A descrição deve ter no máximo 1000 caracteres.';
        }

        return $erros;
    }

    /** @return array{0: ?string, 1: ?string}  [extensão, mensagem de erro] */
    private function validarImagem($arquivo): array
    {
        if (!is_array($arquivo) || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, 'Selecione uma imagem para o produto.'];
        }

        if ($arquivo['error'] === UPLOAD_ERR_INI_SIZE || $arquivo['error'] === UPLOAD_ERR_FORM_SIZE) {
            return [null, 'A imagem deve ter no máximo 2 MB.'];
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
            return [null, 'Falha no envio da imagem. Tente novamente.'];
        }

        if ($arquivo['size'] > self::TAMANHO_MAX_IMAGEM) {
            return [null, 'A imagem deve ter no máximo 2 MB.'];
        }

        // Confere o tipo real pelo conteúdo, não pelo que o navegador declarou
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
        if (!isset(self::TIPOS_IMAGEM[$mime]) || @getimagesize($arquivo['tmp_name']) === false) {
            return [null, 'A imagem deve ser JPG, PNG ou WEBP.'];
        }

        return [self::TIPOS_IMAGEM[$mime], null];
    }

    private function renderizarFormulario(array $form, array $erros): void
    {
        $dados = [
            'categorias'   => $this->categoriaModel->listar(),
            'localizacoes' => $this->localizacaoModel->listar(),
            'form'         => $form,
            'erros'        => $erros,
            'csrf'         => $this->tokenCsrf(),
        ];

        // Ajuste para o método que o seu Controller base usa para carregar views.
        $this->view('produtos/cadastrarProduto', $dados);
    }

    private function formVazio(): array
    {
        return [
            'nome'           => '',
            'categoria'      => '',
            'localizacao'    => '',
            'quantidade'     => '',
            'estoque_minimo' => '',
            'descricao'      => '',
        ];
    }

    private function pastaImagens(): string
    {
        return dirname(__DIR__, 2) . '/public/img/produtos/';
    }

    private function tokenCsrf(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    private function csrfValido(): bool
    {
        $enviado = $_POST['csrf'] ?? '';
        return is_string($enviado)
            && !empty($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $enviado);
    }

    private function calcularPaginacao(int $total, int $porPagina): array
    {
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $paginaAtual  = min(max(1, (int) ($_GET['pagina'] ?? 1)), $totalPaginas);
        $offset       = ($paginaAtual - 1) * $porPagina;

        return [$paginaAtual, $totalPaginas, $offset];
    }

    private function definirMensagem(string $tipo, string $texto): void
    {
        $_SESSION['mensagem_produto'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    private function voltarParaLista(): void
    {
        header('Location: ' . URL . '/produtos/controlarProduto');
        exit;
    }

    private function lerFiltros(): array
    {
        $status = $_GET['status'] ?? '';
        if (!in_array($status, Produto::STATUS_VALIDOS, true)) {
            $status = '';
        }

        return [
            'pesquisa'  => trim($_GET['pesquisa'] ?? ''),
            'categoria' => filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT) ?: null,
            'status'    => $status,
        ];
    }

    public function excluirProduto($id = null)
    {
        // Só aceita POST com token válido (excluir por link GET é inseguro)
        $tokenEnviado = $_POST['csrf'] ?? '';
        $tokenValido  = $_SESSION['csrf'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($tokenValido, $tokenEnviado)) {
            $this->definirMensagem('erro', 'Requisição inválida.');
            $this->voltarParaLista();
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        $produto = $id ? $this->produtoModel->buscarPorId($id) : null;

        if (!$produto) {
            $this->definirMensagem('erro', 'Produto não encontrado.');
            $this->voltarParaLista();
        }

        if ($this->produtoModel->excluir($id)) {
            $this->apagarFoto($produto->prod_foto);
            $this->definirMensagem('sucesso', 'Produto excluído com sucesso.');
        } else {
            // Normalmente é chave estrangeira: o produto já tem movimentações/solicitações
            $this->definirMensagem('erro', 'Não foi possível excluir. Verifique se o produto possui movimentações vinculadas.');
        }

        $this->voltarParaLista();
    }

    private function janelaDePaginas(int $atual, int $total): array
    {
        $paginas = [1, $total];
        for ($i = $atual - 2; $i <= $atual + 2; $i++) {
            if ($i >= 1 && $i <= $total) {
                $paginas[] = $i;
            }
        }
        $paginas = array_unique($paginas);
        sort($paginas);

        $janela = [];
        $anterior = 0;
        foreach ($paginas as $pagina) {
            if ($pagina - $anterior > 1) {
                $janela[] = null;
            }
            $janela[] = $pagina;
            $anterior = $pagina;
        }

        return $janela;
    }
    private function exigirPermissao(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            URL::redirecionar('users/loginUser');
            exit;
        }
        if (!in_array((int) ($_SESSION['usuario_funcao'] ?? 0), self::FUNCOES_PERMITIDAS, true)) {
            $this->redirecionar('/inicio');
        }
    }

    private function redirecionar(string $rota): void
    {
        header('Location: ' . URL . $rota);
        exit;
    }

    private function flash(string $chave, $valor): void
    {
        $_SESSION[$chave] = $valor;
    }

    /** Lê uma mensagem "flash" da sessão e a remove (aparece só uma vez). */
    private function lerFlash(string $chave, $padrao)
    {
        $valor = $_SESSION[$chave] ?? $padrao;
        unset($_SESSION[$chave]);
        return $valor;
    }

    /* ---------- auxiliares ---------- */


    private function apagarFoto(?string $arquivo): void
    {
        if (!$arquivo) {
            return;
        }

        // basename() impede caminhos como ../../algo
        $caminho = dirname(__DIR__, 2) . '/public/img/produtos/' . basename($arquivo);

        if (is_file($caminho)) {
            @unlink($caminho);
        }
    }

    /* ================================ NOTIFICAÇÃO ==================================== */

    private function notificarNovoProduto(int $produtoId, string $nomeProduto): void
    {
        try {

            // Coordenador = 1
            // Estagiário = 2
            // Servidor = 3
            $usuarios = $this->notificacaoModel->buscarUsuariosAtivos([
                1,
                2,
                3
            ]);

            if (empty($usuarios)) {
                return;
            }

            $nomeSeguro = htmlspecialchars(
                $nomeProduto,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            $this->notificacaoModel->notificar(
                'Novo produto disponível',

                "Um novo produto foi cadastrado: {$nomeProduto}.",

                'Produto',

                $usuarios,

                function ($usuario, $extra) {

                    $nomeUsuario = htmlspecialchars(
                        $usuario->usua_nome,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    );

                    $corpo = "
                    <p>Olá, {$nomeUsuario}!</p>

                    <p>
                        Um novo produto foi cadastrado no sistema.
                    </p>

                    <p>
                        <strong>Produto:</strong>
                        {$extra['produto']}
                    </p>

                    <p>
                        O produto já está disponível para consulta
                        e solicitação no almoxarifado.
                    </p>
                ";

                    return EmailTemplate::padrao(
                        'Novo produto disponível',
                        $nomeUsuario,
                        $corpo
                    );
                },

                [
                    'produto' => $nomeSeguro,
                    'produto_id' => $produtoId
                ]
            );
        } catch (Throwable $ex) {

            // A falha da notificação não deve impedir
            // o cadastro do produto.
            error_log(
                '[Produtos::notificarNovoProduto] ' .
                    $ex->getMessage()
            );
        }
    }
}
