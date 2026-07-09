<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(\App\Services\BnbDonationService::class);
    echo "Autenticando con BNB...\n";
    $token = $service->authenticate();
    echo "Token obtenido.\n";
    echo "Solicitando QR...\n";
    $qr = $service->generateFixedQR(100, "Donacion Prueba", uniqid('test_', true));
    if ($qr && isset($qr['qrId'])) {
        echo "Exito! QR ID: " . $qr['qrId'] . "\n";
    } else {
        echo "Error: Respuesta inválida.\n";
        var_dump($qr);
    }
} catch (\Exception $e) {
    echo "Excepción: " . $e->getMessage() . "\n";
}
