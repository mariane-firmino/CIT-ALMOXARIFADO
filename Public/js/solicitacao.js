(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var filtros = document.querySelectorAll('.js-lsol-filtro-auto');

        // Trocar o status ou a data aplica o filtro na hora (o botão "Pesquisar" continua funcionando sem JS).
        filtros.forEach(function (filtro) {
            filtro.addEventListener('change', function () {
                if (filtro.form) {
                    filtro.form.submit();
                }
            });
        });
    });
})();