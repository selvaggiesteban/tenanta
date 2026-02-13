<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'primary_color' => '#673DE6',
            'trial_ends_at' => now()->addDays(14),
        ];
    }
}
