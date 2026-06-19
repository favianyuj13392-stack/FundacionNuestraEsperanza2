<?php

namespace App\Services;

use App\Models\BnbClient;
use App\Models\BnbSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BnbDomiciliacionService
 *
 * Encapsula el consumo de los endpoints de Domiciliación (Débito Automático)
 * del Banco Nacional de Bolivia (BNB).
 *
 * Reutiliza BnbDonationService como fuente del Bearer Token JWT para no
 * duplicar la lógica de autenticación y cache.
 *
 * Endpoints cubiertos:
 *  - POST /DirectDebit/api/Services/UpdateRecord
 *  - POST /DirectDebit/api/Services/GetQRVariableAmount
 */
class BnbDomiciliacionService
{
    // Tiempo de espera máximo (segundos) para las llamadas al banco
    private const TIMEOUT         = 30;
    private const CONNECT_TIMEOUT = 10;

    private string $baseUrl;
    private string $authUrl;
    private string $serviceCode;

    public function __construct()
    {
        $this->baseUrl = config('services.bnb.dom_url', 'http://test.bnb.com.bo/DirectDebit/api') . '/Services';
        // Domiciliación uses its own auth endpoint (may differ from QR Simple in production)
        $this->authUrl = config('services.bnb.dom_auth_url', config('services.bnb.auth_url', 'http://test.bnb.com.bo/ClientAuthentication.API/api/v1')) . '/auth/Token';
        $this->serviceCode = trim((string) config('bnb.service_code', ''));
    }

    // =========================================================================
    // MÉTODOS PÚBLICOS
    // =========================================================================

    /**
     * Registra o actualiza un cliente en el BNB mediante UpdateRecord.
     *
     * El BNB acepta un array de clientes en el payload, pero nosotros
     * lo llamamos de uno en uno para un control granular de errores.
     *
     * Referencia doc: §10 POST UpdateRecord
     *
     * @param  BnbClient  $client  El cliente a sincronizar.
     * @return array{success: bool, message: string, bnb_response: array|null}
     */
    public function syncClient(BnbClient $client): array
    {
        $url = $this->baseUrl . '/UpdateRecord';

        Log::info('BNB Domiciliacion: syncClient iniciado', [
            'client_id'  => $client->id,
            'identifier' => $client->identifier,
        ]);

        // Simulación (Mock) para desarrollo local sin credenciales de Domiciliación
        if (config('bnb.mock_mode')) {
            Log::info('BNB Domiciliacion: Modo SIMULACIÓN (Mock) activo para syncClient.');
            $client->synced_to_bnb   = true;
            $client->last_synced_at  = now();
            $client->save();

            return $this->successResponse('Cliente sincronizado correctamente con BNB (MOCK).', [
                'success' => true,
                'data' => [
                    'clients' => [
                        [
                            'identifier' => $client->identifier,
                            'updated' => true,
                            'errorMessage' => null,
                        ]
                    ]
                ]
            ]);
        }

        $token = $this->getToken();
        if (! $token) {
            return $this->errorResponse('No se pudo obtener el token de autenticación BNB.');
        }

        // El banco espera un array de clientes incluso si solo mandamos uno.
        $payload = [
            'clients' => [
                $client->toBnbPayload(),
            ],
        ];

        Log::debug('BNB Domiciliacion: syncClient payload', ['payload' => $payload]);

        try {
            $response = $this->makePost($url, $token, $payload);

            $body = $response->json();

            Log::debug('BNB Domiciliacion: syncClient response', [
                'http_status' => $response->status(),
                'body'        => $body,
            ]);

            // El BNB responde con success:true a nivel global y con updated:true
            // a nivel de cada cliente dentro de data.clients[].
            if ($response->successful() && ($body['success'] ?? false)) {

                $clientResult = $body['data']['clients'][0] ?? [];

                if (! ($clientResult['updated'] ?? false)) {
                    // El banco aceptó la petición pero reportó error individual del cliente
                    $bnbError = $clientResult['errorMessage'] ?? 'Error desconocido del BNB.';
                    Log::warning('BNB Domiciliacion: syncClient updated=false', [
                        'identifier'    => $client->identifier,
                        'bnb_error'     => $bnbError,
                    ]);
                    return $this->errorResponse("BNB rechazó el cliente: {$bnbError}", $body);
                }

                // ✅ Sincronización exitosa → actualizamos nuestro registro
                $client->synced_to_bnb   = true;
                $client->last_synced_at  = now();
                $client->save();

                Log::info('BNB Domiciliacion: syncClient exitoso', [
                    'identifier' => $client->identifier,
                ]);

                return $this->successResponse('Cliente sincronizado correctamente con BNB.', $body);
            }

            // El banco devolvió success:false a nivel global
            $errorMsg = $body['message'] ?? 'Respuesta inesperada del BNB.';
            Log::error('BNB Domiciliacion: syncClient falló (success=false)', [
                'identifier'  => $client->identifier,
                'http_status' => $response->status(),
                'message'     => $errorMsg,
                'body'        => $body,
            ]);

            return $this->errorResponse($errorMsg, $body);

        } catch (\Exception $e) {
            Log::error('BNB Domiciliacion: syncClient excepción', [
                'identifier' => $client->identifier,
                'message'    => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('Error de conexión con BNB: ' . $e->getMessage());
        }
    }

    /**
     * Genera el QR de domiciliación con monto variable (o fijo recurrente).
     *
     * Regla de negocio: aunque se usa el endpoint GetQRVariableAmount, si
     * $subscription->amount > 0 el BNB cobrará ese monto mensualmente de
     * forma indefinida hasta que el cliente o nosotros lo cancele.
     *
     * Si la operación es exitosa, persiste en $subscription:
     *  - qr_id            → qrId devuelto por el banco
     *  - qr_image_base64  → qrContent (imagen JPEG en base64)
     *  - mime_type        → mimeType devuelto por el banco
     *  - status           → 'pending' (esperando que el cliente escanee)
     *
     * Referencia doc: §7 POST GetQRVariableAmount
     *
     * @param  BnbSubscription  $subscription  La suscripción a generar.
     * @param  BnbClient        $client        El cliente propietario.
     * @return array{success: bool, message: string, bnb_response: array|null}
     */
    public function createSubscriptionQr(BnbSubscription $subscription, BnbClient $client): array
    {
        $url = $this->baseUrl . '/GetQRVariableAmount';

        Log::info('BNB Domiciliacion: createSubscriptionQr iniciado', [
            'subscription_id'   => $subscription->id,
            'client_identifier' => $client->identifier,
            'amount'            => $subscription->amount,
        ]);

        // Simulación (Mock) para desarrollo local sin credenciales de Domiciliación
        if (config('bnb.mock_mode')) {
            Log::info('BNB Domiciliacion: Modo SIMULACIÓN (Mock) activo para createSubscriptionQr.');
            $mockQrId = 'mock-qr-' . time() . '-' . rand(100, 999);
            // Imagen de pixel de prueba de 1x1 transparente
            $mockQrContent = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

            $subscription->qr_id            = $mockQrId;
            $subscription->qr_image_base64  = $mockQrContent;
            $subscription->mime_type        = 'image/png';
            $subscription->status           = BnbSubscription::STATUS_PENDING;
            $subscription->save();

            return $this->successResponse('QR de domiciliación generado correctamente (MOCK).', [
                'success' => true,
                'data' => [
                    'qrId' => $mockQrId,
                    'qrContent' => $mockQrContent,
                    'mimeType' => 'image/png',
                ]
            ]);
        }

        $token = $this->getToken();
        if (! $token) {
            return $this->errorResponse('No se pudo obtener el token de autenticación BNB.');
        }

        $payload = $this->buildQrPayload($subscription, $client);

        Log::debug('BNB Domiciliacion: createSubscriptionQr payload', [
            // Excluimos qrContent del log para no saturarlo con el base64
            'payload' => array_merge($payload, ['qrContent' => '[omitido]']),
        ]);

        try {
            $response = $this->makePost($url, $token, $payload);

            $body = $response->json();

            Log::debug('BNB Domiciliacion: createSubscriptionQr response', [
                'http_status' => $response->status(),
                // Omitimos qrContent del log
                'body_keys'   => array_keys($body ?? []),
                'success'     => $body['success'] ?? null,
                'code'        => $body['code'] ?? null,
                'message'     => $body['message'] ?? null,
            ]);

            if ($response->successful() && ($body['success'] ?? false)) {

                $data = $body['data'] ?? [];

                $qrId      = $data['qrId']      ?? null;
                $qrContent = $data['qrContent'] ?? null;
                $mimeType  = $data['mimeType']  ?? 'image/jpeg';

                if (! $qrId || ! $qrContent) {
                    Log::error('BNB Domiciliacion: createSubscriptionQr respuesta sin qrId/qrContent', [
                        'data' => $data,
                    ]);
                    return $this->errorResponse(
                        'El BNB respondió con éxito pero sin qrId o qrContent.',
                        $body
                    );
                }

                // ✅ Persistimos el QR en nuestra base de datos
                $subscription->qr_id            = $qrId;
                $subscription->qr_image_base64  = $qrContent;
                $subscription->mime_type        = $mimeType;
                $subscription->status           = BnbSubscription::STATUS_PENDING;
                $subscription->save();

                Log::info('BNB Domiciliacion: createSubscriptionQr exitoso', [
                    'subscription_id' => $subscription->id,
                    'qr_id'           => $qrId,
                ]);

                return $this->successResponse('QR de domiciliación generado correctamente.', $body);
            }

            // El banco devolvió success:false
            $errorMsg = $body['message'] ?? 'Respuesta inesperada del BNB.';
            Log::error('BNB Domiciliacion: createSubscriptionQr falló (success=false)', [
                'http_status'       => $response->status(),
                'message'           => $errorMsg,
                'code'              => $body['code'] ?? null,
                'client_identifier' => $client->identifier,
                'amount'            => $subscription->amount,
            ]);

            return $this->errorResponse($errorMsg, $body);

        } catch (\Exception $e) {
            Log::error('BNB Domiciliacion: createSubscriptionQr excepción', [
                'subscription_id' => $subscription->id,
                'message'         => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);
            return $this->errorResponse('Error de conexión con BNB: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el detalle actual de una domiciliación.
     * Referencia doc: §12 POST GetDetail
     * Requerido para poder cancelar una suscripción.
     *
     * @param string $qrId
     * @return array{success: bool, message: string, bnb_response: array|null}
     */
    public function getDetail(string $qrId): array
    {
        $url = $this->baseUrl . '/GetDetail';

        if (config('bnb.mock_mode')) {
            return $this->successResponse('Mock: GetDetail', [
                'success' => true,
                'data' => [
                    'qrId' => $qrId,
                    'installments' => [
                        ['id' => 1, 'amount' => 100, 'scheduledDate' => now()->format('Y-m-d H:i')]
                    ]
                ]
            ]);
        }

        $token = $this->getToken();
        if (! $token) return $this->errorResponse('Sin token BNB.');

        $payload = ['qrId' => $qrId];

        try {
            $response = $this->makePost($url, $token, $payload);
            $body = $response->json();

            if ($response->successful() && ($body['success'] ?? false)) {
                return $this->successResponse('Detalle obtenido correctamente.', $body);
            }
            return $this->errorResponse($body['message'] ?? 'Error BNB.', $body);
        } catch (\Exception $e) {
            return $this->errorResponse('Error de conexión: ' . $e->getMessage());
        }
    }

    /**
     * Cancela una suscripción activa.
     * Referencia doc: §14 POST UpdatePendingQuota
     * Requiere el array de installments exacto devuelto por GetDetail.
     *
     * @param string $qrId
     * @param array $installments Array de cuotas pendientes devuelto por GetDetail
     * @return array{success: bool, message: string, bnb_response: array|null}
     */
    public function cancelSubscription(string $qrId, array $installments): array
    {
        $url = $this->baseUrl . '/UpdatePendingQuota';

        if (config('bnb.mock_mode')) {
            return $this->successResponse('Mock: CancelSubscription exitoso', [
                'success' => true,
                'data' => [
                    'qrId' => $qrId,
                    'installments' => [['id' => 1, 'status' => true, 'errorMessage' => '']]
                ]
            ]);
        }

        $token = $this->getToken();
        if (! $token) return $this->errorResponse('Sin token BNB.');

        // scheduleStatus 4 = Cancelado por la empresa
        $payload = [
            'qrId'           => $qrId,
            'scheduleStatus' => 4,
            'installments'   => $installments
        ];

        try {
            $response = $this->makePost($url, $token, $payload);
            $body = $response->json();

            if ($response->successful() && ($body['success'] ?? false)) {
                // Verificar si alguna cuota dio error (status: false)
                $firstInst = $body['data']['installments'][0] ?? null;
                if ($firstInst && !($firstInst['status'] ?? true)) {
                    return $this->errorResponse('BNB rechazó la actualización de la cuota: ' . ($firstInst['errorMessage'] ?? ''));
                }
                return $this->successResponse('Suscripción cancelada correctamente.', $body);
            }
            return $this->errorResponse($body['message'] ?? 'Error BNB.', $body);
        } catch (\Exception $e) {
            return $this->errorResponse('Error de conexión: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MÉTODOS PRIVADOS / HELPERS
    // =========================================================================

    /**
     * Obtiene el Bearer Token para Domiciliación (con cache de 14 mins).
     */
    private function getToken(): ?string
    {
        $cachedToken = \Illuminate\Support\Facades\Cache::get('bnb_domiciliacion_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $url = $this->authUrl;
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post($url, [
                'accountId'       => config('services.bnb.dom_account_id'),
                'authorizationId' => config('services.bnb.dom_authorization_id'),
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['success'] ?? false)) {
                $token = $body['message'];
                \Illuminate\Support\Facades\Cache::put('bnb_domiciliacion_token', $token, now()->addMinutes(14));
                return $token;
            }

            Log::error('BNB Domiciliacion: Error autenticación', [
                'status' => $response->status(),
                'body'   => $body,
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('BNB Domiciliacion: Excepción al autenticar', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Construye el payload exacto que espera GetQRVariableAmount.
     * No delegamos a toBnbQrPayload() del modelo para tener control total
     * aquí sobre campos como serviceCode (que viene de config) y las fechas.
     */
    private function buildQrPayload(BnbSubscription $subscription, BnbClient $client): array
    {
        return [
            'currencyCode'         => $subscription->currency_code,  // 1=BOB
            'amount'               => (float) $subscription->amount,  // cast explícito a double
            'reference'            => $subscription->reference,
            'serviceCode'          => $this->serviceCode,
            // dueDate: QR válido solo HOY
            'dueDate'              => now()->format('Y-m-d'),
            // Primer cobro: Hoy a las 23:50 (garantiza futuro sin importar hora de escaneo)
            'scheduledDate'        => now()->format('Y-m-d') . ' 23:50',
            'paymentFrequency'     => BnbSubscription::FREQ_MONTHLY,  // 3 = Mensual
            'clientIdentifier'     => $client->identifier,
        ];
    }

    /**
     * Ejecuta un POST al BNB con los headers requeridos por la documentación.
     */
    private function makePost(string $url, string $token, array $payload): \Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'cache-control' => 'no-cache',
            'User-Agent'    => 'PostmanRuntime/7.32.0',
        ])
        ->timeout(self::TIMEOUT)
        ->connectTimeout(self::CONNECT_TIMEOUT)
        ->post($url, $payload);
    }

    /**
     * Estructura de respuesta exitosa estandarizada para el Controlador.
     */
    private function successResponse(string $message, ?array $bnbResponse = null): array
    {
        return [
            'success'      => true,
            'message'      => $message,
            'bnb_response' => $bnbResponse,
        ];
    }

    /**
     * Estructura de respuesta de error estandarizada para el Controlador.
     */
    private function errorResponse(string $message, ?array $bnbResponse = null): array
    {
        return [
            'success'      => false,
            'message'      => $message,
            'bnb_response' => $bnbResponse,
        ];
    }
}
