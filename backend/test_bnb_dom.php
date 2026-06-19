<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = config('services.bnb.dom_auth_url') . '/auth/Token';

$combinations = [
    ['accountId' => base64_encode('FUNDESPDOM'), 'authorizationId' => base64_encode('1234abcd'), 'label' => 'B64 Usuario / B64 Contraseña'],
    ['accountId' => base64_encode('15864'), 'authorizationId' => base64_encode('1234abcd'), 'label' => 'B64 ID / B64 Contraseña'],
];

echo "URL: $url\n";

foreach ($combinations as $combo) {
    echo "Testing: {$combo['label']} -> accountId: {$combo['accountId']}, authId: {$combo['authorizationId']}\n";
    $response = \Illuminate\Support\Facades\Http::post($url, [
        'accountId' => $combo['accountId'],
        'authorizationId' => $combo['authorizationId']
    ]);
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n\n";
}
