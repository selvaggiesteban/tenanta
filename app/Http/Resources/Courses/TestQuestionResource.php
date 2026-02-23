<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'explanation' => $this->when(
                $this->shouldShowExplanation($request),
                $this->explanation
            ),
            'type' => $this->type,
            'type_label' => $this->type_label,
            'points' => $this->points,
            'sort_order' => $this->sort_order,

            // Options
            'options' => $this->when(
                $this->relationLoaded('options'),
                fn() => $this->options->map(fn($option) => [
                    'id' => $option->id,
                    'text' => $option->text,
                    'is_correct' => $this->when(
                        $this->shouldShowCorrectAnswers($request),
                        $option->is_correct
                    ),
                    'sort_order' => $option->sort_order,
                ])
            ),
        ];
    }

    private function shouldShowExplanation(Request $request): bool
    {
        return $request->user()?->hasRole(['admin', 'manager']) ||
               $request->has('_show_explanations');
    }

    private function shouldShowCorrectAnswers(Request $request): bool
    {
        return $request->user()?->hasRole(['admin', 'manager']) ||
               $request->has('_show_correct_answers');
    }
}
