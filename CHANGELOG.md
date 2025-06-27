# Changelog

## [1.0.1] - 2023-06-26

### Fixed

- Corregida la estructura de la clase `PMPro_PagBank_Webhooks`
- Eliminado método duplicado `verify_signature`
- Corregida la indentación y consistencia del estilo del código (reemplazados tabs por espacios)
- Mejorado el manejo de eventos del webhook con declaraciones case adecuadas para diferentes escenarios de pago
- Corregidos métodos que estaban incorrectamente colocados fuera del cuerpo de la clase
- Añadido manejo para eventos adicionales de pago como `PAYMENT_CONFIRMED`, `CHARGE_PAID`, `PAYMENT_FAILED` y `CHARGE_FAILED`

### Archivos modificados

- `includes/class-pmpro-pagbank-webhooks.php`

## [1.0.0] - 2023-06-25

### Added

- Versión inicial del plugin de pasarela de pago PagBank para Paid Memberships Pro
- Soporte para pagos con tarjeta de crédito
- Soporte para pagos con Pix
- Soporte para pagos con Boleto
- Integración con webhooks de PagBank