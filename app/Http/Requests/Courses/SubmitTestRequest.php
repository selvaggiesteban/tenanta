<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'array'],
            'answers.*.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Las respuestas son obligatorias.',
            'answers.array' => 'El formato de respuestas no es válido.',
        ];
    }
}
