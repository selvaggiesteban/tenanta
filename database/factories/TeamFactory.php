<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(['Desarrollo', 'Ventas', 'Soporte', 'Marketing', 'Diseño']) . ' ' . fake()->randomNumber(2),
            'description' => fake()->sentence(),
        ];
    }
}
