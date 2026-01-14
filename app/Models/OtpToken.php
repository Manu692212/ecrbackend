<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OtpToken extends Model
{
    use HasFactory;

    protected $table = 'otp_tokens';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'admin_id',
        'email',
        'channel',
        'context',
        'code_hash',
        'attempts',
        'expires_at',
        'verified_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (OtpToken $token) {
            if (empty($token->id)) {
                $token->id = (string) Str::ulid();
            }
        });
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function markVerified(): void
    {
        $this->forceFill([
            'verified_at' => now(),
        ])->save();
    }

    public function remainingSeconds(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        return max(0, $this->expires_at->diffInSeconds(now(), false) * -1);
    }
}
