<div class="pmpro-payment-method-pix">
    <h3><?php esc_html_e('Pagar com Pix', 'pmpro-pagbank'); ?></h3>
    <?php if (!empty($pix_qr_code)) : ?>
        <div class="pix-qr-code">
            <img src="<?php echo esc_url($pix_qr_code); ?>" alt="QR Code Pix">
            <p><?php esc_html_e('Expira em:', 'pmpro-pagbank'); ?> <?php echo date_i18n('d/m/Y H:i', strtotime($pix_expiration)); ?></p>
        </div>
    <?php else : ?>
        <button id="generate-pix" class="button"><?php esc_html_e('Gerar Pix', 'pmpro-pagbank'); ?></button>
    <?php endif; ?>
</div>