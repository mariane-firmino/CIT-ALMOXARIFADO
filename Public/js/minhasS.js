(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Trocar o status ou a data aplica o filtro na hora (o botão "Pesquisar" funciona sem JS).
        document.querySelectorAll('.js-msol-filtro-auto').forEach(function (filtro) {
            filtro.addEventListener('change', function () {
                if (filtro.form) {
                    filtro.form.submit();
                }
            });
        });

        // Pede confirmação antes de cancelar; a mensagem vem do atributo data-confirmacao.
        document.querySelectorAll('.js-msol-cancelar').forEach(function (formulario) {
            formulario.addEventListener('submit', function (evento) {
                if (!window.confirm(formulario.dataset.confirmacao)) {
                    evento.preventDefault();
                }
            });
        });
    });
})();