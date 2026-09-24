<link rel="stylesheet" href="<?= URL ?>/public/css/redefinir-senha.css">

<div class="reset-password-container">

    <div class="reset-password-wrapper">

        <div class="reset-password-left">

            <h1 class="reset-password-title">
                Redefinir senha
            </h1>

            <?= Sessao::mensagem('usuario') ?>

            <p class="reset-password-description">
                Crie uma nova senha para acessar o sistema.
                Use no mínimo 6 caracteres.
            </p>

            <form
                class="reset-password-form"
                action="<?= URL ?>/users/redefinirSenha/<?= htmlspecialchars($dados['token']) ?>"
                method="post">

                <div class="reset-password-form-group">

                    <label class="reset-password-label" for="reset-password-senha">
                        Nova senha
                    </label>

                    <div class="reset-password-input-wrapper">
                        <input
                            id="reset-password-senha"
                            type="password"
                            name="senha"
                            class="reset-password-input <?= !empty($dados['senha_erro']) ? 'reset-password-input-invalid' : '' ?>"
                            placeholder="Digite a nova senha"
                            autocomplete="new-password"
                            minlength="6"
                            required>
                        <button
                            type="button"
                            class="reset-password-toggle"
                            data-target="reset-password-senha"
                            aria-label="Mostrar ou ocultar senha">
                            Mostrar
                        </button>
                    </div>

                    <?php if (!empty($dados['senha_erro'])) : ?>
                        <div class="reset-password-invalid-feedback">
                            <?= htmlspecialchars($dados['senha_erro']) ?>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="reset-password-form-group">

                    <label class="reset-password-label" for="reset-password-confirma">
                        Confirmar nova senha
                    </label>

                    <div class="reset-password-input-wrapper">
                        <input
                            id="reset-password-confirma"
                            type="password"
                            name="confirma_senha"
                            class="reset-password-input <?= !empty($dados['confirma_senha_erro']) ? 'reset-password-input-invalid' : '' ?>"
                            placeholder="Repita a nova senha"
                            autocomplete="new-password"
                            minlength="6"
                            required>
                        <button
                            type="button"
                            class="reset-password-toggle"
                            data-target="reset-password-confirma"
                            aria-label="Mostrar ou ocultar senha">
                            Mostrar
                        </button>
                    </div>

                    <?php if (!empty($dados['confirma_senha_erro'])) : ?>
                        <div class="reset-password-invalid-feedback">
                            <?= htmlspecialchars($dados['confirma_senha_erro']) ?>
                        </div>
                    <?php endif; ?>

                </div>

                <button class="reset-password-submit" type="submit">
                    Salvar nova senha
                </button>

                <div class="reset-password-footer-links">
                    <span class="reset-password-link-text">Lembrou a senha?</span>
                    <a href="<?= URL ?>/users/loginUser" class="reset-password-link">
                        Voltar ao login
                    </a>
                </div>

            </form>

        </div>

        <div class="reset-password-right">
            <div class="reset-password-logo-container">
                <img
                    src="<?= URL ?>/public/img/logo-sacit.png"
                    alt="SACIT Logo"
                    class="reset-password-logo">
            </div>
        </div>

    </div>

</div>

<script>
    document.querySelectorAll('.reset-password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(btn.dataset.target);
            var mostrar = input.type === 'password';
            input.type = mostrar ? 'text' : 'password';
            btn.textContent = mostrar ? 'Ocultar' : 'Mostrar';
        });
    });
</script>