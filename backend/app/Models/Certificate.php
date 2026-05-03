<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $table = 'certificates';
    
    public $timestamps = false; 

    protected $fillable = [
        'donation_id',
        'folio',
        'pdf_url',
        'issued_on', 
    ];

    protected $casts = [
        'issued_on' => 'datetime',
    ];

    /**
     * A certificate belongs to a donation.
     */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }
}
