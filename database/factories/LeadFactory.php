<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'company_name' => fake()->optional(0.8)->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'position' => fake()->optional()->jobTitle(),
            'status' => 'new',
            'source' => fake()->randomElement(['web', 'referral', 'cold_call', 'social_media', 'email_campaign']),
            'estimated_value' => fake()->optional()->randomFloat(2, 1000, 100000),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'contacted',
        ]);
    }

    public function qualified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'qualified',
        ]);
    }

    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'won',
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'lost',
        ]);
    }
}
