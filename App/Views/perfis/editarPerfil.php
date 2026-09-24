<?php
include "../App/Views/menu.php";

/*
 * Variáveis recebidas do Controller:
 *   $dados['usuario'] -> objeto com o perfil já normalizado pelo Model
 *   $dados['csrf']    -> token de segurança do formulário
 *   $dados['erros']   -> array de mensagens de erro (opcional)
 *   $dados['sucesso'] -> mensagem de sucesso (opcional)
 */
$usuario = $dados['usuario'];
$funcao  = (int) ($_SESSION['usuario_funcao'] ?? 0);
$erros   = $dados['erros'] ?? [];
$sucesso = $dados['sucesso'] ?? '';

// Escapa qualquer valor antes de imprimir (proteção contra XSS)
$esc = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$foto = !empty($usuario->usua_foto) ? $usuario->usua_foto : 'avatar-padrao.png';

/*
 * Cada campo é um array. Para adicionar/remover campos, basta mexer aqui,
 * o HTML é gerado pelo loop mais abaixo.
 *   editavel = true  -> vai no POST (tem "name")
 *   editavel = false -> apenas exibição (readonly, sem "name")
 */
$campoNome = [
    'id' => 'perfil-nome',
    'name' => 'nome',
    'label' => 'Nome completo',
    'icone' => 'bi-person',
    'tipo' => 'text',
    'autocomplete' => 'name',
    'valor' => $usuario->usua_nome,
    'editavel' => true,
];
$campoEmail = [
    'id' => 'perfil-email',
    'name' => 'email',
    'label' => 'E-mail',
    'icone' => 'bi-envelope',
    'tipo' => 'email',
    'autocomplete' => 'email',
    'valor' => $usuario->usua_email,
    'editavel' => true,
];
$campoTelefone = [
    'id' => 'perfil-telefone',
    'name' => 'telefone',
    'label' => 'Telefone celular',
    'icone' => 'bi-telephone',
    'tipo' => 'tel',
    'autocomplete' => 'tel',
    'valor' => $usuario->telefone ?? '',
    'editavel' => true,
];

$campos = null;

if ($funcao === 1 || $funcao === 3) {
    $campos = [
        $campoNome,
        $campoTelefone,
        ['id' => 'perfil-siape', 'label' => 'SIAPE', 'icone' => 'bi-person-vcard', 'valor' => $usuario->usua_siap],
        ['id' => 'perfil-setor', 'label' => 'Setor', 'icone' => 'bi-building', 'valor' => $usuario->seto_nome],
        $campoEmail,
        ['id' => 'perfil-funcao', 'label' => 'Função', 'icone' => 'bi-person-badge', 'valor' => $usuario->func_nome],
    ];
} elseif ($funcao === 2) {
    $campos = [
        $campoNome,
        $campoEmail,
        ['id' => 'perfil-matricula', 'label' => 'Matrícula', 'icone' => 'bi-clipboard', 'valor' => $usuario->usua_matricula],
        ['id' => 'perfil-curso', 'label' => 'Curso', 'icone' => 'bi-graduation-cap', 'valor' => $usuario->turm_curso],
        ['id' => 'perfil-ano', 'label' => 'Ano', 'icone' => 'bi-calendar', 'valor' => $usuario->turm_ano . 'º ano'],
        $campoTelefone,
    ];
}
?>
<main class="perfil-editar">
    <header class="perfil-editar__cabecalho">
        <div class="perfil-editar__titulos">
            <div class="perfil-editar__linha-titulo">
                <span class="perfil-editar__marcador"></span>
                <h1 class="perfil-editar__titulo">Editar Perfil</h1>
            </div>
            <p class="perfil-editar__subtitulo">Edite suas informações de perfil.</p>
        </div>
        <img class="perfil-editar__logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <nav class="page-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/perfis/perfil">Perfil</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Editar Perfil
            </li>
        </ol>
    </nav>

    <?php if ($campos === null) { ?>
        <div class="perfil-editar__alerta perfil-editar__alerta--erro" role="alert">
            Não foi possível identificar o tipo do seu usuário. Faça login novamente.
        </div>
    <?php } else { ?>

        <?php if ($sucesso !== '') { ?>
            <div class="perfil-editar__alerta perfil-editar__alerta--sucesso" role="status">
                <?= $esc($sucesso) ?>
            </div>
        <?php } ?>

        <?php if (!empty($erros)) { ?>
            <div class="perfil-editar__alerta perfil-editar__alerta--erro" role="alert">
                <?php foreach ($erros as $erro) { ?>
                    <p class="perfil-editar__alerta-linha"><?= $esc($erro) ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <section class="perfil-editar__cartao">
            <div class="perfil-editar__cartao-cabecalho">
                <h2 class="perfil-editar__cartao-titulo">Meus dados</h2>
                <div class="perfil-editar__divisor"></div>
            </div>

            <form class="perfil-editar__formulario"
                action="<?= URL ?>/perfis/salvarAlteracoes"
                method="POST"
                enctype="multipart/form-data">

                <input type="hidden" name="csrf" value="<?= $esc($dados['csrf']) ?>">

                <div class="perfil-editar__layout">
                    <div class="perfil-editar__lateral">
                        <div class="perfil-editar__avatar">
                            <img class="perfil-editar__avatar-imagem"
                                id="perfil-avatar-previa"
                                src="<?= URL ?>/img/usuarios/<?= $esc($foto) ?>"
                                alt="Foto de perfil">

                            <label class="perfil-editar__avatar-botao"
                                for="perfil-foto"
                                title="Alterar foto"
                                aria-label="Alterar foto">✎</label>

                            <input class="perfil-editar__arquivo"
                                type="file"
                                id="perfil-foto"
                                name="foto"
                                accept="image/jpeg,image/png,image/webp"
                                hidden>
                        </div>

                        <div class="perfil-editar__acoes">
                            <button class="perfil-editar__botao perfil-editar__botao--primario" type="submit">
                                Salvar alterações
                            </button>
                            <a class="perfil-editar__botao perfil-editar__botao--secundario"
                                href="<?= URL ?>/perfis/alterarSenha">
                                Alterar senha
                            </a>
                        </div>
                    </div>

                    <div class="perfil-editar__campos">
                        <?php foreach ($campos as $campo) {
                            $editavel = !empty($campo['editavel']);
                        ?>
                            <div class="perfil-editar__campo">
                                <span class="perfil-editar__campo-icone">
                                    <i class="text-success bi <?= $e($campo['icone']) ?>"></i>
                                </span>
                                <div class="perfil-editar__campo-conteudo">
                                    <label class="perfil-editar__rotulo" for="<?= $esc($campo['id']) ?>">
                                        <?= $esc($campo['label']) ?>
                                    </label>

                                    <?php if ($editavel) { ?>
                                        <input class="perfil-editar__entrada"
                                            id="<?= $esc($campo['id']) ?>"
                                            name="<?= $esc($campo['name']) ?>"
                                            type="<?= $esc($campo['tipo']) ?>"
                                            autocomplete="<?= $esc($campo['autocomplete']) ?>"
                                            value="<?= $esc($campo['valor']) ?>"
                                            required>
                                    <?php } else { ?>
                                        <input class="perfil-editar__entrada perfil-editar__entrada--leitura"
                                            id="<?= $esc($campo['id']) ?>"
                                            type="text"
                                            value="<?= $esc($campo['valor']) ?>"
                                            readonly>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </section>
    <?php } ?>
</main>

<script src="<?= URL ?>/js/perfil-editar.js"></script>
<?php include "../App/Views/footer.php"; ?>