(function () {
    function formatar(valor) {
        const d = valor.replace(/\D/g, '').slice(0, 11);

        if (d.length === 0) return '';
        if (d.length <= 2) return '(' + d;
        if (d.length <= 6) return '(' + d.slice(0, 2) + ') ' + d.slice(2);
        if (d.length <= 10) return '(' + d.slice(0, 2) + ') ' + d.slice(2, 6) + '-' + d.slice(6);
        return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
    }

    document.querySelectorAll('input[type="tel"]').forEach(function (campo) {
        campo.setAttribute('maxlength', '15');
        campo.setAttribute('inputmode', 'numeric');

        // Formata o valor que já vem preenchido (edição de perfil ou erro de validação)
        campo.value = formatar(campo.value);

        campo.addEventListener('input', function () {
            campo.value = formatar(campo.value);
        });
    });
})();