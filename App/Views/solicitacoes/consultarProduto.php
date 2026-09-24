<?php
include "../App/Views/menu.php";

/**
 * Dados esperados do Controller (Solicitacoes::consultarProduto):
 *  $dados['produtos']      array de objetos do Model Produto::listar(), com:
 *                          prod_id, prod_nome, prod_foto, prod_quantidade (estoque total),
 *                          disponivel (o que sobra depois de descontar reservas pendentes),
 *                          status_exibicao ('Disponível' | 'Estoque baixo' | 'Esgotado'), cate_nome
 *  $dados['categorias']    array de objetos (cate_id, cate_nome)
 *  $dados['opcoesStatus']  array [valor => rótulo]
 *  $dados['filtros']       array [pesquisa, categoria, status]
 *  $dados['paginacao']     array [total, paginaAtual, totalPaginas, inicio, fim]
 *  $dados['sucesso']       string|null - mensagem de sucesso (flash)
 *  $dados['erro']          string|null - mensagem de erro (flash)
 */

// Escapa qualquer valor antes de imprimir (evita XSS).
$e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$produtos     = $dados['produtos'];
$categorias   = $dados['categorias'];
$opcoesStatus = $dados['opcoesStatus'];
$filtros      = $dados['filtros'];
$paginacao    = $dados['paginacao'];

// Classe visual de cada status (qualquer outro valor cai em "crítico").
$classesStatus = [
    'Disponível'    => 'cprod-status--ok',
    'Estoque baixo' => 'cprod-status--baixo',
];

// Mantém os filtros ativos nos links da paginação.
$filtrosAtivos = array_filter($filtros, static fn($valor) => $valor !== '' && $valor !== null);
$urlPagina     = static fn(int $pagina): string => '?' . http_build_query(array_merge($filtrosAtivos, ['pagina' => $pagina]));

// Janela de 5 páginas ao redor da atual.
$paginaInicial = max(1, $paginacao['paginaAtual'] - 2);
$paginaFinal   = min($paginacao['totalPaginas'], $paginacao['paginaAtual'] + 2);
?>
<main class="cprod-pagina">

    <header class="cprod-cabecalho">
        <div class="cprod-cabecalho-texto">
            <div class="cprod-titulo-linha">
                <span class="cprod-titulo-marca"></span>
                <h1 class="cprod-titulo">Consultar produto</h1>
            </div>
            <p class="cprod-subtitulo">Consulte os produtos do sistema.</p>
        </div>
        <img class="cprod-logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <?php if (!empty($dados['sucesso'])): ?>
        <div class="cprod-alerta cprod-alerta--sucesso" role="status"><?= $e($dados['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (!empty($dados['erro'])): ?>
        <div class="cprod-alerta cprod-alerta--erro" role="alert"><?= $e($dados['erro']) ?></div>
    <?php endif; ?>

    <!-- Filtros (GET) -->
    <form class="cprod-filtros" action="<?= URL ?>/produtos/consultarProduto" method="GET">
        <div class="cprod-busca">
            <i class="bi bi-search"></i>
            <input
                class="cprod-busca-campo"
                type="search"
                name="pesquisa"
                value="<?= $e($filtros['pesquisa']) ?>"
                placeholder="Buscar produto..."
                aria-label="Buscar produto">
        </div>

        <select class="cprod-select" name="categoria" aria-label="Filtrar por categoria">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $categoria): ?>
                <option
                    value="<?= $e($categoria->cate_id) ?>"
                    <?= (string) $filtros['categoria'] === (string) $categoria->cate_id ? 'selected' : '' ?>>
                    <?= $e($categoria->cate_nome) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select class="cprod-select" name="status" aria-label="Filtrar por status">
            <option value="">Todos os status</option>
            <?php foreach ($opcoesStatus as $valor => $rotulo): ?>
                <option value="<?= $e($valor) ?>" <?= $filtros['status'] === $valor ? 'selected' : '' ?>>
                    <?= $e($rotulo) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="cprod-botao cprod-botao--primario" type="submit">Pesquisar</button>
    </form>

    <!-- Lista de produtos + solicitação (POST) -->
    <section class="cprod-lista">
        <?php if (empty($produtos)): ?>

            <div class="cprod-vazio">
                <p class="cprod-vazio-titulo">Nenhum produto encontrado</p>
                <p class="cprod-vazio-texto">Tente outro termo de busca ou remova algum filtro.</p>
                <a class="cprod-botao cprod-botao--contorno" href="<?= URL ?>/produtos/consultarProduto">Limpar filtros</a>
            </div>

        <?php else: ?>

            <form action="<?= URL ?>/solicitacoes/nova" method="POST">
                <div class="cprod-barra-solicitacao">
                    <button class="cprod-botao cprod-botao--primario js-cprod-solicitar" type="submit" disabled>
                        Solicitar (0)
                    </button>
                </div>

                <div class="cprod-grade">
                    <?php foreach ($produtos as $produto): ?>
                        <?php
                        // status_exibicao/disponivel vêm do Model Produto::listar() (ver RESERVADO_SQL/
                        // DISPONIVEL_SQL/STATUS_SQL). Os "??" abaixo evitam que a página quebre se o
                        // Model estiver desatualizado, mas o normal é essas chaves sempre existirem —
                        // se você está caindo nos valores padrão aqui, o Model não foi atualizado.
                        $statusExibicao = $produto->status_exibicao ?? ($produto->prod_status ?? 'Disponível');
                        $disponivel     = $produto->disponivel ?? (int) $produto->prod_quantidade;
                        $esgotado       = $statusExibicao === 'Esgotado';
                        $classeStatus   = $classesStatus[$statusExibicao] ?? 'cprod-status--critico';
                        $foto           = !empty($produto->prod_foto) ? $produto->prod_foto : 'sem-foto.png';
                        ?>
                        <article class="cprod-card">
                            <div class="cprod-card-midia">
                                <img
                                    class="cprod-card-foto"
                                    src="<?= URL ?>/img/produtos/<?= $e($foto) ?>"
                                    alt="<?= $e($produto->prod_nome) ?>"
                                    loading="lazy">

                                <label class="cprod-check">
                                    <input
                                        class="cprod-check-input js-cprod-check"
                                        type="checkbox"
                                        name="produtos[]"
                                        value="<?= (int) $produto->prod_id ?>"
                                        data-disponivel="<?= (int) $disponivel ?>"
                                        aria-label="Selecionar <?= $e($produto->prod_nome) ?>"
                                        <?= $esgotado ? 'disabled' : '' ?>>
                                    <span class="cprod-check-marca"></span>
                                </label>

                                <span class="cprod-status <?= $classeStatus ?>">
                                    <?= $e($statusExibicao) ?>
                                </span>
                            </div>

                            <div class="cprod-card-corpo">
                                <div class="cprod-card-cabecalho">
                                    <h2 class="cprod-card-nome"><?= $e($produto->prod_nome) ?></h2>
                                    <a class="cprod-botao cprod-botao--contorno cprod-botao--pequeno"
                                        href="<?= URL ?>/produtos/detalhes/<?= (int) $produto->prod_id ?>">
                                        Ver detalhes
                                    </a>
                                </div>

                                <p class="cprod-card-detalhe"><?= $e($produto->cate_nome) ?></p>

                                <?php if (!$esgotado && $disponivel < (int) $produto->prod_quantidade) { ?>
                                    <p class="cprod-card-detalhe">
                                        <strong>Disponível:</strong>
                                        <?= $disponivel ?> de <?= (int) $produto->prod_quantidade ?> unidades
                                        <span class="cprod-card-reservado">(o restante está reservado em outras solicitações pendentes)</span>
                                    </p>
                                <?php } else { ?>
                                    <p class="cprod-card-detalhe">
                                        <strong>Quantidade:</strong>
                                        <?= $disponivel ?> unidades
                                    </p>
                                <?php } ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </form>

            <footer class="cprod-rodape">
                <p class="cprod-rodape-info">
                    Mostrando <?= $paginacao['inicio'] ?> até <?= $paginacao['fim'] ?>
                    de <?= $paginacao['total'] ?> produtos
                </p>

                <?php if ($paginacao['totalPaginas'] > 1): ?>
                    <nav class="cprod-paginacao" aria-label="Paginação">
                        <?php if ($paginacao['paginaAtual'] > 1): ?>
                            <a class="cprod-pagina-link" href="<?= $e($urlPagina($paginacao['paginaAtual'] - 1)) ?>" aria-label="Página anterior">&#8249;</a>
                        <?php endif; ?>

                        <?php for ($i = $paginaInicial; $i <= $paginaFinal; $i++): ?>
                            <a class="cprod-pagina-link <?= $i === $paginacao['paginaAtual'] ? 'cprod-pagina-link--ativa' : '' ?>"
                                href="<?= $e($urlPagina($i)) ?>"
                                <?= $i === $paginacao['paginaAtual'] ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginacao['paginaAtual'] < $paginacao['totalPaginas']): ?>
                            <a class="cprod-pagina-link" href="<?= $e($urlPagina($paginacao['paginaAtual'] + 1)) ?>" aria-label="Próxima página">&#8250;</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </footer>

        <?php endif; ?>
    </section>
</main>

<script src="<?= URL ?>/js/consultar-produto.js" defer></script>
<?php include "../App/Views/footer.php"; ?>