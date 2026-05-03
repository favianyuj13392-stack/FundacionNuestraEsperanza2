# Especificación de Integración: Pasarela de Donaciones QR BNB

## 1. Contexto del Proyecto
El objetivo es implementar un módulo de cobros por QR para una Fundación. El sistema debe permitir dos modalidades de donación:
1.  **Donación Fija:** El sistema define el monto (ej. campañas específicas de 50 Bs, 100 Bs).
2.  **Donación Variable:** El donante define el monto libremente al momento de pagar.

**Entorno:** Sandbox (Pruebas).
**Moneda por defecto:** Bolivianos (Code: 1).

## 2. Seguridad y Autenticación (Crucial)
* **Gestión de Secretos:** Las credenciales (`userKey`, `accountId`, `authorizationId`) NUNCA deben estar hardcodeadas en el código fuente. Deben cargarse desde variables de entorno (`.env`).
* **Token:** La mayoría de endpoints requieren un Token Bearer. Se debe implementar un método para obtener y renovar este token.

## 3. Definición de Endpoints (Sandbox API)

### A. Autenticación (Obtener Token)
* **Endpoint:** `/ClientAuthentication.API/api/v1/auth/token`
* **Método:** `POST`
* **Headers:** `Content-Type: application/json`
* **Body:**
    ```json
    {
      "accountId": "{{ENV_BNB_ACCOUNT_ID}}",
      "authorizationId": "{{ENV_BNB_AUTH_ID}}"
    }
    ```
* **Respuesta Exitosa:**
    ```json
    { "success": true, "message": "eyJhbGciOiJIUz..." } // El mensaje es el Token
    ```

### B. Generar QR de Donación FIJA
Se utilizará el servicio que devuelve la imagen renderizada para facilitar el frontend.
* **Endpoint:** `/main/getQRWithImageAsync` (Verificar prefijo URL base según entorno: `qrsimpleapiv2`)
* **Método:** `POST`
* **Headers:**
    * `Authorization`: `Bearer <TOKEN>`
    * `Content-Type`: `application/json`
* **Body:**
    ```json
    {
      "currencyCode": 1,        // 1 = Bolivianos
      "amount": 100.00,         // MONTO FIJO DEFINIDO
      "singleUse": true,        // True para donación única, False para campaña permanente
      "expirationDate": "2025-12-31",
      "reference": "Donación Campaña Escolar",
      "additionalData": "ID_Donante_123" // Opcional para tracking interno
    }
    ```
* **Respuesta Clave:**
    * `qr`: String en Base64 (Imagen para mostrar en <img>).
    * `qrId`: ID para consultar el estado.

### C. Generar QR de Donación VARIABLE
Se utiliza el endpoint de Domiciliación Variable o Transferencia Simple sin monto (según disponibilidad del entorno sandbox, intentar primero VariableAmount).
* **Endpoint:** `/DirectDebit/GetQRVariableAmount`
* **Método:** `POST`
* **Headers:** `Authorization`, `Content-Type`
* **Body:**
    ```json
    {
      "currencyCode": 1,
      "reference": "Donación Libre - Fundación XYZ",
      "dueDate": "2025-12-31",
      "serviceCode": "DONACION_LIBRE" // Código interno
    }
    ```
* **Nota de Implementación:** Si este endpoint requiere configuración previa de "Servicio", usar fallback a `/Enterprise/TransferQR` enviando `amount: 0` (sujeto a validación en sandbox).

### D. Consultar Estado de la Donación (Polling)
Para verificar si una donación se completó.
* **Endpoint:** `/main/getQRStatusAsync`
* **Método:** `POST`
* **Body:** `{"qrId": "<ID_RECIBIDO_EN_GENERACION>"}`
* **Respuesta:**
    * `qrId`: Estado (1=No Usado, 2=Pagado/Usado, 3=Expirado).

## 4. Flujo de Integración Sugerido

1.  **Backend (Controller/Service):**
    * Crear clase `BnbQrService`.
    * Método `authenticate()`: Singleton que guarda el token y revisa expiración.
    * Método `createFixedDonation(amount, campaign_name)`: Llama a endpoint B.
    * Método `createVariableDonation(campaign_name)`: Llama a endpoint C.
    * Método `checkStatus(qrId)`: Para confirmar pagos.

2.  **Frontend (UI):**
    * Pantalla con toggle: "Monto sugerido" vs "Otro monto".
    * Renderizar imagen: `<img src="data:image/png;base64,{{response.qr}}" />`
    * Polling: Consultar al backend cada 5 segundos el estado del `qrId` mientras el usuario está en pantalla.

## 5. Manejo de Errores
* **401 Unauthorized:** El token expiró. El servicio debe capturar esto, renovar token y reintentar la petición original automáticamente.
* **Validación:** Asegurar que `amount` siempre sea > 0 en donaciones fijas.