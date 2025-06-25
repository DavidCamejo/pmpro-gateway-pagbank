<?php
class PMPro_PagBank_Webhook_Helper {
    /**
     * Verificar firma del webhook.
     */
    public static function verify_signature($request) {
        $received_signature = $request->get_header('X-PagBank-Signature');
        $payload = $request->get_body();
        $secret = pmpro_getOption('pagbank_api_key');
        $expected_signature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected_signature, $received_signature);
    }

    /**
     * Loggear eventos para depuración.
     */
    public static function log_event($event, $data) {
        $log_file = WP_CONTENT_DIR . '/pagbank-webhooks.log';
        $entry = "[" . date('Y-m-d H:i:s') . "] Evento: $event\n" . print_r($data, true) . "\n\n";
        file_put_contents($log_file, $entry, FILE_APPEND);
    }
}
?>