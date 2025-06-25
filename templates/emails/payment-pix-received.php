<?php
/**
 * Asunto: Tu Pix para la membresía está listo
 * Contenido:
 */
?>
<h2>¡Gracias por tu compra!</h2>
<p>Aquí están los detalles para completar tu pago via Pix:</p>
<ul>
    <li><strong>Código Pix:</strong> <?php echo esc_html($pix_qr_code); ?></li>
    <li><strong>Valor:</strong> R$ <?php echo number_format($order->total, 2, ',', '.'); ?></li>
    <li><strong>Expira en:</strong> <?php echo date_i18n('d/m/Y H:i', $pix_expiration); ?></li>
</ul>