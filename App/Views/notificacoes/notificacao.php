<?php
include "../App/Views/menu.php";

$filtroStatus = $_GET['status']   ?? '';
$filtroBusca  = $_GET['pesquisa'] ?? '';
$filtroData   = $_GET['data']     ?? '';

/** Escapa saída (evita XSS) */
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>

<main class="noti-page">

    <header class="noti-page__header">
        <div class="noti-page__title-group">
            <div class="noti-page__title-row">
                <span class="noti-page__title-mark"></span>
                <h1 class="noti-page__title">Notificações</h1>
            </div>
            <p class="noti-page__subtitle">Acompanhe suas notificações.</p>
        </div>

        <img
            src="<?= URL ?>/img/logo-sacit.png"
            alt="SACIT"
            class="noti-page__logo">
    </header>

    <section class="noti-container">

        <form class="noti-filtros" action="<?= URL ?>/notificacoes/notificacao" method="GET">

            <div class="noti-filtros__grupo">

                <button
                    type="submit"
                    name="status"
                    value=""
                    class="noti-filtros__btn <?= $filtroStatus === '' ? 'noti-filtros__btn--ativo' : '' ?>">
                    Todas
                </button>

                <button
                    type="submit"
                    name="status"
                    value="Não lida"
                    class="noti-filtros__btn <?= $filtroStatus === 'Não lida' ? 'noti-filtros__btn--ativo' : '' ?>">
                    Não lidas
                </button>

                <button
                    type="submit"
                    name="status"
                    value="Lida"
                    class="noti-filtros__btn <?= $filtroStatus === 'Lida' ? 'noti-filtros__btn--ativo' : '' ?>">
                    Lidas
                </button>
            </div>

            <div class="noti-filtros__grupo">

                <div class="noti-filtros__campo">
                    <i class="bi bi-search text-dark" style="font-size: 1rem;"></i>


                    <label class="noti-filtros__label" for="noti-busca">Buscar notificações</label>

                    <input
                        id="noti-busca"
                        class="noti-filtros__input"
                        type="search"
                        name="pesquisa"
                        value="<?= $e($filtroBusca) ?>"
                        placeholder="Buscar notificações...">
                </div>

                <div class="noti-filtros__campo">
                    <label class="noti-filtros__label" for="noti-data">Data</label>

                    <input
                        id="noti-data"
                        class="noti-filtros__input"
                        type="date"
                        name="data"
                        value="<?= $e($filtroData) ?>">
                </div>

                <button type="submit" class="noti-filtros__btn noti-filtros__btn--aplicar">
                    Filtrar
                </button>
            </div>
        </form>

        <div class="noti-lista">

            <?php if (!empty($dados['notificacoes'])): ?>

                <?php foreach ($dados['notificacoes'] as $notificacao): ?>

                    <?php
                    $status    = trim((string) ($notificacao->noti_status ?? ''));
                    $lida      = mb_strtolower($status) === 'lida';
                    $modifier  = $lida ? 'noti-card--lida' : 'noti-card--nao-lida';
                    $timestamp = strtotime((string) ($notificacao->noti_data ?? ''));
                    ?>

                    <article class="noti-card <?= $modifier ?>">

                        <div class="noti-card__icone-box">
                            <?php if ($lida): ?>
                                <i class="bi bi-check-circle-fill text-success fs-1"
                                    title="Notificação lida"
                                    aria-label="Notificação lida"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-circle-fill text-danger fs-1"
                                    title="Notificação não lida"
                                    aria-label="Notificação não lida"></i>
                            <?php endif; ?>
                        </div>

                        <div class="noti-card__conteudo">

                            <div class="noti-card__topo">
                                <h2 class="noti-card__titulo">
                                    <?= $e($notificacao->noti_titulo ?? '') ?>
                                </h2>

                                <span class="noti-card__badge">
                                    <?= $e($status !== '' ? $status : 'Não lida') ?>
                                </span>
                            </div>

                            <p class="noti-card__mensagem">
                                <?= $e($notificacao->noti_mensagem ?? '') ?>
                            </p>

                            <?php if ($timestamp): ?>
                                <time
                                    class="noti-card__data"
                                    datetime="<?= date('c', $timestamp) ?>">
                                    <?= date('d/m/Y H:i', $timestamp) ?>
                                </time>
                            <?php endif; ?>
                        </div>

                        <div class="noti-card__acoes">

                            <button
                                type="button"
                                class="noti-card__acoes-btn"
                                aria-label="Abrir ações da notificação">
                                &#8943;
                            </button>

                            <div class="noti-card__menu">

                                <?php if (!$lida): ?>
                                    <a
                                        class="noti-card__menu-item"
                                        href="<?= URL ?>/notificacoes/lida/<?= (int) ($notificacao->noti_id ?? 0) ?>">
                                        Marcar como lida
                                    </a>
                                <?php endif; ?>

                                <a
                                    class="noti-card__menu-item noti-card__menu-item--excluir"
                                    href="<?= URL ?>/notificacoes/excluir/<?= (int) ($notificacao->noti_id ?? 0) ?>">
                                    Excluir
                                </a>
                            </div>
                        </div>
                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="noti-vazio">
                    <i class="bi bi-bell-slash-fill text-success" style="font-size: 2rem;"></i>

                    <h2 class="noti-vazio__titulo">Sem notificações</h2>

                    <p class="noti-vazio__texto">
                        Você não possui novas notificações no momento.
                    </p>
                </div>

            <?php endif; ?>

        </div>
    </section>
</main>

<?php include "../App/Views/footer.php"; ?>