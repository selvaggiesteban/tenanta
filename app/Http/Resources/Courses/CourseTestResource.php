<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseTestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'time_limit_minutes' => $this->time_limit_minutes,
            'has_time_limit' => $this->has_time_limit,
            'passing_score' => $this->passing_score,
            'max_attempts' => $this->max_attempts,
            'has_unlimited_attempts' => $this->has_unlimited_attempts,
            'show_answers_after' => $this->show_answers_after,
            'shuffle_questions' => $this->shuffle_questions,
            'shuffle_options' => $this->shuffle_options,
            'is_required' => $this->is_required,
            'total_questions' => $this->total_questions,
            'total_points' => $this->total_points,
            'sort_order' => $this->sort_order,

            // Questions (for admin/instructor view)
            'questions' => $this->when(
                $this->relationLoaded('questions') && $request->user()?->hasRole(['admin', 'manager']),
                fn() => TestQuestionResource::collection($this->questions)
            ),
        ];
    }
}
