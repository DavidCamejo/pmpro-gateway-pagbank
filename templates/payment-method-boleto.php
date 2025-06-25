<div class="pmpro-payment-method-boleto">
    <h3><?php esc_html_e('Pagar com Boleto', 'pmpro-pagbank'); ?></h3>
    <?php if (!empty($boleto_url)) : ?>
        <a href="<?php echo esc_url($boleto_url); ?>" class="button" target="_blank">
            <?php esc_html_e('Imprimir Boleto', 'pmpro-pagbank'); ?>
        </a>
    <?php else : ?>
        <button id="generate-boleto" class="button"><?php esc_html_e('Gerar Boleto', 'pmpro-pagbank'); ?></button>
    <?php endif; ?>
</div>