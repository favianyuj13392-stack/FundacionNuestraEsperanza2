<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Validate a reactivation token and return subscription details
     */
    public function validateReactivation(string $token): JsonResponse
    {
        $subscription = Subscription::where('reactivation_token', $token)
            ->where('reactivation_token_expires_at', '>', now())
            ->whereIn('status', ['cancelled', 'failed', 'paused'])
            ->with(['campaign', 'user'])
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Token de reactivación inválido o expirado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $subscription->id,
                'amount' => $subscription->amount,
                'currency' => $subscription->currency,
                'campaign_id' => $subscription->campaign_id,
                'campaign_name' => $subscription->campaign ? $subscription->campaign->name : null,
                'status' => $subscription->status,
                'user_id' => $subscription->user_id,
                'donor_name' => $subscription->user ? $subscription->user->name : null,
                'donor_email' => $subscription->user ? $subscription->user->email : null,
                'has_saved_card' => !empty($subscription->cybersource_payment_token),
            ],
        ]);
    }

    /**
     * Re-activate subscription using existing saved Cybersource TMS token (1-Click Reactivation!)
     */
    public function confirmReactivation(string $token): JsonResponse
    {
        $subscription = Subscription::where('reactivation_token', $token)
            ->where('reactivation_token_expires_at', '>', now())
            ->whereIn('status', ['cancelled', 'failed', 'paused'])
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'El enlace de reactivación es inválido o ha expirado.',
            ], 400);
        }

        $subscription->status = 'active';
        $subscription->cancelled_at = null;
        $subscription->cancellation_reason = null;
        $subscription->reactivation_token = null;
        $subscription->next_charge_date = now()->addMonth();
        $subscription->save();

        return response()->json([
            'success' => true,
            'message' => '¡Tu suscripción ha sido reactivada con éxito con 1 solo clic!',
            'data' => $subscription,
        ]);
    }
}
