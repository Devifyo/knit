<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the global billing catalogue — plans + coupons shared by every tenant.
 * Idempotent so it's safe to run from the main seeder and from tests.
 */
class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['key' => 'free', 'name' => 'Free', 'price_minor' => 0, 'trial_days' => 0, 'seats' => 3, 'sort' => 0,
                'features' => ['ai' => false, 'workflows' => 1, 'support' => 'community']],
            ['key' => 'pro', 'name' => 'Pro', 'price_minor' => 4900, 'trial_days' => 14, 'seats' => 10, 'sort' => 1,
                'features' => ['ai' => true, 'workflows' => 25, 'support' => 'email']],
            ['key' => 'business', 'name' => 'Business', 'price_minor' => 19900, 'trial_days' => 14, 'seats' => null, 'sort' => 2,
                'features' => ['ai' => true, 'workflows' => null, 'support' => 'priority']],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['key' => $plan['key']], [...$plan, 'currency' => 'USD', 'interval' => 'month', 'is_active' => true]);
        }

        Coupon::updateOrCreate(['code' => 'LAUNCH25'], [
            'type' => 'percent', 'value' => 25, 'max_redemptions' => 1000, 'active' => true,
        ]);
        Coupon::updateOrCreate(['code' => 'WELCOME10'], [
            'type' => 'fixed', 'value' => 1000, 'currency' => 'USD', 'active' => true,
        ]);
    }
}
