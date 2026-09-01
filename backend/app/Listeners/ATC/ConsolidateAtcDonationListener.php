<?php

namespace App\Listeners\ATC;

use App\Events\ATC\AtcPaymentCapturedEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\GenerarCertificadoJob;

class ConsolidateAtcDonationListener
{
    /**
     * Handle the event.
     * Consolidates captured ATC card transactions into the central `donations` table.
     */
    public function handle(AtcPaymentCapturedEvent $event): void
    {
        $tx = $event->transaction;

        if ($tx->status !== 'CAPTURED') {
            return;
        }

        // Avoid duplicate consolidation using provider and provider_payment_id
        $existing = DB::table('donations')
            ->where('provider', 'cybersource')
            ->where('provider_payment_id', $tx->merchant_reference_number)
            ->first();

        if ($existing) {
            Log::info("ATC Donation already consolidated for ref: {$tx->merchant_reference_number}");
            return;
        }

        $user = $tx->user;
        $currencyId = ($tx->currency === 'USD') ? 2 : 1;

        $donationId = DB::table('donations')->insertGetId([
            'campaign_id' => $tx->campaign_id,
            'donor_id' => null,
            'qr_id' => null,
            'currency_id' => $currencyId,
            'amount' => $tx->amount,
            'status' => 'succeeded',
            'provider' => 'cybersource',
            'provider_payment_id' => $tx->merchant_reference_number,
            'provider_subscription_id' => $tx->subscription_id ? (string) $tx->subscription_id : null,
            'is_recurring' => $tx->subscription_id ? true : false,
            'is_anonymous' => $user ? false : true,
            'channel' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("ATC Donation successfully consolidated into central donations table. ID: {$donationId}");

        // If user is identified, trigger certificate generation
        if ($user && $user->email) {
            try {
                GenerarCertificadoJob::dispatch($donationId);
                Log::info("GenerarCertificadoJob dispatched for ATC donation ID: {$donationId}");
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch GenerarCertificadoJob: " . $e->getMessage());
            }
        }
    }
}
