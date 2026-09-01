<?php

namespace App\Models\ATC;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Program;

class AtcTransaction extends Model
{
    protected $table = 'atc_transactions';

    protected $fillable = [
        'subscription_id',
        'user_id',
        'campaign_id',
        'program_id',
        'cybersource_request_id',
        'merchant_reference_number',
        'amount',
        'currency',
        'status',
        'flow_type',
        'eci_raw',
        'cavv_raw',
        '3ds_version',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(AtcSubscription::class, 'subscription_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
