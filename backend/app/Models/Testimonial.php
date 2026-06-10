<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    protected $table = 'testimonials';

    protected $fillable = [
        'name',
        'role',
        'content',
        'image',
        'date',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'date' => 'date',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        // If already a full URL (from Cloudinary or other source), return as-is
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        // Try to serve from public storage
        try {
            if (Storage::disk('public')->exists($this->image)) {
                return asset('storage/' . ltrim($this->image, '/'));
            }
        } catch (\Exception $e) {
            // Silently fail if storage check fails
        }

        // Return the path as-is (assuming it's already a valid URL or path)
        return $this->image;
    }
}
