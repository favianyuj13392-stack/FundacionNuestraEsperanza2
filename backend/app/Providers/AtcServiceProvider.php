<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use App\Services\ATC\CybersourceSignature;
use App\Services\ATC\AtcHttpClient;

class AtcServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AtcHttpClient::class, function ($app) {
            return new AtcHttpClient();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Define Http::macro('cybersource') as requested by IA FUNDACIÓN
        Http::macro('cybersource', function () {
            $merchantId = config('services.atc.merchant_id', 'redenlace_000021');
            $keyId = config('services.atc.key_id', '3ada8327-76bd-4ed9-9952-0e8288f6e212');
            $secretKey = config('services.atc.secret_key', '/zFZFhYflXW/P3BMzkULTcIuJhdcXCVD9SKJEo+fJXo=');
            $baseUrl = config('services.atc.base_url', 'https://apitest.cybersource.com');

            return Http::baseUrl($baseUrl)
                ->beforeSending(function ($request) use ($merchantId, $keyId, $secretKey) {
                    $method = $request->method();
                    $url = (string) $request->url();
                    $body = (string) $request->body();

                    $headers = CybersourceSignature::generateSignatureHeaders(
                        $merchantId,
                        $keyId,
                        $secretKey,
                        $method,
                        $url,
                        $body
                    );

                    foreach ($headers as $key => $value) {
                        $request->withHeaders([$key => $value]);
                    }
                });
        });
    }
}
