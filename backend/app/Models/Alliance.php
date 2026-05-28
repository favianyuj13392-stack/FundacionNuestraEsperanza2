<?php

namespace App\Models;

use App\Models\Traits\AdjustsOrder;
use Illuminate\Database\Eloquent\Model;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alliance extends Model
{
    use AdjustsOrder;

    protected static function getOrderColumn(): string
    {
        return 'sort_order';
    }

    public $timestamps = false;
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