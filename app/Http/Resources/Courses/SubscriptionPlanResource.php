<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'formatted_price' => $this->formatted_price,
            'billing_cycle' => $this->billing_cycle,
            'billing_cycle_label' => $this->billing_cycle_label,
            'trial_days' => $this->trial_days,
            'features' => $this->features,
            'course_access' => $this->course_access,
            'max_courses' => $this->max_courses,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,

            // Include course IDs for admin views
            'course_ids' => $this->when(
                $request->user()?->hasRole(['admin', 'super_admin']),
                $this->course_ids
            ),
        ];
    }
}
