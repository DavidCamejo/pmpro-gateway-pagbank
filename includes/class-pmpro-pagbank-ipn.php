<?php
if (!defined('ABSPATH')) exit;

class PMPro_PagBank_IPN {
    private $api;

    public function __construct() {
        $this->api = new PMPro_PagBank_API();
        add_action('init', array($this, 'init_ipn_listener'));
    }

    /**
     * Iniciar listener para IPN de PagBank.
     */
    public function init_ipn_listener() {
        if (isset($_GET['pagbank_ipn'])) {
            $this->handle_ipn();
        }
    }

    /**
     * Procesar notificación IPN.
     */
    public function handle_ipn() {
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        if (!$this->validate_ipn($data)) {
            status_header(403);
            exit;
        }

        // Ejemplo: Actualizar orden cuando el pago es aprobado
        if ($data['event'] === 'PAYMENT_RECEIVED') {
            $order = new MemberOrder();
            $order->getMemberOrderByPaymentTransactionID($data['id']);
            
            if (!empty($order->id)) {
                $order->status = 'success';
                $order->saveOrder();
            }
        }

        status_header(200);
        exit;
    }

    /**
     * Validar firma del IPN.
     */
    private function validate_ipn($data) {
        $received_signature = $_SERVER['HTTP_X_PAGBANK_SIGNATURE'];
        $expected_signature = hash_hmac('sha256', json_encode($data), pmpro_getOption('pagbank_api_key'));
        return hash_equals($expected_signature, $received_signature);
    }

	/**
	 * Manejar pago fallido.
	 */
	private function handle_failed_payment($data) {
		$order = new MemberOrder();
		$order->getMemberOrderByPaymentTransactionID($data['payment_id']);

		if (!empty($order->id)) {
			// Añadir a cola de reintentos
			PMPro_PagBank_Retry_Queue::add_to_queue('retry_payment', array(
				'payment_id' => $data['payment_id'],
				'order_id' => $order->id
			));

			// Notificar al usuario
			wp_mail(
				$order->Email,
				'Seu pagamento falhou - Tentaremos novamente',
				'Estamos tentando processar seu pagamento novamente.'
			);
		}
	}
}

new PMPro_PagBank_IPN();
?>