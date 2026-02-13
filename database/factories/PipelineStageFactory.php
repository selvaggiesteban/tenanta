<?php

namespace Database\Factories;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    public function definition(): array
    {
        return [
            'pipeline_id' => Pipeline::factory(),
            'name' => fake()->randomElement(['Nuevo', 'En Proceso', 'Revisión', 'Completado']),
            'color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(0, 10),
            'probability' => fake()->numberBetween(0, 100),
            'is_won' => false,
            'is_lost' => false,
        ];
    }

    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Ganado',
            'color' => '#22c55e',
            'probability' => 100,
            'is_won' => true,
            'is_lost' => false,
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Perdido',
            'color' => '#ef4444',
            'probability' => 0,
            'is_won' => false,
            'is_lost' => true,
        ]);
    }
}
