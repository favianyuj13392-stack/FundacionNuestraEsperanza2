<?php

namespace App\Services\ATC;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AtcHttpClient
{
    protected string $baseUrl;
    protected string $merchantId;
    protected string $keyId;
    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.atc.base_url', 'https://apitest.cybersource.com');
        $this->merchantId = config('services.atc.merchant_id', 'redenlace_000021');
        $this->keyId = config('services.atc.key_id', '3ada8327-76bd-4ed9-9952-0e8288f6e212');
        $this->secretKey = config('services.atc.secret_key', '/zFZFhYflXW/P3BMzkULTcIuJhdcXCVD9SKJEo+fJXo=');
    }

    /**
     * Send signed POST request to Cybersource REST API.
     */
    public function post(string $endpoint, array $payload): array
    {
        $targetUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

        $headers = CybersourceSignature::generateSignatureHeaders(
            $this->merchantId,
            $this->keyId,
            $this->secretKey,
            'POST',
            $targetUrl,
            $jsonPayload
        );

        $response = Http::timeout(30)
            ->withHeaders($headers)
            ->withBody($jsonPayload, 'application/json')
            ->post($targetUrl);

        if (!$response->successful()) {
            Log::error('Cybersource API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }

        return [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'data' => $response->json() ?? [],
        ];
    }

    /**
     * Send signed GET request to Cybersource REST API.
     */
    public function get(string $endpoint): array
    {
        $targetUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $headers = CybersourceSignature::generateSignatureHeaders(
            $this->merchantId,
            $this->keyId,
            $this->secretKey,
            'GET',
            $targetUrl
        );

        $response = Http::withHeaders($headers)->get($targetUrl);

        return [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'data' => $response->json() ?? [],
        ];
    }
}
