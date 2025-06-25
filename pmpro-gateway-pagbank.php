<?php
/*
Plugin Name: PMPro Gateway - PagBank
Description: Integración de PagBank con Paid Memberships Pro.
Version: 1.1.0
Author: David Camejo
License: GPLv2
*/

// Definir constantes
define('PMPRO_PAGBANK_DIR', dirname(__FILE__));

// Cargar clases
require_once(PMPRO_PAGBANK_DIR . '/includes/class-pmpro-pagbank-api.php');
require_once(PMPRO_PAGBANK_DIR . '/includes/class-pmpro-pagbank-webhooks.php');

class PMProGateway_PagBank extends PMProGateway {
    public function __construct() {
        $this->gateway = 'pagbank';
        $this->gateway_name = 'PagBank';
        $this->payment_type = 'recurring';
        $this->supports = array('subscriptions', 'cancel_subscriptions');

        // Configurar webhooks
        add_action('init', array($this, 'setup_webhooks'));
    }

    /**
     * Procesar pago inicial
     */
    public function process(&$order) {
        $pagbank_api = new PMPro_PagBank_API();
        $response = $pagbank_api->create_subscription($order);

        if ($response['status'] === 'ACTIVE') {
            $order->payment_transaction_id = $response['id'];
            $order->status = 'success';
            return true;
        } else {
            $order->error = $response['error_message'];
            return false;
        }
    }

    /**
     * Cancelar suscripción
     */
    public function cancel(&$order) {
        $pagbank_api = new PMPro_PagBank_API();
        return $pagbank_api->cancel_subscription($order->subscription_transaction_id);
    }

    /**
     * Configurar webhooks
     */
    public function setup_webhooks() {
        $webhooks = new PMPro_PagBank_Webhooks();
        $webhooks->register();
    }

    /**
     * Campos de configuración en el admin
     */
    public static function getGatewaySettings() {
        return array(
            'pagbank_api_key' => array(
                'title' => 'API Key de PagBank',
                'type' => 'text'
            ),
            'pagbank_environment' => array(
                'title' => 'Entorno',
                'type' => 'select',
                'options' => array(
                    'sandbox' => 'Sandbox',
                    'production' => 'Producción'
                )
            )
        );
    }
}

// Registrar el gateway en PMPro
add_action('init', function() {
    if (class_exists('PMProGateway')) {
        PMProGateway::register_gateway('pagbank', 'PMProGateway_PagBank');
    }
});

// Cargar plantilla de campos de pago
add_filter('pmpro_include_payment_information_fields', function($include, $gateway) {
    if ($gateway == 'pagbank') {
        include(PMPRO_PAGBANK_DIR . '/templates/checkout-fields.php');
        return false; // Evita que PMPro cargue sus campos predeterminados
    }
    return $include;
}, 10, 2);
?>