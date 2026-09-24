<?php

/**
 * View: produtos/controlarProduto
 *
 * Espera em $dados:
 *  - produtos      (array de objetos)
 *  - categorias    (array de objetos)
 *  - filtros       (['pesquisa' => string, 'categoria' => ?int, 'status' => string])
 *  - total, paginaAtual, totalPaginas, limite
 *  - csrf          (token para os formulários de exclusão)
 *  - mensagem      (null ou ['tipo' => 'sucesso'|'erro', 'texto' => string])
 */

// Escapa qualquer saída dinâmica (evita XSS). Se já tiver um helper global, use-o no lugar.
$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$filtros      = $dados['filtros'];
$paginaAtual  = $dados['paginaAtual'];
$totalPaginas = $dados['totalPaginas'];
$total        = $dados['total'];

$classesStatus = [
    'Disponível'    => 'cprod-status-ok',
    'Estoque baixo' => 'cprod-status-baixo',
    'Esgotado'      => 'cprod-status-esgotado',
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

<main class="cprod-container">

    <header class="cprod-cabecalho">
        <div>
            <div class="cprod-titulo-linha">
                <span class="cprod-titulo-marca"></span>
                <h1 class="cprod-titulo">Controlar Produto</h1>
            </div>
            <p class="cprod-subtitulo">Controle e gerencie os produtos do sistema.</p>
        </div>

        <img src="<?= URL ?>/img/logo-sacit.png" alt="SACIT" class="cprod-logo">
    </header>

    <?php if (!empty($dados['mensagem'])): ?>
        <div class="cprod-alerta cprod-alerta-<?= $e($dados['mensagem']['tipo']) ?>" role="status">
            <?= $e($dados['mensagem']['texto']) ?>
        </div>
    <?php endif; ?>

    <form action="<?= URL ?>/produtos/controlarProduto" method="GET" class="cprod-filtros">

        <div class="cprod-busca">
            <i class="bi bi-search"></i>
            <input
                type="search"
                name="pesquisa"
                class="cprod-busca-campo"
                value="<?= $e($filtros['pesquisa']) ?>"
                placeholder="Buscar produto..."
                aria-label="Buscar produto">
        </div>

        <select name="categoria" class="cprod-select" aria-label="Filtrar por categoria">
            <option value="">Todas as categorias</option>
            <?php foreach ($dados['categorias'] as $categoria): ?>
                <option
                    value="<?= (int) $categoria->cate_id ?>"
                    <?= ((int) $filtros['categoria'] === (int) $categoria->cate_id) ? 'selected' : '' ?>>
                    <?= $e($categoria->cate_nome) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="cprod-select" aria-label="Filtrar por status">
            <option value="">Todos os status</option>
            <?php foreach (array_keys($classesStatus) as $status): ?>
                <option value="<?= $e($status) ?>" <?= ($filtros['status'] === $status) ? 'selected' : '' ?>>
                    <?= $e($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="cprod-btn-buscar">Pesquisar</button>
    </form>

    <section>
        <div class="cprod-grid">

            <?php if (empty($dados['produtos'])): ?>
                <p class="cprod-vazio">Nenhum produto encontrado com esses filtros.</p>
            <?php endif; ?>

            <?php foreach ($dados['produtos'] as $produto): ?>
                <?php
                $classeStatus = $classesStatus[$produto->prod_status] ?? 'cprod-status-esgotado';
                $foto = !empty($produto->prod_foto)
                    ? URL . '/img/produtos/' . rawurlencode($produto->prod_foto)
                    : URL . '/img/produto-sem-foto.png';
                ?>
                <article class="cprod-card">

                    <div class="cprod-card-imagem">
                        <img
                            src="<?= $e($foto) ?>"
                            alt="<?= $e($produto->prod_nome) ?>"
                            class="cprod-foto"
                            loading="lazy">

                        <details class="cprod-menu">
                            <summary class="cprod-menu-botao" aria-label="Ações do produto">⋮</summary>

                            <div class="cprod-menu-lista">
                                <a
                                    class="cprod-menu-item"
                                    href="<?= URL ?>/produtos/editarProduto/<?= (int) $produto->prod_id ?>">
                                    Editar Produto
                                </a>

                                <form
                                    class="cprod-menu-form"
                                    action="<?= URL ?>/produtos/excluirProduto/<?= (int) $produto->prod_id ?>"
                                    method="POST"
                                    onsubmit="return confirmarExclusaoProduto();">
                                    <input type="hidden" name="csrf" value="<?= $e($dados['csrf']) ?>">
                                    <button type="submit" class="cprod-menu-item cprod-menu-item-perigo">
                                        Excluir Produto
                                    </button>
                                </form>
                            </div>
                        </details>

                        <span class="cprod-status <?= $classeStatus ?>">
                            <?= $e($produto->prod_status) ?>
                        </span>
                    </div>

                    <div class="cprod-info">
                        <h2 class="cprod-nome"><?= $e($produto->prod_nome) ?></h2>
                        <p class="cprod-detalhe"><?= $e($produto->cate_nome) ?></p>
                        <p class="cprod-detalhe">
                            <strong>Quantidade:</strong>
                            <?= (int) $produto->prod_quantidade ?> unidades
                        </p>
                    </div>

                </article>
            <?php endforeach; ?>

        </div>

        <?php if ($total > 0): ?>
            <div class="cprod-rodape-lista">
                <p class="cprod-rodape-info">
                    Mostrando <?= $inicio ?> até <?= $fim ?> de <?= $total ?> produtos
                </p>

                <?php if ($totalPaginas > 1): ?>
                    <nav class="cprod-paginacao" aria-label="Paginação">

                        <?php if ($paginaAtual > 1): ?>
                            <a class="cprod-pag-link" href="<?= $e($urlPagina($paginaAtual - 1)) ?>" aria-label="Página anterior">&#8249;</a>
                        <?php endif; ?>

                        <?php for ($i = $primeiraJanela; $i <= $ultimaJanela; $i++): ?>
                            <a
                                class="cprod-pag-link <?= $i === $paginaAtual ? 'cprod-pag-link-ativa' : '' ?>"
                                href="<?= $e($urlPagina($i)) ?>"
                                <?= $i === $paginaAtual ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginaAtual < $totalPaginas): ?>
                            <a class="cprod-pag-link" href="<?= $e($urlPagina($paginaAtual + 1)) ?>" aria-label="Próxima página">&#8250;</a>
                        <?php endif; ?>

                    </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include "../App/Views/footer.php"; ?>