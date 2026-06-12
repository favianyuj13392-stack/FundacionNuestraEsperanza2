<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BnbDonationService
{
    protected $baseUrlAuth;
    protected $baseUrlSimple;
    protected $baseUrlVariable;
    protected $accountId;
    protected $authId;
    protected $serviceCode;

    public function __construct()
    {
        // Base URIs from Config (fallback to test server)
        $this->baseUrlAuth = config('services.bnb.auth_url', 'http://test.bnb.com.bo/ClientAuthentication.API/api/v1');
        $this->baseUrlSimple = config('services.bnb.qr_url', 'http://test.bnb.com.bo/QRSimple.API/api/v1');
        $this->baseUrlVariable = config('services.bnb.dom_url', 'http://test.bnb.com.bo/DirectDebit/api');

        // Load values from config file.  When `config:cache` is used this
        // ensures we don't accidentally read stale or missing environment data.
        // We still trim to remove stray quotes/whitespace.
        $this->accountId = trim((string) config('bnb.account_id', ''));
        $this->authId = trim((string) config('bnb.authorization_id', ''));
        $this->serviceCode = trim((string) config('bnb.service_code', ''));
        
        // Setup SSL certificate verification for Guzzle/cURL
        $this->setupSslCertificate();
    }

    /**
     * Configure SSL certificate verification for Guzzle/cURL
     * Ensures that HTTPS requests use a valid CA bundle on Windows/Laragon environments
     */
    private function setupSslCertificate()
    {
        // Priority paths to check for CA bundle
        $certificatePaths = [
            base_path('storage/ca-bundle.crt'),                          // Recommended: our downloaded bundle
            getenv('CURL_CA_BUNDLE') ?: '',                              // Check environment variable
            'C:\\laragon\\etc\\ssl\\cacert.pem',                         // Laragon default
            base_path('storage/app/cacert.pem'),                         // Alternative location
        ];

        foreach ($certificatePaths as $certPath) {
            if ($certPath && @file_exists($certPath) && @filesize($certPath) > 0) {
                // Set environment variables that Guzzle will use
                putenv("CURL_CA_BUNDLE=$certPath");
                ini_set('curl.cainfo', $certPath);
                ini_set('openssl.cafile', $certPath);
                
                Log::debug('SSL Certificate configured', ['path' => $certPath]);
                return;
            }
        }

        Log::warning('No valid SSL certificate found, HTTPS requests may fail');
    }

    /**
     * Make an HTTP request with proper SSL certificate configuration
     * This helper ensures that both environment variables and ini settings
     * are properly set before Guzzle makes the request
     */
    private function makeHttpRequest($method, $url, $headers = [], $payload = null, $token = null)
    {
        // Ensure SSL is properly configured before each request
        $this->setupSslCertificate();

        try {
            // Build the request
            $request = Http::withHeaders($headers)
                ->timeout(30)
                ->connectTimeout(10);

            // Add token if provided
            if ($token) {
                $request = $request->withToken($token);
            }

            // Execute request
            if ($method === 'POST') {
                return $request->post($url, $payload);
            } elseif ($method === 'GET') {
                return $request->get($url);
            }

            return null;
        } catch (\Exception $e) {
            Log::error("HTTP Request Exception: $method $url", [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }

    /**
     * Authenticate with BNB to get the Bearer Token.
     * Caches the token for 50 minutes (assuming 1 hour expiry).
     */
    public function authenticate()
    {
        // Token valid for 50 minutes
        // Avoid caching null values: first check cache, otherwise attempt auth and only cache on success.
        $cached = Cache::get('bnb_token');
        if ($cached) {
            return $cached;
        }

        $url = "{$this->baseUrlAuth}/auth/token";

        // Use configured credentials from env via constructor
        $accountId = $this->accountId;
        $authId = $this->authId;

        // Log the attempt (masking sensitive lengths)
        Log::debug('BNB Auth Attempt', [
            'url' => $url,
            'accountId' => $accountId,
            'authIdLen' => $authId ? strlen($authId) : 0
        ]);

        try {
            // Build JSON payload manually to match Postman raw body (avoid json_encode quirks)
            $jsonPayload = '{"accountId":"' . $accountId . '","authorizationId":"' . $authId . '"}';

            Log::debug('BNB Raw Payload', ['payload' => $jsonPayload]);

            // Force Postman-like headers including User-Agent to mimic Postman exactly
            $forcedHeaders = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'PostmanRuntime/7.32.0'
            ];

            // NOTE: the auth endpoint is plain HTTP; SSL verification not needed here.
            // Add timeout to avoid indefinite blocking; 10 seconds is reasonable for BNB authentication.
            $response = Http::withHeaders($forcedHeaders)
                ->withBody($jsonPayload, 'application/json')
                ->timeout(10)
                ->post($url);

            // Log response metadata for diagnosis
            try {
                Log::debug('BNB Response Meta', [
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                ]);
            } catch (\Exception $_) {
                // ignore header logging errors
            }

            if ($response->successful() && $response->json('success')) {
                $token = $response->json('message');
                // Cache only when we obtained a valid token
                Cache::put('bnb_token', $token, 3000);
                Log::info('BNB Auth Success', ['token_preview' => substr($token, 0, 10) . '...']);
                return $token;
            }

            Log::error('BNB Auth Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('BNB Auth Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate a unique gloss for the transaction.
     * Format: FNE-D-{timestamp}-{random}
     */
    private function generateUniqueGloss()
    {
        return 'FNE-D-' . now()->timestamp . '-' . rand(1000, 9999);
    }

    /**
     * Calculate expiration date based on minutes.
     * Returns date string Y-m-d (as per original code, though spec usually allows time).
     * For now keeping Y-m-d + 1 day as per original implementation preference.
     */
    private function calculateExpirationDate()
    {
        return now()->addDays(1)->format('Y-m-d');
    }

    /**
     * Generate a Fixed Amount QR.
     * Endpoint: /main/getQRWithImageAsync
     */
    public function generateFixedQR($amount, $customerGloss = null, $internalId = null)
    {
        // 1. Prepare Data
        $gloss = $this->generateUniqueGloss();
        // If the user provided a gloss (like "Donacion Campana X"), we can append it or store it in additionalData.
        // For strict BNB requirement "Gloss", we use our unique ID to ensure tracking, 
        // or we append the customer text if short enough.
        $sendingGloss = $gloss . ($customerGloss ? " $customerGloss" : "");
        $sendingGloss = substr($sendingGloss, 0, 100); // Ensure limit

        $expirationDate = $this->calculateExpirationDate();
        
        // Use internalId for tracking, or generate one if not provided
        $trackingId = $internalId ?? uniqid('don_', true);

        // --- MOCK MODE CHECK ---
        // Use strict comparison to ensure we have actual boolean true (not string 'true')
        if (config('bnb.mock_mode') === true) {
            Log::info('BNB Mock Mode: Generating fake QR', ['amount' => $amount]);
            
            // Dummy Base64 QR Image (Generic QR Placeholder)
            $dummyQr = 'iVBORw0KGgoAAAANSUhEUgAAAJQAAACUCAYAAAB1PADUAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QA/4ePzL8AAAAJcEhZcwAAEnQAABJ0Ad5mH3gAAAAHdElNRQfmDAkRMh4f6i/IAAABxklEQVR42u3dQYrkMBBA0f9/6d67b2BEEUTYf907RERGKvqYyR/5+P1+/4fx/X6/4w/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4X/wX/gv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+C/8F/4L/4X/wn/hv/Bf+G88n88fAwD//wMAgJ+40gAAAABJRU5ErkJggg==';
            $mockId = 'mock_' . uniqid();

            // Return structure matching DB needs
            return [
                'success' => true,
                'qr' => $dummyQr,
                'qrId' => $mockId,
                'expirationDate' => $expirationDate,
                'gloss' => $gloss, // Return the generated gloss to controller
                'mock' => true
            ];
        }

        $token = $this->authenticate();

        if (!$token) {
            throw new \Exception("Failed to authenticate with BNB.");
        }

        $url = "{$this->baseUrlSimple}/main/getQRWithImageAsync";

        // === HARDCORE MODE: Manual JSON Construction ===
        // Build JSON string manually to match BNB's strict parsing requirements
        // CORRECTED: Follow exact format from BNB PDF specification (NOT Azure endpoint format)
        $jsonPayload = '{'
            . '"currency":"BOB",'  // BNB uses "currency" with string "BOB", not numeric currencyCode
            . '"gloss":"' . addslashes($sendingGloss) . '",'  // BNB uses "gloss" for description
            . '"amount":' . (int)$amount . ','
            . '"singleUse":true,'  // Boolean literal
            . '"expirationDate":"' . $expirationDate . '",'
            . '"additionalData":"' . addslashes($trackingId) . '",'
            . '"destinationAccountId":"1"'  // 1 = cuenta nacional
            . '}';

        Log::debug('BNB QR Raw Payload', ['payload' => $jsonPayload]);

        // === HARDCORE MODE: Explicit Headers ===
        // Match EXACTLY what BNB PDF specifies
        $forcedHeaders = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'cache-control' => 'no-cache',  // BNB PDF specifies this
            'User-Agent' => 'PostmanRuntime/7.32.0',
            'Accept' => 'application/json'
        ];
        
        Log::debug('BNB QR Headers Prepared', [
            'Authorization' => 'Bearer ' . substr($token, 0, 15) . '...',
            'Content-Type' => $forcedHeaders['Content-Type'],
            'cache-control' => $forcedHeaders['cache-control'],
            'User-Agent' => $forcedHeaders['User-Agent']
        ]);

        try {
            // Ensure SSL certificate is configured before making HTTPS request
            $this->setupSslCertificate();

            Log::debug('BNB QR Request Starting', [
                'url' => $url, 
                'amount' => $amount,
                'token_preview' => substr($token, 0, 20) . '...',
                'gloss' => $sendingGloss,
                'auth_header' => 'Bearer ' . substr($token, 0, 20) . '...'
            ]);

            // === HARDCORE MODE: Raw Body POST ===
            // Use withBody() instead of passing array - ensures exact JSON format
            $response = Http::withHeaders($forcedHeaders)
                ->withBody($jsonPayload, 'application/json')
                ->timeout(30)
                ->connectTimeout(10)
                ->post($url);  // HTTP endpoint, no SSL verification needed

            Log::debug('BNB QR Response Received', [
                'status' => $response->status(),
                'content_type' => $response->header('content-type'),
                'body_length' => strlen($response->body()),
                'body_preview' => substr($response->body(), 0, 200)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Map BNB response fields to standard format
                // BNB PDF returns: id, qr, success, message
                // We normalize to: qrId, qr_image for consistency
                if (isset($data['id']) && !isset($data['qrId'])) {
                    $data['qrId'] = $data['id'];  // BNB uses 'id', we use 'qrId'
                }
                if (isset($data['qr']) && !isset($data['qr_image'])) {
                    $data['qr_image'] = $data['qr'];  // Alias for frontend compatibility
                }
                
                // Inject our generated gloss into the response so Controller can save it
                $data['gloss'] = $gloss; 
                $data['success'] = true;
                
                Log::info('BNB Fixed QR Success', [
                    'qrId' => $data['qrId'] ?? $data['id'] ?? 'unknown',
                    'amount' => $amount
                ]);
                return $data;
            }

            // CRITICAL: Handle 401 Unauthorized by refreshing token and retrying
            // Token can expire in BNB's system despite being fresh in our cache
            if ($response->status() === 401) {
                Log::warning('BNB QR Got 401 - Token likely expired, clearing cache and retrying', [
                    'gloss' => $sendingGloss,
                    'attempt' => 'retry_with_fresh_token'
                ]);
                
                // Clear cached token to force fresh authentication
                Cache::forget('bnb_token');
                
                // Retry: Get new token
                $newToken = $this->authenticate();
                if (!$newToken) {
                    Log::error('BNB QR Retry Auth Failed', ['gloss' => $sendingGloss]);
                    return null;
                }
                
                // Retry: Update headers with new token
                $forcedHeaders['Authorization'] = 'Bearer ' . $newToken;
                
                Log::debug('BNB QR Retry With Fresh Token', [
                    'url' => $url,
                    'amount' => $amount,
                    'token_preview' => substr($newToken, 0, 15) . '...'
                ]);
                
                // Retry the request
                $retryResponse = Http::withHeaders($forcedHeaders)
                    ->withBody($jsonPayload, 'application/json')
                    ->timeout(30)
                    ->connectTimeout(10)
                    ->post($url);
                
                Log::debug('BNB QR Retry Response', [
                    'status' => $retryResponse->status(),
                    'success' => $retryResponse->successful()
                ]);
                
                if ($retryResponse->successful()) {
                    $data = $retryResponse->json();
                    
                    // Map response fields again
                    if (isset($data['id']) && !isset($data['qrId'])) {
                        $data['qrId'] = $data['id'];
                    }
                    if (isset($data['qr']) && !isset($data['qr_image'])) {
                        $data['qr_image'] = $data['qr'];
                    }
                    
                    $data['gloss'] = $gloss;
                    $data['success'] = true;
                    
                    Log::info('BNB Fixed QR Success (Retry)', [
                        'qrId' => $data['qrId'] ?? 'unknown',
                        'amount' => $amount,
                        'retry' => true
                    ]);
                    return $data;
                } else {
                    Log::error('BNB Fixed QR Retry Still Failed', [
                        'retry_status' => $retryResponse->status(),
                        'gloss' => $sendingGloss
                    ]);
                    return null;
                }
            }

            Log::error('BNB Fixed QR Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'gloss' => $sendingGloss
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('BNB Fixed QR Exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'gloss' => $sendingGloss
            ]);
            return null;
        }
    }

    /**
     * Generate a Variable Amount QR.
     * Endpoint: /Services/GetQRVariableAmount
     */
    public function generateVariableQR($reference)
    {
        $token = $this->authenticate();

        if (!$token) {
            throw new \Exception("Failed to authenticate with BNB.");
        }

        $url = "{$this->baseUrlVariable}/Services/GetQRVariableAmount";

        $payload = [
            'currencyCode' => 1,
            'amount' => 0, // Initial amount 0 for variable
            'reference' => $reference,
            'serviceCode' => $this->serviceCode,
            'dueDate' => now()->addDays(1)->format('Y-m-d'),
            'installmentsQuantity' => 1,
            'chargeType' => 1,
            'chargeDate' => (int) now()->format('d'),
        ];

        try {
            // Variable QR endpoint is HTTP so no SSL issue, but timeout still applies
            $response = Http::withToken($token)
                ->timeout(30)
                ->connectTimeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('BNB Variable QR Failed', ['body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('BNB Variable QR Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check the status of a QR.
     * Endpoint: /main/getQRStatusAsync
     */
    public function checkStatus($qrId)
    {
        if (str_starts_with($qrId, 'mock_') || env('BNB_MOCK_MODE', false)) {
            Log::info("BNB Service: Mocking status check for {$qrId}");
            return [
                'statusId' => 2, // PAID
                'status' => 'Mock Paid',
                'qrId' => $qrId,
                'mock' => true
            ];
        }

        $token = $this->authenticate();
        if (!$token) return null;

        $url = "{$this->baseUrlSimple}/main/getQRStatusAsync";

        // === HARDCORE MODE: Manual JSON Construction ===
        $jsonPayload = '{"qrId":"' . addslashes($qrId) . '"}';

        // === HARDCORE MODE: Explicit Headers ===
        $forcedHeaders = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'cache-control' => 'no-cache',
            'User-Agent' => 'PostmanRuntime/7.32.0',
            'Accept' => 'application/json'
        ];

        try {
            $this->setupSslCertificate();

            $response = Http::withHeaders($forcedHeaders)
                ->withBody($jsonPayload, 'application/json')
                ->timeout(10)
                ->post($url);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('BNB Check Status Failed', [
                'qrId' => $qrId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('BNB Check Status Exception', ['qrId' => $qrId, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Debug helper: perform auth with forced headers and return request/response details.
     * Useful to compare what the server receives vs Postman.
     */
    public function debugAuthenticate(array $overrides = [])
    {
        $url = "{$this->baseUrlAuth}/auth/token";

        // allow overriding credentials for ad-hoc tests
        $accountId = $overrides['accountId'] ?? env('BNB_ACCOUNT_ID');
        $authId = $overrides['authorizationId'] ?? env('BNB_AUTH_ID');

        $payload = json_encode([
            'accountId' => $accountId,
            'authorizationId' => $authId
        ]);

        // Force headers to imitate Postman
        $forcedHeaders = [
            'User-Agent' => 'PostmanRuntime/7.32.0',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        // Send request
        try {
            $response = Http::withHeaders($forcedHeaders)
                ->withBody($payload, 'application/json')
                ->post($url);

            // Prepare readable headers (some headers may be objects)
            $respHeaders = [];
            try {
                foreach ($response->headers() as $k => $v) {
                    $respHeaders[$k] = is_array($v) ? implode(';', $v) : $v;
                }
            } catch (\Exception $_) {
                $respHeaders = $response->headers();
            }

            return [
                'request' => [
                    'url' => $url,
                    'headers' => $forcedHeaders,
                    'body_raw' => $payload,
                ],
                'response' => [
                    'status' => $response->status(),
                    'headers' => $respHeaders,
                    'body' => $response->body(),
                    'json' => $response->json(),
                ],
            ];

        } catch (\Exception $e) {
            return [
                'request' => [
                    'url' => $url,
                    'headers' => $forcedHeaders,
                    'body_raw' => $payload,
                ],
                'error' => $e->getMessage(),
            ];
        }
    }
}
