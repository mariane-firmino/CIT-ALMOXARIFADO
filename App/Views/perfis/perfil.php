<?php
include "../App/Views/menu.php";

/** Escapa a saída (evita XSS) */
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

/** Valor da sessão com fallback visível quando o campo está vazio */
$s = static fn(string $chave) => trim((string) ($_SESSION[$chave] ?? '')) !== ''
    ? $_SESSION[$chave]
    : '—';

$funcaoId = (int) ($_SESSION['usuario_funcao'] ?? 0);

/**
 * Mapa de funções — conferir com a tabela `funcao`:
 * 1 = Coordenador | 2 = Estagiário | 3 = Servidor
 */
$funcoes = [
    1 => 'Coordenador',
    2 => 'Estagiário',
    3 => 'Servidor',
];

/** Estagiário usa os dados acadêmicos; os demais usam os dados funcionais */
$ehEstagiario = $funcaoId === 2;

if ($ehEstagiario) {
    $campos = [
        ['icone' => 'bi-person',        'label' => 'Nome completo', 'valor' => $s('usuario_nome')],
        ['icone' => 'bi-envelope',       'label' => 'E-mail',        'valor' => $s('usuario_email')],
        ['icone' => 'bi-person-vcard',  'label' => 'Matrícula',     'valor' => $s('usuario_matricula')],
        ['icone' => 'bi-buildings',            'label' => 'Curso',         'valor' => $s('usuario_curso')],
        ['icone' => 'bi-calendar',              'label' => 'Ano',           'valor' => $s('usuario_ano')],
        ['icone' => 'bi-telephone',   'label' => 'Telefone',      'valor' => $s('usuario_telefone')],
    ];
} else {
    $campos = [
        ['icone' => 'bi-person',        'label' => 'Nome completo', 'valor' => $s('usuario_nome')],
        ['icone' => 'bi-envelope',       'label' => 'E-mail',        'valor' => $s('usuario_email')],
        ['icone' => 'bi-telephone',   'label' => 'Telefone',      'valor' => $s('usuario_telefone')],
        ['icone' => 'bi-person-vcard',  'label' => 'SIAPE',         'valor' => $s('usuario_siap')],
        ['icone' => 'bi-buildings',      'label' => 'Setor',         'valor' => $s('usuario_setor')],
        ['icone' => 'bi-diagram-3',     'label' => 'Função',        'valor' => $funcoes[$funcaoId] ?? '—'],
    ];
}

$foto = trim((string) ($_SESSION['usuario_foto'] ?? ''));
$foto = $foto !== '' ? $foto : 'avatar-padrao.png';
?>

<main class="perfil-page">

    <header class="perfil-page__header">
        <div class="perfil-page__title-group">
            <div class="perfil-page__title-row">
                <span class="perfil-page__title-mark"></span>
                <h1 class="perfil-page__title">Meu Perfil</h1>
            </div>
            <p class="perfil-page__subtitle">
                Visualize e gerencie as informações da sua conta.
            </p>
        </div>

        <img
            src="<?= URL ?>/img/logo-sacit.png"
            alt="SACIT"
            class="perfil-page__logo">
    </header>

    <?php if (!isset($funcoes[$funcaoId])): ?>

        <section class="perfil-card">
            <p class="perfil-erro">
                Não foi possível identificar sua função no sistema.
                Entre em contato com a coordenação.
            </p>
        </section>

    <?php else: ?>

        <section class="perfil-card">

            <h2 class="perfil-card__titulo">Meus Dados</h2>

            <div class="perfil-card__grid">

                <aside class="perfil-lateral">

                    <img
                        src="<?= URL ?>/img/usuarios/<?= $e($foto) ?>"
                        alt="Foto de <?= $e($s('usuario_nome')) ?>"
                        class="perfil-lateral__avatar"
                        onerror="this.onerror=null;this.src='<?= URL ?>/img/user/avatar-padrao.png'">

                    <div class="perfil-lateral__acoes">
                        <a
                            href="<?= URL ?>/perfis/editarPerfil"
                            class="perfil-btn perfil-btn--primario">
                            Editar perfil
                        </a>

                        <a
                            href="<?= URL ?>/perfis/alterarSenha"
                            class="perfil-btn perfil-btn--secundario">
                            Alterar senha
                        </a>
                    </div>
                </aside>

                <dl class="perfil-dados">

                    <?php foreach ($campos as $campo): ?>

                        <div class="perfil-dados__item">

                            <span class="perfil-dados__icone-box">
                                <i class="text-success bi <?= $e($campo['icone']) ?>"></i>
                            </span>

                            <div class="perfil-dados__texto">
                                <dt class="perfil-dados__label"><?= $e($campo['label']) ?></dt>
                                <dd class="perfil-dados__valor"><?= $e($campo['valor']) ?></dd>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </dl>
            </div>
        </section>

    <?php endif; ?>

</main>

<?php include "../App/Views/footer.php"; ?>