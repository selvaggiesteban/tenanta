<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'client_id' => Client::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->optional()->phoneNumber(),
            'position' => fake()->jobTitle(),
            'department' => fake()->optional()->randomElement(['Ventas', 'Soporte', 'Administración', 'Gerencia', 'IT']),
            'is_primary' => false,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
