<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Services\CertificadoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerarCertificadoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Donation $donation)
    {
        //
    }

    public function handle(CertificadoService $certificadoService): void
    {
        try {
            $certificate = $certificadoService->generarCertificado($this->donation);

            if ($certificate) {
                Log::info("Certificate {$certificate->folio} generated for Donation ID: {$this->donation->id}");
            }
        } catch (\Exception $e) {
            Log::error("Job failed for Donation ID: {$this->donation->id}. Message: {$e->getMessage()}");
            
            throw $e; 
        }
    }
}