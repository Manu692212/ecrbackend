<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AcademicCouncil extends Model
{
    use HasFactory;

    protected $table = 'academic_councils';

    protected $fillable = [
        'name',
        'position',
        'email',
        'phone',
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

    protected $appends = [
        'image_url',
    ];

    public $timestamps = true;

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
