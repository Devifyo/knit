<?php

declare(strict_types=1);

namespace App\Modules\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sends a newly-created workspace member their login credentials. */
class MemberCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role,
        public string $workspace,
    ) {
        $this->loginUrl = url('/login');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your {$this->workspace} account is ready");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.member-credentials');
    }
}
