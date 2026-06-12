<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title',
        'content',
        'image',
        'publication_date',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'publication_date' => 'date',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        if (Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . ltrim($this->image, '/'));
        }

                try {
            return Storage::disk('cloudinary')->url($this->image);
        } catch (\Exception $e) {
            return null;
        }
    }
}

