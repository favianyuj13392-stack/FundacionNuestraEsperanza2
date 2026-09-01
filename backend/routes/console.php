<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programación del cobro recurrente automático de donaciones por tarjeta (ATC / Cybersource)
Schedule::command('atc:process-recurring-donations')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
