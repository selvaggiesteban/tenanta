<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $dueDate = fake()->dateTimeBetween($startDate, '+3 months');

        return [
            'tenant_id' => Tenant::factory(),
            'client_id' => null,
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['planning', 'active', 'on_hold']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'start_date' => $startDate,
            'due_date' => $dueDate,
            'budget' => fake()->optional()->randomFloat(2, 1000, 100000),
            'hourly_rate' => fake()->optional()->randomFloat(2, 20, 200),
            'is_billable' => fake()->boolean(80),
            'manager_id' => null,
            'created_by' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function withClient(Client $client = null): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $client?->id ?? Client::factory(),
        ]);
    }

    public function withManager(User $manager = null): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => $manager?->id ?? User::factory(),
        ]);
    }
}
