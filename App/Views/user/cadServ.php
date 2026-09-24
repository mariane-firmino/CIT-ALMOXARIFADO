<div class="servidor-cadastro">

    <div class="servidor-cadastro-wrapper">

        <div class="servidor-cadastro-left">

            <h1 class="servidor-cadastro-title">
                Cadastro de Servidor
            </h1>

            <?= Sessao::mensagem('usuario') ?>

            <form
                class="servidor-cadastro-form"
                action="<?= URL ?>/users/cadServidor"
                method="POST">

                <!-- NOME -->
                <div class="servidor-cadastro-group">

                    <label class="servidor-cadastro-label">
                        Nome Completo
                    </label>

                    <input
                        type="text"
                        name="nome"
                        class="servidor-cadastro-input <?= !empty($dados['nome_erro']) ? 'servidor-cadastro-invalid' : '' ?>"
                        placeholder="Digite seu nome completo"
                        value="<?= htmlspecialchars($dados['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>

                    <?php if (!empty($dados['nome_erro'])) : ?>

                        <div class="servidor-cadastro-error">
                            <?= htmlspecialchars($dados['nome_erro'], ENT_QUOTES, 'UTF-8') ?>
                        </div>

                    <?php endif; ?>

                </div>


                <!-- EMAIL -->
                <div class="servidor-cadastro-group">

                    <label class="servidor-cadastro-label">
                        E-mail Institucional
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="servidor-cadastro-input <?= !empty($dados['email_erro']) ? 'servidor-cadastro-invalid' : '' ?>"
                        placeholder="Digite seu e-mail institucional"
                        value="<?= htmlspecialchars($dados['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>

                    <?php if (!empty($dados['email_erro'])) : ?>

                        <div class="servidor-cadastro-error">
                            <?= htmlspecialchars($dados['email_erro'], ENT_QUOTES, 'UTF-8') ?>
                        </div>

                    <?php endif; ?>

                </div>


                <!-- SIAPE / SETOR -->
                <div class="servidor-cadastro-row">

                    <div class="servidor-cadastro-group servidor-cadastro-half">

                        <label class="servidor-cadastro-label">
                            SIAPE
                        </label>

                        <input
                            type="text"
                            name="siap"
                            maxlength="7"
                            class="servidor-cadastro-input <?= !empty($dados['siap_erro']) ? 'servidor-cadastro-invalid' : '' ?>"
                            placeholder="0000000"
                            value="<?= htmlspecialchars($dados['siap'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required>

                        <?php if (!empty($dados['siap_erro'])) : ?>

                            <div class="servidor-cadastro-error">
                                <?= htmlspecialchars($dados['siap_erro'], ENT_QUOTES, 'UTF-8') ?>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="servidor-cadastro-group servidor-cadastro-half">

                        <label class="servidor-cadastro-label">
                            Setor
                        </label>

                        <select
                            name="setor"
                            class="servidor-cadastro-input <?= !empty($dados['setor_erro']) ? 'servidor-cadastro-invalid' : '' ?>"
                            required>

                            <option value="">
                                Selecionar setor
                            </option>

                            <option
                                value="2"
                                <?= (isset($dados['setor']) && $dados['setor'] == 2) ? 'selected' : '' ?>>
                                CIT
                            </option>

                            <option
                                value="3"
                                <?= (isset($dados['setor']) && $dados['setor'] == 3) ? 'selected' : '' ?>>
                                DAPE
                            </option>

                        </select>

                        <?php if (!empty($dados['setor_erro'])) : ?>

                            <div class="servidor-cadastro-error">
                                <?= htmlspecialchars($dados['setor_erro'], ENT_QUOTES, 'UTF-8') ?>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- TELEFONE -->
                <div class="servidor-cadastro-group">

                    <label class="servidor-cadastro-label">
                        Telefone
                    </label>

                    <input
                        type="tel"
                        name="celular"
                        id="sacit-coordenador-celular"
                        maxlength="15"
                        class="servidor-cadastro-input <?= !empty($dados['celular_erro']) ? 'servidor-cadastro-invalid' : '' ?>"
                        placeholder="(99) 99999-9999"
                        value="<?= htmlspecialchars($dados['celular'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>

                    <?php if (!empty($dados['celular_erro'])) : ?>

                        <div class="servidor-cadastro-error">
                            <?= htmlspecialchars($dados['celular_erro'], ENT_QUOTES, 'UTF-8') ?>
                        </div>

                    <?php endif; ?>

                </div>


                <!-- SENHA / CONFIRMAÇÃO -->
                <div class="servidor-cadastro-row">

                    <div class="servidor-cadastro-group servidor-cadastro-half">

                        <label class="servidor-cadastro-label">
                            Senha
                        </label>

                        <input
                            type="password"
                            name="senha"
                            class="servidor-cadastro-input <?= !empty($dados['senha_erro']) ? 'servidor-cadastro-invalid' : '' ?>"
                            placeholder="Digite sua senha"
                            required>

                        <?php if (!empty($dados['senha_erro'])) : ?>

                            <div class="servidor-cadastro-error">
                                <?= htmlspecialchars($dados['senha_erro'], ENT_QUOTES, 'UTF-8') ?>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="servidor-cadastro-group servidor-cadastro-half">

                        <label class="servidor-cadastro-label">
                            Confirmar Senha
                        </label>

                        <input
                            type="password"
                            name="confirma_senha"
                            class="servidor-cadastro-input <?= !empty($dados['confirma_senha_erro']) ? 'servidor-cadastro-invalid' : '' ?>"
                            placeholder="Confirme sua senha"
                            required>

                        <?php if (!empty($dados['confirma_senha_erro'])) : ?>

                            <div class="servidor-cadastro-error">
                                <?= htmlspecialchars($dados['confirma_senha_erro'], ENT_QUOTES, 'UTF-8') ?>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- BOTÃO -->
                <button
                    type="submit"
                    class="servidor-cadastro-button">
                    <span class="servidor-cadastro-button-text">
                        Cadastrar
                    </span>
                </button>


                <!-- LINKS -->
                <div class="servidor-cadastro-links">

                    <div class="servidor-cadastro-link-row">

                        <span class="servidor-cadastro-link-text">
                            Já tem conta?
                        </span>

                        <a
                            href="<?= URL ?>/users/loginUser"
                            class="servidor-cadastro-link">
                            Fazer Login
                        </a>

                    </div>

                    <div class="servidor-cadastro-link-row">

                        <span class="servidor-cadastro-link-text">
                            Voltar para
                        </span>

                        <a
                            href="<?= URL ?>/users/tipoUsuario"
                            class="servidor-cadastro-link">
                            Tipos de Usuários
                        </a>

                    </div>

                </div>

            </form>

        </div>


        <!-- LOGO -->
        <div class="servidor-cadastro-right">

            <div class="servidor-cadastro-logo-container">

                <img
                    src="<?= URL ?>/public/img/logo-sacit.png"
                    alt="SACIT Logo"
                    class="servidor-cadastro-logo">

            </div>

        </div>

    </div>

</div>