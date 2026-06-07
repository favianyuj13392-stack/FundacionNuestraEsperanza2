<?php

namespace App\Http\Controllers;

use App\Models\BnbClient;
use App\Models\BnbSubscription;
use App\Services\BnbDomiciliacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BnbSubscriptionController
 *
 * Orquesta el flujo completo de suscripción de domiciliación (débito automático):
 *   1. Valida el formulario del donante.
 *   2. Crea o actualiza el BnbClient y lo sincroniza con el BNB (UpdateRecord).
 *   3. Crea la BnbSubscription y genera el QR de domiciliación (GetQRVariableAmount).
 *   4. Devuelve el QR en base64 para que el frontend lo muestre al donante.
 *
 * Ruta:
 *   POST /api/subscriptions/domiciliacion
 */
class BnbSubscriptionController extends Controller
{
    private BnbDomiciliacionService $domiciliacionService;

    public function __construct(BnbDomiciliacionService $domiciliacionService)
    {
        $this->domiciliacionService = $domiciliacionService;
    }

    /**
     * Procesa el formulario de donación recurrente y genera el QR de domiciliación.
     *
     * Flujo:
     *   1. Validación del Request
     *   2. Gestión del BnbClient (buscar o crear + marcar para re-sincronizar si cambió)
     *   3. Sincronización del cliente con BNB (UpdateRecord)
     *   4. Creación de BnbSubscription en BD
     *   5. Generación del QR de domiciliación (GetQRVariableAmount)
     *   6. Respuesta JSON con los datos del QR para el frontend Next.js
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // =========================================================================
        // PASO 1: Validación
        // =========================================================================
        $validated = $request->validate([
            'name'         => 'required|string|max:200',
            'email'        => 'required|email|max:200',
            'address'      => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:30',
            'amount'       => 'required|numeric|min:1',
            // campaign_id es opcional: permite vincular la suscripción a una campaña
            'campaign_id'  => 'nullable|integer|exists:campaigns,id',
        ]);

        Log::info('BNB Subscription: store iniciado', [
            'email'  => $validated['email'],
            'amount' => $validated['amount'],
        ]);

        // =========================================================================
        // PASO 2: Gestión del BnbClient
        // Usamos el email como clave de búsqueda. Si el donante ya existe,
        // actualizamos sus datos y lo marcamos para re-sincronizar con BNB.
        // =========================================================================
        try {
            /** @var BnbClient $client */
            $client = BnbClient::firstOrNew(['email' => $validated['email']]);

            $isNew = ! $client->exists;

            // Generamos el identifier si es un cliente nuevo.
            // Formato: fne-{timestamp}{random} → único y rastreable.
            if ($isNew) {
                $client->identifier = 'fne-' . time() . rand(100, 999);
            }

            // Detectamos si algún dato relevante cambió para forzar re-sincronización
            $datosAnteriores = [
                'name'         => $client->name,
                'address'      => $client->address,
                'phone_number' => $client->phone_number,
            ];

            $client->name         = $validated['name'];
            $client->address      = $validated['address'] ?? $client->address;
            $client->phone_number = $validated['phone_number'] ?? $client->phone_number;
            $client->bnb_status   = 1; // Activo

            // Si los datos cambiaron o es nuevo cliente → forzar re-sincronización
            $datosCambiaron = $isNew || (
                $datosAnteriores['name']         !== $client->name         ||
                $datosAnteriores['address']       !== $client->address      ||
                $datosAnteriores['phone_number']  !== $client->phone_number
            );

            if ($datosCambiaron) {
                $client->synced_to_bnb = false;
            }

            $client->save();

        } catch (\Exception $e) {
            Log::error('BNB Subscription: error al guardar BnbClient', [
                'email'   => $validated['email'],
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar tu información. Por favor intenta de nuevo.',
            ], 500);
        }

        // =========================================================================
        // PASO 3: Sincronización del cliente con BNB (UpdateRecord)
        // Solo sincronizamos si es necesario (nuevo o datos cambiados).
        // =========================================================================
        if (! $client->synced_to_bnb) {
            $syncResult = $this->domiciliacionService->syncClient($client);

            if (! $syncResult['success']) {
                Log::error('BNB Subscription: syncClient falló', [
                    'client_id'  => $client->id,
                    'identifier' => $client->identifier,
                    'message'    => $syncResult['message'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No pudimos registrar tu información con el banco. Por favor intenta más tarde.',
                    'detail'  => $syncResult['message'],
                ], 502);
            }
        }

        // =========================================================================
        // PASO 4 & 5: Creación de BnbSubscription + Generación del QR
        // Envuelto en una transacción: si el banco falla, no guardamos el registro.
        // =========================================================================
        try {
            $subscription = null;

            DB::transaction(function () use ($validated, $client, &$subscription) {

                // Creamos la suscripción en BD con status 'pending'
                $subscription = BnbSubscription::create([
                    'bnb_client_id'        => $client->id,
                    'currency_code'        => BnbSubscription::CURRENCY_BOB,  // Siempre BOB
                    'amount'               => $validated['amount'],
                    'service_code'         => config('bnb.service_code', ''),
                    'reference'            => 'Suscripcion FNE ' . time(),
                    'qr_type'              => BnbSubscription::QR_TYPE_VARIABLE,
                    'installments_quantity'=> 1,
                    'payment_frequency'    => BnbSubscription::FREQ_MONTHLY,
                    'status'               => BnbSubscription::STATUS_PENDING,
                ]);

                Log::info('BNB Subscription: BnbSubscription creada en BD', [
                    'subscription_id' => $subscription->id,
                    'client_id'       => $client->id,
                    'amount'          => $validated['amount'],
                ]);
            });

        } catch (\Exception $e) {
            Log::error('BNB Subscription: error al crear BnbSubscription en BD', [
                'client_id' => $client->id,
                'amount'    => $validated['amount'],
                'message'   => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la suscripción. Por favor intenta de nuevo.',
            ], 500);
        }

        // Llamamos al banco para generar el QR.
        // El servicio internamente persiste qr_id, qr_image_base64 y mime_type en $subscription.
        $qrResult = $this->domiciliacionService->createSubscriptionQr($subscription, $client);

        if (! $qrResult['success']) {
            // El QR no se generó → eliminamos el registro pendiente para no dejar basura en BD
            $subscription->forceDelete();

            Log::error('BNB Subscription: createSubscriptionQr falló, suscripción eliminada', [
                'client_id' => $client->id,
                'amount'    => $validated['amount'],
                'message'   => $qrResult['message'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No pudimos generar el código QR con el banco. Por favor intenta más tarde.',
                'detail'  => $qrResult['message'],
            ], 502);
        }

        // Recargamos el modelo para obtener los datos que el servicio persistió
        $subscription->refresh();

        Log::info('BNB Subscription: flujo completo exitoso', [
            'subscription_id' => $subscription->id,
            'qr_id'           => $subscription->qr_id,
            'client_id'       => $client->id,
            'amount'          => $subscription->amount,
        ]);

        // =========================================================================
        // PASO 6: Respuesta al frontend (Next.js SPA)
        // Devolvemos el base64 del QR para que React lo dibuje con <img>.
        //
        // Uso en el frontend:
        //   <img src={`data:${subscription.mime_type};base64,${subscription.qr_image_base64}`} />
        // =========================================================================
        return response()->json([
            'success' => true,
            'message' => '¡Suscripción creada! Escanea el código QR con tu app bancaria para activarla.',
            'data'    => [
                'subscription_id'  => $subscription->id,
                'qr_id'            => $subscription->qr_id,
                'qr_image_base64'  => $subscription->qr_image_base64,
                'mime_type'        => $subscription->mime_type,
                'amount'           => (float) $subscription->amount,
                'currency'         => 'BOB',
                'status'           => $subscription->status,
                'reference'        => $subscription->reference,
                // Instrucción clara para el usuario final
                'instructions'     => 'Abre tu app bancaria, escanea este QR y acepta la domiciliación. '
                                    . 'A partir del próximo mes, tu donación de Bs '
                                    . number_format($subscription->amount, 2)
                                    . ' se debitará automáticamente de tu cuenta.',
            ],
        ], 201);
    }

    /**
     * Consulta el estado actual de una suscripción por su ID.
     * Útil para que el frontend haga polling hasta que el estado cambie de
     * 'pending' a 'enrolled' (cuando el donante escanee el QR).
     *
     * GET /api/subscriptions/domiciliacion/{id}/status
     */
    public function status(int $id): JsonResponse
    {
        $subscription = BnbSubscription::with('client:id,name,email')->find($id);

        if (! $subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Suscripción no encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'subscription_id' => $subscription->id,
                'qr_id'           => $subscription->qr_id,
                'status'          => $subscription->status,
                'amount'          => (float) $subscription->amount,
                'currency'        => 'BOB',
                'client_name'     => $subscription->client->name ?? null,
                'is_enrolled'     => $subscription->isEnrolled(),
                'is_pending'      => $subscription->isPending(),
            ],
        ]);
    }
}
