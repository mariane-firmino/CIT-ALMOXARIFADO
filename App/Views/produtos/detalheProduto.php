<?php

/**
 * View: produtos/detalhesProduto
 *
 * Espera em $dados:
 *  - produto (objeto): prod_id, prod_nome, prod_descricao, prod_foto,
 *    prod_quantidade, prod_estoque_minimo, prod_status, tem_qrcode,
 *    cate_nome, loca_nome
 */

// Escapa qualquer saída dinâmica (evita XSS). Se já tiver um helper global, use-o no lugar.
$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$produto = $dados['produto'];

$urlFoto     = URL . '/img/produtos/' . rawurlencode((string) $produto->prod_foto);
$urlQr       = URL . '/produtos/qrcode/' . (int) $produto->prod_id;
$urlEtiqueta = URL . '/produtos/etiqueta/' . (int) $produto->prod_id;

$status       = trim((string) $produto->prod_status);
$classeStatus = ($status === 'Estoque baixo') ? 'detprod-status-baixo' : 'detprod-status-ok';
?>
<?php include "../App/Views/menu.php"; ?>

<main class="detprod-container">

    <header class="detprod-cabecalho">
        <div class="detprod-titulo-grupo">
            <div class="detprod-titulo-marca"></div>
            <div>
                <h1 class="detprod-titulo">Detalhes do Produto</h1>
                <p class="detprod-subtitulo">Visualize as informações detalhadas do produto.</p>
            </div>
        </div>

        <img src="<?= URL ?>/img/logo-sacit.png" alt="SACIT" class="detprod-logo">
    </header>

    <?php
    $funcao = $_SESSION['usuario_funcao'] ?? null;

    if ($funcao == 1 || $funcao == 2) {
        $linkBreadcrumb = URL . '/produtos/estoque';
        $textoBreadcrumb = 'Controlar Estoque';
    } elseif ($funcao == 3) {
        $linkBreadcrumb = URL . '/solicitacoes/consultarProduto';
        $textoBreadcrumb = 'Consultar Produto';
    } else {
        $linkBreadcrumb = '#';
        $textoBreadcrumb = 'Início';
    }
    ?>

    <nav class="page-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">

            <li class="breadcrumb-item">
                <a href="<?= $linkBreadcrumb ?>">
                    <?= $textoBreadcrumb ?>
                </a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">
                Ver Detalhes
            </li>

        </ol>
    </nav>

    <section class="detprod-painel">
        <div class="detprod-cartao">

            <div class="detprod-coluna-esq">
                <div class="detprod-imagem-box">
                    <img
                        src="<?= $e($urlFoto) ?>"
                        alt="Foto do produto <?= $e($produto->prod_nome) ?>"
                        class="detprod-imagem">
                </div>

                <div class="detprod-qr">
                    <div class="detprod-qr-titulo">QR / Etiqueta</div>

                    <?php if (!empty($produto->tem_qrcode)): ?>
                        <img
                            src="<?= $e($urlQr) ?>"
                            alt="QR Code do produto <?= $e($produto->prod_nome) ?>"
                            class="detprod-qr-imagem"
                            width="160"
                            height="160">

                        <a
                            href="<?= $e($urlEtiqueta) ?>"
                            target="_blank"
                            rel="noopener"
                            class="detprod-btn">
                            Imprimir etiqueta
                        </a>
                    <?php else: ?>
                        <p class="detprod-qr-vazio">Este produto ainda não tem QR Code.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detprod-coluna-dir">
                <div class="detprod-topo">
                    <h2 class="detprod-topo-titulo">Detalhes do Produto</h2>

                    <a
                        href="<?= URL ?>/produtos/estoque"
                        class="detprod-fechar"
                        aria-label="Fechar e voltar ao estoque">
                        <img src="<?= URL ?>/img/fechar.png" alt="" class="detprod-fechar-icone">
                    </a>
                </div>

                <div class="detprod-bloco">
                    <dl class="detprod-info">
                        <div class="detprod-info-linha">
                            <dt class="detprod-info-rotulo">Nome:</dt>
                            <dd class="detprod-info-valor"><?= $e($produto->prod_nome) ?></dd>
                        </div>

                        <div class="detprod-info-linha">
                            <dt class="detprod-info-rotulo">Código:</dt>
                            <dd class="detprod-info-valor"><?= (int) $produto->prod_id ?></dd>
                        </div>

                        <div class="detprod-info-linha">
                            <dt class="detprod-info-rotulo">Categoria:</dt>
                            <dd class="detprod-info-valor"><?= $e($produto->cate_nome) ?></dd>
                        </div>

                        <div class="detprod-info-linha">
                            <dt class="detprod-info-rotulo">Quantidade:</dt>
                            <dd class="detprod-info-valor"><?= (int) $produto->prod_quantidade ?></dd>
                        </div>

                        <div class="detprod-info-linha">
                            <dt class="detprod-info-rotulo">Estoque mínimo:</dt>
                            <dd class="detprod-info-valor"><?= (int) $produto->prod_estoque_minimo ?></dd>
                        </div>

                        <div class="detprod-info-linha">
                            <dt class="detprod-info-rotulo">Localização:</dt>
                            <dd class="detprod-info-valor"><?= $e($produto->loca_nome) ?></dd>
                        </div>

                        <?php if ($status !== ''): ?>
                            <div class="detprod-info-linha">
                                <dt class="detprod-info-rotulo">Status:</dt>
                                <dd class="detprod-info-valor">
                                    <span class="detprod-status <?= $classeStatus ?>"><?= $e($status) ?></span>
                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>

                    <div class="detprod-descricao">
                        <h3 class="detprod-descricao-titulo">Descrição</h3>
                        <p class="detprod-descricao-texto"><?= nl2br($e($produto->prod_descricao)) ?></p>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<?php include "../App/Views/footer.php"; ?>