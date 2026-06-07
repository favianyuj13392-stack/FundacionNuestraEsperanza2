<?php

namespace App\Http\Controllers;

use App\Models\BnbSubscription;
use App\Models\Donation;
use App\Models\Donor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BnbDomiciliacionWebhookController
 *
 * Gestiona las notificaciones entrantes del Banco Nacional de Bolivia (BNB)
 * para el servicio de Domiciliación (débito automático recurrente).
 *
 * Rutas:
 *   POST /api/webhooks/bnb/enroll   → método enroll()
 *   POST /api/webhooks/bnb/payment  → método payment()
 *
 * ⚠️ REGLAS CRÍTICAS DE ARQUITECTURA:
 *   1. CERO validaciones de ?secret= en URL. La ruta es limpia por requisito del BNB.
 *   2. La respuesta SIEMPRE debe incluir "status": 100 junto a success:true/false.
 *      Si falta ese campo, el BNB marcará el webhook como fallido e intentará
 *      reenviar la notificación indefinidamente.
 *   3. SIEMPRE devolver HTTP 200, incluso en errores lógicos internos. Si devolvemos
 *      4xx/5xx, el BNB reintentará la notificación.
 *
 * Referencia: Documentación BNB Open Banking - Domiciliación §15
 */
class BnbDomiciliacionWebhookController extends Controller
{
    /**
     * ENROLL - El donante escaneó y aceptó el QR por primera vez.
     *
     * Payload que envía el BNB (§15.1.1):
     * {
     *   "qrId":           "12546542132121",
     *   "currencyCode":   1,
     *   "amount":         1000,
     *   "accountType":    1,
     *   "accountNumber":  "15012121212",
     *   "reference":      "Donación mensual Fundación",
     *   "accountHolderId":"655656",
     *   "accountHolder":  "Juan Pérez",
     *   "serviceCode":    "XJKSKS",
     *   "originBankId":   1
     * }
     *
     * Respuesta requerida por el BNB (§15.1.2):
     * { "success": true, "message": "OK", "status": 100 }
     */
    public function enroll(Request $request): JsonResponse
    {
        // ✅ REGLA: Log del payload completo para auditoría contable
        Log::info('BNB Domiciliacion ENROLL webhook recibido', [
            'payload'    => $request->all(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $qrId = $request->input('qrId');

        if (! $qrId) {
            Log::warning('BNB Domiciliacion ENROLL: payload sin qrId', [
                'payload' => $request->all(),
            ]);
            // ✅ REGLA: Devolver HTTP 200 siempre con status:100
            return $this->bnbResponse(false, 'qrId requerido.');
        }

        // Buscamos la suscripción correspondiente en nuestra base de datos
        $subscription = BnbSubscription::where('qr_id', $qrId)->first();

        if (! $subscription) {
            Log::error('BNB Domiciliacion ENROLL: suscripción no encontrada', [
                'qr_id' => $qrId,
            ]);
            // ✅ REGLA: Devolver HTTP 200 aunque no la encontremos (evitar reintentos)
            return $this->bnbResponse(false, 'Suscripción no encontrada.');
        }

        // Evitar procesar duplicados si ya está enrollada
        if ($subscription->status === BnbSubscription::STATUS_ENROLLED) {
            Log::warning('BNB Domiciliacion ENROLL: suscripción ya estaba enrolled', [
                'qr_id'           => $qrId,
                'subscription_id' => $subscription->id,
            ]);
            return $this->bnbResponse(true, 'OK');
        }

        // ✅ Actualizamos el estado de la suscripción a "activa"
        $subscription->status = BnbSubscription::STATUS_ENROLLED;
        $subscription->save();

        Log::info('BNB Domiciliacion ENROLL: suscripción activada exitosamente', [
            'qr_id'              => $qrId,
            'subscription_id'    => $subscription->id,
            'bnb_client_id'      => $subscription->bnb_client_id,
            'account_holder'     => $request->input('accountHolder'),
            'account_holder_id'  => $request->input('accountHolderId'),
            'origin_bank_id'     => $request->input('originBankId'),
        ]);

        // ✅ REGLA: Respuesta exacta que exige el BNB
        return $this->bnbResponse(true, 'OK');
    }

    /**
     * PAYMENT - El banco debitó exitosamente una cuota mensual del donante.
     *
     * Payload que envía el BNB (§15.2.1):
     * {
     *   "qrId":                "ACJSS1231",
     *   "installmentId":       1,
     *   "amount":              1000,
     *   "reference":           "Donación mensual Fundación",
     *   "serviceCode":         "XJKSKS",
     *   "originBankId":        1,
     *   "originName":          "Juan Pérez",
     *   "voucherId":           "AB12345678",
     *   "transactionDateTime": "19/12/2022 17:30:15"
     * }
     *
     * Respuesta requerida por el BNB (§15.2.2):
     * { "success": true, "message": "OK", "status": 100 }
     */
    public function payment(Request $request): JsonResponse
    {
        // ✅ REGLA: Log del payload completo para auditoría contable
        Log::info('BNB Domiciliacion PAYMENT webhook recibido', [
            'payload'    => $request->all(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $qrId              = $request->input('qrId');
        $voucherId         = $request->input('voucherId');
        $transactionDtStr  = $request->input('transactionDateTime');
        $amount            = $request->input('amount');

        if (! $qrId || ! $voucherId) {
            Log::warning('BNB Domiciliacion PAYMENT: payload incompleto', [
                'payload' => $request->all(),
            ]);
            return $this->bnbResponse(false, 'qrId y voucherId son requeridos.');
        }

        // Buscamos la suscripción para obtener el cliente y la moneda
        $subscription = BnbSubscription::with('client')->where('qr_id', $qrId)->first();

        if (! $subscription) {
            Log::error('BNB Domiciliacion PAYMENT: suscripción no encontrada', [
                'qr_id'      => $qrId,
                'voucher_id' => $voucherId,
            ]);
            return $this->bnbResponse(false, 'Suscripción no encontrada.');
        }

        // ✅ Idempotencia: evitar registrar el mismo cobro dos veces.
        // El voucherId es el comprobante único del banco para esta transacción.
        $yaRegistrado = Donation::where('provider', 'bnb_domiciliacion')
            ->where('provider_payment_id', $voucherId)
            ->exists();

        if ($yaRegistrado) {
            Log::warning('BNB Domiciliacion PAYMENT: voucher ya registrado (duplicado ignorado)', [
                'qr_id'      => $qrId,
                'voucher_id' => $voucherId,
            ]);
            return $this->bnbResponse(true, 'OK');
        }

        // Parseamos la fecha del banco: formato "DD/MM/YYYY HH:mm:ss"
        try {
            $transactionDate = Carbon::createFromFormat('d/m/Y H:i:s', $transactionDtStr);
        } catch (\Exception $e) {
            Log::warning('BNB Domiciliacion PAYMENT: formato de fecha inválido, usando now()', [
                'transactionDateTime' => $transactionDtStr,
                'error'               => $e->getMessage(),
            ]);
            $transactionDate = now();
        }

        // Obtenemos el donor_id a través del BnbClient → User si existe, o null
        $donorId = null;
        if ($subscription->client && $subscription->client->user_id) {
            $donor   = Donor::where('user_id', $subscription->client->user_id)->first();
            $donorId = $donor?->id;
        }

        try {
            DB::transaction(function () use ($subscription, $amount, $voucherId, $transactionDate, $donorId, $qrId, $request) {

                // ✅ Creamos el registro de donación en nuestra tabla donations
                $donation = Donation::create([
                    'date'                     => $transactionDate,
                    'donor_id'                 => $donorId,
                    'currency_id'              => $subscription->currency_code, // 1=BOB, 2=USD
                    'amount'                   => (float) $amount,
                    'net_amount'               => (float) $amount,  // Sin comisión de plataforma
                    'status'                   => 'succeeded',
                    'provider'                 => 'bnb_domiciliacion',
                    'provider_payment_id'      => $voucherId,           // Comprobante único del banco
                    'provider_subscription_id' => $subscription->qr_id, // Vinculamos al QR de domiciliación
                    'is_recurring'             => true,                 // Es una donación recurrente
                    'is_anonymous'             => false,
                    'channel'                  => 'bnb_webhook',
                    'ip'                       => $request->ip(),
                ]);

                Log::info('BNB Domiciliacion PAYMENT: donación creada exitosamente', [
                    'donation_id'     => $donation->id,
                    'qr_id'           => $qrId,
                    'voucher_id'      => $voucherId,
                    'amount'          => $amount,
                    'subscription_id' => $subscription->id,
                    'donor_id'        => $donorId,
                    'origin_name'     => $request->input('originName'),
                    'origin_bank_id'  => $request->input('originBankId'),
                    'installment_id'  => $request->input('installmentId'),
                ]);
            });

        } catch (\Exception $e) {
            Log::error('BNB Domiciliacion PAYMENT: error al guardar donación', [
                'qr_id'      => $qrId,
                'voucher_id' => $voucherId,
                'amount'     => $amount,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            // ✅ REGLA: Devolvemos HTTP 200 para evitar reintentos del BNB.
            // El error quedó en el log para revisión manual del equipo.
            return $this->bnbResponse(false, 'Error interno al procesar el pago.');
        }

        // ✅ REGLA: Respuesta exacta que exige el BNB
        return $this->bnbResponse(true, 'OK');
    }

    // =========================================================================
    // HELPER PRIVADO
    // =========================================================================

    /**
     * Construye la respuesta JSON estándar que exige el BNB para sus webhooks.
     *
     * ⚠️ El campo "status": 100 es OBLIGATORIO según §15.1.2 y §15.2.2.
     *    Sin él, el banco considera la notificación como fallida.
     *
     * Siempre retorna HTTP 200 para evitar que el BNB reintente la notificación.
     */
    private function bnbResponse(bool $success, string $message): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'status'  => 100,
        ], 200);
    }
}
