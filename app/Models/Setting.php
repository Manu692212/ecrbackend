<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    private const ENCRYPTED_PREFIX = 'enc:';

    /**
     * Keys whose values should be encrypted at rest.
     */
    private const SENSITIVE_KEYS = [
        'smtp.password',
        'smtp.username',
        'smtp.resend_api_key',
    ];

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public'
    ];

    protected $casts = [
        'value' => 'string',
        'is_public' => 'boolean',
    ];

    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, $value, string $type = 'text')
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public function getValueAttribute($value)
    {
        $key = $this->attributes['key'] ?? null;

        if (!$key || !static::isSensitiveKey($key) || $value === null) {
            return $value;
        }

        return static::decryptValue($value);
    }

    public function setValueAttribute($value): void
    {
        $key = $this->attributes['key'] ?? $this->getAttribute('key');

        if ($key && static::isSensitiveKey($key) && $value !== null) {
            $this->attributes['value'] = static::encryptValue($value);
            return;
        }

        $this->attributes['value'] = $value;
    }

    private static function isSensitiveKey(string $key): bool
    {
        return in_array($key, self::SENSITIVE_KEYS, true);
    }

    private static function encryptValue(string $value): string
    {
        return self::ENCRYPTED_PREFIX . Crypt::encryptString($value);
    }

    private static function decryptValue(string $value): string
    {
        if (!str_starts_with($value, self::ENCRYPTED_PREFIX)) {
            // Legacy plaintext - return as-is so next save will encrypt it.
            return $value;
        }

        $payload = substr($value, strlen(self::ENCRYPTED_PREFIX));

        try {
            return Crypt::decryptString($payload);
        } catch (\Throwable) {
            // Corrupted payload; return raw so caller can decide what to do.
            return $value;
        }
    }
}
