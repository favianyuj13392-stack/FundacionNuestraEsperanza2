<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qr extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'donor_id',
        'code',
        'external_qr_id',
        'voucher_id',
        'gloss',
        'donor_name',
        'url',
        'amount',
        'status',
        'bnb_blob',
        'expiration_date',
        'payment_date',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
