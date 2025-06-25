<?php
if (!defined('ABSPATH')) exit;
wp_nonce_field('pagbank_process_payment', 'pagbank_payment_nonce');
?>

<div class="pmpro_checkout-fields">
    <h3>Información de Pago</h3>
    <div class="pmpro_checkout-field">
        <label for="cpf">CPF (Obligatorio)</label>
        <input id="cpf" name="cpf" type="text" class="input" required />
    </div>
    <div class="pmpro_checkout-field">
        <label for="phone">Teléfono</label>
        <input id="phone" name="phone" type="tel" class="input" required />
    </div>
    <div class="pmpro_checkout-field pmpro_payment-card-number">
        <label for="cardnumber">Número de Tarjeta</label>
        <input id="cardnumber" name="cardnumber" type="text" class="input" />
    </div>
    <div class="pmpro_checkout-field pmpro_payment-expiry">
        <label for="exp_month">Fecha de Expiración (MM/AA)</label>
        <input id="exp_month" name="exp_month" type="text" placeholder="MM" />
        <input id="exp_year" name="exp_year" type="text" placeholder="AA" />
    </div>
    <div class="pmpro_checkout-field pmpro_payment-cvv">
        <label for="cvv">CVV</label>
        <input id="cvv" name="cvv" type="text" class="input" />
    </div>
</div>