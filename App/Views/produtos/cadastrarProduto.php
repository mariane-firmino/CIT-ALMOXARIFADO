<?php

/**
 * View: produtos/cadastrarProduto
 *
 * Espera em $dados:
 *  - categorias, localizacoes (arrays de objetos)
 *  - form   (valores para repopular: nome, categoria, localizacao, quantidade,
 *            estoque_minimo, descricao)
 *  - erros  (array de strings; vazio quando não há erro)
 *  - csrf   (token do formulário)
 */

// Escapa qualquer saída dinâmica (evita XSS). Se já tiver um helper global, use-o no lugar.
$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$form  = $dados['form'];
$erros = $dados['erros'];
?>
<?php include "../App/Views/menu.php"; ?>

<main class="cadprod-container">

    <header class="cadprod-cabecalho">
        <div class="cadprod-titulo-grupo">
            <div class="cadprod-titulo-marca"></div>
            <div>
                <h1 class="cadprod-titulo">Cadastrar Produto</h1>
                <p class="cadprod-subtitulo">Cadastre novos produtos no estoque.</p>
            </div>
        </div>

        <img src="<?= URL ?>/img/logo-sacit.png" alt="SACIT" class="cadprod-logo">
    </header>

    <nav class="page-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/produtos/estoque">Controlar Estoque</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Cadastrar Produto
            </li>
        </ol>
    </nav>

    <section class="cadprod-painel">

        <div class="cadprod-painel-topo">
            <h2 class="cadprod-painel-titulo">Cadastro de Produto</h2>

            <a href="<?= URL ?>/produtos/estoque" class="cadprod-fechar" aria-label="Fechar e voltar ao estoque">
                <img src="<?= URL ?>/img/fechar.png" alt="" class="cadprod-fechar-icone">
            </a>
        </div>

        <?php if (!empty($erros)): ?>
            <div class="cadprod-erros" role="alert">
                <p class="cadprod-erros-titulo">Corrija os itens abaixo:</p>
                <ul class="cadprod-erros-lista">
                    <?php foreach ($erros as $erro): ?>
                        <li class="cadprod-erros-item"><?= $e($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form
            class="cadprod-form"
            action="<?= URL ?>/produtos/salvarProduto"
            method="POST"
            enctype="multipart/form-data">

            <input type="hidden" name="csrf" value="<?= $e($dados['csrf']) ?>">

            <div class="cadprod-upload-bloco">
                <label class="cadprod-upload" id="cadprod-upload" for="cadprod-imagem">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="3"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <path d="M21 15l-5-5L5 21"></path>
                    </svg>

                    <span class="cadprod-upload-texto">
                        Clique para escolher a imagem<br>JPG, PNG ou WEBP, até 2 MB
                    </span>

                    <input
                        type="file"
                        id="cadprod-imagem"
                        name="imagem"
                        class="cadprod-upload-arquivo"
                        accept="image/jpeg,image/png,image/webp"
                        required>
                </label>
            </div>

            <div class="cadprod-grupo">
                <label class="cadprod-rotulo" for="cadprod-nome">Nome</label>
                <input
                    id="cadprod-nome"
                    name="nome"
                    type="text"
                    class="cadprod-campo"
                    placeholder="Digite o nome do produto..."
                    maxlength="100"
                    value="<?= $e($form['nome']) ?>"
                    required>
            </div>

            <div class="cadprod-linha">
                <div class="cadprod-grupo">
                    <label class="cadprod-rotulo" for="cadprod-categoria">Categoria</label>
                    <select id="cadprod-categoria" name="categoria" class="cadprod-campo" required>
                        <option value="">Selecionar categoria</option>
                        <?php foreach ($dados['categorias'] as $categoria): ?>
                            <option
                                value="<?= (int) $categoria->cate_id ?>"
                                <?= ((string) $form['categoria'] === (string) $categoria->cate_id) ? 'selected' : '' ?>>
                                <?= $e($categoria->cate_nome) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cadprod-grupo">
                    <label class="cadprod-rotulo" for="cadprod-localizacao">Localização</label>
                    <select id="cadprod-localizacao" name="localizacao" class="cadprod-campo" required>
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
            </div>

            <div class="cadprod-linha">
                <div class="cadprod-grupo">
                    <label class="cadprod-rotulo" for="cadprod-quantidade">Quantidade</label>
                    <input
                        id="cadprod-quantidade"
                        name="quantidade"
                        type="number"
                        class="cadprod-campo"
                        placeholder="0"
                        min="0"
                        step="1"
                        inputmode="numeric"
                        value="<?= $e($form['quantidade']) ?>"
                        required>
                </div>

                <div class="cadprod-grupo">
                    <label class="cadprod-rotulo" for="cadprod-minimo">Estoque mínimo</label>
                    <input
                        id="cadprod-minimo"
                        name="estoque_minimo"
                        type="number"
                        class="cadprod-campo"
                        min="0"
                        step="1"
                        inputmode="numeric"
                        aria-describedby="cadprod-minimo-ajuda"
                        value="<?= $e($form['estoque_minimo']) ?>"
                        required>
                    <p class="cadprod-ajuda" id="cadprod-minimo-ajuda">
                        Abaixo ou igual a esse valor, o produto fica com "Estoque baixo".
                    </p>
                </div>
            </div>

            <div class="cadprod-grupo">
                <label class="cadprod-rotulo" for="cadprod-descricao">Descrição</label>
                <textarea
                    id="cadprod-descricao"
                    name="descricao"
                    class="cadprod-campo cadprod-campo-area"
                    rows="5"
                    maxlength="1000"
                    placeholder="Descreva o produto..."
                    required><?= $e($form['descricao']) ?></textarea>
            </div>

            <button type="submit" class="cadprod-btn">Cadastrar Produto</button>
        </form>
    </section>
</main>

<script>
    // Pré-visualização da imagem escolhida + checagem de tamanho antes do envio
    (function() {
        var TAMANHO_MAX = 2 * 1024 * 1024; // 2 MB (o servidor confere de novo)
        var entrada = document.getElementById('cadprod-imagem');
        var previa = document.getElementById('cadprod-previa');
        var bloco = document.getElementById('cadprod-upload');
        var iconeOriginal = previa.getAttribute('src');
        var urlAtual = null;

        function limpar() {
            if (urlAtual) {
                URL.revokeObjectURL(urlAtual);
                urlAtual = null;
            }
            previa.setAttribute('src', iconeOriginal);
            bloco.classList.remove('cadprod-upload-preenchido');
        }

        entrada.addEventListener('change', function() {
            entrada.setCustomValidity('');
            limpar();

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
            bloco.classList.add('cadprod-upload-preenchido');
        });
    })();
</script>

<?php include "../App/Views/footer.php"; ?>