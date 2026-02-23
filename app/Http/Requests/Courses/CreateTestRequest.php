<?php

namespace App\Http\Requests\Courses;

use App\Models\Courses\CourseTest;
use App\Models\Courses\TestQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'string', Rule::in(array_keys(CourseTest::TYPES))],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:0'],
            'show_answers_after' => ['nullable', 'boolean'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            // Questions
            'questions' => ['nullable', 'array'],
            'questions.*.question' => ['required_with:questions', 'string', 'max:1000'],
            'questions.*.explanation' => ['nullable', 'string', 'max:2000'],
            'questions.*.type' => ['required_with:questions', 'string', Rule::in(array_keys(TestQuestion::TYPES))],
            'questions.*.points' => ['nullable', 'integer', 'min:1'],
            'questions.*.sort_order' => ['nullable', 'integer', 'min:0'],

            // Options for each question
            'questions.*.options' => ['required_with:questions', 'array', 'min:2'],
            'questions.*.options.*.text' => ['required_with:questions.*.options', 'string', 'max:500'],
            'questions.*.options.*.is_correct' => ['required_with:questions.*.options', 'boolean'],
            'questions.*.options.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título del examen es obligatorio.',
            'type.required' => 'El tipo de examen es obligatorio.',
            'type.in' => 'El tipo de examen no es válido.',
            'passing_score.required' => 'La puntuación mínima para aprobar es obligatoria.',
            'passing_score.min' => 'La puntuación mínima debe ser al menos 0.',
            'passing_score.max' => 'La puntuación mínima no puede exceder 100.',
            'time_limit_minutes.min' => 'El límite de tiempo debe ser al menos 1 minuto.',
            'questions.*.question.required_with' => 'El texto de la pregunta es obligatorio.',
            'questions.*.type.required_with' => 'El tipo de pregunta es obligatorio.',
            'questions.*.type.in' => 'El tipo de pregunta no es válido.',
            'questions.*.options.required_with' => 'Las opciones de respuesta son obligatorias.',
            'questions.*.options.min' => 'Cada pregunta debe tener al menos 2 opciones.',
            'questions.*.options.*.text.required_with' => 'El texto de la opción es obligatorio.',
            'questions.*.options.*.is_correct.required_with' => 'Debe indicar si la opción es correcta.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $questions = $this->input('questions', []);

            foreach ($questions as $index => $question) {
                $options = $question['options'] ?? [];
                $hasCorrect = collect($options)->where('is_correct', true)->isNotEmpty();

                if (!$hasCorrect) {
                    $validator->errors()->add(
                        "questions.{$index}.options",
                        'Cada pregunta debe tener al menos una opción correcta.'
                    );
                }

                // For single choice, only one correct
                if (($question['type'] ?? '') === 'single') {
                    $correctCount = collect($options)->where('is_correct', true)->count();
                    if ($correctCount > 1) {
                        $validator->errors()->add(
                            "questions.{$index}.options",
                            'Las preguntas de opción única solo pueden tener una respuesta correcta.'
                        );
                    }
                }
            }
        });
    }
}
