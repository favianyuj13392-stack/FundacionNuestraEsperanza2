<?php

namespace App\Services\ATC;

class CybersourceSignature
{
    /**
     * Generate Cybersource Digest header for request body.
     * Digest: SHA-256=base64(sha256(payload))
     */
    public static function generateDigest(string $payload): string
    {
        $hash = hash('sha256', $payload, true);
        return 'SHA-256=' . base64_encode($hash);
    }

    /**
     * Generate HTTP Signature header for Cybersource REST API.
     * Uses HMAC-SHA256 signature scheme required by Cybersource.
     */
    public static function generateSignatureHeaders(
        string $merchantId,
        string $keyId,
        string $secretKey,
        string $method,
        string $targetUrl,
        string $payload = '',
        string $dateStr = ''
    ): array {
        $dateStr = $dateStr ?: gmdate('D, d M Y H:i:s GMT');
        $parsedUrl = parse_url($targetUrl);
        $host = $parsedUrl['host'] ?? 'apitest.cybersource.com';
        $path = ($parsedUrl['path'] ?? '/') . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
        
        $methodLower = strtolower($method);

        if ($methodLower === 'post' || $methodLower === 'put' || $methodLower === 'patch') {
            $digest = self::generateDigest($payload);
            $signedHeaders = '(request-target) host date digest v-c-merchant-id';
            $signatureString = "(request-target): {$methodLower} {$path}\n" .
                "host: {$host}\n" .
                "date: {$dateStr}\n" .
                "digest: {$digest}\n" .
                "v-c-merchant-id: {$merchantId}";
        } else {
            $digest = null;
            $signedHeaders = '(request-target) host date v-c-merchant-id';
            $signatureString = "(request-target): {$methodLower} {$path}\n" .
                "host: {$host}\n" .
                "date: {$dateStr}\n" .
                "v-c-merchant-id: {$merchantId}";
        }

        // Decode base64 secret key
        $secretKeyDecoded = base64_decode($secretKey);
        $signatureHash = hash_hmac('sha256', $signatureString, $secretKeyDecoded, true);
        $signatureBase64 = base64_encode($signatureHash);

        $signatureHeader = sprintf(
            'keyid="%s", algorithm="HmacSHA256", headers="%s", signature="%s"',
            $keyId,
            $signedHeaders,
            $signatureBase64
        );

        $headers = [
            'v-c-merchant-id' => $merchantId,
            'Date' => $dateStr,
            'Signature' => $signatureHeader,
            'Host' => $host,
            'Content-Type' => 'application/json',
        ];

        if ($digest) {
            $headers['Digest'] = $digest;
        }

        return $headers;
    }
}
