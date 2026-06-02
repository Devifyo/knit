<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Deal> */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->catchPhrase(),
            'amount' => fake()->numberBetween(1000, 200000) * 100,
            'currency' => 'USD',
            'status' => 'open',
            'expected_close_date' => fake()->dateTimeBetween('now', '+3 months'),
        ];
    }
}
