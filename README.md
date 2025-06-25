# PMPro Gateway - PagBank

![Plugin Version](https://img.shields.io/badge/version-1.0-blue) 
![PMPro Compatible](https://img.shields.io/badge/PMPro-2.9%2B-green) 
![License](https://img.shields.io/badge/license-GPLv2-orange)

Gateway de pago nativo para **Paid Memberships Pro** que integra PagBank como método de pago, soportando suscripciones recurrentes, pagos únicos y webhooks automáticos.

## 🚀 Características
- ✅ **Pagos recurrentes** con tarjeta de crédito.
- ✅ **Webhooks** para procesar renovaciones automáticas.
- ✅ **Compatibilidad total** con PMPro (niveles de membresía, cancelaciones, etc.).
- ✅ **Validaciones** de tarjetas y CPF en el checkout.
- ✅ **Modo Sandbox** para pruebas.

## 📦 Instalación
1. Descarga el [ZIP del plugin](https://github.com/tu-usuario/pmpro-gateway-pagbank/archive/main.zip).
2. En WordPress: **Plugins > Añadir nuevo > Subir plugin**.
3. Activa el plugin **PMPro Gateway - PagBank**.

## ⚙️ Configuración
1. Ve a **Memberships > Configuración de Pagos** en WordPress.
2. Selecciona **PagBank** como gateway.
3. Ingresa tus credenciales:
   - **API Key**: Obténla desde el [dashboard de PagBank](https://pagseguro.uol.com.br/).
   - **Entorno**: Elige entre Sandbox o Producción.

![Configuración del Gateway](assets/screenshots/settings.png)

## 🛠️ Requisitos
- WordPress 5.0+.
- Paid Memberships Pro 2.9+.
- PHP 7.4+.

## 🌐 Endpoints de Webhook
Para procesar pagos recurrentes, registra esta URL en PagBank:


## 📄 Estructura de Archivos

/pmpro-gateway-pagbank/
├── assets/ # JS, CSS e imágenes
├── includes/ # Lógica del gateway
├── templates/ # Plantillas de checkout
├── pmpro-gateway-pagbank.php # Archivo principal
└── README.md


## 🐛 Soporte
¿Problemas? Abre un [issue en GitHub](https://github.com/tu-usuario/pmpro-gateway-pagbank/issues) o consulta la [documentación de PagBank](https://dev.pagbank.uol.com.br/docs).

## 📜 Licencia
Licenciado bajo **GPLv2**. Ver [LICENSE](LICENSE) para más detalles.

## 📌 Notas adicionales:
1. Badges Personalizables:
   ◦ Actualiza los enlaces de https://img.shields.io si tienes un repositorio público.
   ◦ Ejemplo para versión:

    ![Version](https://img.shields.io/badge/version-1.0.0-blue)

2. Capturas de Pantalla:
   Si añades imágenes, guárdalas en /assets/screenshots/ y actualiza la ruta en el README.

3. Enlaces Reales:
   Reemplaza https://github.com/tu-usuario/... con tu URL de GitHub.

4. Documentación Extendida:
   Puedes añadir una sección "Desarrollo" con instrucciones para contribuir.

