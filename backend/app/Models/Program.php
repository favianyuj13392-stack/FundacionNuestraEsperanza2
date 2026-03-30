<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'programs';

    protected $fillable = [
        'title',
        'description',
        'image',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
