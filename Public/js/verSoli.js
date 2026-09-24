(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var formulario = document.querySelector('.js-vsol-formulario');

        if (!formulario) {
            return;
        }

        var retirada = formulario.querySelector('.js-vsol-retirada');
        var devolucao = formulario.querySelector('.js-vsol-devolucao');
        var observacao = formulario.querySelector('.js-vsol-observacao');
        var botoes = formulario.querySelectorAll('.js-vsol-acao');

        // A devolução nunca pode ser anterior à retirada.
        retirada.addEventListener('change', function () {
            if (!retirada.value) {
                return;
            }

            devolucao.min = retirada.value;

            if (devolucao.value && devolucao.value < retirada.value) {
                devolucao.value = retirada.value;
            }
        });

        // Ao negar, a observação passa a ser obrigatória.
        observacao.addEventListener('input', function () {
            observacao.setCustomValidity('');
        });

        botoes.forEach(function (botao) {
            botao.addEventListener('click', function (evento) {
                if (botao.value === 'Negada' && observacao.value.trim() === '') {
                    evento.preventDefault();
                    observacao.setCustomValidity('Informe o motivo da negação.');
                    observacao.reportValidity();
                    return;
                }

                // Aprovar: deixa o navegador mostrar os campos inválidos antes de pedir confirmação.
                if (botao.value === 'Aprovada' && !formulario.checkValidity()) {
                    return;
                }

                if (!window.confirm(botao.dataset.confirmacao)) {
                    evento.preventDefault();
                }
            });
        });
    });
})();