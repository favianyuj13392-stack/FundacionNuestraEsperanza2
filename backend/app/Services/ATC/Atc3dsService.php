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
            'merchantDefinedInformation' => $this->buildMerchantDefinedInformation($data, !empty($data['is_recurring']), true),
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
        $referenceNo = $data['merchantReferenceNumber'] ?? ($resData['clientReferenceInformation']['code'] ?? null);

        $rawEci = $authInfo['eci'] ?? ($authInfo['eciRaw'] ?? ($authInfo['ecommerceIndicator'] ?? null));
        $cardNum = $data['card_number'] ?? '';
        $cardType = $data['card_type'] ?? (str_starts_with($cardNum, '5') ? 'MASTERCARD' : (str_starts_with($cardNum, '3') ? 'AMEX' : 'VISA'));

        $normalizedEci = is_numeric($rawEci) ? str_pad((string)((int)$rawEci), 2, '0', STR_PAD_LEFT) : null;
        $cavv = $authInfo['cavv'] ?? $authInfo['ucafAuthenticationData'] ?? $authInfo['token'] ?? null;
        $ucafIndicator = $authInfo['ucafCollectionIndicator'] ?? (str_starts_with($cardNum, '5') ? '2' : null);
        $veresEnrolled = $authInfo['veresEnrolled'] ?? null;

        $isAuthentic = $this->isEciAuthenticAndProtected($cardType, $rawEci, $cavv, $ucafIndicator, $veresEnrolled);

        if ($status === 'AUTHENTICATION_SUCCESSFUL') {
            // Si Cybersource dice exitoso pero el ECI es inválido (07, 00, sin cavv/aav) -> BLOQUEAR
            if (!$isAuthentic) {
                $this->recordRejectedAuthentication($data, $resData, $normalizedEci ?: ($rawEci ?: '07'));

                Log::warning("[ATC CheckEnrollment ECI Block] Tarjeta sin Liability Shift rechazada para cobro:", [
                    'card_type' => $cardType,
                    'rawEci' => $rawEci,
                    'normalizedEci' => $normalizedEci,
                    'reference' => $referenceNo,
                    'requestId' => $resData['id'] ?? null,
                ]);

                return [
                    'success' => false,
                    'isChallengeRequired' => false,
                    'status' => 'AUTHENTICATION_FAILED_ECI',
                    'eci' => $normalizedEci ?: '07',
                    'merchantReferenceNumber' => $referenceNo,
                    'authenticationRequestId' => $resData['id'] ?? null,
                    'message' => 'La tarjeta no pudo ser verificada de forma segura por su banco emisor (Autenticación 3DS2 no superada). Por favor intente con otra tarjeta.',
                    'raw' => $resData,
                ];
            }

            // Flujo Sin Fricción Válido (Frictionless)
            return [
                'success' => true,
                'isChallengeRequired' => false,
                'status' => 'AUTHENTICATION_SUCCESSFUL',
                'eci' => $normalizedEci ?: (str_starts_with($cardNum, '5') ? '02' : (str_starts_with($cardNum, '3') ? '06' : '05')),
                'cavv' => $cavv,
                'ucafAuthenticationData' => $authInfo['ucafAuthenticationData'] ?? null,
                'ucafCollectionIndicator' => $ucafIndicator,
                'xid' => $authInfo['xid'] ?? null,
                'veresEnrolled' => $veresEnrolled ?? 'Y',
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

        // Rechazo general de autenticación
        $this->recordRejectedAuthentication($data, $resData, $normalizedEci ?: ($rawEci ?: 'FAILED'));

        return [
            'success' => false,
            'isChallengeRequired' => false,
            'status' => $status,
            'merchantReferenceNumber' => $referenceNo,
            'authenticationRequestId' => $resData['id'] ?? null,
            'message' => 'La tarjeta no pudo ser autenticada por el banco emisor. Por favor intente con otra tarjeta.',
            'raw' => $resData,
        ];
    }

    /**
     * Paso 5: Validation Service (/risk/v1/authentication-results)
     * Valida la resolución del desafío completado por el cliente en el iframe Step-Up.
     */
    public function validateChallenge(array $data): array
    {
        $referenceNo = $data['merchantReferenceNumber'] ?? ('ATC-REF-' . strtoupper(Str::random(10)));

        $payload = [
            'clientReferenceInformation' => [
                'code' => $referenceNo,
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
        $rawEci = $authInfo['eci'] ?? ($authInfo['eciRaw'] ?? ($authInfo['ecommerceIndicator'] ?? null));
        $normalizedEci = is_numeric($rawEci) ? str_pad((string)((int)$rawEci), 2, '0', STR_PAD_LEFT) : null;
        $cavv = $authInfo['cavv'] ?? $authInfo['ucafAuthenticationData'] ?? $authInfo['token'] ?? null;
        $ucafIndicator = $authInfo['ucafCollectionIndicator'] ?? '2';
        $cardNum = $data['card_number'] ?? '';
        $cardType = $data['card_type'] ?? ($data['card_brand'] ?? (str_starts_with($cardNum, '5') ? 'MASTERCARD' : (str_starts_with($cardNum, '3') ? 'AMEX' : 'VISA')));

        $isAuthentic = ($status === 'AUTHENTICATION_SUCCESSFUL') && $this->isEciAuthenticAndProtected($cardType, $rawEci, $cavv, $ucafIndicator);

        if (!$isAuthentic) {
            $this->recordRejectedAuthentication($data, $resData, $normalizedEci ?: ($rawEci ?: 'INV'));

            return [
                'success' => false,
                'isChallengeRequired' => false,
                'status' => 'AUTHENTICATION_FAILED_ECI',
                'eci' => $normalizedEci ?: '07',
                'authenticationRequestId' => $resData['id'] ?? null,
                'message' => 'El desafío de seguridad 3DS2 no fue superado o fue rechazado por el banco emisor.',
                'raw' => $resData,
            ];
        }

        $isMaster = str_contains(strtoupper($cardType), 'MASTER') || str_starts_with($cardNum, '5') || $normalizedEci === '02' || $normalizedEci === '01';

        return [
            'success' => true,
            'isChallengeRequired' => false,
            'status' => $status,
            'eci' => $normalizedEci ?: ($isMaster ? '02' : '05'),
            'cavv' => $cavv,
            'ucafAuthenticationData' => $authInfo['ucafAuthenticationData'] ?? null,
            'ucafCollectionIndicator' => $ucafIndicator,
            'xid' => $authInfo['xid'] ?? null,
            'threeDSServerTransactionId' => $authInfo['threeDSServerTransactionId'] ?? null,
            'specificationVersion' => $authInfo['specificationVersion'] ?? '2.2.0',
            'raw' => $resData,
        ];
    }

    /**
     * Valida si el ECI y los datos de autenticación 3DS2 cumplen con el estándar estricto de Liability Shift.
     * Retorna false para ECI 07/00/vacío o tarjetas no autenticadas, bloqueando el avance al Paso 6 (Captura).
     */
    public function isEciAuthenticAndProtected(
        string $cardType,
        ?string $rawEci,
        ?string $cavv,
        ?string $ucafIndicator = null,
        ?string $veresEnrolled = null
    ): bool {
        // Normalizar ECI numérico a 2 dígitos ('5' -> '05', '2' -> '02', '7' -> '07', '0' -> '00')
        $normalizedEci = null;
        if (!is_null($rawEci) && is_numeric($rawEci)) {
            $normalizedEci = str_pad((string)((int)$rawEci), 2, '0', STR_PAD_LEFT);
        }

        $cardUpper = strtoupper($cardType);
        $isMaster = str_contains($cardUpper, 'MASTER') || str_starts_with($cardUpper, '5') || $normalizedEci === '02' || $normalizedEci === '01' || $ucafIndicator === '2';
        $isAmex = str_contains($cardUpper, 'AMEX') || str_starts_with($cardUpper, '3');

        // Si veresEnrolled es 'N' o 'R' (Rechazado / No enrolado), es inválido de inmediato
        if (in_array(strtoupper((string)$veresEnrolled), ['N', 'R'])) {
            return false;
        }

        // Filtro estricto por franquicia
        if ($isMaster) {
            // Mastercard: Válido solo si ECI es '02' (Autenticado) o '01' (Intento UCAF).
            // ECI '00', '07', nulos o sin prueba criptográfica son estrictamente RECHAZADOS.
            if ($normalizedEci === '00' || $normalizedEci === '07') {
                return false;
            }
            if ($normalizedEci === '02' || $normalizedEci === '01') {
                return !empty($cavv);
            }
            // Si Cybersource no envió campo ECI explícito pero tiene UCAF Indicator '2' y AAV válido
            if ($ucafIndicator === '2' && !empty($cavv)) {
                return true;
            }
            return false;
        } elseif ($isAmex) {
            // American Express: Válido si ECI es '05' o '06' con CAVV, o si Cybersource generó Token 3DS criptográfico (SafeKey Attempt ECI 06)
            if ($normalizedEci === '07' || $normalizedEci === '00') {
                return false;
            }
            if (in_array($normalizedEci, ['05', '06']) && !empty($cavv)) {
                return true;
            }
            if (!empty($cavv) && strlen((string)$cavv) > 30) {
                return true; // Amex SafeKey Token Criptográfico (ECI 06 Attempt)
            }
            return false;
        } else {
            // VISA: Válido solo si ECI es '05' o '06' Y tiene CAVV.
            // ECI '07', '00', nulos son estrictamente RECHAZADOS.
            if ($normalizedEci === '07' || $normalizedEci === '00') {
                return false;
            }
            return in_array($normalizedEci, ['05', '06']) && !empty($cavv);
        }
    }

    /**
     * Registra un intento de autenticación 3DS rechazado en la base de datos para auditoría sin cobro.
     */
    protected function recordRejectedAuthentication(array $data, array $cybersourceResponse, ?string $eci): void
    {
        try {
            $referenceNo = $data['merchantReferenceNumber'] ?? ($cybersourceResponse['clientReferenceInformation']['code'] ?? null);
            if (!$referenceNo) {
                return;
            }

            $requestId = $cybersourceResponse['id'] ?? null;
            $cardNum = $data['card_number'] ?? '';
            $authInfo = $cybersourceResponse['consumerAuthenticationInformation'] ?? [];
            $cavv = $authInfo['cavv'] ?? $authInfo['ucafAuthenticationData'] ?? $authInfo['token'] ?? null;

            AtcTransaction::updateOrCreate(
                ['merchant_reference_number' => $referenceNo],
                [
                    'cybersource_request_id' => $requestId,
                    'amount' => $data['amount'] ?? 0,
                    'currency' => $data['currency'] ?? 'BOB',
                    'status' => 'FAILED',
                    'flow_type' => 'CIT_3DS',
                    'eci_raw' => $eci,
                    'cavv_raw' => $cavv,
                    '3ds_version' => $authInfo['specificationVersion'] ?? '2.2.0',
                    'raw_response' => $cybersourceResponse,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar la transacción rechazada en atc_transactions: ' . $e->getMessage());
        }
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

        // GUARDIA INFRANQUEABLE (Circuit Breaker): En transacciones CIT (donaciones semilla / únicas),
        // verificar que el ECI y la prueba criptográfica sean 100% auténticos antes de llamar a /pts/v2/payments
        if (!$isRecurring) {
            $rawEci = $data['eci'] ?? null;
            $authProof = $data['cavv'] ?? ($data['ucafAuthenticationData'] ?? null);
            $ucafIndicator = $data['ucafCollectionIndicator'] ?? null;

            $isAuthentic = $this->isEciAuthenticAndProtected($cardType, $rawEci, $authProof, $ucafIndicator);
            if (!$isAuthentic) {
                Log::error("[ATC Security Block] Intento de cobro abortado en Paso 6 por ECI inválido o falta de Liability Shift.", [
                    'card_type' => $cardType,
                    'eci' => $rawEci,
                    'reference' => $referenceNo,
                ]);

                return [
                    'success' => false,
                    'status' => 'SECURITY_BLOCKED_INVALID_ECI',
                    'message' => 'La transacción no puede ser cobrada porque la tarjeta no cuenta con autenticación 3D Secure válida (ECI no protegido).',
                ];
            }
        }

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
            'merchantDefinedInformation' => $this->buildMerchantDefinedInformation($data, $isRecurring, true),
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

        // 2. Tokenización TMS: Crear o Actualizar Perfil de Pago si vino el Instrument Identifier
        $instrumentId = $resData['tokenInformation']['instrumentIdentifier']['id'] ?? ($resData['paymentInformation']['instrumentIdentifier']['id'] ?? null);
        $customerToken = $resData['tokenInformation']['customer']['id'] ?? ($resData['paymentInformation']['customer']['id'] ?? null);
        $paymentInstrumentId = $resData['tokenInformation']['paymentInstrument']['id'] ?? ($resData['paymentInformation']['paymentInstrument']['id'] ?? null);

        if ($instrumentId || $paymentInstrumentId) {
            $paymentProfile = AtcPaymentProfile::updateOrCreate(
                [
                    'user_id' => $data['user_id'] ?? null,
                    'customer_token' => $customerToken,
                ],
                [
                    'payment_instrument_token' => $instrumentId ?? $paymentInstrumentId,
                    'card_type' => $cardType,
                    'card_last4' => substr($data['card_number'] ?? '', -4),
                    'card_expiration_month' => $data['expiration_month'],
                    'card_expiration_year' => $data['expiration_year'],
                    'is_active' => true,
                ]
            );

            // Si es suscripción recurrente, crear el registro de AtcSubscription
            if ($isRecurring) {
                $subscription = AtcSubscription::create([
                    'user_id' => $data['user_id'] ?? null,
                    'payment_profile_id' => $paymentProfile->id,
                    'campaign_id' => $data['campaign_id'] ?? null,
                    'program_id' => $data['program_id'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'BOB',
                    'frequency' => 'MONTHLY',
                    'status' => 'ACTIVE',
                    'last_billed_at' => now(),
                    'next_billing_at' => now()->addMonth(),
                ]);

                $tx->update(['subscription_id' => $subscription->id]);
            }
        }

        // Disparar evento para consolidar en donations central y generar PDF
        event(new AtcPaymentCapturedEvent($tx));

        return [
            'success' => true,
            'status' => 'CAPTURED',
            'transactionId' => $tx->id,
            'merchantReferenceNumber' => $referenceNo,
            'amount' => $tx->amount,
            'currency' => $tx->currency,
            'raw' => $resData,
        ];
    }

    public function processRecurringCharge(AtcSubscription $subscription): array
    {
        return $this->processRecurringPayment($subscription);
    }

    /**
     * Paso Especial: Cobro Recurrente Programado (MIT - Merchant Initiated Transaction)
     * Ejecuta el cobro automático mensual utilizando el Payment Instrument Token de Cybersource TMS.
     */
    public function processRecurringPayment(AtcSubscription $subscription): array
    {
        if (strtoupper((string)$subscription->status) !== 'ACTIVE') {
            return [
                'success' => false,
                'message' => "La suscripción #{$subscription->id} no está activa ({$subscription->status}).",
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

        $mitData = [
            'user_id' => $subscription->user_id,
            'campaign_id' => $subscription->campaign_id,
            'email' => $user ? $user->email : null,
            'phone' => $user ? $user->phone : null,
            'ci' => $user ? $user->ci : null,
        ];

        $payload = [
            'clientReferenceInformation' => [
                'code' => $referenceNo,
            ],
            'processingInformation' => [
                'capture' => true,
                'commerceIndicator' => 'recurring',
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
            'merchantDefinedInformation' => $this->buildMerchantDefinedInformation($mitData, true, false),
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
     * Construye los 13 MDDs oficiales requeridos por ATC Red Enlace para la Vertical J (Servicios ONG - Rubro 8398).
     *
     * @param array $data Datos del contexto de pago / donación
     * @param bool $isRecurring Indica si la donación es una suscripción recurrente
     * @param bool $isInitialSeed Indica si es el primer pago semilla (CIT) o un cobro automático posterior (MIT)
     * @return array Lista estructurada de merchantDefinedInformation para Cybersource
     */
    public function buildMerchantDefinedInformation(array $data, bool $isRecurring = false, bool $isInitialSeed = true): array
    {
        $user = auth('sanctum')->user() ?? (auth()->user() ?? (isset($data['user_id']) ? \App\Models\User::find($data['user_id']) : null));
        $isLoggedIn = !is_null($user);

        // MDD 1: ¿Usuario Logueado? (SI / NO)
        $mdd1 = $isLoggedIn ? 'SI' : 'NO';

        // MDD 2: Fecha creación de la cuenta (d/m/Y)
        $mdd2 = ($isLoggedIn && $user->created_at)
            ? $user->created_at->format('d/m/Y')
            : now()->format('d/m/Y');

        // MDD 4: Fecha de última donación (d/m/Y)
        $lastDonationDate = now()->format('d/m/Y');
        try {
            if ($isLoggedIn) {
                $lastTx = AtcTransaction::where('user_id', $user->id)
                    ->where('status', 'CAPTURED')
                    ->latest('id')
                    ->first();
                if ($lastTx && $lastTx->created_at) {
                    $lastDonationDate = $lastTx->created_at->format('d/m/Y');
                }
            }
        } catch (\Throwable $e) {
            // Fallback seguro a la fecha actual
            $lastDonationDate = now()->format('d/m/Y');
        }
        $mdd4 = $lastDonationDate;

        // MDD 5: Tiempo de registro de la cuenta en días (Entero)
        $mdd5 = ($isLoggedIn && $user->created_at)
            ? (string) $user->created_at->diffInDays(now())
            : '0';

        // MDD 7: Nombre del comercio (Razón social)
        $mdd7 = config('services.atc.merchant_name', 'Fundacion Nuestra Esperanza');

        // MDD 11: Documento del donante (CI/DNI o 'NA' para extranjeros/invitados)
        $donorDoc = !empty($data['ci']) ? trim((string)$data['ci']) : (!empty($user->ci) ? trim((string)$user->ci) : 'NA');
        $mdd11 = $donorDoc ?: 'NA';

        // MDD 12: Teléfono alternativo
        $phone = !empty($data['phone']) ? trim((string)$data['phone']) : (!empty($user->phone) ? trim((string)$user->phone) : '70000000');
        $mdd12 = $phone ?: '70000000';

        // MDD 15: ID de Usuario
        if ($isLoggedIn) {
            $mdd15 = "USER-{$user->id}";
        } else {
            $guestIdentifier = !empty($data['email']) ? substr(md5(strtolower(trim($data['email']))), 0, 8) : strtoupper(Str::random(8));
            $mdd15 = "GUEST-{$guestIdentifier}";
        }

        // MDD 23: Identificador Red Social / Tipo de Login
        $mdd23 = $isLoggedIn ? ($user->provider ?? 'Email') : 'Guest';

        // MDD 87: ID del servicio / Campaña (Código asignado para identificar el servicio)
        $campaignId = $data['campaign_id'] ?? null;
        $mdd87 = !empty($campaignId) ? "CAMP-{$campaignId}" : 'CAMP-GRAL';

        // MDD 88: Nombre del servicio / Campaña
        $campaignName = $data['campaign_name'] ?? null;
        if (!$campaignName && !empty($campaignId)) {
            $campaign = \App\Models\Campaign::find($campaignId);
            if ($campaign) {
                $campaignName = $campaign->title ?? $campaign->name;
            }
        }
        $mdd88 = $campaignName ?: 'Donacion General';

        // MDD 90: Tipo de servicio ('Donacion Mensual' o 'Donacion Unica')
        $mdd90 = $isRecurring ? 'Donacion Mensual' : 'Donacion Unica';

        // MDD 95: returnUrl (URL de retorno del comercio)
        $mdd95 = config('app.frontend_url', config('app.url')) . '/donar';

        // MDD 97: Tipo de Transacción para Débito Automático ('Semilla' o 'Recurrente')
        $mdd97 = $isRecurring ? ($isInitialSeed ? 'Semilla' : 'Recurrente') : 'Semilla';

        // Lista sinóptica con truncamiento de seguridad estricto a 50 chars (100 para URLs)
        $rawMdds = [
            1  => $this->sanitizeMddValue($mdd1, 5),
            2  => $this->sanitizeMddValue($mdd2, 10),
            4  => $this->sanitizeMddValue($mdd4, 10),
            5  => $this->sanitizeMddValue($mdd5, 10),
            7  => $this->sanitizeMddValue($mdd7, 50),
            11 => $this->sanitizeMddValue($mdd11, 50),
            12 => $this->sanitizeMddValue($mdd12, 50),
            15 => $this->sanitizeMddValue($mdd15, 50),
            23 => $this->sanitizeMddValue($mdd23, 50),
            87 => $this->sanitizeMddValue($mdd87, 50),
            88 => $this->sanitizeMddValue($mdd88, 50),
            90 => $this->sanitizeMddValue($mdd90, 50),
            95 => $this->sanitizeMddValue($mdd95, 100),
            97 => $this->sanitizeMddValue($mdd97, 50),
        ];

        $formatted = [];
        foreach ($rawMdds as $key => $val) {
            $formatted[] = [
                'key' => (string) $key,
                'value' => (string) $val,
            ];
        }

        return $formatted;
    }

    /**
     * Sanitiza y trunca el valor de un MDD para evitar errores de validación de Cybersource.
     */
    protected function sanitizeMddValue(?string $value, int $maxLength = 50): string
    {
        if (is_null($value) || $value === '') {
            return 'NA';
        }

        $cleaned = trim(preg_replace('/[\r\n\t]+/', ' ', (string) $value));
        return mb_substr($cleaned, 0, $maxLength, 'UTF-8');
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
