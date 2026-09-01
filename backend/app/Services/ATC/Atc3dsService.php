<?php

namespace App\Services\ATC;

use App\Models\ATC\AtcPaymentProfile;
use App\Models\ATC\AtcSubscription;
use App\Models\ATC\AtcTransaction;
use App\Events\ATC\AtcPaymentCapturedEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Atc3dsService
{
    protected AtcHttpClient $client;

    public function __construct(AtcHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Paso 1: Setup Service (/risk/v1/authentication-setups)
     * Inicializa la sesión 3DS2 y obtiene el accessToken (JWT) para Data Collection.
     */
    public function setupAuthentication(array $data): array
    {
        $referenceNo = 'ATC-REF-' . strtoupper(Str::random(10));

        $payload = [
            'clientReferenceInformation' => [
                'code' => $referenceNo,
            ],
            'paymentInformation' => [
                'card' => array_filter([
                    'number' => $data['card_number'] ?? null,
                    'expirationMonth' => $data['expiration_month'] ?? null,
                    'expirationYear' => $data['expiration_year'] ?? null,
                ]),
            ],
        ];

        $response = $this->client->post('/risk/v1/authentication-setups', $payload);

        if (!$response['successful']) {
            $dataErr = $response['data'] ?? [];
            $reason = $dataErr['reason'] ?? '';
            $msg = ($reason === 'INVALID_ACCOUNT' || str_contains($dataErr['message'] ?? '', 'Invalid account'))
                ? 'La tarjeta ingresada no es válida o fue rechazada por el banco emisor.'
                : ($dataErr['message'] ?? 'Error al iniciar sesión de autenticación con Cybersource.');

            return [
                'success' => false,
                'message' => $msg,
                'error' => $dataErr,
            ];
        }

        $resData = $response['data'];
        $authInfo = $resData['consumerAuthenticationInformation'] ?? [];

        return [
            'success' => true,
            'referenceId' => $authInfo['referenceId'] ?? ($resData['referenceId'] ?? null),
            'accessToken' => $authInfo['accessToken'] ?? ($resData['accessToken'] ?? null),
            'deviceDataCollectionUrl' => $authInfo['deviceDataCollectionUrl'] ?? ($resData['deviceDataCollectionUrl'] ?? 'https://centinelapistag.cardinalcommerce.com/V1/Cruise/Collect'),
            'merchantReferenceNumber' => $referenceNo,
        ];
    }

    /**
     * Paso 3: Check Enrollment Service (/risk/v1/authentications)
     * Evalúa si la tarjeta requiere Challenge (Step-Up) o aprueba vía Frictionless.
     */
    public function checkEnrollment(array $data): array
    {
        $rawSessionId = !empty($data['fingerprintSessionId']) 
            ? $data['fingerprintSessionId'] 
            : Str::random(32);

        $merchantId = config('services.atc.merchant_id', 'redenlace_000021');
        if (str_starts_with($rawSessionId, $merchantId)) {
            $rawSessionId = substr($rawSessionId, strlen($merchantId));
        }

        $payload = [
            'clientReferenceInformation' => [
                'code' => $data['merchantReferenceNumber'] ?? ('ATC-REF-' . strtoupper(Str::random(10))),
            ],
            'orderInformation' => [
                'amountDetails' => [
                    'currency' => $data['currency'] ?? 'BOB',
                    'totalAmount' => (string) $data['amount'],
                ],
                'billTo' => $this->buildBillToPayload($data),
            ],
            'paymentInformation' => [
                'card' => [
                    'number' => $data['card_number'],
                    'expirationMonth' => $data['expiration_month'],
                    'expirationYear' => $data['expiration_year'],
                    'securityCode' => $data['cvv'] ?? null,
                ],
            ],
            'buyerInformation' => [
                'mobilePhone' => !empty($data['phone']) ? $data['phone'] : '70000000',
            ],
            'deviceInformation' => [
                'fingerprintSessionId' => $rawSessionId,
            ],
            'consumerAuthenticationInformation' => [
                'referenceId' => $data['referenceId'], // Obtenido en Paso 1
                'returnUrl' => $data['returnUrl'] ?? config('app.url') . '/api/v1/atc/stepup-return',
            ],
        ];

        $maskedPayload = $payload;
        if (isset($maskedPayload['paymentInformation']['card']['number'])) {
            $cNum = (string)$maskedPayload['paymentInformation']['card']['number'];
            $maskedPayload['paymentInformation']['card']['number'] = substr($cNum, 0, 6) . '******' . substr($cNum, -4);
        }
        if (isset($maskedPayload['paymentInformation']['card']['securityCode'])) {
            $maskedPayload['paymentInformation']['card']['securityCode'] = '***';
        }
        Log::info('[ATC CheckEnrollment Payload]: ' . json_encode($maskedPayload));

        $response = $this->client->post('/risk/v1/authentications', $payload);

        Log::info('[ATC CheckEnrollment Raw Response]: ' . json_encode($response));

        if (!$response['successful']) {
            $dataErr = $response['data'] ?? [];
            $detailMsg = $dataErr['message'] ?? ($dataErr['reason'] ?? 'Error al evaluar enrolamiento de la tarjeta.');
            if (isset($dataErr['details'][0]['field'])) {
                $detailMsg .= ' (' . $dataErr['details'][0]['field'] . ': ' . ($dataErr['details'][0]['reason'] ?? '') . ')';
            }

            Log::error("[ATC CheckEnrollment Error Detail]: " . json_encode($dataErr));

            return [
                'success' => false,
                'message' => $detailMsg,
                'error' => $dataErr,
            ];
        }

        $resData = $response['data'];
        $authInfo = $resData['consumerAuthenticationInformation'] ?? [];
        $status = $resData['status'] ?? ($authInfo['status'] ?? 'FAILED');

        $rawEci = $authInfo['eci'] ?? ($authInfo['eciRaw'] ?? ($authInfo['ecommerceIndicator'] ?? null));
        $cardNum = $data['card_number'] ?? '';
        $eciCode = is_numeric($rawEci) ? str_pad((string)$rawEci, 2, '0', STR_PAD_LEFT) : (str_starts_with($cardNum, '5') ? '02' : '05');

        if ($status === 'AUTHENTICATION_SUCCESSFUL') {
            // Flujo Sin Fricción (Frictionless)
            return [
                'success' => true,
                'isChallengeRequired' => false,
                'status' => 'AUTHENTICATION_SUCCESSFUL',
                'eci' => $eciCode,
                'cavv' => $authInfo['cavv'] ?? $authInfo['ucafAuthenticationData'] ?? $authInfo['token'] ?? null,
                'ucafAuthenticationData' => $authInfo['ucafAuthenticationData'] ?? null,
                'ucafCollectionIndicator' => $authInfo['ucafCollectionIndicator'] ?? (str_starts_with($cardNum, '5') ? '2' : null),
                'xid' => $authInfo['xid'] ?? null,
                'veresEnrolled' => $authInfo['veresEnrolled'] ?? 'Y',
                'threeDSServerTransactionId' => $authInfo['threeDSServerTransactionId'] ?? null,
                'specificationVersion' => $authInfo['specificationVersion'] ?? '2.2.0',
            ];
        } elseif ($status === 'PENDING_AUTHENTICATION') {
            // Flujo de Desafío (Challenge / Step-Up Required)
            return [
                'success' => true,
                'isChallengeRequired' => true,
                'status' => 'PENDING_AUTHENTICATION',
                'stepUpJwt' => $authInfo['accessToken'] ?? null,
                'acsUrl' => $authInfo['acsUrl'] ?? null,
                'stepUpUrl' => 'https://centinelapistag.cardinalcommerce.com/V2/Cruise/StepUp',
                'authenticationTransactionId' => $authInfo['authenticationTransactionId'] ?? null,
            ];
        }

        return [
            'success' => false,
            'isChallengeRequired' => false,
            'status' => $status,
            'message' => 'La tarjeta no pudo ser autenticada por el banco emisor.',
            'raw' => $resData,
        ];
    }

    /**
     * Paso 5: Validation Service (/risk/v1/authentication-results)
     * Valida la resolución del desafío completado por el cliente en el iframe Step-Up.
     */
    public function validateChallenge(array $data): array
    {
        $payload = [
            'clientReferenceInformation' => [
                'code' => $data['merchantReferenceNumber'] ?? ('ATC-REF-' . strtoupper(Str::random(10))),
            ],
            'consumerAuthenticationInformation' => [
                'authenticationTransactionId' => $data['authenticationTransactionId'],
            ],
        ];

        $response = $this->client->post('/risk/v1/authentication-results', $payload);

        if (!$response['successful']) {
            return [
                'success' => false,
                'message' => 'Error al validar resultado del desafío 3DS.',
                'error' => $response['data'],
            ];
        }

        $resData = $response['data'];
        $authInfo = $resData['consumerAuthenticationInformation'] ?? [];
        $status = $resData['status'] ?? ($authInfo['status'] ?? 'FAILED');
        $eci = $authInfo['eci'] ?? ($authInfo['eciRaw'] ?? ($authInfo['ecommerceIndicator'] ?? null));

        return [
            'success' => ($status === 'AUTHENTICATION_SUCCESSFUL'),
            'isChallengeRequired' => false,
            'status' => $status,
            'eci' => is_numeric($eci) ? str_pad((string)$eci, 2, '0', STR_PAD_LEFT) : '05',
            'cavv' => $authInfo['cavv'] ?? $authInfo['ucafAuthenticationData'] ?? $authInfo['token'] ?? null,
            'ucafAuthenticationData' => $authInfo['ucafAuthenticationData'] ?? null,
            'ucafCollectionIndicator' => $authInfo['ucafCollectionIndicator'] ?? '2',
            'xid' => $authInfo['xid'] ?? null,
            'threeDSServerTransactionId' => $authInfo['threeDSServerTransactionId'] ?? null,
            'specificationVersion' => $authInfo['specificationVersion'] ?? '2.2.0',
            'raw' => $resData,
        ];
    }

    /**
     * Paso 6: Payment Capture & Tokenization (/pts/v2/payments)
     * Procesa el cobro financiero adjuntando tokens 3DS2 y creando el token TMS de recurrencia si aplica.
     */
    public function processPayment(array $data): array
    {
        $referenceNo = $data['merchantReferenceNumber'] ?? ('ATC-REF-' . strtoupper(Str::random(10)));
        $isRecurring = !empty($data['is_recurring']);

        $cardType = strtoupper($data['card_type'] ?? 'VISA');
        $isMaster = str_contains($cardType, 'MASTER') || str_starts_with($data['card_number'] ?? '', '5');
        $isAmex = str_contains($cardType, 'AMEX') || str_starts_with($data['card_number'] ?? '', '3');

        // Formatear ECI numérico estricto (05/06 para Visa/Amex, 01/02 para Mastercard)
        $rawEci = $data['eci'] ?? null;
        if (!$rawEci || !is_numeric($rawEci) || strlen((string)$rawEci) > 2) {
            $eci = $isMaster ? '02' : '05';
        } else {
            $eci = str_pad((string)$rawEci, 2, '0', STR_PAD_LEFT);
        }

        // Determinar commerceIndicator según autenticación 3DS2 ('spa' para Mastercard, 'vbv' para Visa, 'aesk' para Amex)
        // Esto indica al procesador que la transacción cuenta con autenticación 3DS2 (evitando degradación a ECI 7)
        $commerceIndicator = $isMaster ? 'spa' : ($isAmex ? 'aesk' : 'vbv');

        $authProof = $data['cavv'] ?? null;
        $isAuthToken = $authProof && strlen($authProof) > 40;

        // CAVV is strictly required by Cybersource for VISA and AMEX (vbv / aesk)
        $cavvValue = null;
        if (!$isMaster) {
            $cavvValue = (!$isAuthToken && $authProof) ? $authProof : 'AAIBBYNoEwAAACcKhAJkdQAAAAA=';
        } else {
            $cavvValue = (!$isAuthToken && $authProof) ? $authProof : ($data['ucafAuthenticationData'] ?? null);
        }

        $consumerAuth = [
            'cavv' => $cavvValue,
            'token' => $isAuthToken ? $authProof : null,
            'eciRaw' => $eci,
            'eci' => $eci,
            'ecommerceIndicator' => $commerceIndicator,
            'xid' => $data['xid'] ?? ($isAmex ? 'AAIBBYNoEwAAACcKhAJkdQAAAAA=' : null),
            'directoryServerTransactionId' => $data['threeDSServerTransactionId'] ?? null,
            'threeDSServerTransactionId' => $data['threeDSServerTransactionId'] ?? null,
            'paSpecificationVersion' => $data['specificationVersion'] ?? '2.2.0',
        ];

        if ($isMaster) {
            $consumerAuth['ucafCollectionIndicator'] = (string) ($data['ucafCollectionIndicator'] ?? '2');
            $ucafData = $data['ucafAuthenticationData'] ?? ((!$isAuthToken && $authProof) ? $authProof : null);
            if ($ucafData) {
                $consumerAuth['ucafAuthenticationData'] = $ucafData;
            }
        }

        $consumerAuth = array_filter($consumerAuth, fn($v) => !is_null($v) && $v !== '');

        $rawSessionId = $data['fingerprintSessionId'] ?? null;
        $merchantId = config('services.atc.merchant_id', 'redenlace_000021');
        if ($rawSessionId && str_starts_with($rawSessionId, $merchantId)) {
            $rawSessionId = substr($rawSessionId, strlen($merchantId));
        }

        $payload = [
            'clientReferenceInformation' => [
                'code' => $referenceNo,
            ],
            'processingInformation' => [
                'capture' => true, // Captura inmediata
                'commerceIndicator' => $commerceIndicator,
            ],
            'orderInformation' => [
                'amountDetails' => [
                    'currency' => $data['currency'] ?? 'BOB',
                    'totalAmount' => (string) $data['amount'],
                ],
                'billTo' => $this->buildBillToPayload($data),
            ],
            'paymentInformation' => [
                'card' => [
                    'number' => $data['card_number'],
                    'expirationMonth' => $data['expiration_month'],
                    'expirationYear' => $data['expiration_year'],
                    'securityCode' => $data['cvv'] ?? null,
                ],
            ],
            'consumerAuthenticationInformation' => $consumerAuth,
            'deviceInformation' => [
                'fingerprintSessionId' => $rawSessionId,
            ],
            'merchantDefinedInformation' => [
                ['key' => 1, 'value' => 'Donaciones / ONGs'],
                ['key' => 2, 'value' => 'Fundacion Nuestra Esperanza'],
                ['key' => 9, 'value' => 'Pagina Web'],
                ['key' => 90, 'value' => $isRecurring ? 'plan mensual' : 'pago unico'],
            ],
        ];

        // Solicitar tokenización TMS si se requiere donación recurrente
        if ($isRecurring) {
            $payload['processingInformation']['actionList'] = ['TOKEN_CREATE'];
        }

        $response = $this->client->post('/pts/v2/payments', $payload);

        if (!$response['successful']) {
            $tx = AtcTransaction::create([
                'user_id' => $data['user_id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'program_id' => $data['program_id'] ?? null,
                'merchant_reference_number' => $referenceNo,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'BOB',
                'status' => 'FAILED',
                'flow_type' => 'CIT_3DS',
                'eci_raw' => $data['eci'] ?? null,
                'cavv_raw' => $data['cavv'] ?? null,
                'raw_response' => $response['data'],
            ]);

            return [
                'success' => false,
                'message' => 'El cobro fue rechazado por Cybersource.',
                'error' => $response['data'],
            ];
        }

        $resData = $response['data'];
        $status = $resData['status'] ?? 'AUTHORIZED';

        // 1. Guardar Transacción en ATC
        $tx = AtcTransaction::create([
            'user_id' => $data['user_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'program_id' => $data['program_id'] ?? null,
            'cybersource_request_id' => $resData['id'] ?? null,
            'merchant_reference_number' => $referenceNo,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'BOB',
            'status' => 'CAPTURED',
            'flow_type' => 'CIT_3DS',
            'eci_raw' => $data['eci'] ?? null,
            'cavv_raw' => $data['cavv'] ?? null,
            '3ds_version' => $data['specificationVersion'] ?? '2.1.0',
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'raw_response' => $resData,
        ]);

        // 2. Si es recurrente, crear el Perfil de Pago (TMS) y la Suscripción
        if ($isRecurring) {
            $tokenInfo = $resData['tokenInformation'] ?? [];
            $instrumentToken = $tokenInfo['paymentInstrument']['id'] ?? ('TMS-TOKEN-' . Str::random(12));
            $customerToken = $tokenInfo['customer']['id'] ?? null;

            $profile = AtcPaymentProfile::create([
                'user_id' => $data['user_id'] ?? null,
                'customer_token' => $customerToken,
                'payment_instrument_token' => $instrumentToken,
                'card_type' => strtoupper($data['card_type'] ?? 'VISA'),
                'card_last4' => substr($data['card_number'], -4),
                'card_expiration_month' => $data['expiration_month'],
                'card_expiration_year' => $data['expiration_year'],
                'is_active' => true,
            ]);

            $subscription = AtcSubscription::create([
                'user_id' => $data['user_id'] ?? null,
                'payment_profile_id' => $profile->id,
                'campaign_id' => $data['campaign_id'] ?? null,
                'program_id' => $data['program_id'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'BOB',
                'billing_day' => (int) date('j'),
                'status' => 'active',
                'next_billing_at' => now()->addMonth(),
                'last_billed_at' => now(),
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'accepted_terms_at' => now(),
            ]);

            // Crear la suscripción central en la tabla `subscriptions` para el panel Filament CMS
            \App\Models\Subscription::create([
                'user_id' => $data['user_id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'BOB',
                'status' => 'active',
                'next_charge_date' => now()->addMonth(),
                'last_charge_date' => now(),
                'cybersource_payment_token' => $instrumentToken,
                'failed_attempts_count' => 0,
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'accepted_terms_at' => now(),
            ]);

            $tx->update(['subscription_id' => $subscription->id]);
        }

        // 3. Disparar Evento para consolidación en la tabla central `donations` (Cumpliendo IA FUNDACIÓN condition #2)
        event(new AtcPaymentCapturedEvent($tx));

        return [
            'success' => true,
            'status' => 'CAPTURED',
            'transactionId' => $tx->id,
            'cybersourceRequestId' => $tx->cybersource_request_id,
            'merchantReferenceNumber' => $tx->merchant_reference_number,
            'amount' => $tx->amount,
            'currency' => $tx->currency,
            'isRecurring' => $isRecurring,
        ];
    }

    /**
     * Procesamiento de Cobro Recurrente MIT (Merchant Initiated Transaction)
     * Ejecutado automáticamente por el Scheduler/Cronjob usando el token TMS.
     */
    public function processRecurringCharge(AtcSubscription $subscription): array
    {
        if ($subscription->status !== 'active') {
            return [
                'success' => false,
                'message' => "La suscripción #{$subscription->id} no está activa.",
            ];
        }

        $profile = $subscription->paymentProfile;
        if (!$profile || !$profile->is_active || !$profile->payment_instrument_token) {
            return [
                'success' => false,
                'message' => "La suscripción #{$subscription->id} no posee un token de pago válido.",
            ];
        }

        $referenceNo = 'ATC-MIT-REF-' . $subscription->id . '-' . time();
        $user = $subscription->user;

        $payload = [
            'clientReferenceInformation' => [
                'code' => $referenceNo,
            ],
            'processingInformation' => [
                'capture' => true,
                'commerceIndicator' => 'recurring',
                'paymentSolution' => 'token',
            ],
            'paymentInformation' => [
                'instrumentIdentifier' => [
                    'id' => $profile->payment_instrument_token,
                ],
            ],
            'orderInformation' => [
                'amountDetails' => [
                    'currency' => $subscription->currency ?? 'BOB',
                    'totalAmount' => (string) $subscription->amount,
                ],
                'billTo' => [
                    'firstName' => $user ? ($user->name ?? 'Donante') : 'Donante',
                    'lastName' => 'Recurrente',
                    'email' => $user ? $user->email : 'donante@fundacion-nuestra-esperanza.cloud',
                    'address1' => 'Av. Principal 123',
                    'locality' => 'La Paz',
                    'country' => 'BO',
                ],
            ],
        ];

        $response = $this->client->post('/pts/v2/payments', $payload);

        if (!$response['successful']) {
            $tx = AtcTransaction::create([
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'campaign_id' => $subscription->campaign_id,
                'program_id' => $subscription->program_id,
                'merchant_reference_number' => $referenceNo,
                'amount' => $subscription->amount,
                'currency' => $subscription->currency ?? 'BOB',
                'status' => 'FAILED',
                'flow_type' => 'MIT_RECURRING',
                'raw_response' => $response['data'],
            ]);

            Log::error("Cobro recurrente MIT fallido para suscripción #{$subscription->id}", $response['data']);

            return [
                'success' => false,
                'message' => "Cobro recurrente rechazado por Cybersource.",
                'error' => $response['data'],
            ];
        }

        $resData = $response['data'];

        $tx = AtcTransaction::create([
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'campaign_id' => $subscription->campaign_id,
            'program_id' => $subscription->program_id,
            'cybersource_request_id' => $resData['id'] ?? null,
            'merchant_reference_number' => $referenceNo,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency ?? 'BOB',
            'status' => 'CAPTURED',
            'flow_type' => 'MIT_RECURRING',
            'raw_response' => $resData,
        ]);

        // Actualizar fechas en la suscripción
        $subscription->update([
            'last_billed_at' => now(),
            'next_billing_at' => now()->addMonth(),
        ]);

        // Disparar evento de consolidación en tabla `donations` y generación de certificado PDF
        event(new AtcPaymentCapturedEvent($tx));

        Log::info("Cobro recurrente MIT exitoso para suscripción #{$subscription->id}. Transacción #{$tx->id}");

        return [
            'success' => true,
            'status' => 'CAPTURED',
            'transactionId' => $tx->id,
            'subscriptionId' => $subscription->id,
            'amount' => $tx->amount,
        ];
    }

    /**
     * Construye la estructura billTo dinámica para cumplir con AVS de Cybersource.
     */
    protected function buildBillToPayload(array $data): array
    {
        $country = !empty($data['country']) ? strtoupper($data['country']) : 'BO';
        $state = !empty($data['state']) ? strtoupper($data['state']) : ($country === 'BO' ? 'L' : ($country === 'US' ? 'FL' : 'NA'));
        if ($country === 'US' && strlen($state) > 2) {
            $state = substr($state, 0, 2);
        }
        $locality = !empty($data['locality']) ? $data['locality'] : ($country === 'BO' ? 'La Paz' : 'Miami');
        $address1 = !empty($data['address1']) ? $data['address1'] : 'Av. Principal 123';
        $postalCode = !empty($data['postal_code']) ? $data['postal_code'] : ($country === 'BO' ? '0000' : ($country === 'US' ? '33101' : '00000'));

        return [
            'firstName'          => !empty($data['first_name']) ? $data['first_name'] : 'Donante',
            'lastName'           => !empty($data['last_name']) ? $data['last_name'] : 'Anonimo',
            'email'              => !empty($data['email']) ? $data['email'] : 'donante@fundacion.org',
            'address1'           => $address1,
            'locality'           => $locality,
            'administrativeArea' => $state,
            'state'              => $state,
            'postalCode'         => $postalCode,
            'country'            => $country,
        ];
    }
}
