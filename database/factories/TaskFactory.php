<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['pending', 'in_progress']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'assignee_id' => null,
            'reviewer_id' => null,
            'due_date' => fake()->optional()->dateTimeBetween('now', '+2 months'),
            'estimated_hours' => fake()->optional()->numberBetween(1, 40),
            'sort_order' => 0,
            'created_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'review',
            'submitted_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function withAssignee(User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'assignee_id' => $user?->id ?? User::factory(),
        ]);
    }
}
