<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    // Agregamos los campos que el frontend va a enviar
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'message',
        'is_read',
    ];
}