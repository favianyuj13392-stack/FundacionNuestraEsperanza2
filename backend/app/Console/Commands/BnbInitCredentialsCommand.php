<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BnbInitCredentialsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bnb:init-credentials {new_password : New secure password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize BNB credentials by updating the default password.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = env('BNB_ACCOUNT_ID');
        $currentAuthId = env('BNB_AUTH_ID');
        $newAuthId = $this->argument('new_password');

        if (!$accountId || !$currentAuthId) {
            $this->error('BNB_ACCOUNT_ID or BNB_AUTH_ID not set in .env');
            return 1;
        }

        $url = 'http://test.bnb.com.bo/ClientAuthentication.API/api/v1/auth/UpdateCredentials';

        $this->info("Updating credentials for Account ID: $accountId");

        try {
            $response = Http::post($url, [
                'AccountId' => $accountId,
                'actualAuthorizationId' => $currentAuthId,
                'newAuthorizationId' => $newAuthId,
            ]);

            if ($response->successful()) {
                $this->info('Credentials updated successfully!');
                $this->info("Please update your .env with the new password: BNB_AUTH_ID=$newAuthId");
                return 0;
            }

            $this->error('Failed to update credentials.');
            $this->error('Status: ' . $response->status());
            $this->error('Body: ' . $response->body());
            return 1;

        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            return 1;
        }
    }
}
