<?php

namespace App\Models;

use App\Models\Traits\AdjustsOrder;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TestimonialsSection extends Model
{
    use AdjustsOrder;

    protected $fillable = [
        'name',
        'identifier',
        'is_active',
        'order',
        'image',
        'title',
        'subtitle',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (is_numeric($this->image)) {
            $media = Media::find($this->image);
            return $media ? $media->url : null;
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


