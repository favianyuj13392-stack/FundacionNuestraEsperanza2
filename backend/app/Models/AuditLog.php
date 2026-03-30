<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    
    public $timestamps = false; // Custom created_at

    protected $fillable = [
        'entity',
        'entity_id',
        'action',
        'user_id',
        'changes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'changes' => 'array', // Assuming JSON or serialized data
    ];

    /**
     * An audit log belongs to a user (actor).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
