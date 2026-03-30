<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController;

Route::get('/certificates/{uuid}', [CertificateController::class, 'download'])
     ->name('certificates.download');
Route::get('/', function () {
    return view('welcome');
});
