<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
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
        'is_active'
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
        if (!$this->image) {
            return null;
        }

        $path = ltrim($this->image, '/');

        $host = null;

        if (app()->bound('request')) {
            $host = app('request')->getSchemeAndHttpHost();
        }

        if (!$host) {
            $fallback = config('app.url');
            $host = $fallback ? rtrim($fallback, '/') : rtrim(URL::to('/'), '/');
        }

        return rtrim($host, '/') . '/media/' . $path;
    }
}
