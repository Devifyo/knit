<?php

declare(strict_types=1);

namespace App\Modules\Leads\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** New-lead alert sent to workspace members who opted into lead notifications. */
class NewLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;

    public function __construct(
        public Lead $lead,
        public string $workspace,
    ) {
        $this->url = url('/leads/'.$lead->id);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "New lead: {$this->lead->name} — {$this->workspace}");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.lead-new');
    }
}
