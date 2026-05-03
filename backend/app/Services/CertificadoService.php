<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Certificate; 
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class CertificadoService
{
    public function generarCertificado(Donation $donation): ?Certificate
    {
        // 1. Verificar monto mínimo
        $montoMinimo = config('services.donaciones.monto_minimo_certificado', 100);
        
        if ((float)$donation->amount < (float)$montoMinimo) {
            return null;
        }

        // 2. Verificar existencia
        if ($donation->certificate) {
            return $donation->certificate;
        }

        // 3. Preparar datos
        // 3. Preparar datos
        $certificadoUuid = Str::uuid()->toString();
        
        // --- 3a. Prepare Background Image ---
        $bgPath = resource_path('views/pdf/fondoCertificado.png');
        $bgBase64 = '';
        if (File::exists($bgPath)) {
            $bgData = File::get($bgPath);
            $bgBase64 = 'data:image/png;base64,' . base64_encode($bgData);
        }

        // --- 3b. Format Date (es) ---
        // Ensure locale is Spanish for this operation
        $date = $donation->date instanceof \Carbon\Carbon ? $donation->date : \Carbon\Carbon::parse($donation->date);
        $date->locale('es');
        $fechaTexto = $date->isoFormat('D [de] MMMM [de] YYYY'); // e.g. 19 de septiembre de 2025

        // --- 3c. Amount in Words ---
        $amount = $donation->amount;
        $formatter = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        $amountInWords = strtoupper($formatter->format($amount));
        
        $data = [
            'donante_nombre' => $donation->qr->donor_name ?? $donation->donor->full_name ?? 'Donante Anónimo',
            'donacion_monto' => number_format($donation->amount, 2),
            'donacion_monto_letras' => $amountInWords,
            'donacion_fecha_texto' => $fechaTexto,
            'certificado_uuid' => $certificadoUuid,
            'fondo_imagen' => $bgBase64,
        ];
        $filename = 'certificados/donacion-' . $certificadoUuid . '.pdf';
        $absolutePath = Storage::disk('public')->path($filename);

        // 4. GENERACIÓN DE PDF
        try {
            $pdf = Pdf::loadView('pdf.certificado', $data)
                      ->setPaper('a4', 'landscape'); 

            $pdfContent = $pdf->output();

            if (empty($pdfContent)) {
                throw new \Exception("DomPDF generó contenido vacío.");
            }
            
            Log::debug("DEBUG - Certificado Service: Guardando PDF.", [
                'filename' => $filename,
                'path' => $absolutePath
            ]);
            
            Storage::disk('public')->put($filename, $pdfContent);
            
            if (!Storage::disk('public')->exists($filename)) {
                throw new \Exception("El archivo PDF no se encontró después de guardar.");
            }

        } catch (\Exception $e) {
            Log::error(
                "Error de DOMPDF detectado en Service. Donation ID: {$donation->id}", 
                ['error' => $e->getMessage()]
            );
            throw $e;
        }

        // 7. Crear el registro en DB
        $certificate = Certificate::create([
            'donation_id' => $donation->id,
            'folio' => $certificadoUuid, 
            'pdf_url' => $filename, 
            'issued_on' => now(),
        ]);

        // 8. ASIGNAR PERMISOS
        if (File::exists($absolutePath)) {
            File::chmod($absolutePath, 0644);
        }

        return $certificate;
    }
}