<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <-- ¡Declaración USE crítica!
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificadoController extends Controller
{
    /**
     * Busca un certificado por su UUID/Folio y lo entrega como descarga.
     *
     * @param string $uuid
     * @return StreamedResponse
     */
    public function descargar($uuid): StreamedResponse
    {
        // CORREGIDO: Buscar por 'folio' en lugar de 'codigo_uuid'
        $certificado = Certificado::where('folio', $uuid)->firstOrFail();

        // CORREGIDO: Verificar usando 'pdf_url'
        if (!Storage::disk('public')->exists($certificado->pdf_url)) {
            abort(404, 'Archivo no encontrado.');
        }

        /**
         * El método download() SÍ existe en la instancia del disco.
         * Tu editor (Intelephense) puede marcarlo erróneamente como
         * "undefined method" porque analiza el Contrato (Filesystem)
         * y no la implementación concreta. El código es correcto.
         */
        // CORREGIDO: Descargar usando 'pdf_url'
        return Storage::disk('public')->download($certificado->pdf_url);
    }
}