<div class="sacit-coordenador-page">
    <div class="sacit-coordenador-wrapper row g-0">
        <!-- LADO DO FORMULÁRIO -->
        <div class="sacit-coordenador-form-side col-12 col-md-7">
            <div class="sacit-coordenador-content">
                <h1 class="sacit-coordenador-title">
                    Cadastro de Coordenador
                </h1>

                <?= Sessao::mensagem('usuario') ?>

                <form
                    action="<?= URL ?>/users/cadCoordenador"
                    method="POST"
                    class="sacit-coordenador-form">

                    <!-- NOME -->
                    <div class="sacit-coordenador-field">
                        <label
                            for="sacit-coordenador-nome"
                            class="sacit-coordenador-label">
                            Nome Completo
                        </label>

                        <input
                            type="text"
                            id="sacit-coordenador-nome"
                            name="nome"
                            class="sacit-coordenador-input form-control <?= !empty($dados['nome_erro']) ? 'is-invalid' : '' ?>"
                            placeholder="Digite seu nome completo"
                            value="<?= isset($dados['nome']) ? $dados['nome'] : '' ?>"
                            required>

                        <?php if (!empty($dados['nome_erro'])): ?>
                            <div class="invalid-feedback">
                                <?= $dados['nome_erro'] ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- E-MAIL -->
                    <div class="sacit-coordenador-field">
                        <label
                            for="sacit-coordenador-email"
                            class="sacit-coordenador-label">
                            E-mail
                        </label>

                        <input
                            type="email"
                            id="sacit-coordenador-email"
                            name="email"
                            class="sacit-coordenador-input form-control <?= !empty($dados['email_erro']) ? 'is-invalid' : '' ?>"
                            placeholder="Digite seu e-mail institucional"
                            value="<?= isset($dados['email']) ? $dados['email'] : '' ?>"
                            required>

                        <?php if (!empty($dados['email_erro'])): ?>
                            <div class="invalid-feedback">
                                <?= $dados['email_erro'] ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- SIAPE + SETOR -->
                    <div class="row g-3">
                        <!-- SIAPE -->
                        <div class="col-12 col-sm-6">
                            <div class="sacit-coordenador-field">

                                <label
                                    for="sacit-coordenador-siape"
                                    class="sacit-coordenador-label">
                                    SIAPE
                                </label>

                                <input
                                    type="text"
                                    id="sacit-coordenador-siape"
                                    name="siap"
                                    maxlength="7"
                                    class="sacit-coordenador-input form-control <?= !empty($dados['siap_erro']) ? 'is-invalid' : '' ?>"
                                    placeholder="0000000"
                                    value="<?= isset($dados['siap']) ? $dados['siap'] : '' ?>"
                                    required>

                                <?php if (!empty($dados['siap_erro'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $dados['siap_erro'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>


                        <!-- SETOR -->
                        <div class="col-12 col-sm-6">
                            <div class="sacit-coordenador-field">
                                <label
                                    for="sacit-coordenador-setor"
                                    class="sacit-coordenador-label">
                                    Setor
                                </label>

                                <select
                                    id="sacit-coordenador-setor"
                                    name="setor"
                                    class="sacit-coordenador-input form-select <?= !empty($dados['setor_erro']) ? 'is-invalid' : '' ?>"
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

                                <?php if (!empty($dados['setor_erro'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $dados['setor_erro'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- TELEFONE -->
                    <div class="sacit-coordenador-field">

                        <label
                            for="sacit-coordenador-celular"
                            class="sacit-coordenador-label">
                            Telefone
                        </label>

                        <input
                            type="tel"
                            id="sacit-coordenador-celular"
                            name="celular"
                            maxlength="15"
                            class="sacit-coordenador-input form-control <?= !empty($dados['celular_erro']) ? 'is-invalid' : '' ?>"
                            placeholder="(99) 99999-9999"
                            value="<?= isset($dados['celular']) ? $dados['celular'] : '' ?>"
                            required>

                        <?php if (!empty($dados['celular_erro'])): ?>
                            <div class="invalid-feedback">
                                <?= $dados['celular_erro'] ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- SENHA + CONFIRMAÇÃO -->
                    <div class="row g-3">
                        <!-- SENHA -->
                        <div class="col-12 col-sm-6">
                            <div class="sacit-coordenador-field">

                                <label
                                    for="sacit-coordenador-senha"
                                    class="sacit-coordenador-label">
                                    Senha
                                </label>

                                <input
                                    type="password"
                                    id="sacit-coordenador-senha"
                                    name="senha"
                                    class="sacit-coordenador-input form-control <?= !empty($dados['senha_erro']) ? 'is-invalid' : '' ?>"
                                    placeholder="Digite sua senha"
                                    required>

                                <?php if (!empty($dados['senha_erro'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $dados['senha_erro'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>


                        <!-- CONFIRMAR SENHA -->
                        <div class="col-12 col-sm-6">
                            <div class="sacit-coordenador-field">
                                <label
                                    for="sacit-coordenador-confirma-senha"
                                    class="sacit-coordenador-label">
                                    Confirmar Senha
                                </label>

                                <input
                                    type="password"
                                    id="sacit-coordenador-confirma-senha"
                                    name="confirma_senha"
                                    class="sacit-coordenador-input form-control <?= !empty($dados['confirma_senha_erro']) ? 'is-invalid' : '' ?>"
                                    placeholder="Confirme sua senha"
                                    required>

                                <?php if (!empty($dados['confirma_senha_erro'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $dados['confirma_senha_erro'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>


                    <!-- BOTÃO -->
                    <button
                        type="submit"
                        class="sacit-coordenador-submit btn">
                        Cadastrar
                    </button>


                    <!-- LINKS -->
                    <div class="sacit-coordenador-footer">
                        <div class="sacit-coordenador-footer-row">
                            <span>
                                Já tem login?
                            </span>

                            <a
                                href="<?= URL ?>/users/loginUser"
                                class="sacit-coordenador-link">
                                Login
                            </a>
                        </div>


                        <div class="sacit-coordenador-footer-row">
                            <span>
                                Voltar para
                            </span>

                            <a
                                href="<?= URL ?>/users/tipoUsuario"
                                class="sacit-coordenador-link">
                                Tipos de Usuários
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <!-- LADO DA LOGO -->
        <div class="sacit-coordenador-logo-side col-12 col-md-5">
            <img
                src="<?= URL ?>/public/img/logo-sacit.png"
                alt="Logo SACIT"
                class="sacit-coordenador-logo">
        </div>
    </div>
</div>