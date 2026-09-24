<?php

/**
 * Menu lateral - CIT Almoxarifado
 *
 * Um único markup para todas as funções: o que muda é só a lista de itens.
 * Atenção: o menu apenas esconde links. A permissão de cada página
 * também precisa ser verificada no controller.
 *
 * Requer o CSS do Bootstrap Icons carregado no <head> do layout.
 */

$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

/* ---------- Catálogo de itens: chave => [texto, rota, ícone Bootstrap] ---------- */
$catalogoMenu = [
    'inicio'              => ['Início',              'paginas/home',                      'bi-house-door-fill'],
    'perfil'              => ['Perfil',              'perfis/perfil',                    'bi-person-circle'],
    'notificacoes'        => ['Notificações',        'notificacoes/notificacao',         'bi-bell-fill'],
    'gerenciarPerfis'     => ['Gerenciar Perfis',    'perfis/gerenciarPerfis',         'bi-people-fill'],
    'analisarSolicitacao' => ['Analisar Solicitação', 'solicitacoes/analisarSolicitacao', 'bi-clipboard-check-fill'],
    'minhasSolicitacoes'  => ['Minhas Solicitações', 'solicitacoes/solicitacaoServidor', 'bi-file-earmark-text-fill'],
    'estoque'             => ['Controlar Estoque',   'produtos/estoque',                 'bi-box-seam-fill'],
    'controlarProduto'    => ['Controlar Produto',   'produtos/controlarProduto',        'bi-tags-fill'],
    'consultarProduto'    => ['Consultar Produto',   'solicitacoes/consultarProduto',        'bi-search'],
    'historico'           => ['Consultar Histórico', 'historicos/consultarHistorico',                 'bi-clock-history'],
    'sair'                => ['Sair',                'users/logoutUser',                      'bi-box-arrow-left'],
];

/* ---------- Quais itens cada função enxerga (na ordem do menu) ---------- */
$menuPorFuncao = [
    1 => ['inicio', 'perfil', 'notificacoes', 'gerenciarPerfis', 'analisarSolicitacao', 'estoque', 'controlarProduto', 'historico', 'sair'],
    2 => ['inicio', 'perfil', 'notificacoes', 'estoque', 'controlarProduto', 'sair'],
    3 => ['inicio', 'perfil', 'notificacoes', 'minhasSolicitacoes', 'consultarProduto', 'historico', 'sair'],
];

$funcao       = (int) ($_SESSION['usuario_funcao'] ?? 0);
$itensDoMenu  = $menuPorFuncao[$funcao] ?? ['sair']; // função desconhecida: só o botão de sair

/* ---------- Notificações não lidas (bolinha do menu) ---------- */
$naoLidas  = 0;
$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0); // AJUSTE: chave de sessão do id do usuário

if ($usuarioId > 0 && in_array('notificacoes', $itensDoMenu, true)) {
    try {
        if (!class_exists('Notificacao')) {
            require_once '../app/Models/Notificacao.php'; // mesmo caminho do Controller::model()
        }
        $naoLidas = (new Notificacao)->contarNaoLidas2($usuarioId);
    } catch (Throwable $ex) {
        // Se falhar, o menu continua funcionando, só sem a bolinha
        error_log('[menu] ' . $ex->getMessage());
    }
}

/* ---------- Dados do usuário ---------- */
$nomeUsuario  = $_SESSION['usuario_nome'] ?? '';
$fotoUsuario  = !empty($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : 'avatar-padrao.png';
$avatarPadrao = URL . '/img/user/avatar-padrao.png';

/* ---------- Detecta a página atual para marcar o link ativo ---------- */
$caminhoAtual = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$rotaAtiva = function (string $rota) use ($caminhoAtual): bool {
    return substr($caminhoAtual, -strlen($rota) - 1) === '/' . $rota;
};
?>
<input type="checkbox" id="cit-menu-toggle" class="cit-menu__checkbox">

<label for="cit-menu-toggle" class="cit-menu__toggle" aria-label="Abrir ou fechar o menu">
    <span class="cit-menu__barra"></span>
    <span class="cit-menu__barra"></span>
    <span class="cit-menu__barra"></span>
    <?php if ($naoLidas > 0): ?>
        <span class="cit-menu__toggle-ponto" aria-hidden="true"></span>
    <?php endif; ?>
</label>

<label for="cit-menu-toggle" class="cit-menu__overlay"></label>

<aside class="cit-menu__sidebar" id="cit-menu">
    <div class="cit-menu__header">
        <img
            src="<?= URL ?>/img/usuarios/<?= rawurlencode($fotoUsuario) ?>"
            alt=""
            class="cit-menu__avatar"
            onerror="this.onerror=null;this.src='<?= $e($avatarPadrao) ?>'">
        <p class="cit-menu__nome"><?= $e($nomeUsuario) ?></p>
    </div>

    <nav class="cit-menu__nav" aria-label="Menu principal">
        <?php foreach ($itensDoMenu as $chave):
            [$texto, $rota, $icone] = $catalogoMenu[$chave];
            $ativo = $rotaAtiva($rota);
        ?>
            <a href="<?= URL ?>/<?= $rota ?>"
                class="cit-menu__link<?= $ativo ? ' cit-menu__link--ativo' : '' ?>"
                <?= $ativo ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= $icone ?> cit-menu__icone" aria-hidden="true"></i>
                <?= $e($texto) ?>
                <?php if ($chave === 'notificacoes' && $naoLidas > 0): ?>
                    <span class="cit-menu__badge" aria-hidden="true"><?= $naoLidas > 99 ? '99+' : $naoLidas ?></span>
                    <span class="cit-menu__sr"><?= $naoLidas ?> não lida<?= $naoLidas > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>