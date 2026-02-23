<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'thumbnail' => $this->thumbnail,
            'level' => $this->level,
            'level_label' => $this->level_label,
            'duration_hours' => $this->duration_hours,
            'price' => $this->price,
            'currency' => $this->currency,
            'formatted_price' => $this->formatted_price,
            'status' => $this->status,
            'total_topics' => $this->total_topics,
            'enrolled_count' => $this->enrolled_count,
            'rating' => $this->rating,
            'reviews_count' => $this->reviews_count,
            'instructor_name' => $this->when(
                $this->relationLoaded('instructor'),
                fn() => $this->instructor?->name
            ),
        ];
    }
}
