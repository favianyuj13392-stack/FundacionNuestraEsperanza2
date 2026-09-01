<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\AdminDonationController;
use App\Http\Controllers\PublicDonationController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\Program;
use App\Models\News;
use App\Models\Testimonial;
use App\Models\ContactMessage;
use App\Models\Subscriber;
use App\Models\Setting;
use App\Models\NavLink;
use App\Models\Stat;
use App\Models\HomeSection;
use App\Models\AboutUsSection;
use App\Models\ProgramsSection;
use App\Models\TestimonialsSection;
use App\Models\NewsSection;
use App\Models\HowToHelpSection;
use App\Models\Alliance;
use App\Models\Advertisement;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
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
    // --- Gestión de Roles ---
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('users/{userId}/roles/assign', [RoleController::class, 'assign']);
    Route::post('users/{userId}/roles/revoke', [RoleController::class, 'revoke']);

    // --- Gestión de Donaciones y QR ---
    Route::get('admin/donation-tiers', [AdminDonationController::class, 'indexTiers']);
    Route::post('admin/donation-tiers', [AdminDonationController::class, 'storeTier']);
    Route::put('admin/donation-tiers/{tier}', [AdminDonationController::class, 'updateTier']);
    Route::delete('admin/donation-tiers/{tier}', [AdminDonationController::class, 'destroyTier']);
    Route::post('admin/campaign-qr', [AdminDonationController::class, 'generateCampaignQr']);
});


/*
|--------------------------------------------------------------------------
| Public Content Endpoints — CMS Dinámico (Fix)
|--------------------------------------------------------------------------
*/

// 1. ENDPOINT DE PROGRAMAS
Route::get('/programs', function () {
    return Program::where('is_active', true)->latest()->get()->map(function ($program) {
        return [
            'id'          => $program->id,
            'title'       => $program->title,
            'description' => $program->description,
            'image'       => $program->image_url,
            'color'       => $program->color,
        ];
    });
});

// 2. ENDPOINT DE NOTICIAS
Route::get('/news', function () {
    return News::latest('publication_date')->get()->map(function ($news) {
        return [
            'id'      => $news->id,
            'title'   => $news->title,
            'content' => $news->content,
            'image'   => $news->image_url,
            'date'    => $news->publication_date ? $news->publication_date->format('d/m/Y') : null,
        ];
    });
});

// 3. ENDPOINT DE TESTIMONIOS
Route::get('/testimonials', function () {
    return Testimonial::latest()->get()->map(function ($testimonial) {
        return [
            'id'           => $testimonial->id,
            'name'         => $testimonial->name,
            'content'      => $testimonial->content,
            'type'         => $testimonial->type ?? 'image',
            'embedUrl'     => $testimonial->embed_url ?? null,
            'externalLink' => $testimonial->external_link ?? '#',
            'age'          => $testimonial->age ?? '',
            'image'        => $testimonial->image_url,
        ];
    });
});

// 4. ENDPOINT PARA CONTACTO
Route::post('/contact', function (Request $request) {
    $validated = $request->validate([
        'name'      => 'required|string',
        'last_name' => 'nullable|string',
        'email'     => 'required|email',
        'message'   => 'required|string',
    ]);

    ContactMessage::create($validated);

    return response()->json(['message' => 'Mensaje enviado con éxito'], 201);
})->middleware('throttle:10,1');

// 4.b ENDPOINT PARA REGISTRAR VISITAS
Route::post('/track-visit', [AnalyticsController::class, 'trackVisit'])->middleware('throttle:60,1');

// 5. ENDPOINT PARA SUSCRIPCIÓN
Route::post('/subscribe', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email|unique:subscribers,email',
    ]);

    Subscriber::create($validated);

    return response()->json(['message' => 'Suscripción exitosa'], 201);
})->middleware('throttle:10,1');

// 6. ENDPOINT PARA SETTINGS
Route::get('/settings', function () {
    return Setting::all()->mapWithKeys(function ($setting) {
        $value = $setting->value;

        if ($setting->type === 'image' && filled($value)) {
            $value = Storage::disk('cloudinary')->url($value);
        }

        return [$setting->key => $value];
    });
});

// 7. ENDPOINT PARA LINKS MENÚ
Route::get('/nav-links', function () {
    return NavLink::orderBy('order', 'asc')->get();
});

// 8. ENDPOINT PARA STATS
Route::get('/stats', function () {
    return response()->json(Stat::all());
});

// 9. ENDPOINT PARA HOME SECTIONS
Route::get('/home-sections', function () {
    return HomeSection::where('is_active', true)
        ->orderBy('order', 'asc')
        ->get()
        ->map(function ($section) {
            return [
                'id'               => $section->id,
                'identifier'       => $section->identifier,
                'name'             => $section->name,
                'title'            => $section->title,
                'subtitle'         => $section->subtitle,
                'content'          => $section->content,
                'image'            => $section->image_url,
                'is_active'        => $section->is_active,
                'order'            => $section->order,
                'meta_title'       => $section->meta_title,
                'meta_description' => $section->meta_description,
                'meta_keywords'    => $section->meta_keywords,
            ];
        });
});

Route::get('/section-statuses', function () {
    $homeSectionStatuses = HomeSection::select('identifier', 'is_active')->get()
        ->map(fn ($section) => [
            'identifier' => $section->identifier,
            'is_active'  => $section->is_active,
        ]);

    $sectionModels = [
        AboutUsSection::class,
        ProgramsSection::class,
        TestimonialsSection::class,
        NewsSection::class,
        HowToHelpSection::class,
    ];

    $pageSectionStatuses = collect($sectionModels)
        ->flatMap(fn ($model) => $model::select('identifier', 'is_active')->get())
        ->map(fn ($section) => [
            'identifier' => $section->identifier,
            'is_active'  => $section->is_active,
        ]);

    return $homeSectionStatuses
        ->concat($pageSectionStatuses)
        ->keyBy('identifier')
        ->values();
});

Route::get('/how-to-help-sections', function () {
    return HowToHelpSection::where('is_active', true)
        ->orderBy('order', 'asc')
        ->get()
        ->map(function ($section) {
            return [
                'id'               => $section->id,
                'identifier'       => $section->identifier,
                'name'             => $section->name,
                'title'            => $section->title,
                'subtitle'         => $section->subtitle,
                'content'          => $section->content,
                'image'            => $section->image_url,
                'is_active'        => $section->is_active,
                'order'            => $section->order,
                'meta_title'       => $section->meta_title,
                'meta_description' => $section->meta_description,
                'meta_keywords'    => $section->meta_keywords,
            ];
        });
});

Route::get('/about-us-sections', function () {
    return AboutUsSection::where('is_active', true)
        ->orderBy('order', 'asc')
        ->get()
        ->map(function ($section) {
            return [
                'id'               => $section->id,
                'identifier'       => $section->identifier,
                'name'             => $section->name,
                'title'            => $section->title,
                'subtitle'         => $section->subtitle,
                'content'          => $section->content,
                'image'            => $section->image_url,
                'is_active'        => $section->is_active,
                'order'            => $section->order,
                'meta_title'       => $section->meta_title,
                'meta_description' => $section->meta_description,
                'meta_keywords'    => $section->meta_keywords,
            ];
        });
});

Route::get('/news-sections', function () {
    return NewsSection::where('is_active', true)
        ->orderBy('order', 'asc')
        ->get()
        ->map(function ($section) {
            return [
                'id'               => $section->id,
                'identifier'       => $section->identifier,
                'name'             => $section->name,
                'title'            => $section->title,
                'subtitle'         => $section->subtitle,
                'content'          => $section->content,
                'image'            => $section->image_url,
                'is_active'        => $section->is_active,
                'order'            => $section->order,
                'meta_title'       => $section->meta_title,
                'meta_description' => $section->meta_description,
                'meta_keywords'    => $section->meta_keywords,
            ];
        });
});

Route::get('/programs-sections', function () {
    return ProgramsSection::where('is_active', true)
        ->orderBy('order', 'asc')
        ->get()
        ->map(function ($section) {
            return [
                'id'               => $section->id,
                'identifier'       => $section->identifier,
                'name'             => $section->name,
                'title'            => $section->title,
                'subtitle'         => $section->subtitle,
                'content'          => $section->content,
                'image'            => $section->image_url,
                'is_active'        => $section->is_active,
                'order'            => $section->order,
                'meta_title'       => $section->meta_title,
                'meta_description' => $section->meta_description,
                'meta_keywords'    => $section->meta_keywords,
            ];
        });
});

Route::get('/testimonials-sections', function () {
    return TestimonialsSection::where('is_active', true)
        ->orderBy('order', 'asc')
        ->get()
        ->map(function ($section) {
            return [
                'id'               => $section->id,
                'identifier'       => $section->identifier,
                'name'             => $section->name,
                'title'            => $section->title,
                'subtitle'         => $section->subtitle,
                'content'          => $section->content,
                'image'            => $section->image_url,
                'is_active'        => $section->is_active,
                'order'            => $section->order,
                'meta_title'       => $section->meta_title,
                'meta_description' => $section->meta_description,
                'meta_keywords'    => $section->meta_keywords,
            ];
        });
});

Route::get('/sections/{identifier}', function (string $identifier) {
    $sectionMap = [
        'about_us'     => AboutUsSection::class,
        'programs'     => ProgramsSection::class,
        'testimonials' => TestimonialsSection::class,
        'news'         => NewsSection::class,
        'how_to_help'  => HowToHelpSection::class,
    ];

    if (! array_key_exists($identifier, $sectionMap)) {
        return response()->json(['message' => 'Section not found.'], 404);
    }

    $model   = $sectionMap[$identifier];
    $section = $model::where('identifier', $identifier)->first();

    if (! $section) {
        return response()->json(['message' => 'Section not found.'], 404);
    }

    return [
        'id'               => $section->id,
        'identifier'       => $section->identifier,
        'name'             => $section->name,
        'title'            => $section->title,
        'subtitle'         => $section->subtitle,
        'content'          => $section->content,
        'image'            => $section->image_url,
        'is_active'        => $section->is_active,
        'order'            => $section->order,
        'meta_title'       => $section->meta_title,
        'meta_description' => $section->meta_description,
        'meta_keywords'    => $section->meta_keywords,
    ];
});

// 10. ENDPOINT PARA ALLIANCES
Route::get('/alliances', function () {
    return Alliance::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->get()
        ->map(function ($alliance) {
            return [
                'id'       => $alliance->id,
                'name'     => $alliance->name,
                'url'      => $alliance->url,
                'logo_url' => $alliance->logo_url,
            ];
        });
});

// 11. ENDPOINT ADVERTISEMENTS
Route::get('/advertisements', function () {
    $now = Carbon::now();
    return Advertisement::where('is_active', true)
        ->where(function ($query) use ($now) {
            $query->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $now);
        })
        ->where(function ($query) use ($now) {
            $query->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $now);
        })
        ->get();
});

// 12. ENDPOINT PARA ABOUT US
Route::get('/about-us', function () {
    return AboutUsSection::where('is_active', true)
        ->orderBy('order', 'asc')
        ->get()
        ->map(function ($section) {
            return [
                'id'               => $section->id,
                'identifier'       => $section->identifier,
                'name'             => $section->name,
                'title'            => $section->title,
                'subtitle'         => $section->subtitle,
                'content'          => $section->content,
                'image'            => $section->image_url,
                'is_active'        => $section->is_active,
                'order'            => $section->order,
                'meta_title'       => $section->meta_title,
                'meta_description' => $section->meta_description,
                'meta_keywords'    => $section->meta_keywords,
            ];
        });
});


/*
|--------------------------------------------------------------------------
| Public Donation Routes (main)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->middleware('throttle:30,1')->group(function () {
    Route::get('campaigns', [\App\Http\Controllers\PublicCampaignController::class, 'index']);
    Route::get('donation-options', [PublicDonationController::class, 'getOptions']);
    Route::post('request-qr', [PublicDonationController::class, 'requestQr']);
    Route::get('check-status/{qrId}', [PublicDonationController::class, 'checkStatus']);
});

/*
|--------------------------------------------------------------------------
| Subscriptions API (v1)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/subscriptions')->group(function () {
    Route::get('validate-reactivation/{token}', [\App\Http\Controllers\Api\SubscriptionController::class, 'validateReactivation']);
});

/*
|--------------------------------------------------------------------------
| Domiciliación / Suscripción Recurrente (Público, sin auth requerida)
| El donante no necesita estar registrado para suscribirse.
|--------------------------------------------------------------------------
*/
Route::prefix('subscriptions')->middleware('throttle:30,1')->group(function () {
    // Crear suscripción + generar QR de domiciliación
    Route::post('domiciliacion', [\App\Http\Controllers\BnbSubscriptionController::class, 'store']);
    // Consultar estado de una suscripción (para polling desde el frontend)
    Route::get('domiciliacion/{id}/status', [\App\Http\Controllers\BnbSubscriptionController::class, 'status']);
});


/*
|--------------------------------------------------------------------------
| Transparencia (main)
|--------------------------------------------------------------------------
*/
Route::get('/transparency', [\App\Http\Controllers\Api\TransparencyController::class, 'index']);
Route::get('/transparency/{slug}', [\App\Http\Controllers\Api\TransparencyController::class, 'show']);


/*
|--------------------------------------------------------------------------
| Internal Debug Endpoints (DO NOT expose in production)
|--------------------------------------------------------------------------
*/
Route::post('internal/debug/bnb-auth', function (Request $request) {
    $service   = app(\App\Services\BnbDonationService::class);
    $overrides = [];
    if ($request->has('accountId'))     $overrides['accountId']     = $request->input('accountId');
    if ($request->has('authorizationId')) $overrides['authorizationId'] = $request->input('authorizationId');

    $envAccount = env('BNB_ACCOUNT_ID');
    $envAuth    = env('BNB_AUTH_ID');

    $result = $service->debugAuthenticate($overrides);
    return response()->json(array_merge(['env' => ['account' => $envAccount, 'auth' => $envAuth]], $result));
});

/**
 * Test endpoint to see if QR service can authenticate and generate a QR
 */
Route::post('internal/debug/test-qr', function (Request $request) {
    try {
        $service = app(\App\Services\BnbDonationService::class);
        $amount  = $request->input('amount', 10);

        \Illuminate\Support\Facades\Log::info('Test QR: Starting', ['amount' => $amount]);

        $result = $service->generateFixedQR($amount);

        \Illuminate\Support\Facades\Log::info('Test QR: Generated', ['result_keys' => array_keys((array) $result)]);

        return response()->json([
            'success' => true,
            'result'  => $result,
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Test QR Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
        ], 500);
    }
});


/*
|--------------------------------------------------------------------------
| Webhooks BNB
|--------------------------------------------------------------------------
*/
// Webhook simple QR
Route::post('webhooks/bnb', [\App\Http\Controllers\BnbWebhookController::class, 'handle']);

// Webhooks de Domiciliación (Débito Automático)
// ⚠️ Sin middleware de autenticación: el BNB llama a estos endpoints directamente.
// ⚠️ Sin ?secret=: el BNB no soporta query params en URLs de webhook.
Route::prefix('webhooks/bnb')->middleware('throttle:60,1')->group(function () {
    Route::post('enroll',  [\App\Http\Controllers\BnbDomiciliacionWebhookController::class, 'enroll']);
    Route::post('payment', [\App\Http\Controllers\BnbDomiciliacionWebhookController::class, 'payment']);
});

/*
|--------------------------------------------------------------------------
| ATC Cybersource 3DS2 Payment Routes (Isolated Module)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/atc')->middleware('throttle:30,1')->group(function () {
    Route::post('setup-authentication', [\App\Http\Controllers\Api\ATC\AtcPaymentController::class, 'setupAuthentication']);
    Route::post('check-enrollment', [\App\Http\Controllers\Api\ATC\AtcPaymentController::class, 'checkEnrollment']);
    Route::post('validate-challenge', [\App\Http\Controllers\Api\ATC\AtcPaymentController::class, 'validateChallenge']);
    Route::post('process-payment', [\App\Http\Controllers\Api\ATC\AtcPaymentController::class, 'processPayment']);
    Route::post('stepup-return', [\App\Http\Controllers\Api\ATC\AtcPaymentController::class, 'stepUpReturn']);
    Route::get('stepup-return', [\App\Http\Controllers\Api\ATC\AtcPaymentController::class, 'stepUpReturn']);
});

Route::get('v1/subscriptions/validate-reactivation/{token}', [\App\Http\Controllers\Api\SubscriptionController::class, 'validateReactivation']);
Route::post('v1/subscriptions/confirm-reactivation/{token}', [\App\Http\Controllers\Api\SubscriptionController::class, 'confirmReactivation']);
