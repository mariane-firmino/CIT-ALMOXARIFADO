<?php
include "../App/Views/menu.php";

/*
 * Variáveis recebidas do Controller:
 *   $dados['csrf']    -> token de segurança do formulário
 *   $dados['erros']   -> array de mensagens de erro (opcional)
 *   $dados['sucesso'] -> mensagem de sucesso (opcional)
 */
$erros   = $dados['erros'] ?? [];
$sucesso = $dados['sucesso'] ?? '';

// Escapa qualquer valor antes de imprimir (proteção contra XSS)
$esc = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

// Os 3 campos são iguais, só mudam os dados: o HTML é gerado pelo loop abaixo.
$campos = [
    [
        'id' => 'senha-atual',
        'name' => 'senha_atual',
        'label' => 'Senha atual',
        'placeholder' => 'Digite sua senha atual',
        'autocomplete' => 'current-password',
    ],
    [
        'id' => 'senha-nova',
        'name' => 'nova_senha',
        'label' => 'Nova senha',
        'placeholder' => 'Mínimo de 8 caracteres',
        'autocomplete' => 'new-password',
        'minlength' => 8,
    ],
    [
        'id' => 'senha-confirmacao',
        'name' => 'confirmar_senha',
        'label' => 'Confirme a nova senha',
        'placeholder' => 'Repita a nova senha',
        'autocomplete' => 'new-password',
        'minlength' => 8,
    ],
];
?>
<main class="senha-editar">
    <header class="senha-editar__cabecalho">
        <div class="senha-editar__titulos">
            <div class="senha-editar__linha-titulo">
                <span class="senha-editar__marcador"></span>
                <h1 class="senha-editar__titulo">Alterar Senha</h1>
            </div>
            <p class="senha-editar__subtitulo">Bem-vindo(a), <?= $esc($_SESSION['usuario_nome'] ?? '') ?>!</p>
        </div>
        <img class="senha-editar__logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <nav class="page-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/perfis/perfil">Perfil</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Alterar Senha
            </li>
        </ol>
    </nav>

    <?php if ($sucesso !== '') { ?>
        <div class="senha-editar__alerta senha-editar__alerta--sucesso" role="status">
            <?= $esc($sucesso) ?>
        </div>
    <?php } ?>

    <?php if (!empty($erros)) { ?>
        <div class="senha-editar__alerta senha-editar__alerta--erro" role="alert">
            <?php foreach ($erros as $erro) { ?>
                <p class="senha-editar__alerta-linha"><?= $esc($erro) ?></p>
            <?php } ?>
        </div>
    <?php } ?>

    <section class="senha-editar__cartao">
        <div class="senha-editar__cartao-cabecalho">
            <h2 class="senha-editar__cartao-titulo">Meus dados</h2>
            <div class="senha-editar__divisor"></div>
        </div>

        <form class="senha-editar__formulario"
            id="senha-formulario"
            action="<?= URL ?>/perfis/salvarSenha"
            method="POST"
            data-icone-oculta="<?= URL ?>/img/olhoAberto.png"
            data-icone-visivel="<?= URL ?>/img/olhoFechado.png">

            <input type="hidden" name="csrf" value="<?= $esc($dados['csrf']) ?>">

            <?php foreach ($campos as $campo) { ?>
                <div class="senha-editar__grupo">
                    <label class="senha-editar__rotulo" for="<?= $esc($campo['id']) ?>">
                        <?= $esc($campo['label']) ?>
                    </label>

                    <div class="senha-editar__envoltorio">
                        <input class="senha-editar__entrada"
                            id="<?= $esc($campo['id']) ?>"
                            name="<?= $esc($campo['name']) ?>"
                            type="password"
                            placeholder="<?= $esc($campo['placeholder']) ?>"
                            autocomplete="<?= $esc($campo['autocomplete']) ?>"
                            <?= isset($campo['minlength']) ? 'minlength="' . (int) $campo['minlength'] . '"' : '' ?>
                            required>

                        <button class="senha-editar__alternar"
                            type="button"
                            data-alvo="<?= $esc($campo['id']) ?>"
                            aria-label="Mostrar senha"
                            aria-pressed="false">
                        </button>
                    </div>
                </div>
            <?php } ?>

            <div class="senha-editar__acoes">
                <a class="senha-editar__botao senha-editar__botao--secundario"
                    href="<?= URL ?>/perfis/editarPerfil">
                    Voltar
                </a>
                <button class="senha-editar__botao senha-editar__botao--primario" type="submit">
                    Salvar alterações
                </button>
            </div>
        </form>
    </section>
</main>

<script src="<?= URL ?>/js/senha-editar.js"></script>
<?php include "../App/Views/footer.php"; ?>