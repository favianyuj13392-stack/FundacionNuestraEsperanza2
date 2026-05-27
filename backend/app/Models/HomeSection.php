<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Awcodes\Curator\Models\Media;

class HomeSection extends Model
{
    protected $fillable = ['name', 'identifier', 'is_active', 'order', 'image', 'title', 'subtitle', 'content', 'meta_title', 'meta_description', 'meta_keywords'];
    protected $casts = ['is_active' => 'boolean'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (is_numeric($this->image)) {
            $media = Media::find($this->image);
            return $media ? $media->url : null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }
}