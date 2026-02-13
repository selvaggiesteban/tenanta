<?php

namespace Database\Factories;

use App\Models\Pipeline;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PipelineFactory extends Factory
{
    protected $model = Pipeline::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(['Ventas', 'Marketing', 'Soporte', 'Proyectos']),
            'description' => fake()->optional()->sentence(),
            'type' => fake()->randomElement(['leads', 'deals', 'projects', 'custom']),
            'is_default' => false,
            'is_active' => true,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function forLeads(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'leads',
            'name' => 'Pipeline de Leads',
        ]);
    }

    public function forDeals(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'deals',
            'name' => 'Pipeline de Negocios',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
