<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Advertisement extends Model
{
    use HasFactory;
    protected $appends = ['image_url'];
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image',
        'link_url',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
    public function getImageUrlAttribute() {
        
        $media = \Awcodes\Curator\Models\Media::find($this->image);
        return $media ? $media->url : null;
    }
}