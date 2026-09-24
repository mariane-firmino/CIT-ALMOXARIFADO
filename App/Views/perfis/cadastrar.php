<?php

/**
 * View: cadastro de usuário (Servidor, Estagiário ou Coordenador).
 *
 * Variáveis recebidas do Controller:
 *   $perfil          string  Slug do perfil atual (servidor|estagiario|coordenador)
 *   $tiposDePerfil   array   ['slug' => [rótulo, ícone]]
 *   $campos          array   Campos do perfil atual (definidos no Model)
 *   $antigos         array   Valores digitados (para repopular em caso de erro)
 *   $erros           array   ['campo' => 'mensagem']; a chave '_geral' é o erro global
 *   $sucesso         string  Mensagem de sucesso (flash) ou ''
 *   $csrf            string  Token anti-CSRF
 *   $e               callable
 */

[$rotuloPerfil] = $tiposDePerfil[$perfil];
include "../App/Views/menu.php";
?>

<main class="cit-cadastro">
    <header class="cit-cadastro__cabecalho">
        <div>
            <h1 class="cit-cadastro__titulo">Cadastrar <?= $e($rotuloPerfil) ?></h1>
            <p class="cit-cadastro__subtitulo">Cadastre novos perfis de usuário.</p>
        </div>
        <img class="cit-cadastro__logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <nav class="page-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/perfis/gerenciarPerfis">Gerenciar Perfil</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Cadastrar Produto
            </li>
        </ol>
    </nav>

    <section class="cit-cadastro__cartao">

        <nav class="cit-cadastro__abas" aria-label="Tipo de perfil">
            <?php foreach ($tiposDePerfil as $slug => [$textoPerfil, $iconePerfil]): ?>
                <a class="cit-cadastro__aba<?= $slug === $perfil ? ' cit-cadastro__aba--ativa' : '' ?>"
                    href="<?= URL ?>/perfis/cadastrar?perfil=<?= $slug ?>"
                    <?= $slug === $perfil ? 'aria-current="page"' : '' ?>>
                    <i class="bi <?= $iconePerfil ?>" aria-hidden="true"></i>
                    <?= $e($textoPerfil) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="cit-cadastro__area">
            <a class="cit-cadastro__fechar" href="<?= URL ?>/usuarios" title="Fechar" aria-label="Fechar">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </a>

            <h2 class="cit-cadastro__titulo-form">Cadastro de <?= $e($rotuloPerfil) ?></h2>

            <?php if ($sucesso): ?>
                <p class="cit-cadastro__alerta cit-cadastro__alerta--sucesso" role="status"><?= $e($sucesso) ?></p>
            <?php endif; ?>

            <?php if (!empty($erros['_geral'])): ?>
                <p class="cit-cadastro__alerta cit-cadastro__alerta--erro" role="alert"><?= $e($erros['_geral']) ?></p>
            <?php endif; ?>

            <form class="cit-cadastro__form" method="post"
                action="<?= URL ?>/perfis/cadastrar?perfil=<?= $e($perfil) ?>">

                <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">

                <div class="cit-cadastro__grade">
                    <?php foreach ($campos as $nome => $campo):
                        $idCampo   = 'cit-cadastro-' . $nome;
                        $idErro    = $idCampo . '-erro';
                        $mensagem  = $erros[$nome] ?? '';
                        $valorAtual = (string) ($antigos[$nome] ?? '');
                        $atributos = 'class="cit-cadastro__entrada" id="' . $idCampo . '" name="' . $nome . '" required'
                            . ($mensagem ? ' aria-invalid="true" aria-describedby="' . $idErro . '"' : '')
                            . (isset($campo['autocomplete']) ? ' autocomplete="' . $campo['autocomplete'] . '"' : '');
                    ?>
                        <div class="cit-cadastro__campo<?= $mensagem ? ' cit-cadastro__campo--erro' : '' ?>">
                            <label class="cit-cadastro__rotulo" for="<?= $idCampo ?>"><?= $e($campo['rotulo']) ?></label>

                            <?php if ($campo['tipo'] === 'select'): ?>
                                <select <?= $atributos ?>>
                                    <option value=""><?= $e($campo['placeholder']) ?></option>
                                    <?php foreach ($campo['opcoes'] as $valor => $rotuloOpcao): ?>
                                        <option value="<?= $e($valor) ?>" <?= (string) $valor === $valorAtual ? 'selected' : '' ?>>
                                            <?= $e($rotuloOpcao) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input <?= $atributos ?>
                                    type="<?= $campo['tipo'] ?>"
                                    placeholder="<?= $e($campo['placeholder']) ?>"
                                    <?= isset($campo['inputmode']) ? 'inputmode="' . $campo['inputmode'] . '"' : '' ?>
                                    <?= isset($campo['maxlength']) ? 'maxlength="' . $campo['maxlength'] . '"' : '' ?>
                                    <?= $campo['tipo'] !== 'password' ? 'value="' . $e($valorAtual) . '"' : '' ?>>
                            <?php endif; ?>

                            <?php if ($mensagem): ?>
                                <span class="cit-cadastro__erro" id="<?= $idErro ?>"><?= $e($mensagem) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cit-cadastro__acoes">
                    <button class="cit-cadastro__botao" type="submit">Cadastrar</button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php include "../App/Views/footer.php"; ?>