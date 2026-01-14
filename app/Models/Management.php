<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Management extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'designation',
        'bio',
        'qualifications',
        'image',
        'image_size',
        'image_width',
        'image_height',
        'department',
        'order',
        'is_active',
        'image_data',
        'image_mime',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'image_width' => 'integer',
        'image_height' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_data && $this->image_mime) {
            return sprintf('data:%s;base64,%s', $this->image_mime, $this->image_data);
        }

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
