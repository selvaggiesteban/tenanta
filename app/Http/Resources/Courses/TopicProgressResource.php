<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_completed' => $this->is_completed,
            'completed_at' => $this->completed_at?->toISOString(),
            'watch_time_seconds' => $this->watch_time_seconds,
            'watch_percentage' => $this->watch_percentage,
            'last_position_seconds' => $this->last_position_seconds,
            'last_watched_at' => $this->last_watched_at?->toISOString(),
        ];
    }
}
