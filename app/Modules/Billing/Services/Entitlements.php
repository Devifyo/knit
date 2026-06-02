<?php

declare(strict_types=1);

namespace App\Modules\Billing\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

/**
 * Resolves what the current tenant is entitled to from its active subscription's
 * plan, and enforces the limits. When a tenant has no subscription it falls back
 * to the free plan (or an unlimited synthetic plan if none is seeded), so the
 * app never hard-blocks on missing billing data.
 */
class Entitlements
{
    public function activeSubscription(): ?Subscription
    {
        return Subscription::with('plan')
            ->whereIn('status', ['trialing', 'active', 'past_due'])
            ->latest()
            ->first();
    }

    public function plan(): Plan
    {
        $plan = optional($this->activeSubscription())->plan
            ?? Plan::where('key', 'free')->where('is_active', true)->first();

        // No catalogue seeded (e.g. a bare test DB): unlimited synthetic plan so
        // gating only ever bites when a tenant is really on a limited plan.
        return $plan ?? new Plan(['key' => 'free', 'name' => 'Free', 'seats' => null, 'features' => []]);
    }

    public function seatLimit(): ?int
    {
        return $this->plan()->seats;
    }

    public function seatsUsed(): int
    {
        return User::count();
    }

    public function canAddSeat(): bool
    {
        $limit = $this->seatLimit();

        return $limit === null || $this->seatsUsed() < $limit;
    }

    public function feature(string $key, mixed $default = null): mixed
    {
        return $this->plan()->feature($key, $default);
    }
}
