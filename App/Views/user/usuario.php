<div class="sacit-register-page">
    <div class="sacit-register-wrapper row g-0">
        <!-- LADO DAS OPÇÕES -->
        <div class="sacit-register-options-side col-12 col-md-6">
            <div class="sacit-register-content">
                <h1 class="sacit-register-title">
                    Identifique-se
                </h1>
                <div class="sacit-register-options">

                    <!-- SERVIDOR -->
                    <a
                        href="<?= URL ?>/users/cadServidor"
                        class="sacit-register-option">
                        <span class="sacit-register-option-text">
                            Servidor
                        </span>
                    </a>

                    <!-- COORDENADOR -->
                    <a
                        href="<?= URL ?>/users/cadCoordenador"
                        class="sacit-register-option">
                        <span class="sacit-register-option-text">
                            Coordenador
                        </span>
                    </a>

                    <!-- ESTAGIÁRIO -->
                    <a
                        href="<?= URL ?>/users/cadEstagiario"
                        class="sacit-register-option">
                        <span class="sacit-register-option-text">
                            Estagiário
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <!-- LADO DA LOGO -->
        <div class="sacit-register-logo-side col-12 col-md-6">
            <img
                src="<?= URL ?>/public/img/logo-sacit.png"
                alt="Logo SACIT"
                class="sacit-register-logo">
        </div>
    </div>
</div>