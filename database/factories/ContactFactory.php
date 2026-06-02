<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Contact> */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'job_title' => fake()->jobTitle(),
            'lifecycle_stage' => fake()->randomElement(['lead', 'mql', 'sql', 'customer']),
            'source' => fake()->randomElement(['Website', 'Referral', 'Event', 'Cold outreach']),
        ];
    }
}
