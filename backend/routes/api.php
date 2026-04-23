<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\AdminDonationController;
use App\Http\Controllers\PublicDonationController; 
use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\News;
use App\Models\Testimonial;
use App\Models\ContactMessage;
use App\Models\Subscriber;
use App\Models\Setting;
use App\Models\NavLink;
use App\Models\Stat;
use App\Models\HomeSection;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('login', function () {
        return response()->json(['message' => 'Unauthenticated.'], 401);
    })->name('login');

    /*
    |--------------------------------------------------------------------------
    | User Routes (Protected by Auth)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('change-password', [AuthController::class, 'changePassword']);

        // Donor Panel Routes
        Route::get('donations/my', [DonationController::class, 'myDonations']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
    });
});


/*
|--------------------------------------------------------------------------
| Admin Routes (Protected by Role: Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // --- Gestión de Roles (Tu código) ---
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('users/{userId}/roles/assign', [RoleController::class, 'assign']);
    Route::post('users/{userId}/roles/revoke', [RoleController::class, 'revoke']);

    // --- Gestión de Donaciones y QR (Del Repositorio) ---
    // Nota: He usado la ruta completa de la clase como estaba en el repo, 
    // pero idealmente deberías importarlos arriba con "use".
    Route::get('admin/donation-tiers', [\App\Http\Controllers\AdminDonationController::class, 'indexTiers']);
    Route::post('admin/donation-tiers', [\App\Http\Controllers\AdminDonationController::class, 'storeTier']);
    Route::put('admin/donation-tiers/{tier}', [\App\Http\Controllers\AdminDonationController::class, 'updateTier']);
    Route::delete('admin/donation-tiers/{tier}', [\App\Http\Controllers\AdminDonationController::class, 'destroyTier']);
    Route::post('admin/campaign-qr', [\App\Http\Controllers\AdminDonationController::class, 'generateCampaignQr']);
});


/*
|--------------------------------------------------------------------------
| Public Content Endpoints (Tu código - CMS)
|--------------------------------------------------------------------------
*/

// 1. ENDPOINT DE PROGRAMAS
Route::get('/programs', function () {
    return Program::where('is_active', true)->latest()->get()->map(function ($program) {
        return [
            'id' => $program->id,
            'title' => $program->title,
            'description' => $program->description, 
            'image' => $program->image ? asset('storage/' . $program->image) : null,
            'color' => $program->color,
        ];
    });
});

// 2. ENDPOINT DE NOTICIAS
Route::get('/news', function () {
    return News::latest('publication_date')->get()->map(function ($news) {
        return [
            'id' => $news->id,
            'title' => $news->title,
            'content' => $news->content,
            'image' => $news->image ? asset('storage/' . $news->image) : null,
            'date' => $news->publication_date ? $news->publication_date->format('d/m/Y') : null,
        ];
    });
});

// 3. ENDPOINT DE TESTIMONIOS
Route::get('/testimonials', function () {
    return Testimonial::latest()->get()->map(function ($testimonial) {
        return [

            'id' => $testimonial->id,
            'name' => $testimonial->name,
            'content' => $testimonial->content, 
            'type' => $testimonial->type ?? 'image', 
            'embedUrl' => $testimonial->embed_url ?? null,
            'externalLink' => $testimonial->external_link ?? '#',
            'age' => $testimonial->age ?? '',
            'image' => $testimonial->image ? asset('storage/' . $testimonial->image) : null,
        ];
    });
});

// 4. ENDPOINT PARA CONTACTO
Route::post('/contact', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string',
        'last_name' => 'nullable|string',
        'email' => 'required|email',
        'message' => 'required|string',
    ]);

    ContactMessage::create($validated);

    return response()->json(['message' => 'Mensaje enviado con éxito'], 201);
});

// 5. ENDPOINT PARA SUSCRIPCIÓN
Route::post('/subscribe', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email|unique:subscribers,email',
    ]);

    Subscriber::create($validated);

    return response()->json(['message' => 'Suscripción exitosa'], 201);
});
// 6. ENDPOINT PARA SETTINGS
Route::get('/settings', function () {
    return Setting::all()->pluck('value', 'key');
});
// 7. ENDPOINT PARA LINKS MENÚ
Route::get('/nav-links', function () {
    return NavLink::orderBy('order', 'asc')->get();
});
// 8. ENDPOINT PARA STATS
Route::get('/stats', function () {
    return response()->json(\App\Models\Stat::all());
});
// 9. ENPOINT PARA HOME
Route::get('/home-sections', function () {
    return HomeSection::pluck('is_active', 'identifier');
});
/*
|--------------------------------------------------------------------------
| Public Donation Routes (Del Repositorio)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('donation-options', [\App\Http\Controllers\PublicDonationController::class, 'getOptions']);
    Route::post('request-qr', [\App\Http\Controllers\PublicDonationController::class, 'requestQr']);
    Route::get('check-status/{qrId}', [\App\Http\Controllers\PublicDonationController::class, 'checkStatus']);
});

/**
 * Internal debug endpoints (DO NOT expose in production).
 * /internal/debug/bnb-auth returns the exact headers, raw body and BNB response.
 */
Route::post('internal/debug/bnb-auth', function (\Illuminate\Http\Request $request) {
    $service = app(\App\Services\BnbDonationService::class);
    $overrides = [];
    if ($request->has('accountId')) $overrides['accountId'] = $request->input('accountId');
    if ($request->has('authorizationId')) $overrides['authorizationId'] = $request->input('authorizationId');

    // capture the environment value directly
    $envAccount = env('BNB_ACCOUNT_ID');
    $envAuth = env('BNB_AUTH_ID');

    $result = $service->debugAuthenticate($overrides);
    return response()->json(array_merge(['env' => ['account' => $envAccount, 'auth' => $envAuth]], $result));
});

/**
 * Test endpoint to see if QR service can authenticate and generate a QR
 */
Route::post('internal/debug/test-qr', function (\Illuminate\Http\Request $request) {
    try {
        $service = app(\App\Services\BnbDonationService::class);
        $amount = $request->input('amount', 10);
        
        \Illuminate\Support\Facades\Log::info('Test QR: Starting', ['amount' => $amount]);
        
        $result = $service->generateFixedQR($amount);
        
        \Illuminate\Support\Facades\Log::info('Test QR: Generated', ['result_keys' => array_keys((array)$result)]);
        
        return response()->json([
            'success' => true,
            'result' => $result
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Test QR Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Webhooks
|--------------------------------------------------------------------------
*/
Route::post('webhooks/bnb', [\App\Http\Controllers\BnbWebhookController::class, 'handle']);