<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $purpose;
    public int $expiresInSeconds;
    public ?array $meta;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, string $purpose = 'Verification', int $expiresInSeconds = 300, ?array $meta = null)
    {
        $this->code = $code;
        $this->purpose = $purpose;
        $this->expiresInSeconds = $expiresInSeconds;
        $this->meta = $meta;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $minutes = max(1, intdiv($this->expiresInSeconds, 60));

        return $this->subject("{$this->purpose} OTP: {$this->code}")
            ->view('emails.otp')
            ->with([
                'code' => $this->code,
                'purpose' => $this->purpose,
                'expiresInSeconds' => $this->expiresInSeconds,
                'expiresInMinutes' => $minutes,
                'meta' => $this->meta,
            ]);
    }
}
