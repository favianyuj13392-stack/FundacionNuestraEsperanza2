<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
// bootstrap the framework so facades and config work
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$token = $app->make('App\\Services\\BnbDonationService')->authenticate();
echo "TOKEN: ".($token ?: 'null')."\n";
