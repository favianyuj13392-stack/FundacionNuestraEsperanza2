# 🛡️ Módulo de Donaciones y Pasarela BNB - ZONA RESTRINGIDA

Este documento especifica todos los artefactos (Base de datos, Backend, Frontend y Entorno) que componen el **Módulo de Donaciones** y la **Integración con la Pasarela de Pagos BNB**. 

> [!WARNING]
> **ATENCIÓN DESARROLLADORES DE CMS / OTROS MÓDULOS**
> El sistema de donaciones y la pasarela BNB se encuentran en estado **FUNCIONAL validado de extremo a extremo**. **NO alterar, renombrar ni modificar** las estructuras de datos, controladores, rutas o servicios listados a continuación, ya que cualquier cambio puede romper el flujo de pagos, la validación de webhooks o la emisión de certificados.

---

## 1. Estructura de Base de Datos (Tablas Intocables)

Las siguientes tablas están estrechamente interconectadas para manejar la trazabilidad desde que un usuario genera un QR hasta que se valida el pago y se emite su certificado.

- `qrs`: Almacena la intención de pago. 
  - *Columnas críticas:* `code`, `url` (Base64), `amount`, `status` (generated, scanned, paid, expired), `bnb_blob`, `external_qr_id`, `donor_id`.
- `donations`: Registro oficial del aporte monetario exitoso.
  - *Columnas críticas:* `status`, `amount`, `qr_id`, `donor_id`, `provider`, `provider_payment_id`.
- `donors`: Información del donante (vinculado opcionalmente a un `user_id` del sistema).
- `certificates`: PDFs generados como comprobantes.
  - *Columnas críticas:* `donation_id`, `folio`, `pdf_url`.
- `donation_tiers`: Catálogo de montos rápidos u opciones de donación en el frontend.
- `campaigns` / `currencies`: Entidades base a las cuales se asocia el QR/Donación temporal o permanentemente.

---

## 2. API y Rutas (Backend: Laravel)

Cualquier cambio en los nombres de estas rutas, middlewares (ej. `auth:sanctum`) o formatos de respuesta JSON afectará el _polling_ del frontend o los _callbacks_ del banco.

**Públicas (Generación y Estado de QR):**
- `GET /api/public/donation-options` -> `PublicDonationController@getOptions`
- `POST /api/public/request-qr` -> `PublicDonationController@requestQr`
- `GET /api/public/check-status/{qrId}` -> `PublicDonationController@checkStatus`

**Webhooks (Transaccional Banco):**
- `POST /api/webhooks/bnb` -> `BnbWebhookController@handle` *(⚠️ CRÍTICO: Recibe la notificación de pago del BNB).*

**Protegidas (Perfil / Historial de Usuario):**
- `GET /api/auth/donations/my` -> `DonationController@myDonations`
- `GET /api/admin/donation-tiers` y variaciones CRUD -> `AdminDonationController`

---

## 3. Servicios y Lógica de Negocio (Backend)

Los siguientes archivos de `app/Services`, `app/Console/Commands` y controladores contienen la lógica core:

- `App\Services\BnbDonationService.php`: Maneja la autenticación estricta y generación de QRs en BNB. Contiene URLs `test.bnb.com.bo` y codificación manual de JSONs (`withBody()`) para evitar errores "Cuenta Inexistente". **No cambiar Headers ni formatos de payload.**
- `App\Services\CertificadoService.php`: Servicio de renderizado y timbrado del PDF con la plantilla Blade.
- `App\Console\Commands\TestBnbConnection.php`: Comando auxiliar vital para diagnósticos de conexión al BNB.
- `App\Http\Controllers\BnbWebhookController.php`: Validador del `$secret` configurado contra el payload del banco y despachador del certificado.
- `App\Jobs\GenerarCertificadoJob.php`: Trabajo en cola (`php artisan queue:work`) que corre asíncronamente tras el éxito del webhook.

---

## 4. Variables de Entorno `.env`

Si cambian, el sistema de donaciones muere inmediatamente. Deben reflejarse correctamente en sus nombres.

```env
BNB_ACCOUNT_ID="NoCambiar"
BNB_AUTH_ID="NoCambiar"
BNB_MOCK_MODE=false
BNB_WEBHOOK_SECRET="NoCambiarEstaFirma"
```

---

## 5. Frontend (React / Next.js)

Componentes que interactúan mediante `polling` constante.

- `src/components/DonationForm.tsx`: Componente de multi-pasos que maneja la petición del QR, el renderizado de la imagen Base64 cruda (`data:image/png;base64,...`) y los intervalos de tiempo haciendo _polling_ a `/check-status`.
- `src/services/donationService.ts`: Definición de clientes Axios.
- **Rutas asociadas CMS:** La migración a CMS no debe interceptar las rutas de donación ni los endpoints modificando headers de CORS o _layouts_ incompatibles con los _client components_ de la pasarela.

> **TL;DR:** Todo lo relacionado a las tablas de donativos y la integración con el banco queda estrictamente congelado y es responsabilidad del equipo de pagos. Si el CMS necesita integrarse, debe consultarlo para no corromper la validación de "extremo a extremo" que ya opera de manera fluida y simulada en el BNB Sandbox.
