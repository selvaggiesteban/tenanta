<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

class AddListSubscribersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subscribers' => ['required', 'array', 'min:1'],
            'subscribers.*.email' => ['required_without:subscribers.*.user_id', 'email'],
            'subscribers.*.user_id' => ['required_without:subscribers.*.email', 'integer', 'exists:users,id'],
            'subscribers.*.name' => ['nullable', 'string', 'max:255'],
            'subscribers.*.source' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'subscribers.required' => 'Debe proporcionar al menos un suscriptor',
            'subscribers.*.email.required_without' => 'Cada suscriptor debe tener email o user_id',
            'subscribers.*.email.email' => 'El email debe ser válido',
        ];
    }
}
