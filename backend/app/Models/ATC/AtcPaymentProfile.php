<?php

namespace App\Models\ATC;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class AtcPaymentProfile extends Model
{
    protected $table = 'atc_payment_profiles';

    protected $fillable = [
        'user_id',
        'customer_token',
        'payment_instrument_token',
        'card_type',
        'card_last4',
        'card_expiration_month',
        'card_expiration_year',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(AtcSubscription::class, 'payment_profile_id');
    }
}
