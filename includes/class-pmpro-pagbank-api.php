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
}
?>