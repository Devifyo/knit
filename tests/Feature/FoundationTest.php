<?php

declare(strict_types=1);

use App\Services\AI\GeminiService;

it('renders the Inertia welcome page on the home route', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Welcome', false));
});

it('exposes a health check endpoint', function () {
    $this->get('/up')->assertOk();
});

it('resolves a single GeminiService singleton', function () {
    expect(app(GeminiService::class))->toBe(app(GeminiService::class));
});

it('degrades AI gracefully to a safe fallback when disabled', function () {
    $lead = (object) ['id' => 1, 'email' => 'jane@acme.test'];

    $result = app(GeminiService::class)->scoreLead($lead);

    expect($result)->toHaveKeys(['score', 'reasons'])
        ->and($result['score'])->toBe(0)
        ->and($result['reasons'])->toBeArray();
});
