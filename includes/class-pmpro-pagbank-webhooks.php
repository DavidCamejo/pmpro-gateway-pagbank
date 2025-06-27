<?php
class PMPro_PagBank_Webhooks {
    public function register() {
        add_action('rest_api_init', function() {
            // Webhook principal (para todos los eventos)
            register_rest_route('pmpro-pagbank/v1', '/webhook', array(
                'methods'  => 'POST',
                'callback' => array($this, 'handle_webhook'),
                'permission_callback' => '__return_true'
            ));
            
            // Webhook específico para Pix (opcional)
            register_rest_route('pmpro-pagbank/v1', '/webhook-pix', array(
                'methods'  => 'POST',
                'callback' => array($this, 'handle_pix_webhook'),
                'permission_callback' => '__return_true'
            ));
        });
    }

    /**
     * Verifica la firma del webhook.
     */
    private function verify_signature($request) {
        $payload = $request->get_json_params();
        $signature = $request->get_header('PagBank-Signature');
        $secret = pmpro_getOption('pagbank_api_key');
        $computed_signature = hash_hmac('sha256', json_encode($payload), $secret);
        return hash_equals($signature, $computed_signature);
    }

    /**
     * Manejar eventos genéricos.
     */
    public function handle_webhook($request) {
        $payload = $request->get_json_params();
        if (!$this->verify_signature($request)) {
            return new WP_REST_Response(array('error' => 'Firma inválida'), 403);
        }

        switch ($payload['event']) {
            case 'PAYMENT_PIX_RECEIVED':
                $this->handle_pix_payment($payload);
                break;
            case 'PAYMENT_BOLETO_PAID':
                $this->handle_boleto_payment($payload);
                break;
            case 'PAYMENT_CONFIRMED':
            case 'CHARGE_PAID':
                $this->process_recurring_payment($payload);
                break;
            case 'PAYMENT_FAILED':
            case 'CHARGE_FAILED':
                $this->process_failed_payment($payload);
                break;
            default:
                error_log('PagBank: Evento no manejado: ' . $payload['event']);
        }
        return new WP_REST_Response(array('status' => 'success'), 200);
    }

    /**
     * Webhook dedicado solo para Pix (ejemplo avanzado).
     */
    public function handle_pix_webhook($request) {
        $payload = $request->get_json_params();
        if (!$this->verify_signature($request)) {
            error_log("Webhook Pix: Firma inválida");
            return new WP_REST_Response(array('error' => 'Firma inválida'), 403);
        }

        $this->handle_pix_payment($payload);
        return new WP_REST_Response(array('status' => 'processed'), 200);
    }

    /**
     * Procesar pago exitoso via Pix.
     */
    private function handle_pix_payment($payload) {
        $payment_id = $payload['payment']['id'];
        $order = $this->get_order_by_payment_id($payment_id);

        if ($order && $payload['payment']['status'] === 'PAID') {
            pmpro_changeMembershipLevel($order->membership_id, $order->user_id);
            $order->status = 'success';
            $order->saveOrder();
            
            // Opcional: Enviar email de confirmación
            wp_mail(
                $order->user_email,
                'Pago con Pix confirmado',
                'Tu membresía ha sido activada.'
            );
        }
    }

    /**
     * Procesar pago exitoso via Boleto.
     */
    private function handle_boleto_payment($payload) {
        $payment_id = $payload['payment']['id'];
        $order = $this->get_order_by_payment_id($payment_id);

        if ($order && $payload['payment']['status'] === 'PAID') {
            $order->status = 'success';
            $order->saveOrder();
            
            // Actualizar membresía y notificar
            pmpro_changeMembershipLevel($order->membership_id, $order->user_id);
        }
    }

    /**
     * Helper: Obtener orden por payment_id.
     */
    private function get_order_by_payment_id($payment_id) {
        global $wpdb;
        $order_id = $wpdb->get_var(
            $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_payment_id' AND meta_value = %s", $payment_id)
        );
        return new MemberOrder($order_id);
    }
    
    /**
     * Procesar pagos recurrentes exitosos.
     */
    private function process_recurring_payment($payload) {
        $subscription_id = $payload['subscription_id'];
        $order = new MemberOrder();
        $order->getLastMemberOrderBySubscriptionTransactionID($subscription_id);

        if (empty($order->id)) {
            error_log("Orden no encontrada para suscripción: " . $subscription_id);
            return;
        }

        // Registrar el pago en PMPro
        $order->payment_transaction_id = $payload['id'];
        $order->status = 'success';
        $order->saveOrder();
    }

    /**
     * Manejar pagos fallidos.
     */
    private function process_failed_payment($payload) {
        $subscription_id = $payload['subscription_id'];
        $order = new MemberOrder();
        $order->getLastMemberOrderBySubscriptionTransactionID($subscription_id);

        if (!empty($order->id)) {
            $order->status = 'error';
            $order->error = $payload['failure_reason'];
            $order->saveOrder();
            
            // Opcional: Enviar email al admin
            wp_mail(
                get_option('admin_email'),
                'Pago recurrente fallido',
                "La suscripción {$subscription_id} falló. Motivo: " . $payload['failure_reason']
            );
        }
    }
}
?>