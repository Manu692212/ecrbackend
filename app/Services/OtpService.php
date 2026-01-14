<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\OtpToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OtpService
{
    public const DEFAULT_TTL_SECONDS = 300; // 5 minutes
    public const MAX_ATTEMPTS = 5;

    /**
     * Issue a new OTP token and return the token + plain code.
     *
     * @return array{token: OtpToken, code: string}
     */
    public function issue(string $email, string $context, ?Admin $admin = null, int $ttl = self::DEFAULT_TTL_SECONDS, array $metadata = []): array
    {
        $code = $this->generateCode();

        $token = OtpToken::create([
            'admin_id' => $admin?->id,
            'email' => $email,
            'context' => $context,
            'channel' => 'email',
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addSeconds($ttl),
            'metadata' => $metadata,
        ]);

        return [
            'token' => $token,
            'code' => $code,
        ];
    }

    public function verify(OtpToken|string $tokenOrId, string $code): bool
    {
        $token = $tokenOrId instanceof OtpToken
            ? $tokenOrId
            : OtpToken::findOrFail($tokenOrId);

        if ($token->verified_at) {
            return true;
        }

        if ($token->expires_at->isPast()) {
            return false;
        }

        if ($token->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        $token->increment('attempts');

        if (!Hash::check($code, $token->code_hash)) {
            return false;
        }

        $token->markVerified();

        return true;
    }

    public function cleanupExpired(): int
    {
        return OtpToken::where('expires_at', '<', now()->subMinutes(10))->delete();
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
