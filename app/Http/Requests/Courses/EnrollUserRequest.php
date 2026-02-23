<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

class EnrollUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'subscription_id' => ['nullable', 'integer', 'exists:subscriptions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'El curso es obligatorio.',
            'course_id.exists' => 'El curso seleccionado no existe.',
            'subscription_id.exists' => 'La suscripción seleccionada no existe.',
        ];
    }
}
