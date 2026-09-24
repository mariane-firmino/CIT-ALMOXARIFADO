(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var formulario = document.querySelector('.js-rsol-formulario');

        if (!formulario) {
            return;
        }

        var botaoEnviar = formulario.querySelector('.js-rsol-enviar');
        var avisoVazio = formulario.querySelector('.js-rsol-vazio');
        var textoOriginal = botaoEnviar.textContent;

        function atualizar() {
            var restantes = formulario.querySelectorAll('.js-rsol-item').length;

            botaoEnviar.disabled = restantes === 0;
            avisoVazio.hidden = restantes !== 0;
        }

        // Remover um produto tira o card inteiro (e com ele os campos do POST).
        formulario.addEventListener('click', function (evento) {
            var botao = evento.target.closest('.js-rsol-remover');

            if (!botao) {
                return;
            }

            var item = botao.closest('.js-rsol-item');

            if (item) {
                item.remove();
                atualizar();
            }
        });

        // Evita enviar a solicitação duas vezes com cliques repetidos.
        formulario.addEventListener('submit', function () {
            botaoEnviar.disabled = true;
            botaoEnviar.textContent = 'Enviando...';
        });

        // Se o usuário voltar pelo navegador, restaura o botão.
        window.addEventListener('pageshow', function (evento) {
            if (evento.persisted) {
                botaoEnviar.textContent = textoOriginal;
                atualizar();
            }
        });
    });
})();