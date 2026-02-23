<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'progress_percentage' => $this->progress_percentage,
            'completed_topics' => $this->completed_topics,
            'total_topics' => $this->total_topics,
            'enrolled_at' => $this->enrolled_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'is_active' => $this->isActive(),
            'is_completed' => $this->status === 'completed',
            'is_expired' => $this->status === 'expired',

            // Relationships
            'course' => $this->when(
                $this->relationLoaded('course'),
                fn() => new CourseListResource($this->course)
            ),
            'subscription' => $this->when(
                $this->relationLoaded('subscription'),
                fn() => new SubscriptionResource($this->subscription)
            ),
        ];
    }
}
