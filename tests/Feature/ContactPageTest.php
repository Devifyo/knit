<?php

declare(strict_types=1);

use App\Models\ContactSubmission;

it('renders the public contact, privacy and terms pages', function () {
    $this->get('/contact')->assertOk()
        ->assertInertia(fn ($p) => $p->component('Public/Contact', false)
            ->where('consentText', fn ($t) => str_contains((string) $t, 'Reply STOP to opt out')));
    $this->get('/privacy')->assertOk()->assertInertia(fn ($p) => $p->component('Public/Privacy', false));
    $this->get('/terms')->assertOk()->assertInertia(fn ($p) => $p->component('Public/Terms', false));
});

it('stores a consultation request with an auditable SMS consent record', function () {
    $this->post('/contact', [
        'name' => 'Pat Buyer',
        'company' => 'Acme Co',
        'email' => 'pat@acme.test',
        'phone' => '+15551234567',
        'requirements' => 'We need a SaaS app.',
        'sms_consent' => true,
    ])->assertRedirect()->assertSessionHas('success');

    $row = ContactSubmission::firstOrFail();
    expect($row->name)->toBe('Pat Buyer')
        ->and($row->sms_consent)->toBeTrue()
        ->and($row->consent_text)->toContain('Reply STOP to opt out')
        ->and($row->consented_at)->not->toBeNull()
        ->and($row->consent_ip)->not->toBeNull();
});

it('rejects a submission without SMS consent', function () {
    $this->post('/contact', [
        'name' => 'Pat Buyer',
        'email' => 'pat@acme.test',
        'phone' => '+15551234567',
        'sms_consent' => false,
    ])->assertSessionHasErrors('sms_consent');

    expect(ContactSubmission::count())->toBe(0);
});
