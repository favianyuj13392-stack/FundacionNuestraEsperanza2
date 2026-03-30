<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Donation extends Model
{
    protected $table = 'donations';

    protected $fillable = [
        'campaign_id',
        'donor_id',
        'qr_id',
        'currency_id',
        'amount',
        'status',
        'provider',
        'provider_payment_id',
        'provider_subscription_id',
        'is_recurring',
        'is_anonymous',
        'fee',
        'net_amount',
        'channel',
        'ip',
        'date', // If manually setting date, though it's timestamp in migration
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'datetime',
        'is_recurring' => 'boolean',
        'is_anonymous' => 'boolean',
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    /**
     * A donation belongs to a donor.
     */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }

    /**
     * A donation belongs to a campaign.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * A donation belongs to a currency.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * A donation belongs to a QR.
     */
    public function qr(): BelongsTo
    {
        return $this->belongsTo(Qr::class);
    }

    /**
     * A donation can have one certificate.
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    /**
     * Accessor to get formatted amount.
     */
    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: function () {
                $symbol = $this->currency ? $this->currency->symbol : '';
                return number_format($this->amount, 2, ',', '.') . ' ' . $symbol;
            }
        );
    }
}
