<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Company> */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'domain' => fake()->domainName(),
            'industry' => fake()->randomElement(['SaaS', 'Fintech', 'Healthcare', 'Retail']),
            'size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-1000']),
            'annual_revenue' => fake()->numberBetween(100000, 50000000) * 100,
            'health_score' => fake()->numberBetween(20, 95),
        ];
    }
}
