(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var botaoSolicitar = document.querySelector('.js-cprod-solicitar');
        var checkboxes = document.querySelectorAll('.js-cprod-check');

        if (!botaoSolicitar || checkboxes.length === 0) {
            return;
        }

        function atualizarSelecao() {
            var selecionados = 0;

            checkboxes.forEach(function (checkbox) {
                var card = checkbox.closest('.cprod-card');

                if (card) {
                    card.classList.toggle('cprod-card--selecionado', checkbox.checked);
                }

                if (checkbox.checked) {
                    selecionados++;
                }
            });

            botaoSolicitar.disabled = selecionados === 0;
            botaoSolicitar.textContent = 'Solicitar (' + selecionados + ')';
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', atualizarSelecao);
        });

        // Sincroniza o estado inicial (ex.: navegador restaurou checkboxes ao voltar)
        atualizarSelecao();
    });
})();