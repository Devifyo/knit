<?php

declare(strict_types=1);

namespace App\Modules\Leads\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Confirmation sent to a person who submitted a lead/enquiry. */
class LeadReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $workspace,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "We received your message — {$this->workspace}");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.lead-received');
    }
}
