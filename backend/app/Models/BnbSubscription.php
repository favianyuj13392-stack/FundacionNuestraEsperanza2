<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BnbSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bnb_subscriptions';

    protected $fillable = [
        'bnb_client_id',
        'qr_id',               // ID único devuelto por el BNB
        'qr_image_base64',     // Imagen del QR en base64 (temporal, para mostrar al usuario)
        'mime_type',
        'qr_type',             // 1=Fijo, 2=Variable
        'currency_code',       // 1=BOB, 2=USD
        'amount',
        'service_code',
        'reference',
        'installments_quantity',
        'payment_frequency',   // 1=Diario 2=Semanal 3=Mensual 4=Trimestral 5=Semestral 6=Anual
        'due_date',
        'scheduled_date',
        'status',              // pending | enrolled | cancelled
        'notes',
    ];

    protected $casts = [
        'amount'                 => 'decimal:2',
        'currency_code'          => 'integer',
        'qr_type'                => 'integer',
        'installments_quantity'  => 'integer',
        'payment_frequency'      => 'integer',
        'due_date'               => 'date',
        'scheduled_date'         => 'datetime',
    ];

    // --- Constantes para legibilidad ---

    const STATUS_PENDING   = 'pending';
    const STATUS_ENROLLED  = 'enrolled';
    const STATUS_CANCELLED = 'cancelled';

    const QR_TYPE_FIXED    = 1;
    const QR_TYPE_VARIABLE = 2;

    const CURRENCY_BOB = 1;
    const CURRENCY_USD = 2;

    const FREQ_DAILY     = 1;
    const FREQ_WEEKLY    = 2;
    const FREQ_MONTHLY   = 3;
    const FREQ_QUARTERLY = 4;
    const FREQ_BIANNUAL  = 5;
    const FREQ_ANNUAL    = 6;

    // --- Relaciones ---

    /**
     * El cliente BNB al que pertenece esta suscripción.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(BnbClient::class, 'bnb_client_id');
    }

    // --- Helpers ---

    /**
     * Devuelve el payload que el BNB espera en GetQRFixedAmount / GetQRVariableAmount.
     */
    public function toBnbQrPayload(): array
    {
        $payload = [
            'currencyCode'          => $this->currency_code,
            'amount'                => (float) $this->amount,
            'reference'             => $this->reference,
            'serviceCode'           => $this->service_code,
            'dueDate'               => $this->due_date?->format('Y-m-d'),
            'installmentsQuantity'  => $this->installments_quantity,
            'scheduledDate'         => $this->scheduled_date?->format('Y-m-d H:i'),
            'paymentFrequency'      => $this->payment_frequency,
            'clientIdentifier'      => $this->client->identifier,
        ];

        return $payload;
    }

    /**
     * ¿Está activa esta suscripción?
     */
    public function isEnrolled(): bool
    {
        return $this->status === self::STATUS_ENROLLED;
    }

    /**
     * ¿Está pendiente de que el cliente escanee el QR?
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
