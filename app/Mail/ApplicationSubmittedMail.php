<?php

namespace App\Mail;

use App\Models\ApplicationSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public ApplicationSubmission $submission;

    /**
     * Create a new message instance.
     */
    public function __construct(ApplicationSubmission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject("New {$this->submission->form_type} application: {$this->submission->full_name}")
            ->view('emails.application-submission')
            ->with([
                'submission' => $this->submission,
                'payload' => $this->submission->payload ?? [],
            ]);
    }
}
