<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Schema;

class SmtpConfigService
{
    public function apply(): void
    {
        if (!$this->settingsTableExists()) {
            return;
        }

        $mailer = $this->getMailer();

        $config = $this->buildMailerConfig();

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp' => array_merge(config('mail.mailers.smtp', []), $config),
            'mail.from.address' => $this->getSenderEmail(),
            'mail.from.name' => $this->getSenderName(),
        ]);

        try {
            app(MailManager::class)->forgetMailers();
        } catch (\Throwable $e) {
            // ignore
        }

        if ($mailer === 'resend') {
            $resendKey = $this->getSetting('smtp.resend_api_key', env('RESEND_API_KEY'));
            if ($resendKey) {
                config([
                    'services.resend.key' => $resendKey,
                ]);
            }
        }
    }

    private function getMailer(): string
    {
        $mailer = $this->getSetting('smtp.mailer', config('mail.default', 'smtp'));
        $mailer = is_string($mailer) ? trim($mailer) : '';

        if ($mailer === '') {
            return 'smtp';
        }

        $availableMailers = array_keys((array) config('mail.mailers', []));
        if (!in_array($mailer, $availableMailers, true)) {
            return 'smtp';
        }

        return $mailer;
    }

    public function getSenderEmail(): string
    {
        $address = $this->getSetting('smtp.from_address', config('mail.from.address'));
        if (!$address) {
            $address = env('MAIL_FROM_ADDRESS');
        }

        return $address ?: 'no-reply@example.com';
    }

    public function getSenderName(): string
    {
        $name = $this->getSetting('smtp.from_name', config('mail.from.name'));
        if (!$name) {
            $name = env('MAIL_FROM_NAME', config('app.name', 'ECR'));
        }

        return $name ?: 'ECR';
    }

    public function getRecipientEmail(): string
    {
        return $this->getSetting('smtp.recipient_address', $this->getSenderEmail());
    }

    public function getRecipientName(): string
    {
        return $this->getSetting('smtp.recipient_name', 'ECR Admin');
    }

    /**
     * Retrieve the currently effective SMTP config as array.
     */
    public function buildMailerConfig(): array
    {
        $scheme = $this->getSetting('smtp.scheme', env('MAIL_SCHEME'));
        $encryption = $this->getSetting('smtp.encryption', env('MAIL_ENCRYPTION'));

        if (!$scheme && $encryption) {
            $scheme = $encryption;
        }

        return [
            'transport' => 'smtp',
            'scheme' => $scheme ?: null,
            'url' => $this->getSetting('smtp.url', env('MAIL_URL')),
            'host' => $this->getSetting('smtp.host', env('MAIL_HOST', '127.0.0.1')),
            'port' => (int) $this->getSetting('smtp.port', env('MAIL_PORT', 2525)),
            'username' => $this->getSetting('smtp.username', env('MAIL_USERNAME')),
            'password' => $this->getSetting('smtp.password', env('MAIL_PASSWORD')),
            'timeout' => null,
            'local_domain' => $this->getSetting(
                'smtp.local_domain',
                env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST))
            ),
            'encryption' => $encryption ?: null,
        ];
    }

    private function getSetting(string $key, $default = null): mixed
    {
        return Setting::getValue($key, $default);
    }

    private function settingsTableExists(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
