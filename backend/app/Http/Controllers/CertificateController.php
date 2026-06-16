<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    /**
     * Find a certificate by its UUID/Folio and return it as a download.
     *
     * @param string $uuid
     * @param \App\Services\CertificadoService $certificadoService
     * @return StreamedResponse
     */
    public function download($uuid, \App\Services\CertificadoService $certificadoService): StreamedResponse
    {
        $certificate = Certificate::where('folio', $uuid)->firstOrFail();

        // Normalizar la ruta del archivo relativa al disco public
        $filename = ltrim($certificate->pdf_url, '/');
        if (str_starts_with($filename, 'storage/')) {
            $filename = substr($filename, 8);
        }

        if (!Storage::disk('public')->exists($filename)) {
            // Intentar regenerar el archivo PDF al vuelo
            $regenerated = $certificadoService->regenerarCertificadoArchivo($certificate);
            if (!$regenerated) {
                abort(404, 'File not found and could not be regenerated.');
            }
            // Refrescar el nombre del archivo tras actualizar la DB
            $filename = ltrim($certificate->fresh()->pdf_url, '/');
            if (str_starts_with($filename, 'storage/')) {
                $filename = substr($filename, 8);
            }
        }

        return Storage::disk('public')->download($filename);
    }
}
