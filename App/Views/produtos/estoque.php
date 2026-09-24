<?php

/**
 * View: produtos/estoque
 *
 * Espera em $dados:
 *  - resumo        (objeto: total, disponiveis, baixo, esgotados)
 *  - produtos      (array de objetos: prod_id, prod_nome, cate_nome, prod_quantidade,
 *                   prod_estoque_minimo, prod_status)
 *  - categorias    (array de objetos)
 *  - filtros       (['pesquisa' => string, 'categoria' => ?int, 'status' => string])
 *  - total, paginaAtual, totalPaginas, limite
 */

// Escapa qualquer saída dinâmica (evita XSS). Se já tiver um helper global, use-o no lugar.
$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$filtros      = $dados['filtros'];
$resumo       = $dados['resumo'];
$paginaAtual  = $dados['paginaAtual'];
$totalPaginas = $dados['totalPaginas'];
$total        = $dados['total'];

// Cards-resumo: um único bloco de HTML repetido pelo loop
$cardsResumo = [
    ['cor' => 'verde',    'icone' => 'bi-box-seam', 'rotulo' => 'Total de Produtos', 'valor' => $resumo->total,       'texto' => 'Produtos cadastrados'],
    ['cor' => 'azul',     'icone' => 'bi-boxes',  'rotulo' => 'Itens em Estoque',  'valor' => $resumo->disponiveis, 'texto' => 'Produtos disponíveis'],
    ['cor' => 'amarelo',  'icone' => 'bi-exclamation-triangle', 'rotulo' => 'Estoque Baixo',     'valor' => $resumo->baixo,       'texto' => 'Produtos com estoque baixo'],
    ['cor' => 'vermelho', 'icone' => 'bi-x-circle',                'rotulo' => 'Sem Estoque',       'valor' => $resumo->esgotados,   'texto' => 'Produtos indisponíveis'],
];

$classesStatus = [
    'Disponível'    => 'estq-status-ok',
    'Estoque baixo' => 'estq-status-baixo',
    'Esgotado'      => 'estq-status-esgotado',
];

// Monta a URL de uma página mantendo pesquisa/categoria/status
$urlPagina = function (int $pagina) use ($filtros): string {
    $query = array_filter(
        [
            'pesquisa'  => $filtros['pesquisa'],
            'categoria' => $filtros['categoria'],
            'status'    => $filtros['status'],
        ],
        fn($valor) => $valor !== '' && $valor !== null
    );
    $query['pagina'] = $pagina;

    return '?' . http_build_query($query);
};

// Janela de páginas (2 antes e 2 depois da atual)
$primeiraJanela = max(1, $paginaAtual - 2);
$ultimaJanela   = min($totalPaginas, $paginaAtual + 2);

$inicio = $total > 0 ? (($paginaAtual - 1) * $dados['limite']) + 1 : 0;
$fim    = min($paginaAtual * $dados['limite'], $total);
?>
<?php include "../App/Views/menu.php"; ?>

<main class="estq-container">

    <header class="estq-cabecalho">
        <div class="estq-titulo-grupo">
            <div class="estq-titulo-linha">
                <span class="estq-titulo-marca"></span>
                <h1 class="estq-titulo">Controlar Estoque</h1>
            </div>
            <p class="estq-subtitulo">Gerencie os produtos em estoque.</p>
        </div>

        <img src="<?= URL ?>/img/logo-sacit.png" alt="SACIT" class="estq-logo">
    </header>

    <?php Sessao::mensagem('produto'); ?>

    <section class="estq-resumo" aria-label="Resumo do estoque">
        <?php foreach ($cardsResumo as $card): ?>
            <article class="estq-resumo-card">
                <div class="estq-resumo-topo">
                    <div class="estq-resumo-icone estq-resumo-icone-<?= $card['cor'] ?>">
                        <i class="bi <?= $e($card['icone']) ?>"></i>
                    </div>
                    <p class="estq-resumo-rotulo"><?= $card['rotulo'] ?></p>
                </div>

                <p class="estq-resumo-valor"><?= (int) $card['valor'] ?></p>
                <span class="estq-resumo-texto"><?= $card['texto'] ?></span>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="estq-painel estq-painel-relatorio mb-3" aria-labelledby="estq-relatorio-titulo">
            <h2 class="estq-painel-titulo" id="estq-relatorio-titulo">Relatório de produtos</h2>
            <p class="estq-painel-descricao">
                Escolha uma categoria ou gere o relatório de todos os produtos.
            </p>

            <form action="<?= URL ?>/produtos/relatorio" method="GET" class="estq-relatorio">
                <select name="categoria" class="estq-select" aria-label="Categoria do relatório">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($dados['categorias'] as $categoria): ?>
                        <option value="<?= (int) $categoria->cate_id ?>">
                            <?= $e($categoria->cate_nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="acao" value="imprimir" formtarget="_blank"
                    class="estq-btn estq-btn-principal">
                    <i class="bi bi-printer"></i>&nbsp;Imprimir
                </button>

                <button type="submit" name="acao" value="baixar"
                    class="estq-btn estq-btn-principal estq-btn-secundario">
                    <i class="bi bi-download"></i>&nbsp;Baixar PDF
                </button>
            </form>
        </section>

    <section class="estq-painel">

        <div class="estq-painel-topo">
            <h2 class="estq-painel-titulo">Pesquisar produto</h2>

            <a href="<?= URL ?>/produtos/cadastrarProduto" class="estq-btn estq-btn-principal">
                + Novo Produto
            </a>
        </div>


        <form action="<?= URL ?>/produtos/estoque" method="GET" class="estq-filtros">

            <div class="estq-busca">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    name="pesquisa"
                    class="estq-busca-campo"
                    value="<?= $e($filtros['pesquisa']) ?>"
                    placeholder="Digite o nome do produto..."
                    aria-label="Buscar produto">
            </div>

            <select name="categoria" class="estq-select" aria-label="Filtrar por categoria">
                <option value="">Todas as categorias</option>
                <?php foreach ($dados['categorias'] as $categoria): ?>
                    <option
                        value="<?= (int) $categoria->cate_id ?>"
                        <?= ((int) $filtros['categoria'] === (int) $categoria->cate_id) ? 'selected' : '' ?>>
                        <?= $e($categoria->cate_nome) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="estq-select" aria-label="Filtrar por status">
                <option value="">Todos os status</option>
                <?php foreach (array_keys($classesStatus) as $status): ?>
                    <option value="<?= $e($status) ?>" <?= ($filtros['status'] === $status) ? 'selected' : '' ?>>
                        <?= $e($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="estq-btn estq-btn-principal">Pesquisar</button>
        </form>

        <div class="estq-tabela-wrapper" role="region" aria-label="Tabela de produtos" tabindex="0">
            <table class="estq-tabela">
                <caption class="estq-somente-leitor">Produtos em estoque</caption>
                <thead>
                    <tr>
                        <th scope="col" class="estq-th">Código</th>
                        <th scope="col" class="estq-th">Produto</th>
                        <th scope="col" class="estq-th">Categoria</th>
                        <th scope="col" class="estq-th estq-col-centro">Estoque Atual</th>
                        <th scope="col" class="estq-th estq-col-centro">Estoque Mínimo</th>
                        <th scope="col" class="estq-th">Status</th>
                        <th scope="col" class="estq-th">Ações</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (empty($dados['produtos'])): ?>
                        <tr>
                            <td colspan="7" class="estq-td estq-vazio">
                                Nenhum produto encontrado com esses filtros.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($dados['produtos'] as $produto): ?>
                        <?php $classeStatus = $classesStatus[$produto->prod_status] ?? 'estq-status-esgotado'; ?>
                        <tr class="estq-linha">
                            <td class="estq-td"><?= (int) $produto->prod_id ?></td>
                            <td class="estq-td"><?= $e($produto->prod_nome) ?></td>
                            <td class="estq-td"><?= $e($produto->cate_nome) ?></td>
                            <td class="estq-td estq-col-centro"><?= (int) $produto->prod_quantidade ?></td>
                            <td class="estq-td estq-col-centro"><?= (int) $produto->prod_estoque_minimo ?></td>
                            <td class="estq-td">
                                <span class="estq-status <?= $classeStatus ?>">
                                    <?= $e($produto->prod_status) ?>
                                </span>
                            </td>
                            <td class="estq-td">
                                <a
                                    href="<?= URL ?>/produtos/detalhes/<?= (int) $produto->prod_id ?>"
                                    class="estq-btn estq-btn-detalhe">
                                    Ver detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <?php if ($total > 0): ?>
            <div class="estq-rodape-tabela">
                <p class="estq-rodape-info">
                    Mostrando <?= $inicio ?> até <?= $fim ?> de <?= $total ?> produtos
                </p>

                <?php if ($totalPaginas > 1): ?>
                    <nav class="estq-paginacao" aria-label="Paginação">

                        <?php if ($paginaAtual > 1): ?>
                            <a class="estq-pag-link" href="<?= $e($urlPagina($paginaAtual - 1)) ?>" aria-label="Página anterior">&#8249;</a>
                        <?php endif; ?>

                        <?php for ($i = $primeiraJanela; $i <= $ultimaJanela; $i++): ?>
                            <a
                                class="estq-pag-link <?= $i === $paginaAtual ? 'estq-pag-link-ativa' : '' ?>"
                                href="<?= $e($urlPagina($i)) ?>"
                                <?= $i === $paginaAtual ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginaAtual < $totalPaginas): ?>
                            <a class="estq-pag-link" href="<?= $e($urlPagina($paginaAtual + 1)) ?>" aria-label="Próxima página">&#8250;</a>
                        <?php endif; ?>

                    </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </section>
</main>

<?php include "../App/Views/footer.php"; ?>