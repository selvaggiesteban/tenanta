<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'topics_count' => $this->when(
                $this->relationLoaded('topics'),
                fn() => $this->topics->count()
            ),
            'total_duration_seconds' => $this->when(
                $this->relationLoaded('topics'),
                fn() => $this->topics->sum('video_duration_seconds')
            ),
            'topics' => $this->when(
                $this->relationLoaded('topics'),
                fn() => CourseTopicResource::collection($this->topics)
            ),
        ];
    }
}
