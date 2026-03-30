<?php

namespace App\Console\Commands;

use App\Jobs\GenerarCertificadoJob;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedJohnDoeDonations extends Command
{
    protected $signature = 'seed:john-doe-donations';
    protected $description = 'Creates user john.doe@example.com and assigns 3 donations with certificates.';

    public function handle()
    {
        $this->info('🚀 Starting seeding for John Doe...');

        try {
            // 1. Create or find User
            $email = 'john.doe@example.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'John',
                    'last_name' => 'Doe',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'ci' => '12345678'
                ]
            );

            // 2. Create or find Donor profile
            $donor = Donor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => '555-0199',
                    'country' => 'BO',
                    'city' => 'La Paz',
                    'marketing_opt_in' => true
                ]
            );

            // 3. Ensure Currency
            $currency = Currency::firstOrCreate(
                ['iso_code' => 'BOB'],
                ['name' => 'Boliviano', 'symbol' => 'Bs.']
            );

            // 4. Create 3 donations
            for ($i = 1; $i <= 3; $i++) {
                $donation = Donation::create([
                    'donor_id' => $donor->id,
                    'amount' => 100.00 * $i,
                    'currency_id' => $currency->id,
                    'status' => 'succeeded',
                    'provider' => 'seed_script',
                    'provider_payment_id' => 'seed_' . uniqid(),
                    'date' => now()->subDays($i),
                    'is_recurring' => false,
                    'is_anonymous' => false,
                    'fee' => 0,
                    'net_amount' => 100.00 * $i,
                    'channel' => 'manual_seed',
                    'ip' => '127.0.0.1'
                ]);

                // Dispatch Job to generate PDF
                GenerarCertificadoJob::dispatch($donation);
                $this->line("✅ Donation ID: {$donation->id} created and Job dispatched.");
            }

            $this->info("User created/found: {$email} / password");
            $this->info('Command finished successfully.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
