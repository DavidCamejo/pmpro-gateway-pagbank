<?php
/*
Plugin Name: PMPro Gateway - PagBank
Plugin URI: https://github.com/DavidCamejo/pmpro-gateway-pagbank
Description: Integración de PagBank como gateway de pago para Paid Memberships Pro
Version: 1.0.3
Author: David Camejo
License: GPLv2
*/

// Definir constantes
define('PMPRO_PAGBANK_DIR', dirname(__FILE__));

// Cargar clases
require_once(PMPRO_PAGBANK_DIR . '/includes/class-pmpro-pagbank-api.php');
require_once(PMPRO_PAGBANK_DIR . '/includes/class-pmpro-pagbank-webhooks.php');

// Inicializar API
$pagbank_api = new PMPro_PagBank_API();

// Registrar scripts y estilos
add_action('wp_enqueue_scripts', array($pagbank_api, 'enqueue_scripts'));

// Registrar webhooks en la activación del plugin
function pmpro_pagbank_activate() {
    $api = new PMPro_PagBank_API();
    $api->register_webhooks();
}
register_activation_hook(__FILE__, 'pmpro_pagbank_activate');

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
     * Estático: Registrar el gateway
     */
    public static function register_gateway($gateway_name) {
        // Registrar el gateway manualmente ya que la función que necesitamos no está disponible
        global $pmpro_gateways;
        $pmpro_gateways[$gateway_name] = __CLASS__;
        add_filter('pmpro_gateways', function($gateways) use ($gateway_name) {
            $gateways[$gateway_name] = __CLASS__;
            return $gateways;
        });
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
        // Usar el método de la clase PMProGateway para registrar el gateway
        PMProGateway_PagBank::register_gateway('pagbank');
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