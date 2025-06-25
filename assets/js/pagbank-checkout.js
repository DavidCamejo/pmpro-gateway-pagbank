jQuery(document).ready(function($) {
    // Validar CPF
    $('#cpf').on('blur', function() {
        const cpf = $(this).val().replace(/[^\d]/g, '');
        if (!validarCPF(cpf)) {
            alert('CPF inválido');
            $(this).focus();
        }
    });

    // Validar tarjeta con Luhn
    $('#cardnumber').on('blur', function() {
        const cardNumber = $(this).val().replace(/\s+/g, '');
        if (!validarTarjeta(cardNumber)) {
            alert('Número de tarjeta inválido');
            $(this).focus();
        }
    });

    function validarCPF(cpf) {
        // Implementar lógica de validación de CPF
        return cpf.length === 11;
    }

    function validarTarjeta(number) {
        // Algoritmo de Luhn
        let sum = 0;
        for (let i = 0; i < number.length; i++) {
            let digit = parseInt(number.charAt(i));
            if (i % 2 === 0) digit *= 2;
            if (digit > 9) digit -= 9;
            sum += digit;
        }
        return sum % 10 === 0;
    }
});