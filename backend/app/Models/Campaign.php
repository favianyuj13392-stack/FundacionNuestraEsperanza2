<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    protected $table = 'campaigns';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'currency_id',
        'monetary_goal',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'monetary_goal' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * A campaign can have many donations.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * A campaign belongs to a currency.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * A campaign can have many QRs.
     */
    public function qrs(): HasMany
    {
        return $this->hasMany(Qr::class);
    }
}
