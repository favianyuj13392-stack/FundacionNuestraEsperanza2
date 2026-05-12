<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alliance extends Model
{
    // Permitimos la carga masiva de estos campos
    protected $fillable = [
        'name',
        'logo', // Aquí guardamos el ID de Curator
        'url',
        'is_active',
        'sort_order'
    ];

    // Relación para obtener la imagen desde la biblioteca de medios
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo');
    }
}