<?php
if (!defined('ABSPATH')) exit;

class PMPro_PagBank_Retry_Queue {
    private static $table_name = 'pmpro_pagbank_retry_queue';

    /**
     * Inicializar la tabla de cola.
     */
    public static function init_db() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;
        
        $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(50) NOT NULL,
            data TEXT NOT NULL,
            attempts INT DEFAULT 0,
            next_retry DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    /**
     * Añadir acción a la cola.
     */
    public static function add_to_queue($action, $data, $delay_minutes = 10) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;
        
        $wpdb->insert($table, array(
            'action' => $action,
            'data' => json_encode($data),
            'next_retry' => date('Y-m-d H:i:s', strtotime("+$delay_minutes minutes"))
        ));
    }

    /**
     * Procesar cola de reintentos.
     */
    public static function process_queue() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;
        $now = current_time('mysql');
        $items = $wpdb->get_results("SELECT * FROM $table WHERE next_retry <= '$now' AND attempts < 3");

        foreach ($items as $item) {
            $data = json_decode($item->data, true);
            $success = false;

            switch ($item->action) {
                case 'retry_payment':
                    $api = new PMPro_PagBank_API();
                    $success = $api->retry_payment($data['payment_id']);
                    break;
            }

            if ($success) {
                $wpdb->delete($table, array('id' => $item->id));
            } else {
                $wpdb->update($table, array(
                    'attempts' => $item->attempts + 1,
                    'next_retry' => date('Y-m-d H:i:s', strtotime('+1 hour'))
                ), array('id' => $item->id));
            }
        }
    }
}

// Registrar evento diario para procesar la cola
add_action('wp', function() {
    if (!wp_next_scheduled('pmpro_pagbank_process_retry_queue')) {
        wp_schedule_event(time(), 'hourly', 'pmpro_pagbank_process_retry_queue');
    }
});

add_action('pmpro_pagbank_process_retry_queue', array('PMPro_PagBank_Retry_Queue', 'process_queue'));
register_activation_hook(__FILE__, array('PMPro_PagBank_Retry_Queue', 'init_db'));
?>