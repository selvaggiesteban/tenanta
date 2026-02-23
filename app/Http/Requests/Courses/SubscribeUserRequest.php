<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'payment_provider' => ['nullable', 'string', 'max:50'],
            'payment_provider_id' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'El plan de suscripción es obligatorio.',
            'plan_id.exists' => 'El plan seleccionado no existe.',
        ];
    }
}
