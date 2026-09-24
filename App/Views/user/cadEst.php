<div class="estagiario-cadastro-container">

    <div class="estagiario-cadastro-wrapper">

        <div class="estagiario-cadastro-left">

            <h1 class="estagiario-cadastro-title">
                Cadastro de Estagiário
            </h1>

            <?= Sessao::mensagem('usuario') ?>

            <form
                class="estagiario-cadastro-form"
                action="<?= URL ?>/users/cadEstagiario"
                method="post"
            >

                <!-- NOME -->
                <div class="estagiario-cadastro-group">

                    <label class="estagiario-cadastro-label">
                        Nome Completo
                    </label>

                    <input
                        type="text"
                        name="nome"
                        class="estagiario-cadastro-input <?= !empty($dados['nome_erro']) ? 'is-invalid' : '' ?>"
                        placeholder="Digite seu nome completo"
                        value="<?= isset($dados['nome']) ? htmlspecialchars($dados['nome']) : '' ?>"
                        required
                    >

                    <?php if (!empty($dados['nome_erro'])) : ?>

                        <div class="estagiario-cadastro-error">
                            <?= $dados['nome_erro'] ?>
                        </div>

                    <?php endif; ?>

                </div>


                <!-- E-MAIL -->
                <div class="estagiario-cadastro-group">

                    <label class="estagiario-cadastro-label">
                        E-mail Institucional
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="estagiario-cadastro-input <?= !empty($dados['email_erro']) ? 'is-invalid' : '' ?>"
                        placeholder="Digite seu e-mail institucional"
                        value="<?= isset($dados['email']) ? htmlspecialchars($dados['email']) : '' ?>"
                        required
                    >

                    <?php if (!empty($dados['email_erro'])) : ?>

                        <div class="estagiario-cadastro-error">
                            <?= $dados['email_erro'] ?>
                        </div>

                    <?php endif; ?>

                </div>


                <!-- MATRÍCULA + CURSO -->
                <div class="estagiario-cadastro-row">

                    <div class="estagiario-cadastro-group estagiario-cadastro-half">

                        <label class="estagiario-cadastro-label">
                            Matrícula
                        </label>

                        <input
                            type="text"
                            name="matricula"
                            maxlength="13"
                            class="estagiario-cadastro-input <?= !empty($dados['matricula_erro']) ? 'is-invalid' : '' ?>"
                            placeholder="0000000000"
                            value="<?= isset($dados['matricula']) ? htmlspecialchars($dados['matricula']) : '' ?>"
                            required
                        >

                        <?php if (!empty($dados['matricula_erro'])) : ?>

                            <div class="estagiario-cadastro-error">
                                <?= $dados['matricula_erro'] ?>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="estagiario-cadastro-group estagiario-cadastro-half">

                        <label class="estagiario-cadastro-label">
                            Curso
                        </label>

                        <select
                            name="curso"
                            class="estagiario-cadastro-input <?= !empty($dados['curso_erro']) ? 'is-invalid' : '' ?>"
                            required
                        >

                            <option value="">
                                Selecionar curso
                            </option>

                            <option
                                value="Técnico em Informática"
                                <?= (isset($dados['curso']) && $dados['curso'] == 'Técnico em Informática') ? 'selected' : '' ?>
                            >
                                Informática
                            </option>

                            <option
                                value="Técnico em Biotecnologia"
                                <?= (isset($dados['curso']) && $dados['curso'] == 'Técnico em Biotecnologia') ? 'selected' : '' ?>
                            >
                                Biotecnologia
                            </option>

                        </select>

                        <?php if (!empty($dados['curso_erro'])) : ?>

                            <div class="estagiario-cadastro-error">
                                <?= $dados['curso_erro'] ?>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- ANO + TELEFONE -->
                <div class="estagiario-cadastro-row">

                    <div class="estagiario-cadastro-group estagiario-cadastro-half">

                        <label class="estagiario-cadastro-label">
                            Ano
                        </label>

                        <select
                            name="ano"
                            class="estagiario-cadastro-input <?= !empty($dados['ano_erro']) ? 'is-invalid' : '' ?>"
                            required
                        >

                            <option value="">
                                Selecione o ano
                            </option>

                            <option
                                value="1"
                                <?= (isset($dados['ano']) && $dados['ano'] == 1) ? 'selected' : '' ?>
                            >
                                1º Ano
                            </option>

                            <option
                                value="2"
                                <?= (isset($dados['ano']) && $dados['ano'] == 2) ? 'selected' : '' ?>
                            >
                                2º Ano
                            </option>

                            <option
                                value="3"
                                <?= (isset($dados['ano']) && $dados['ano'] == 3) ? 'selected' : '' ?>
                            >
                                3º Ano
                            </option>

                        </select>

                        <?php if (!empty($dados['ano_erro'])) : ?>

                            <div class="estagiario-cadastro-error">
                                <?= $dados['ano_erro'] ?>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="estagiario-cadastro-group estagiario-cadastro-half">

                        <label class="estagiario-cadastro-label">
                            Telefone
                        </label>

                        <input
                            type="tel"
                            name="celular"
                            id="estagiario-cadastro-celular"
                            maxlength="15"
                            class="estagiario-cadastro-input <?= !empty($dados['celular_erro']) ? 'is-invalid' : '' ?>"
                            placeholder="(99) 99999-9999"
                            value="<?= isset($dados['celular']) ? htmlspecialchars($dados['celular']) : '' ?>"
                            required
                        >

                        <?php if (!empty($dados['celular_erro'])) : ?>

                            <div class="estagiario-cadastro-error">
                                <?= $dados['celular_erro'] ?>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- SENHA + CONFIRMAÇÃO -->
                <div class="estagiario-cadastro-row">

                    <div class="estagiario-cadastro-group estagiario-cadastro-half">

                        <label class="estagiario-cadastro-label">
                            Senha
                        </label>

                        <input
                            type="password"
                            name="senha"
                            class="estagiario-cadastro-input <?= !empty($dados['senha_erro']) ? 'is-invalid' : '' ?>"
                            placeholder="Digite sua senha"
                            required
                        >

                        <?php if (!empty($dados['senha_erro'])) : ?>

                            <div class="estagiario-cadastro-error">
                                <?= $dados['senha_erro'] ?>
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="estagiario-cadastro-group estagiario-cadastro-half">

                        <label class="estagiario-cadastro-label">
                            Confirmar Senha
                        </label>

                        <input
                            type="password"
                            name="confirma_senha"
                            class="estagiario-cadastro-input <?= !empty($dados['confirma_senha_erro']) ? 'is-invalid' : '' ?>"
                            placeholder="Confirme sua senha"
                            required
                        >

                        <?php if (!empty($dados['confirma_senha_erro'])) : ?>

                            <div class="estagiario-cadastro-error">
                                <?= $dados['confirma_senha_erro'] ?>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- BOTÃO -->
                <button
                    type="submit"
                    class="estagiario-cadastro-submit"
                >
                    <span class="estagiario-cadastro-submit-text">
                        Cadastrar
                    </span>
                </button>


                <!-- LINKS -->
                <div class="estagiario-cadastro-footer">

                    <div class="estagiario-cadastro-link-row">

                        <span class="estagiario-cadastro-link-text">
                            Já tem conta?
                        </span>

                        <a
                            href="<?= URL ?>/users/loginUser"
                            class="estagiario-cadastro-link"
                        >
                            Fazer Login
                        </a>

                    </div>


                    <div class="estagiario-cadastro-link-row">

                        <span class="estagiario-cadastro-link-text">
                            Voltar para
                        </span>

                        <a
                            href="<?= URL ?>/users/tipoUsuario"
                            class="estagiario-cadastro-link"
                        >
                            Tipos de Usuários
                        </a>

                    </div>

                </div>

            </form>

        </div>


        <!-- LADO DIREITO -->
        <div class="estagiario-cadastro-right">

            <div class="estagiario-cadastro-logo-container">

                <img
                    src="<?= URL ?>/public/img/logo-sacit.png"
                    alt="SACIT Logo"
                    class="estagiario-cadastro-logo"
                >

            </div>

        </div>

    </div>

</div>