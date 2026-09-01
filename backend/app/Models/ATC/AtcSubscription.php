<?php

namespace App\Models\ATC;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Program;

class AtcSubscription extends Model
{
    protected $table = 'atc_subscriptions';

    protected $fillable = [
        'user_id',
        'payment_profile_id',
        'campaign_id',
        'program_id',
        'amount',
        'currency',
        'billing_day',
        'status',
        'next_billing_at',
        'last_billed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billing_day' => 'integer',
        'next_billing_at' => 'datetime',
        'last_billed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentProfile(): BelongsTo
    {
        return $this->belongsTo(AtcPaymentProfile::class, 'payment_profile_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AtcTransaction::class, 'subscription_id');
    }
}
