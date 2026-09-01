<?php

namespace Tests\Feature\ATC;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AtcPaymentControllerTest extends TestCase
{
    public function test_setup_authentication_validation(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            '*' => \Illuminate\Support\Facades\Http::response([
                'referenceId' => 'mock-ref-id',
                'accessToken' => 'mock-jwt-token',
                'deviceDataCollectionUrl' => 'https://mock.url'
            ], 201),
        ]);

        $response = $this->postJson('/api/v1/atc/setup-authentication', []);

        // Should return 200 with generated mock/real reference
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'merchantReferenceNumber']);
    }

    public function test_check_enrollment_validation(): void
    {
        $response = $this->postJson('/api/v1/atc/check-enrollment', []);

        // Validation should fail due to missing required fields
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'referenceId',
            'fingerprintSessionId',
            'merchantReferenceNumber',
            'amount',
            'card_number',
            'expiration_month',
            'expiration_year',
        ]);
    }

    public function test_process_payment_validation(): void
    {
        $response = $this->postJson('/api/v1/atc/process-payment', []);

        // Validation should fail due to missing required fields
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'merchantReferenceNumber',
            'amount',
            'card_number',
            'expiration_month',
            'expiration_year',
        ]);
    }
}
