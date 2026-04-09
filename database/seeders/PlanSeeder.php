<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Courses\SubscriptionPlan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = config('plans');

        foreach ($plans as $key => $data) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $key],
                [
                    'name' => $data['name'],
                    'description' => implode(', ', $data['features']),
                    'price' => $data['price_monthly'],
                    'invoice_period' => 1,
                    'invoice_interval' => 'month',
                    'currency' => 'ARS',
                    'is_active' => true,
                ]
            );
        }
    }
}
