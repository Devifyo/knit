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
        $source = fake()->randomElement(['Capture form', 'Referral', 'Website', 'Event']);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'source' => $source,
            'source_url' => in_array($source, ['Capture form', 'Website'], true)
                ? rtrim((string) config('app.url'), '/').'/f/acme-inc'
                : null,
            'status' => fake()->randomElement(['new', 'working', 'qualified']),
            'score' => fake()->numberBetween(18, 95),
        ];
    }
}
