<?php

namespace App\Http\Controllers\Api\ATC;

use App\Http\Controllers\Controller;
use App\Services\ATC\Atc3dsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AtcPaymentController extends Controller
{
    protected Atc3dsService $atcService;

    public function __construct(Atc3dsService $atcService)
    {
        $this->atcService = $atcService;
    }

    /**
     * POST /api/v1/atc/setup-authentication (Paso 1)
     */
    public function setupAuthentication(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'card_number' => 'nullable|string|min:13|max:19',
            'expiration_month' => 'nullable|string|size:2',
            'expiration_year' => 'nullable|string|size:4',
        ]);

        $result = $this->atcService->setupAuthentication($validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /api/v1/atc/check-enrollment (Paso 3)
     */
    public function checkEnrollment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'referenceId' => 'required|string',
            'fingerprintSessionId' => 'required|string',
            'merchantReferenceNumber' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|in:BOB,USD',
            'card_number' => 'required|string|min:13|max:19',
            'expiration_month' => 'required|string|size:2',
            'expiration_year' => 'required|string|size:4',
            'cvv' => 'nullable|string|min:3|max:4',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'ci' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address1' => 'nullable|string|max:150',
            'locality' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|size:2',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'campaign_name' => 'nullable|string|max:100',
            'program_id' => 'nullable|integer|exists:programs,id',
            'is_recurring' => 'nullable|boolean',
            'returnUrl' => 'nullable|url',
        ]);

        $result = $this->atcService->checkEnrollment($validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /api/v1/atc/validate-challenge (Paso 5)
     */
    public function validateChallenge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'authenticationTransactionId' => 'required|string',
            'merchantReferenceNumber' => 'nullable|string',
        ]);

        $result = $this->atcService->validateChallenge($validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST /api/v1/atc/process-payment (Paso 6)
     */
    public function processPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'merchantReferenceNumber' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|in:BOB,USD',
            'card_number' => 'required|string|min:13|max:19',
            'expiration_month' => 'required|string|size:2',
            'expiration_year' => 'required|string|size:4',
            'cvv' => 'nullable|string|min:3|max:4',
            'card_type' => 'nullable|string|in:VISA,MASTERCARD,AMEX',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'ci' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address1' => 'nullable|string|max:150',
            'locality' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|size:2',
            'fingerprintSessionId' => 'nullable|string',
            'eci' => 'nullable|string',
            'cavv' => 'nullable|string',
            'ucafCollectionIndicator' => 'nullable|string',
            'ucafAuthenticationData' => 'nullable|string',
            'xid' => 'nullable|string',
            'threeDSServerTransactionId' => 'nullable|string',
            'specificationVersion' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'campaign_name' => 'nullable|string|max:100',
            'program_id' => 'nullable|integer|exists:programs,id',
        ]);

        // Attach IP, User Agent, and authenticated user for compliance logging
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $result = $this->atcService->processPayment($validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * POST/GET /api/v1/atc/stepup-return
     * Callback endpoint invoked by Cardinal Commerce ACS iframe upon completing Step-Up Challenge OTP.
     */
    public function stepUpReturn(Request $request)
    {
        $payload = $request->all();
        Log::info('[ATC StepUp Return Payload]:', $payload);

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Step-Up Complete</title></head><body>'
            . '<script>'
            . 'try { window.parent.postMessage({ type: "STEP_UP_COMPLETED", payload: ' . json_encode($payload) . ' }, "*"); } catch(e) {}'
            . '</script>'
            . '<p style="font-family:sans-serif;text-align:center;color:#4B5563;margin-top:20px;">Autenticación completada con éxito. Procesando...</p>'
            . '</body></html>';

        return response($html, 200)
            ->header('Content-Type', 'text/html')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Access-Control-Allow-Private-Network', 'true');
    }
}
