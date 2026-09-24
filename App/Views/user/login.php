<form action="<?= URL ?>/users/loginUser" method="post">
    <div class="sacit-login-page">
        <div class="sacit-login-wrapper row g-0">
            <!-- LADO DO FORMULÁRIO -->
            <div class="sacit-login-form-side col-12 col-md-6">
                <div class="sacit-login-content">
                    <h1 class="sacit-login-title">
                        Login
                    </h1>

                    <?= Sessao::mensagem('user') ?>

                    <div class="sacit-login-fields">
                        <!-- E-MAIL -->
                        <div class="mb-4">
                            <label
                                for="sacit-login-email"
                                class="sacit-login-label">
                                Usuário
                            </label>

                            <input
                                type="email"
                                id="sacit-login-email"
                                name="email"
                                value="<?= isset($dados['email']) ? $dados['email'] : '' ?>"
                                class="sacit-login-input form-control <?= !empty($dados['email_erro']) ? 'is-invalid' : '' ?>"
                                placeholder="Digite seu e-mail"
                                required>

                            <?php if (!empty($dados['email_erro'])): ?>
                                <div class="invalid-feedback">
                                    <?= $dados['email_erro'] ?>
                                </div>
                            <?php endif; ?>

                        </div>

                        <!-- SENHA -->
                        <div class="mb-4">
                            <label
                                for="sacit-login-senha"
                                class="sacit-login-label">
                                Senha
                            </label>

                            <input
                                type="password"
                                id="sacit-login-senha"
                                name="senha"
                                class="sacit-login-input form-control <?= !empty($dados['senha_erro']) ? 'is-invalid' : '' ?>"
                                placeholder="Digite sua senha"
                                required>

                            <?php if (!empty($dados['senha_erro'])): ?>
                                <div class="invalid-feedback">
                                    <?= $dados['senha_erro'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- BOTÃO -->
                        <button
                            type="submit"
                            class="sacit-login-button btn">
                            Enviar
                        </button>
                    </div>

                    <!-- LINKS -->
                    <div class="sacit-login-links">
                        <div class="sacit-login-link-row">
                            <span>
                                Esqueceu a senha?
                            </span>

                            <a
                                href="<?= URL ?>/users/esqueciSenha"
                                class="sacit-login-link">
                                Esqueci minha senha
                            </a>
                        </div>

                        <div class="sacit-login-link-row">
                            <span>
                                Ainda não é cadastrado?
                            </span>
                            <a
                                href="<?= URL ?>/users/tipoUsuario"
                                class="sacit-login-link">
                                Cadastre-se
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- LADO DA LOGO -->
            <div class="sacit-login-logo-side col-12 col-md-6">
                <img
                    src="<?= URL ?>/public/img/logo-sacit.png"
                    alt="Logo SACIT"
                    class="sacit-login-logo">

            </div>
        </div>
    </div>
</form>