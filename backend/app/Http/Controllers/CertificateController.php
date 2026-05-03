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
     * @return StreamedResponse
     */
    public function download($uuid): StreamedResponse
    {
        $certificate = Certificate::where('folio', $uuid)->firstOrFail();

        if (!Storage::disk('public')->exists($certificate->pdf_url)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($certificate->pdf_url);
    }
}
