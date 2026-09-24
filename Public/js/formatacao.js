const inputTelefone = document.querySelector("#sacit-coordenador-celular");

if (inputTelefone) {

    inputTelefone.addEventListener("input", function () {

        let valor = inputTelefone.value.replace(/\D/g, "");

        if (valor.length > 11) {
            valor = valor.substring(0, 11);
        }

        if (valor.length <= 10) {

            // Telefone fixo
            valor = valor.replace(/^(\d{2})(\d)/, "($1) $2");
            valor = valor.replace(/(\d{4})(\d)/, "$1-$2");

        } else {

            // Celular
            valor = valor.replace(/^(\d{2})(\d)/, "($1) $2");
            valor = valor.replace(/(\d{5})(\d)/, "$1-$2");

        }

        inputTelefone.value = valor;
    });
}


const inputCelular = document.querySelector("#estagiario-cadastro-celular");

if (inputCelular) {

    inputCelular.addEventListener("input", function () {

        let valor = inputCelular.value.replace(/\D/g, "");

        if (valor.length > 11) {
            valor = valor.substring(0, 11);
        }

        if (valor.length <= 10) {

            // Telefone fixo
            valor = valor.replace(/^(\d{2})(\d)/, "($1) $2");
            valor = valor.replace(/(\d{4})(\d)/, "$1-$2");

        } else {

            // Celular
            valor = valor.replace(/^(\d{2})(\d)/, "($1) $2");
            valor = valor.replace(/(\d{5})(\d)/, "$1-$2");

        }

        inputCelular.value = valor;
    });
}
