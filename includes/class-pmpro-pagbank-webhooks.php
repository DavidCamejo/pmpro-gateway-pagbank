<?php
class PMPro_PagBank_Webhooks {
    public function register() {
        add_action('rest_api_init', function() {
            register_rest_route('pmpro-pagbank/v1', '/webhook', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_webhook'),
                'permission_callback' => '__return_true'
            ));
        });
    }

    public function handle_webhook($request) {
        $payload = $request->get_json_params();
        $signature = $request->get_header('X-PagBank-Signature');

        if (!$this->verify_signature($payload, $signature)) {
            return new WP_REST_Response(array('error' => 'Firma inválida'), 403);
        }

        if ($payload['event'] == 'RECURRENCE_SUCCESS') {
            $this->process_recurring_payment($payload);
        }

        return new WP_REST_Response(array('status' => 'success'), 200);
    }

    private function verify_signature($payload, $signature) {
        $secret = pmpro_getOption('pagbank_api_key');
        $computed_signature = hash_hmac('sha256', json_encode($payload), $secret);
        return hash_equals($signature, $computed_signature);
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