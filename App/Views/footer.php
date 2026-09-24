<footer class="site-footer">
    <nav class="site-footer__links" aria-label="Links do rodapé">
        <a href="<?= URL ?>/paginas/home">Início</a>
        <a href="<?= URL ?>/paginas/sobre">Sobre nós</a>

        <?php if (isset($_SESSION['usuario_funcao']) && $_SESSION['usuario_funcao'] == 1): ?>
            <a href="<?= URL ?>/perfis/gerenciarPerfis">Gerenciar perfis</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['usuario_funcao']) && $_SESSION['usuario_funcao'] == 3): ?>
            <a href="<?= URL ?>/solicitacoes/consultarProduto">Consultar produtos</a>
        <?php endif; ?>
    </nav>

    <div class="site-footer__center">
        <img
            src="<?= URL ?>/img/ifro-logo.webp"
            alt="Instituto Federal de Rondônia"
            class="site-footer__logo"
        >
    </div>

    <div class="site-footer__contact">
        <p class="site-footer__contact-title">Contatos:</p>
        <p>Telefone: (99) 99999-9999</p>
        <p>E-mail: algumacoisa@ifro.edu.br</p>
    </div>
</footer>