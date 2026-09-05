<?php

namespace App\Http\Controllers;

use App\Models\DonationTier;
use App\Models\Donation;
use App\Models\Qr;
use App\Services\BnbDonationService;
use App\Jobs\GenerarCertificadoJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicDonationController extends Controller
{
    protected $bnbService;

    public function __construct(BnbDonationService $bnbService)
    {
        $this->bnbService = $bnbService;
    }

    /**
     * Get active donation options (tiers).
     */
    public function getOptions()
    {
        $tiers = DonationTier::where('is_active', true)
            ->orderBy('order')
            ->get();
            
        return response()->json($tiers);
    }

    /**
     * Request a QR code for donation.
     */
    public function requestQr(Request $request)
    {
        Log::info('requestQr: Started');
        
        $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'tier_id' => 'nullable|exists:donation_tiers,id',
            'custom_amount' => 'nullable|numeric|min:1',
            'is_anonymous' => 'boolean',
            'donor_name' => 'nullable|required_if:is_anonymous,false|string|max:100',
            'donor_ci' => 'nullable|string|max:20',
            'donor_phone' => 'nullable|string|max:30',
        ]);

        Log::notice('requestQr: Validation passed');
        
        $amount = 0;
        $glossBase = "Donacion Web";
        $customerGloss = "";

        if ($request->tier_id) {
            $tier = DonationTier::find($request->tier_id);
            $amount = $tier->amount;
            $glossBase = "Donacion: " . $tier->label;
        } elseif ($request->custom_amount) {
            $amount = $request->custom_amount;
            $glossBase = "Donacion Libre";
        } else {
            return response()->json(['message' => 'Amount or Tier required'], 400);
        }

        Log::notice('requestQr: Amount set', ['amount' => $amount]);

        // Logic for Donor Identity
        $donorName = "Anónimo";
        $donorId = null; // Capture ID
        
        if (!$request->boolean('is_anonymous') && $request->donor_name) {
            $donorName = $request->donor_name;
            $customerGloss = $donorName;

            $user = $request->user('sanctum');
            
            // Data for update/create
            $donorData = [
                'first_name' => $donorName, 
                'phone' => $request->donor_phone,
                'identity_document' => $request->donor_ci
            ];

            if ($user) {
                $donorData['email'] = $user->email;
                $donorData['user_id'] = $user->id;
                
                $donor = \App\Models\Donor::updateOrCreate(
                    ['user_id' => $user->id],
                    $donorData
                );
            } else {
                // Guest Donor logic
                // Since 'email' is required in the database but we don't collect it here,
                // we'll generate a placeholder email to satisfy the constraint.
                // We trust identity_document/phone for identification in this case.
                $donorData['email'] = 'guest_' . uniqid() . '@no-email.com'; 
                
                $donor = \App\Models\Donor::create($donorData);
            }
            
            $donorId = $donor->id;
        }

        try {
            // Generate an internal reference ID for tracking
            $internalId = uniqid('don_', true);

            Log::notice('requestQr: About to call generateFixedQR', ['amount' => $amount]);
            
            // Generate QR
            // We pass $customerGloss to be included in the BNB Gloss if possible
            $response = $this->bnbService->generateFixedQR($amount, $glossBase, $internalId);

            Log::notice('requestQr: generateFixedQR returned', ['response_keys' => $response ? array_keys((array)$response) : 'null']);

            if (!$response || !isset($response['success'])) {
                Log::notice('requestQr: Response validation failed');
                return response()->json(['message' => 'Error communicating with Payment Gateway'], 503);
            }

            Log::notice('requestQr: Response passed validation, about to create QR record');

            // Save QR record with enhanced fields
            $qr = Qr::create([
                'campaign_id' => $request->campaign_id,
                'amount' => $amount,
                'status' => 'generated',
                'bnb_blob' => json_encode($response),
                'expiration_date' => $response['expirationDate'] ?? now()->addDays(1),
                'url' => $response['qr_image'] ?? $response['qr'] ?? null,  // BNB uses 'qr' or our alias 'qr_image'
                'code' => $response['qrId'] ?? $response['id'] ?? null,  // BNB returns 'id', we map to 'qrId'
                'external_qr_id' => $response['qrId'] ?? $response['id'] ?? null,
                'gloss' => $response['gloss'] ?? $glossBase,
                'donor_name' => $donorName, 
                'donor_id' => $donorId,
            ]);
            
            Log::notice('requestQr: QR record created', ['qr_id' => $qr->id]);

            // If identified, we might want to link this QR to a user/donor?
            // The Qr model currently doesn't have donor_id, but the Donation model does.
            // When the payment is webhooked, we usually create the Donation then.
            // But we need to know who it was. 
            // We are saving donor_name in QR, so we can use that to create the Donation later.

            Log::notice('requestQr: About to return success response');
            
            return response()->json([
                'qr_image' => $response['qr'] ?? null,
                'qr_id' => $response['qrId'] ?? null,
                'expiration' => now()->addDays(1)->toIso8601String(),
                'mock' => $response['mock'] ?? false // Pass mock flag for frontend simulation
            ]);

        } catch (\Exception $e) {
            Log::error('Public QR Request Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Check status of a QR with real-time BNB verification fallback.
     */
    public function checkStatus($qrId)
    {
        $qr = Qr::where('code', $qrId)
                ->orWhere('external_qr_id', $qrId)
                ->first();

        if (!$qr) {
            return response()->json(['message' => 'QR not found'], 404);
        }

        // 1. Fast path: If already marked as paid, return immediately
        if ($qr->status === 'paid') {
            return response()->json(['status' => 'paid', 'qrId' => $qrId]);
        }

        // 2. Active Sync: Query BNB API in real-time to check if money arrived
        try {
            $bnbStatus = $this->bnbService->checkStatus($qrId);

            if ($bnbStatus) {
                $statusId = $bnbStatus['statusId'] ?? ($bnbStatus['status'] ?? null);

                // BNB Spec: statusId 2 = Used / Paid
                if ($statusId == 2 || $statusId === '2' || (isset($bnbStatus['status']) && strtolower((string)$bnbStatus['status']) === 'paid')) {
                    DB::transaction(function () use ($qr, $bnbStatus, $qrId) {
                        $lockedQr = Qr::where('id', $qr->id)->lockForUpdate()->first();

                        if ($lockedQr && $lockedQr->status !== 'paid') {
                            $lockedQr->status = 'paid';
                            $lockedQr->voucher_id = $bnbStatus['voucherId'] ?? ($bnbStatus['id'] ?? $lockedQr->voucher_id);
                            $lockedQr->payment_date = now();

                            $blob = json_decode($lockedQr->bnb_blob, true) ?? [];
                            $blob['polling_verified_at'] = now()->toIso8601String();
                            $blob['bnb_status_response'] = $bnbStatus;
                            $lockedQr->bnb_blob = json_encode($blob);
                            $lockedQr->save();

                            // Create donation record if not already created
                            $existingDonation = Donation::where('qr_id', $lockedQr->id)->first();
                            if (!$existingDonation) {
                                $donation = Donation::create([
                                    'campaign_id' => $lockedQr->campaign_id,
                                    'amount' => $lockedQr->amount,
                                    'currency_id' => 1, // Default BOB
                                    'status' => 'succeeded',
                                    'provider' => 'bnb',
                                    'qr_id' => $lockedQr->id,
                                    'donor_id' => $lockedQr->donor_id,
                                    'is_anonymous' => empty($lockedQr->donor_id),
                                    'date' => $lockedQr->payment_date,
                                ]);

                                Log::info("QR {$qrId} marked as paid via Polling Sync. Donation #{$donation->id} created.");

                                if ($donation->donor_id) {
                                    GenerarCertificadoJob::dispatch($donation);
                                }
                            }
                        }
                    });

                    return response()->json(['status' => 'paid', 'qrId' => $qrId]);
                } elseif ($statusId == 3 || $statusId === '3') {
                    $qr->update(['status' => 'expired']);
                    return response()->json(['status' => 'expired', 'qrId' => $qrId]);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Error verifying BNB QR status during polling for QR {$qrId}: " . $e->getMessage());
        }

        return response()->json(['status' => $qr->status, 'qrId' => $qrId]);
    }
}
