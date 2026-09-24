(function () {
    'use strict';

    var TAMANHO_MAXIMO_FOTO = 2 * 1024 * 1024; // 2 MB (o Controller valida de novo)
    var TIPOS_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp'];

    var campoFoto = document.getElementById('perfil-foto');
    var previaFoto = document.getElementById('perfil-avatar-previa');
    var campoTelefone = document.getElementById('perfil-telefone');
    var urlPreviaAtual = null;

    /* ---------- Prévia da foto ---------- */
    if (campoFoto && previaFoto) {
        campoFoto.addEventListener('change', function () {
            var arquivo = campoFoto.files[0];
            if (!arquivo) {
                return;
            }

            if (TIPOS_PERMITIDOS.indexOf(arquivo.type) === -1) {
                alert('Escolha uma imagem JPG, PNG ou WEBP.');
                campoFoto.value = '';
                return;
            }

            if (arquivo.size > TAMANHO_MAXIMO_FOTO) {
                alert('A foto deve ter no máximo 2 MB.');
                campoFoto.value = '';
                return;
            }

            if (urlPreviaAtual) {
                URL.revokeObjectURL(urlPreviaAtual);
            }
            urlPreviaAtual = URL.createObjectURL(arquivo);
            previaFoto.src = urlPreviaAtual;
        });
    }

    /* ---------- Máscara de telefone: (00) 00000-0000 ---------- */
    function formatarTelefone(valor) {
        var numeros = valor.replace(/\D/g, '').slice(0, 11);

        if (numeros.length <= 2) {
            return numeros;
        }
        if (numeros.length <= 6) {
            return '(' + numeros.slice(0, 2) + ') ' + numeros.slice(2);
        }
        if (numeros.length <= 10) {
            return '(' + numeros.slice(0, 2) + ') ' + numeros.slice(2, 6) + '-' + numeros.slice(6);
        }
        return '(' + numeros.slice(0, 2) + ') ' + numeros.slice(2, 7) + '-' + numeros.slice(7);
    }

    if (campoTelefone) {
        campoTelefone.value = formatarTelefone(campoTelefone.value);
        campoTelefone.addEventListener('input', function () {
            campoTelefone.value = formatarTelefone(campoTelefone.value);
        });
    }
})();