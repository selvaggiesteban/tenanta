<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'test_id' => $this->test_id,
            'score' => $this->when($this->is_completed, $this->score),
            'total_points' => $this->when($this->is_completed, $this->total_points),
            'percentage' => $this->when($this->is_completed, $this->percentage),
            'passed' => $this->when($this->is_completed, $this->passed),
            'time_spent_seconds' => $this->time_spent_seconds,
            'formatted_time_spent' => $this->formatted_time_spent,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'is_completed' => $this->is_completed,
            'is_in_progress' => $this->is_in_progress,
            'time_remaining_seconds' => $this->when(
                $this->is_in_progress,
                $this->time_remaining_seconds
            ),
            'is_timed_out' => $this->when(
                $this->is_in_progress,
                $this->is_timed_out
            ),

            // Results (only after completion and if test allows)
            'results' => $this->when(
                $this->is_completed && $this->test?->show_answers_after,
                $this->results
            ),

            // Test info
            'test' => $this->when(
                $this->relationLoaded('test'),
                fn() => new CourseTestResource($this->test)
            ),
        ];
    }
}
