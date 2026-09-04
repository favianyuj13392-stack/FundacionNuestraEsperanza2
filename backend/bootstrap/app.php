<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Fix for cURL SSL certificate verification on Windows/Laragon environments
// This ensures that HTTP client requests have a valid CA bundle configured
// even if Laragon's PHP installation doesn't have it properly set up.
// This is critical for making HTTPS requests to external APIs (like BNB QR service).
if (!getenv('CURL_CA_BUNDLE') && !ini_get('curl.cainfo')) {
    // Priority order for CA certificate detection
    $possibleCaCerts = [
        dirname(__DIR__) . '/storage/ca-bundle.crt',      // Downloaded Mozilla CA bundle (RECOMMENDED)
        'C:\\laragon\\etc\\ssl\\cacert.pem',               // Laragon default location
        dirname(__DIR__) . '/storage/app/cacert.pem',      // Alternative storage location
        ini_get('openssl.cafile') ?: '',                   // OpenSSL's configured CA file
        getenv('PATH_TO_CACERT') ?: '',                    // Custom env variable
    ];
    
    foreach ($possibleCaCerts as $cert) {
        if ($cert && @file_exists($cert) && @filesize($cert) > 0) {
            @putenv("CURL_CA_BUNDLE=$cert");
            @ini_set('curl.cainfo', $cert);
            
            // Log the successful configuration (helpful for debugging)
            if (function_exists('error_log')) {
                @error_log("[SSL] Configured cURL CA bundle: $cert");
            }
            break;
        }
    }
}

return Application::configure(basePath: dirname(__DIR__))
    /**
     * Paso 1: Definir los archivos de rutas.
     * Es crucial que la línea 'api' esté aquí para que Laravel cargue
     * nuestras rutas de API desde el archivo routes/api.php.
     * La versión de la otra IA omitía esto.
     */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    /**
     * Paso 2: Configurar los Middlewares.
     * Aquí le decimos a Laravel cómo proteger nuestras rutas.
     */
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * Confiar en el proxy inverso (Traefik) para que Laravel genere
         * URLs HTTPS correctas y valide firmas temporales de Livewire.
         */
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );

        /**
         * Le decimos a Laravel que use el guardián de Sanctum para
         * proteger TODAS las rutas definidas en routes/api.php.
         */
        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        /**
         * Aquí registramos los "apodos" para nuestros middlewares personalizados,
         * permitiéndonos usarlos fácilmente en las rutas (ej: ->middleware('role:admin')).
         */
        $middleware->alias([
            //'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/api/auth/*',
            '/api/public/*',
            '/api/webhooks/*',
            '/api/subscriptions/*',
            'api/v1/subscriptions/*',
            '/api/subscribe',
            '/api/contact',
            '/api/track-visit',
            'api/webhooks/bnb', 
            'api/webhooks/*',
            'api/v1/atc/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Configuración de manejo de excepciones.
    })->create();
