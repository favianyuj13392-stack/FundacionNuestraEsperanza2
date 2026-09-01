<?php

namespace Tests\Feature\ATC;

use Tests\TestCase;
use App\Models\ATC\AtcPaymentProfile;
use App\Models\ATC\AtcSubscription;
use Illuminate\Support\Facades\Http;

class ProcessRecurringDonationsCommandTest extends TestCase
{
    public function test_recurring_donations_command_execution(): void
    {
        $this->artisan('atc:process-recurring-donations')
             ->expectsOutput('Iniciando procesamiento de donaciones recurrentes por tarjeta (ATC)...')
             ->assertExitCode(0);
    }
}
