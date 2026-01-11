<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'category',
        'capacity',
        'location',
        'features',
        'is_featured',
        'is_active',
        'order'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            $url = $this->image;
            return Str::startsWith($url, 'http://')
                ? preg_replace('#^http://#', 'https://', $url)
                : $url;
        }

        return url('/media/' . ltrim($this->image, '/'));
    }
}
