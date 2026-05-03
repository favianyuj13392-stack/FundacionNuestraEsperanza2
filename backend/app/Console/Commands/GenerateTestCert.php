<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Donation;
use App\Services\CertificadoService;
use Carbon\Carbon;

class GenerateTestCert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:generate-cert {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a test certificate for a user';

    /**
     * Execute the console command.
     */
    public function handle(CertificadoService $certificadoService)
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->info("User $email not found. Creating...");
            $user = User::factory()->create([
                'email' => $email,
                'name' => 'JOHN DOE TEST USER', // Uppercase name for cert
                'password' => bcrypt('password'), // dummy
            ]);
        }

        $this->info("User: {$user->name} (ID: {$user->id})");

        // Create or find Donor
        $donor = \App\Models\Donor::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $email,
                'first_name' => 'John',
                'last_name' => 'Doe Test',
                'marketing_opt_in' => true
            ]
        );

        // Create a test donation
        // Status must be 'succeeded' (from enum)
        $donation = Donation::create([
            'donor_id' => $donor->id,
            'amount' => 150.00,
            'currency_id' => 1,
            'status' => 'succeeded', 
            'provider' => 'manual',
            'date' => Carbon::now(),
        ]);

        $this->info("Created Test Donation ID: {$donation->id}");

        try {
            $this->info("Generating Certificate...");
            $certificate = $certificadoService->generarCertificado($donation);

            if ($certificate) {
                $this->info("Success! Certificate generated.");
                $this->info("UUID/Folio: " . $certificate->folio);
                $this->info("URL/Path: " . $certificate->pdf_url);
                $this->info("Full System Path: " . storage_path('app/public/' . $certificate->pdf_url));
            } else {
                $this->error("Certificate generation returned null (maybe amount too low or already exists?).");
            }

        } catch (\Exception $e) {
            $this->error("Error generating certificate: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
