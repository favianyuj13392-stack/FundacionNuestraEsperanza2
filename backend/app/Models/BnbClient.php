<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BnbClient extends Model
{
    use HasFactory;

    protected $table = 'bnb_clients';

    protected $fillable = [
        'user_id',
        'identifier',   // Código único del cliente enviado al BNB (ej. CI o slug)
        'name',
        'address',
        'email',
        'phone_number',
        'bnb_status',      // 1=Activo, 2=Inactivo
        'synced_to_bnb',
        'last_synced_at',
    ];

    protected $casts = [
        'synced_to_bnb'  => 'boolean',
        'last_synced_at' => 'datetime',
        'bnb_status'     => 'integer',
    ];

    // --- Relaciones ---

    /**
     * El usuario de nuestra plataforma al que pertenece este cliente BNB.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Todas las suscripciones de domiciliación de este cliente.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(BnbSubscription::class, 'bnb_client_id');
    }

    /**
     * Sólo las suscripciones activas/enrolladas.
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(BnbSubscription::class, 'bnb_client_id')
            ->where('status', 'enrolled');
    }

    // --- Helpers ---

    /**
     * Devuelve el payload que el BNB espera en UpdateRecord.
     */
    public function toBnbPayload(): array
    {
        return [
            'identifier'  => $this->identifier,
            'name'        => $this->name,
            'address'     => $this->address ?? '',
            'email'       => $this->email,
            'phoneNumber' => $this->phone_number ?? '',
            'status'      => $this->bnb_status,
        ];
    }
}
