<?php

namespace Tests\Feature\ATC;

use Tests\TestCase;
use App\Services\ATC\CybersourceSignature;

class CybersourceSignatureTest extends TestCase
{
    public function test_digest_generation(): void
    {
        $payload = json_encode(['merchantReferenceCode' => 'TEST_12345']);
        $digest = CybersourceSignature::generateDigest($payload);

        $this->assertStringStartsWith('SHA-256=', $digest);
        $this->assertNotEmpty(substr($digest, 8));
    }

    public function test_signature_headers_generation(): void
    {
        $merchantId = 'redenlace_000021';
        $keyId = '3ada8327-76bd-4ed9-9952-0e8288f6e212';
        $secretKey = '/zFZFhYflXW/P3BMzkULTcIuJhdcXCVD9SKJEo+fJXo=';
        $targetUrl = 'https://apitest.cybersource.com/risk/v1/authentication-setups';
        $payload = json_encode(['test' => true]);

        $headers = CybersourceSignature::generateSignatureHeaders(
            $merchantId,
            $keyId,
            $secretKey,
            'POST',
            $targetUrl,
            $payload
        );

        $this->assertArrayHasKey('v-c-merchant-id', $headers);
        $this->assertArrayHasKey('Date', $headers);
        $this->assertArrayHasKey('Signature', $headers);
        $this->assertArrayHasKey('Digest', $headers);

        $this->assertEquals('redenlace_000021', $headers['v-c-merchant-id']);
        $this->assertStringContainsString('keyid="3ada8327-76bd-4ed9-9952-0e8288f6e212"', $headers['Signature']);
        $this->assertStringContainsString('algorithm="HmacSHA256"', $headers['Signature']);
    }
}
