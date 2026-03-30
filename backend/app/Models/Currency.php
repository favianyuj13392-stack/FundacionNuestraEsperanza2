<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $table = 'currencies';
    
    public $timestamps = false; // Migration doesn't specify timestamps for currencies

    protected $fillable = [
        'name',
        'iso_code',
        'symbol',
    ];

    /**
     * A currency can be used in many donations.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * A currency can be used in many campaigns.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
