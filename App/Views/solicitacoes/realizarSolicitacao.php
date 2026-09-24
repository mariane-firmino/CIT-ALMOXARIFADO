<?php
include "../App/Views/menu.php";

/**
 * Dados esperados do Controller (Solicitacoes::nova / Solicitacoes::realizar):
 *  $dados['produtos']    array de objetos (prod_id, prod_nome, prod_foto, prod_quantidade, cate_nome)
 *  $dados['quantidades'] array [prod_id => quantidade] com o que o usuário já digitou (opcional)
 *  $dados['erro']        string|null - mensagem de erro
 */

$e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$produtos    = $dados['produtos'];
$quantidades = $dados['quantidades'] ?? [];
$erro        = $dados['erro'] ?? null;
?>
<main class="rsol-pagina">

    <header class="rsol-cabecalho">
        <div class="rsol-cabecalho-texto">
            <div class="rsol-titulo-linha">
                <span class="rsol-titulo-marca"></span>
                <h1 class="rsol-titulo">Realizar solicitação</h1>
            </div>
            <p class="rsol-subtitulo">Escolha os produtos que deseja solicitar.</p>
        </div>
        <img class="rsol-logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <?php if (!empty($erro)): ?>
        <div class="rsol-alerta rsol-alerta--erro" role="alert"><?= $e($erro) ?></div>
    <?php endif; ?>

    <section class="rsol-painel">

        <header class="rsol-painel-cabecalho">
            <h2 class="rsol-painel-titulo">Solicitação de produto(s)</h2>
            <a class="rsol-fechar" href="<?= URL ?>/produtos/consultarProduto" aria-label="Voltar para a consulta de produtos">
                <img class="rsol-fechar-icone" src="<?= URL ?>/img/fechar.png" alt="" aria-hidden="true">
            </a>
        </header>

        <form class="rsol-formulario js-rsol-formulario" action="<?= URL ?>/solicitacoes/realizar" method="POST">

            <div class="rsol-lista">
                <?php foreach ($produtos as $produto): ?>
                    <?php
                    $id         = (int) $produto->prod_id;
                    $estoque    = (int) $produto->prod_quantidade;
                    $foto       = !empty($produto->prod_foto) ? $produto->prod_foto : 'sem-foto.png';
                    $quantidade = (int) ($quantidades[$id] ?? 1);
                    ?>
                    <article class="rsol-item js-rsol-item">
                        <img
                            class="rsol-item-foto"
                            src="<?= URL ?>/img/produtos/<?= $e($foto) ?>"
                            alt="<?= $e($produto->prod_nome) ?>"
                            loading="lazy">

                        <div class="rsol-item-info">
                            <h3 class="rsol-item-nome"><?= $e($produto->prod_nome) ?></h3>
                            <p class="rsol-item-categoria"><?= $e($produto->cate_nome) ?></p>
                        </div>

                        <div class="rsol-quantidade">
                            <label class="rsol-quantidade-rotulo" for="rsol-quantidade-<?= $id ?>">Quantidade</label>
                            <input
                                class="rsol-quantidade-campo"
                                id="rsol-quantidade-<?= $id ?>"
                                type="number"
                                name="quantidade[<?= $id ?>]"
                                min="1"
                                max="<?= $estoque ?>"
                                value="<?= $quantidade ?>"
                                inputmode="numeric"
                                required>
                            <span class="rsol-quantidade-estoque">Em estoque: <?= $estoque ?></span>
                        </div>

                        <input type="hidden" name="produtos[]" value="<?= $id ?>">

                        <button class="rsol-remover js-rsol-remover" type="button"
                            aria-label="Remover <?= $e($produto->prod_nome) ?> da solicitação">
                            Remover
                        </button>
                    </article>
                <?php endforeach; ?>
            </div>

            <p class="rsol-vazio js-rsol-vazio" hidden>
                Nenhum produto na solicitação.
                <a class="rsol-vazio-link" href="<?= URL ?>/produtos/consultarProduto">Escolher produtos</a>
            </p>

            <button class="rsol-enviar js-rsol-enviar" type="submit">Enviar</button>
        </form>
    </section>
</main>

<script src="<?= URL ?>/js/realizar-solicitacao.js" defer></script>
<?php include "../App/Views/footer.php"; ?>