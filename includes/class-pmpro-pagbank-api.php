<?php
class PMPro_PagBank_API {
    private $api_key;
    private $environment;

    public function __construct() {
        $this->api_key = pmpro_getOption('pagbank_api_key');
        $this->environment = pmpro_getOption('pagbank_environment');
    }

    /**
     * Crear una suscripción en PagBank
     */
    public function create_subscription($order) {
        $endpoint = $this->get_api_url() . '/subscriptions';
        $plan_id = $this->get_or_create_plan($order->membership_level);

        $data = array(
            'plan_id' => $plan_id,
            'customer' => $this->get_customer_data($order),
            'payment_method' => array(
                'type' => 'CREDIT_CARD',
                'card' => array(
                    'number' => sanitize_text_field($_POST['cardnumber']),
                    'exp_month' => sanitize_text_field($_POST['exp_month']),
                    'exp_year' => sanitize_text_field($_POST['exp_year']),
                    'cvv' => sanitize_text_field($_POST['cvv'])
                )
            )
        );

        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($data)
        ));

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function get_api_url() {
        return ($this->environment == 'sandbox') 
            ? 'https://sandbox.api.pagseguro.com' 
            : 'https://api.pagseguro.com';
    }

	/**
	 * Obtener o crear un plan en PagBank según el nivel de membresía.
	 */
	private function get_or_create_plan($membership_level) {
		$plan_id = 'pmpro_plan_' . $membership_level->id;
		$endpoint = $this->get_api_url() . '/plans';

		// Verificar si el plan ya existe
		$response = wp_remote_get($endpoint . '/' . $plan_id, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->api_key
			)
		));

		if (wp_remote_retrieve_response_code($response) === 200) {
			return $plan_id;
		}

		// Crear nuevo plan
		$data = array(
			'id' => $plan_id,
			'name' => $membership_level->name,
			'amount' => $membership_level->initial_payment * 100, // En centavos
			'interval' => array(
				'length' => $membership_level->cycle_number,
				'unit' => strtoupper($membership_level->cycle_period) // DAY, MONTH, YEAR
			)
		);

		$response = wp_remote_post($endpoint, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->api_key,
				'Content-Type' => 'application/json'
			),
			'body' => json_encode($data)
		));

		return (wp_remote_retrieve_response_code($response) === 201) ? $plan_id : false;
	}


    /**
     * Solicitud genérica a la API.
     */
    private function api_request($endpoint, $method = 'GET', $data = array()) {
        $url = $this->get_api_url() . '/' . $endpoint;
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'method' => $method,
            'body' => $method !== 'GET' ? json_encode($data) : null
        );

        $response = wp_remote_request($url, $args);
        return json_decode(wp_remote_retrieve_body($response), true);
    }

	/**
	 * Preparar datos del cliente para PagBank.
	 */
	private function get_customer_data($order) {
		$user = get_userdata($order->user_id);
		return array(
			'name' => $user->first_name . ' ' . $user->last_name,
			'email' => $user->user_email,
			'tax_id' => sanitize_text_field($_POST['cpf']), // Campo personalizado en checkout
			'phones' => array(
				array(
					'country' => '55',
					'area' => '11',
					'number' => sanitize_text_field($_POST['phone']),
					'type' => 'MOBILE'
				)
			)
		);
	}

	// Cargar JS y CSS
	add_action('wp_enqueue_scripts', function() {
		if (pmpro_is_checkout()) {
			wp_enqueue_script(
				'pmpro-pagbank-js',
				plugin_dir_url(__FILE__) . 'assets/js/pagbank-checkout.js',
				array('jquery'),
				'1.0',
				true
			);
			wp_enqueue_style(
				'pmpro-pagbank-css',
				plugin_dir_url(__FILE__) . 'assets/css/pagbank-checkout.css',
				array(),
				'1.0'
			);
		}
	});

	public function cancel_subscription($subscription_id) {
		$endpoint = $this->get_api_url() . '/subscriptions/' . $subscription_id . '/cancel';
		$response = wp_remote_post($endpoint, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->api_key
			)
		));
		return wp_remote_retrieve_response_code($response) === 200;
	}

	/**
	 * Procesar pago via Pix.
	 */
	public function create_pix_payment($order) {
		$data = array(
			'payment_method' => 'PIX',
			'amount' => $order->initial_payment * 100, // En centavos
			'customer' => $this->get_customer_data($order),
			'metadata' => array(
				'order_id' => $order->code,
				'pmpro_level_id' => $order->membership_level->id
			),
			'expires_in' => 1800 // 30 minutos (estándar para Pix)
		);

		$response = $this->api_request('payments', 'POST', $data);
		
		if ($response['status'] === 'WAITING') {
			return array(
				'status' => 'success',
				'payment_id' => $response['id'],
				'pix_qr_code' => $response['pix']['qr_code'],
				'pix_expiration' => $response['pix']['expiration_date']
			);
		} else {
			return array(
				'status' => 'error',
				'error_message' => $response['error_message'] ?? 'Error al generar Pix'
			);
		}
	}

	/**
	 * Procesar pago via Boleto.
	 */
	public function create_boleto_payment($order) {
		$data = array(
			'payment_method' => 'BOLETO',
			'amount' => $order->initial_payment * 100,
			'customer' => $this->get_customer_data($order),
			'boleto' => array(
				'due_date' => date('Y-m-d', strtotime('+3 days')),
				'instructions' => 'Pagar até a data de vencimento.'
			)
		);

		$response = $this->api_request('payments', 'POST', $data);
		
		if ($response['status'] === 'WAITING') {
			return array(
				'status' => 'success',
				'payment_id' => $response['id'],
				'boleto_url' => $response['boleto']['url']
			);
		} else {
			return array(
				'status' => 'error',
				'error_message' => $response['error_message'] ?? 'Error al generar Boleto'
			);
		}
	}

	/**
	 * Reintentar pago fallido.
	 */
	public function retry_payment($payment_id) {
		$response = $this->api_request("payments/$payment_id/retry", 'POST');
		return $response['status'] === 'PAID';
	}

	/**
	 * Verificar estado de pago (para Pix/Boleto).
	 */
	public function check_payment_status($payment_id) {
		$response = $this->api_request("payments/$payment_id", 'GET');
		return $response['status']; // PAID, WAITING, FAILED
	}

	/**
	 * Registrar webhooks en PagBank al activar el plugin.
	 */
	public function register_webhooks() {
		$webhook_url = rest_url('pmpro-pagbank/v1/webhook');
		$data = array(
			'url' => $webhook_url,
			'events' => array(
				'PAYMENT_PIX_RECEIVED',
				'PAYMENT_BOLETO_PAID'
			)
		);
		$response = $this->api_request('webhooks', 'POST', $data);
		if ($response['id']) {
			update_option('pmpro_pagbank_webhook_id', $response['id']);
		}
	}

	// Llamar al activar el plugin
	register_activation_hook(__FILE__, array('PMPro_PagBank_API', 'register_webhooks'));
}
?>