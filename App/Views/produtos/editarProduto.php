<?php

/**
 * View: produtos/editarProduto
 *
 * Espera em $dados:
 *  - produto      (objeto): prod_id, prod_nome, prod_foto, cate_nome
 *  - localizacoes (array de objetos: loca_id, loca_nome)
 *  - form         (valores para repopular: localizacao, quantidade,
 *                  estoque_minimo, descricao)
 *  - erros        (array de strings; vazio quando não há erro)
 *  - csrf         (token do formulário)
 */

// Escapa qualquer saída dinâmica (evita XSS). Se já tiver um helper global, use-o no lugar.
$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$produto = $dados['produto'];
$form    = $dados['form'];
$erros   = $dados['erros'];

$urlFoto = !empty($produto->prod_foto)
    ? URL . '/img/produtos/' . rawurlencode((string) $produto->prod_foto)
    : URL . '/img/adicionar-img.png';
?>
<?php include "../App/Views/menu.php"; ?>

<main class="editprod-container">

    <header class="editprod-cabecalho">
        <div class="editprod-titulo-grupo">
            <div class="editprod-titulo-marca"></div>
            <div>
                <h1 class="editprod-titulo">Editar Produto</h1>
                <p class="editprod-subtitulo">Edite as informações do produto.</p>
            </div>
        </div>

        <img src="<?= URL ?>/img/logo-sacit.png" alt="SACIT" class="editprod-logo">
    </header>

    <nav class="page-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/produtos/controlarProduto">Controlar Produto</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Editar Produto
            </li>
        </ol>
    </nav>

    <section class="editprod-painel">

        <div class="editprod-painel-topo">
            <h2 class="editprod-painel-titulo">Editar Produto</h2>

            <a href="<?= URL ?>/produtos/estoque" class="editprod-fechar" aria-label="Fechar e voltar ao estoque">
                <img src="<?= URL ?>/img/fechar.png" alt="" class="editprod-fechar-icone">
            </a>
        </div>

        <?php if (!empty($erros)): ?>
            <div class="editprod-erros" role="alert">
                <p class="editprod-erros-titulo">Corrija os itens abaixo:</p>
                <ul class="editprod-erros-lista">
                    <?php foreach ($erros as $erro): ?>
                        <li class="editprod-erros-item"><?= $e($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form
            class="editprod-form"
            action="<?= URL ?>/produtos/atualizarProduto"
            method="POST"
            enctype="multipart/form-data">

            <input type="hidden" name="csrf" value="<?= $e($dados['csrf']) ?>">
            <input type="hidden" name="id" value="<?= (int) $produto->prod_id ?>">

            <div class="editprod-imagem-bloco">
                <div class="editprod-imagem-caixa">
                    <img
                        src="<?= $e($urlFoto) ?>"
                        id="editprod-previa"
                        class="editprod-imagem"
                        alt="Imagem do produto <?= $e($produto->prod_nome) ?>">
                </div>

                <label class="editprod-btn-secundario" for="editprod-imagem">
                    Substituir imagem
                    <input
                        type="file"
                        id="editprod-imagem"
                        name="imagem"
                        class="editprod-arquivo"
                        accept="image/jpeg,image/png,image/webp">
                </label>

                <p class="editprod-imagem-ajuda">Opcional. JPG, PNG ou WEBP, até 2 MB.</p>
            </div>

            <div class="editprod-linha">
                <div class="editprod-grupo">
                    <label class="editprod-rotulo" for="editprod-nome">Nome</label>
                    <input
                        id="editprod-nome"
                        type="text"
                        class="editprod-campo editprod-campo-leitura"
                        value="<?= $e($produto->prod_nome) ?>"
                        readonly>
                </div>

                <div class="editprod-grupo">
                    <label class="editprod-rotulo" for="editprod-categoria">Categoria</label>
                    <input
                        id="editprod-categoria"
                        type="text"
                        class="editprod-campo editprod-campo-leitura"
                        value="<?= $e($produto->cate_nome) ?>"
                        readonly>
                </div>
            </div>

            <div class="editprod-grupo">
                <label class="editprod-rotulo" for="editprod-localizacao">Localização</label>
                <select id="editprod-localizacao" name="localizacao" class="editprod-campo" required>
                    <option value="">Selecionar localização</option>
                    <?php foreach ($dados['localizacoes'] as $localizacao): ?>
                        <option
                            value="<?= (int) $localizacao->loca_id ?>"
                            <?= ((string) $form['localizacao'] === (string) $localizacao->loca_id) ? 'selected' : '' ?>>
                            <?= $e($localizacao->loca_nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="editprod-linha">
                <div class="editprod-grupo">
                    <label class="editprod-rotulo" for="editprod-quantidade">Quantidade</label>
                    <input
                        id="editprod-quantidade"
                        name="quantidade"
                        type="number"
                        class="editprod-campo"
                        min="0"
                        step="1"
                        inputmode="numeric"
                        value="<?= $e($form['quantidade']) ?>"
                        required>
                </div>

                <div class="editprod-grupo">
                    <label class="editprod-rotulo" for="editprod-minimo">Estoque mínimo</label>
                    <input
                        id="editprod-minimo"
                        name="estoque_minimo"
                        type="number"
                        class="editprod-campo"
                        min="0"
                        step="1"
                        inputmode="numeric"
                        aria-describedby="editprod-minimo-ajuda"
                        value="<?= $e($form['estoque_minimo']) ?>"
                        required>
                    <p class="editprod-ajuda" id="editprod-minimo-ajuda">
                        Abaixo ou igual a esse valor, o produto fica com "Estoque baixo".
                    </p>
                </div>
            </div>

            <div class="editprod-grupo">
                <label class="editprod-rotulo" for="editprod-descricao">Descrição</label>
                <textarea
                    id="editprod-descricao"
                    name="descricao"
                    class="editprod-campo editprod-campo-area"
                    rows="4"
                    maxlength="1000"
                    placeholder="Descreva o produto..."
                    required><?= $e($form['descricao']) ?></textarea>
            </div>

            <div class="editprod-acoes">
                <a href="<?= URL ?>/produtos/estoque" class="editprod-btn editprod-btn-cancelar">Cancelar</a>
                <button type="submit" class="editprod-btn editprod-btn-salvar">Salvar alterações</button>
            </div>
        </form>
    </section>
</main>

<script>
    // Pré-visualização da nova imagem + checagem de tamanho antes do envio
    (function() {
        var TAMANHO_MAX = 2 * 1024 * 1024; // 2 MB (o servidor confere de novo)
        var entrada = document.getElementById('editprod-imagem');
        var previa = document.getElementById('editprod-previa');
        var imagemOriginal = previa.getAttribute('src');
        var urlAtual = null;

        function restaurar() {
            if (urlAtual) {
                URL.revokeObjectURL(urlAtual);
                urlAtual = null;
            }
            previa.setAttribute('src', imagemOriginal);
        }

        entrada.addEventListener('change', function() {
            entrada.setCustomValidity('');
            restaurar();

            var arquivo = entrada.files[0];
            if (!arquivo) {
                return;
            }

            if (arquivo.size > TAMANHO_MAX) {
                entrada.value = '';
                entrada.setCustomValidity('A imagem deve ter no máximo 2 MB.');
                entrada.reportValidity();
                return;
            }

            urlAtual = URL.createObjectURL(arquivo);
            previa.setAttribute('src', urlAtual);
        });
    })();
</script>

<?php include "../App/Views/footer.php"; ?>