jQuery(document).ready(function($) {
    // Máscara para CPF
    $('#pagbank_cpf').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 3) value = value.replace(/^(\d{3})/, '$1.');
        if (value.length > 7) value = value.replace(/^(\d{3}\.\d{3})/, '$1.');
        if (value.length > 11) value = value.replace(/^(\d{3}\.\d{3}\.\d{3})/, '$1-');
        $(this).val(value.substring(0, 14));
    });

    // Validar tarjeta con Luhn
    $('#cardnumber').on('blur', function() {
        const cardNumber = $(this).val().replace(/\s+/g, '');
        if (!validateCreditCard(cardNumber)) {
            alert('Número de tarjeta inválido');
            $(this).focus();
        }
    });

    function validateCreditCard(number) {
        return /^[0-9]{13,16}$/.test(number) && luhnCheck(number);
    }

    function luhnCheck(number) {
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