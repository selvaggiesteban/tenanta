<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_provider' => $this->payment_provider,
            'payment_method' => $this->payment_method,
            'last_payment_at' => $this->last_payment_at?->toISOString(),
            'next_payment_at' => $this->next_payment_at?->toISOString(),
            'is_active' => $this->isActive(),
            'is_on_trial' => $this->isOnTrial(),
            'is_cancelled' => $this->cancelled_at !== null,
            'days_remaining' => $this->ends_at ? now()->diffInDays($this->ends_at, false) : null,

            // Relationships
            'plan' => $this->when(
                $this->relationLoaded('plan'),
                fn() => new SubscriptionPlanResource($this->plan)
            ),
        ];
    }
}
