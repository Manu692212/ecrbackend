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

        $diskUrl = Storage::disk('public')->url($this->image);

        $host = parse_url($diskUrl, PHP_URL_HOST);
        if ($host && !in_array($host, ['localhost', '127.0.0.1', '0.0.0.0'])) {
            return $diskUrl;
        }

        $relativePath = Str::startsWith($diskUrl, ['http://', 'https://'])
            ? parse_url($diskUrl, PHP_URL_PATH) ?? $diskUrl
            : $diskUrl;

        $appUrl = config('app.url');
        $assetUrl = config('app.asset_url');
        $frontendUrl = config('app.frontend_url');
        $requestHost = app()->bound('request') ? optional(request())->getSchemeAndHttpHost() : null;

        $baseUrlCandidates = array_filter([
            $assetUrl,
            $appUrl,
            Str::contains($requestHost, ['localhost', '127.0.0.1', '0.0.0.0']) ? $frontendUrl : $requestHost,
        ]);

        foreach ($baseUrlCandidates as $candidate) {
            if (!$candidate) {
                continue;
            }

            $candidateHost = parse_url($candidate, PHP_URL_HOST);
            if ($candidateHost && in_array($candidateHost, ['localhost', '127.0.0.1', '0.0.0.0'])) {
                continue;
            }

            return rtrim($candidate, '/') . '/' . ltrim($relativePath, '/');
        }

        return $diskUrl;
    }
}
