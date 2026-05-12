<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = ['name', 'identifier', 'is_active', 'order', 'image', 'title', 'subtitle', 'content', 'meta_title', 'meta_description', 'meta_keywords'];
    protected $casts = ['is_active' => 'boolean'];
}