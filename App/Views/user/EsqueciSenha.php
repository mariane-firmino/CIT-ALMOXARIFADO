<div class="forgot-password-container">

    <div class="forgot-password-wrapper">

        <div class="forgot-password-left">

            <h1 class="forgot-password-title">
                Esqueci minha senha
            </h1>

            <?= Sessao::mensagem('usuario') ?>

            <p class="forgot-password-description">
                Informe seu e-mail institucional para receber o link
                de redefinição de senha.
            </p>

            <form
                class="forgot-password-form"
                action="<?= URL ?>/users/esqueciSenha"
                method="post">

                <div class="forgot-password-form-group">

                    <label
                        class="forgot-password-label"
                        for="forgot-password-email">
                        E-mail institucional
                    </label>

                    <input
                        id="forgot-password-email"
                        type="email"
                        name="email"
                        class="forgot-password-input <?= !empty($dados['email_erro']) ? 'forgot-password-input-invalid' : '' ?>"
                        placeholder="seuemail@dominio.com"
                        value="<?= isset($dados['email']) ? htmlspecialchars($dados['email']) : '' ?>"
                        autocomplete="email"
                        required>

                    <?php if (!empty($dados['email_erro'])) : ?>

                        <div class="forgot-password-invalid-feedback">
                            <?= htmlspecialchars($dados['email_erro']) ?>
                        </div>

                    <?php endif; ?>

                </div>

                <button
                    class="forgot-password-submit"
                    type="submit">
                    <span class="forgot-password-submit-text">
                        Enviar
                    </span>
                </button>

                <div class="forgot-password-footer-links">

                    <div class="forgot-password-link-row">

                        <span class="forgot-password-link-text">
                            Lembrou a senha?
                        </span>

                        <a
                            href="<?= URL ?>/users/loginUser"
                            class="forgot-password-link">
                            Voltar ao login
                        </a>

                    </div>

                </div>

            </form>

        </div>

        <div class="forgot-password-right">

            <div class="forgot-password-logo-container">

                <img
                    src="<?= URL ?>/public/img/logo-sacit.png"
                    alt="SACIT Logo"
                    class="forgot-password-logo">

            </div>

        </div>

    </div>

</div>