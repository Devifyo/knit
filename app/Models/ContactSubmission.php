<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A public consultation request from the marketing site. Deliberately NOT
 * tenant-scoped — these are leads to the company itself, captured on the central
 * (landlord) host. Stores an auditable SMS-consent record.
 */
class ContactSubmission extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name', 'company', 'email', 'phone', 'requirements',
        'sms_consent', 'consent_text', 'consent_ip', 'consent_user_agent', 'consented_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'sms_consent' => 'boolean',
        'consented_at' => 'datetime',
    ];
}
