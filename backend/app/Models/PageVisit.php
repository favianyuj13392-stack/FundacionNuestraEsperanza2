<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_visited',
        'ip_address',
        'user_agent',
        'visited_on',
    ];

    protected $casts = [
        'visited_on' => 'date',
    ];
}
