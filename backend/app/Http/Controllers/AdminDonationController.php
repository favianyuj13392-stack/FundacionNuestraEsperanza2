<?php

namespace App\Http\Controllers;

use App\Models\DonationTier;
use App\Models\Qr;
use App\Services\BnbDonationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminDonationController extends Controller
{
    protected $bnbService;

    public function __construct(BnbDonationService $bnbService)
    {
        $this->bnbService = $bnbService;
    }

    /**
     * List all donation tiers.
     */
    public function indexTiers()
    {
        return response()->json(DonationTier::orderBy('order')->get());
    }

    /**
     * Store a new donation tier.
     */
    public function storeTier(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'label' => 'required|string|max:50',
            'currency_id' => 'exists:currencies,id',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $tier = DonationTier::create($validated);

        return response()->json($tier, 201);
    }

    /**
     * Update a donation tier.
     */
    public function updateTier(Request $request, DonationTier $tier)
    {
        $validated = $request->validate([
            'amount' => 'numeric|min:0',
            'label' => 'string|max:50',
            'currency_id' => 'exists:currencies,id',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $tier->update($validated);

        return response()->json($tier);
    }

    /**
     * Delete a donation tier.
     */
    public function destroyTier(DonationTier $tier)
    {
        $tier->delete();
        return response()->json(['message' => 'Tier deleted']);
    }

    /**
     * Generate a specific QR for a campaign (Admin use).
     */
    public function generateCampaignQr(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'gloss' => 'required|string|max:100',
            'campaign_id' => 'nullable|exists:campaigns,id',
        ]);

        $amount = $request->amount;
        $gloss = $request->gloss;

        try {
            if ($amount > 0) {
                $response = $this->bnbService->generateFixedQR($amount, $gloss);
            } else {
                // Variable amount QR
                $response = $this->bnbService->generateVariableQR($gloss);
            }

            if (!$response) {
                return response()->json(['message' => 'Failed to generate QR with BNB'], 500);
            }

            // Save to DB
            $qr = Qr::create([
                'campaign_id' => $request->campaign_id,
                'amount' => $amount > 0 ? $amount : null,
                'status' => 'generated',
                'bnb_blob' => json_encode($response),
                'expiration_date' => now()->addDays(1),
                // Assuming response has 'qr' and 'qrId' based on spec
                'url' => $response['qr'] ?? null, // Storing base64 in url field for now or we should use bnb_blob
                'code' => $response['qrId'] ?? null,
            ]);

            return response()->json([
                'qr_image' => $response['qr'] ?? null,
                'qr_id' => $response['qrId'] ?? null,
                'db_id' => $qr->id
            ]);

        } catch (\Exception $e) {
            Log::error('Generate Campaign QR Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
