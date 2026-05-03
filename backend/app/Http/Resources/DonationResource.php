<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d H:i:s'), 
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount, // e.g., "250,00 Bs."
            'status' => $this->status,
            'provider' => $this->provider,
            'is_recurring' => (bool) $this->is_recurring,
            
            // Certificate transformation (if loaded)
            'certificate' => $this->whenLoaded('certificate', function () {
                if ($this->certificate) {
                    return [
                        'folio' => $this->certificate->folio,
                        'download_link' => route('certificates.download', ['uuid' => $this->certificate->folio]),
                        'issued_on' => $this->certificate->issued_on ? $this->certificate->issued_on->format('Y-m-d H:i:s') : null,
                    ];
                }
                return null;
            }),
            'certificate_url' => $this->whenLoaded('certificate', function () {
                return $this->certificate ? route('certificates.download', ['uuid' => $this->certificate->folio]) : null;
            }),

            // QR transformation
            'qr' => $this->whenLoaded('qr', function () {
                return $this->qr ? [
                    'code' => $this->qr->code ?? $this->qr->id, // Fallback if code is missing
                    'image' => $this->qr->image,
                ] : null;
            }),
        ];
    }
}
