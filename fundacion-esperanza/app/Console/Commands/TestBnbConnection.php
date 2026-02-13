<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BnbDonationService;
use Illuminate\Support\Facades\Config;

class TestBnbConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bnb:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test authentication with real BNB API (Bypassing Mock Mode)';

    /**
     * Execute the console command.
     */
    public function handle(BnbDonationService $service)
    {
        $this->info("Starting BNB Connection Test...");

        // 1. Force Mock Mode OFF for this process
        if (env('BNB_MOCK_MODE')) {
            $this->warn("⚠️  BNB_MOCK_MODE is enabled in .env, but we will force it OFF for this test.");
            // Note: Since BnbDonationService reads env() directly in __construct, 
            // changing Config::set() might not be enough if it was already instantiated or if it uses env().
            // However, the Service reads credentials in constructor, but MOCK MODE check is inside methods usually.
            // Let's check: generateFixedQR checks env('BNB_MOCK_MODE').
            // authenticate() does NOT check mock mode, it just tries to auth.
            // So calling authenticate() is safe and will try real network.
        }

        $this->info("Attempting to switch Account ID and Auth ID from .env...");
        $this->line("Account ID: " . substr(env('BNB_ACCOUNT_ID'), 0, 5) . "...");
        
        try {
            // 2. Call Authenticate
            $token = $service->authenticate();

            if ($token) {
                $this->info("✅ Authentication SUCCESSFUL!");
                $this->line("Token received (first 20 chars): " . substr($token, 0, 20) . "...");
                return 0;
            } else {
                $this->error("❌ Authentication FAILED. Check logs for details.");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Authentication EXCEPTION: " . $e->getMessage());
            return 1;
        }
    }
}
