<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'thumbnail' => $this->thumbnail,
            'trailer_video_url' => $this->trailer_video_url,
            'level' => $this->level,
            'level_label' => $this->level_label,
            'language' => $this->language,
            'duration_hours' => $this->duration_hours,
            'price' => $this->price,
            'currency' => $this->currency,
            'formatted_price' => $this->formatted_price,
            'status' => $this->status,
            'requirements' => $this->requirements,
            'what_you_learn' => $this->what_you_learn,
            'target_audience' => $this->target_audience,
            'total_blocks' => $this->total_blocks,
            'total_topics' => $this->total_topics,
            'total_duration_seconds' => $this->total_duration_seconds,
            'enrolled_count' => $this->enrolled_count,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships (conditionally loaded)
            'instructor' => $this->when(
                $this->relationLoaded('instructor'),
                fn() => new CourseInstructorResource($this->instructor)
            ),
            'blocks' => $this->when(
                $this->relationLoaded('blocks'),
                fn() => CourseBlockResource::collection($this->blocks)
            ),
        ];
    }
}
