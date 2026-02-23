<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTopicProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_seconds' => ['required', 'integer', 'min:0'],
            'watched_seconds' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'position_seconds.required' => 'La posición del video es obligatoria.',
            'position_seconds.min' => 'La posición no puede ser negativa.',
            'watched_seconds.required' => 'El tiempo visto es obligatorio.',
            'watched_seconds.min' => 'El tiempo visto no puede ser negativo.',
        ];
    }
}
