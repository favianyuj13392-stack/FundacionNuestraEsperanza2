<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'campaign_id',
        'amount',
        'currency',
        'status',
        'next_charge_date',
        'last_charge_date',
        'cancellation_reason',
        'cancelled_at',
        'reactivation_token',
        'reactivation_token_expires_at',
        'cybersource_payment_token',
        'failed_attempts_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'next_charge_date' => 'datetime',
            'last_charge_date' => 'datetime',
            'cancelled_at' => 'datetime',
            'reactivation_token_expires_at' => 'datetime',
            'failed_attempts_count' => 'integer',
        ];
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaign associated with the subscription.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
