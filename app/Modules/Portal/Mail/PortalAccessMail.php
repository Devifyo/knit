<?php

declare(strict_types=1);

namespace App\Modules\Portal\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sends a customer their portal set-password link — used both for the initial
 * invite and for a self-service password reset.
 */
class PortalAccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $url,
        public string $workspace,
        public bool $reset = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reset
                ? "Reset your {$this->workspace} portal password"
                : "Activate your {$this->workspace} portal access",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.portal-access');
    }
}
