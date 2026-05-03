<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$svc = app(App\Services\BnbDonationService::class);
$r = $svc->generateFixedQR(10, 'Prueba', uniqid('don_', true));
var_export($r);
