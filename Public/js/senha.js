(function () {
    'use strict';

    var formulario = document.getElementById('senha-formulario');
    if (!formulario) {
        return;
    }

    var iconeOculta = formulario.dataset.iconeOculta;   // senha escondida  -> ícone "olho aberto"
    var iconeVisivel = formulario.dataset.iconeVisivel; // senha à mostra   -> ícone "olho fechado"

    /* ---------- Mostrar / ocultar senha ---------- */
    formulario.addEventListener('click', function (evento) {
        var botao = evento.target.closest('.senha-editar__alternar');
        if (!botao) {
            return;
        }

        var campo = document.getElementById(botao.dataset.alvo);
        var icone = botao.querySelector('.senha-editar__alternar-icone');
        var mostrar = campo.type === 'password';

        campo.type = mostrar ? 'text' : 'password';
        icone.src = mostrar ? iconeVisivel : iconeOculta;
        botao.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
        botao.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
    });

    /* ---------- Confirmação precisa ser igual à nova senha ---------- */
    var campoNova = document.getElementById('senha-nova');
    var campoConfirmacao = document.getElementById('senha-confirmacao');

    function conferirConfirmacao() {
        var diferente = campoConfirmacao.value !== '' && campoConfirmacao.value !== campoNova.value;
        campoConfirmacao.setCustomValidity(diferente ? 'As senhas não coincidem.' : '');
    }

    campoNova.addEventListener('input', conferirConfirmacao);
    campoConfirmacao.addEventListener('input', conferirConfirmacao);
})();