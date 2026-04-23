<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = ['name', 'identifier', 'is_active', 'order', 'image'];
    protected $casts = ['is_active' => 'boolean'];
}