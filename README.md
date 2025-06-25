# PMPro Gateway - PagBank

![Plugin Version](https://img.shields.io/badge/version-1.0-blue) 
![PMPro Compatible](https://img.shields.io/badge/PMPro-2.9%2B-green) 
![License](https://img.shields.io/badge/license-GPLv2-orange)

Gateway de pago nativo para **Paid Memberships Pro** que integra PagBank como método de pago, soportando suscripciones recurrentes, pagos únicos y webhooks automáticos. Compatible con los métodos de pago **tarjeta de crédito**, **Pix** y **Boleto**.

---

## 🚀 Características

- ✅ **Pagos recurrentes** y únicos con tarjeta de crédito, Pix y Boleto.
- ✅ **Webhooks e IPN** para procesar renovaciones automáticas y manejar eventos de PagBank.
- ✅ **Compatibilidad total** con PMPro (niveles de membresía, altas, renovaciones, cancelaciones, reintentos).
- ✅ **Validaciones** de tarjeta, CPF y teléfono en el checkout.
- ✅ **Modo Sandbox** y Producción seleccionable.
- ✅ **Reintentos automáticos** ante pagos fallidos.
- ✅ **Notificaciones por email** a administradores y usuarios.
- ✅ **Checkout personalizado** con campos dedicados a PagBank.
- ✅ **Creación automática de planes** en PagBank según nivel de membresía.

---

## 📦 Instalación

1. Descarga el [ZIP del plugin](https://github.com/DavidCamejo/pmpro-gateway-pagbank/archive/main.zip).
2. En WordPress: **Plugins > Añadir nuevo > Subir plugin**.
3. Activa el plugin **PMPro Gateway - PagBank**.

---

## ⚙️ Configuración

1. Ve a **Memberships > Configuración de Pagos** en WordPress.
2. Selecciona **PagBank** como gateway de pago.
3. Ingresa tus credenciales:
   - **API Key**: Obténla desde el [dashboard de PagBank](https://pagseguro.uol.com.br/).
   - **Entorno**: Elige entre Sandbox o Producción.
4. Guarda los cambios.

> **Nota:** Asegúrate de tener las credenciales correctas para cada entorno.

---

## 💵 Métodos de pago soportados

- **Tarjeta de crédito** (pagos recurrentes y únicos)
- **Pix** (pago único)
- **Boleto** (pago único)

La selección del método de pago se realiza en el checkout. Los campos requeridos (como CPF y teléfono) aparecerán automáticamente.

---

## 📝 Campos personalizados en el checkout

El plugin añade validaciones y campos específicos para Brasil:

- **CPF** (obligatorio para PagBank)
- **Teléfono móvil**
- **Datos de tarjeta/Pix/Boleto** según método seleccionado

Ejemplo de campos:
```
[Nombre completo] [Email] [CPF] [Teléfono] [Número tarjeta] [Expiración] [CVV]
```
*Los campos pueden variar según el método.*

---

## 🌐 Endpoints de Webhook / IPN

Para procesar pagos recurrentes y notificaciones automáticas:

1. Copia la URL IPN que genera el plugin (por ejemplo: `https://tusitio.com/?pagbank_ipn=1`).
2. Regístrala en la sección de webhooks del [dashboard de PagBank](https://dev.pagbank.uol.com.br/docs).
3. El plugin valida automáticamente la firma y procesa eventos como pagos recibidos, fallos y cancelaciones.

---

## 📄 Estructura de Archivos

```
/pmpro-gateway-pagbank/
├── assets/         # JS, CSS e imágenes
├── includes/       # Lógica principal del gateway
├── templates/      # Plantillas para el checkout
├── pmpro-gateway-pagbank.php # Archivo principal del plugin
└── README.md
```

---

## 🛠️ Requisitos

- WordPress 5.0 o superior.
- Paid Memberships Pro 2.9 o superior.
- PHP 7.4 o superior.
- Cuenta activa en PagBank.

---

## 🧪 Pruebas recomendadas

- Realiza pruebas completas en modo **Sandbox** antes de activar en producción.
- Verifica:
  - Altas y pagos con cada método.
  - Renovaciones automáticas y cancelaciones.
  - Notificaciones de pagos fallidos y reintentos.
  - Recepción y validación de webhooks/IPN.
- Consulta logs y habilita el modo debug de WordPress si tienes problemas.

---

## ⚠️ Limitaciones conocidas

- Soporte oficial solo para Brasil.
- El plugin depende de la estabilidad de la API de PagBank y PMPro.
- Asegúrate que PMPro esté actualizado.

---

## 💬 Soporte

¿Problemas?  
- Abre un [issue en GitHub](https://github.com/DavidCamejo/pmpro-gateway-pagbank/issues).
- Consulta la [documentación de PagBank](https://dev.pagbank.uol.com.br/docs).

---

## 📜 Licencia

Licenciado bajo **GPLv2**. Ver [LICENSE](LICENSE) para más detalles.

---

## 📌 Notas adicionales

- Puedes contribuir enviando pull requests o reportando errores.
- El roadmap y futuras mejoras se publicarán en la sección de issues/discusiones del repositorio.
