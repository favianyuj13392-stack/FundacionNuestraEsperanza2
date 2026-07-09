<?php

namespace App\Http\Controllers;

use App\Models\Qr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BnbWebhookController extends Controller
{
    public function handle(Request $request, \App\Services\BnbDonationService $bnbService)
    {
        // === TEMPORARY BYPASS PARA REGISTRO DE WEBHOOK EN PRODUCCION ===
        return response()->json(['success' => true, 'message' => 'OK']);
        // ===============================================================

        // 1. SECURITY: Trust but Verify
        // El secret por URL fue eliminado para evitar fricción con el banco.
        // La seguridad está garantizada porque más abajo consultamos activamente
        // el estado del QRId directamente a los servidores del BNB.

        // Log the incoming webhook for debugging
        Log::info('BNB Webhook Received', $request->all());

        $qrId = $request->input('QRId');
        
        if (!$qrId) {
            return response()->json(['success' => false, 'message' => 'QRId missing'], 400);
        }

        // 2. TRUST BUT VERIFY: Check status with BNB directly
        try {
            // We ignore mock mode logic here, we want real confirmation if possible.
            // If checking status fails (e.g. timeout), we might decide to fail or proceed with caution.
            // For now, strict: if we can't verify, we don't process.
            $statusData = $bnbService->checkStatus($qrId);
            
            if (!$statusData) {
                Log::error("BNB Webhook: Could not verify status for QR {$qrId}");
                return response()->json(['success' => false, 'message' => 'Verification failed'], 502);
            }

            // BNB Spec: statusId 2 = Used/Paid
            $statusId = $statusData['statusId'] ?? null;
            if ($statusId != 2) {
                Log::warning("BNB Webhook: QR {$qrId} status mismatch in verification. BNB says: {$statusId}");
                // If ID is 1 (Not Used), maybe it was a duplicate call or confusion. We reject.
                 return response()->json(['success' => false, 'message' => 'Status Mismatch'], 409);
            }

            Log::info("BNB Webhook: Verified QR {$qrId} is PAID (status 2). Proceeding.");

        } catch (\Exception $e) {
             Log::error("BNB Webhook: Verification Exception", ['error' => $e->getMessage()]);
             return response()->json(['success' => false, 'message' => 'Internal Error during verification'], 500);
        }

        return DB::transaction(function () use ($request, $qrId) {
            // Find the QR and lock it to prevent race conditions
            $qr = Qr::where('external_qr_id', $qrId)
                    ->orWhere('code', $qrId)
                    ->lockForUpdate()
                    ->first();

            if ($qr) {
                // Idempotency check
                if ($qr->status === 'paid') {
                    return response()->json([
                        'success' => true, 
                        'message' => 'Already paid'
                    ]);
                }

                // Update status
                $qr->status = 'paid';
                $qr->voucher_id = $request->input('VoucherId');
                
                // Only overwrite donor_name if we don't have a specific one already
                if (empty($qr->donor_name) || $qr->donor_name === 'Anónimo') {
                    $qr->donor_name = $request->input('originName'); 
                }
                
                try {
                    $qr->payment_date = \Carbon\Carbon::parse($request->input('TransactionDateTime'));
                } catch (\Exception $e) {
                    Log::warning("Date parse fail", ['date' => $request->input('TransactionDateTime')]);
                    $qr->payment_date = now();
                }

                // Still keep the full blob for audit
                $blob = json_decode($qr->bnb_blob, true) ?? [];
                $blob['webhook_payload'] = $request->all();
                $qr->bnb_blob = json_encode($blob);
                
                $qr->save();

                // --- CREATE DONATION RECORD ---
                $donation = \App\Models\Donation::create([
                    'campaign_id' => $qr->campaign_id,
                    'amount' => $qr->amount,
                    'currency_id' => 1, // Default BOB
                    'status' => 'succeeded',
                    'provider' => 'bnb',
                    'qr_id' => $qr->id,
                    'donor_id' => $qr->donor_id, // Link to the donor we identified
                    'is_anonymous' => empty($qr->donor_id),
                    'date' => $qr->payment_date,
                ]);

                Log::info("QR {$qrId} marked as paid. Donation #{$donation->id} created.");

                // --- TRIGGER CERTIFICATE GENERATION ---
                if ($donation->donor_id) {
                    \App\Jobs\GenerarCertificadoJob::dispatch($donation);
                }
            } else {
                Log::warning("QR {$qrId} not found via webhook.");
                // If not found in our DB but paid in Bank, maybe we should log it differently?
                // For now, return success to Bank to stop retries, but log error.
            }

            // Return strict success response as per docs
            return response()->json([
                'success' => true,
                'message' => 'OK'
            ]);
        });
    }
}
