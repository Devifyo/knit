<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lead> */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'source' => fake()->randomElement(['Website', 'Referral', 'Event']),
            'status' => fake()->randomElement(['new', 'working', 'qualified']),
            'score' => fake()->numberBetween(0, 100),
        ];
    }
}
