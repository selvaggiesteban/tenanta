<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Determine if the user is authorized to make this request.
        // For API endpoint, typically checked via middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'El nombre de la empresa es obligatorio.',
            'company_name.string' => 'El nombre de la empresa debe ser una cadena de texto.',
            'company_name.max' => 'El nombre de la empresa no debe exceder los 255 caracteres.',
            'name.required' => 'El nombre del usuario administrador es obligatorio.',
            'name.string' => 'El nombre del usuario administrador debe ser una cadena de texto.',
            'name.max' => 'El nombre del usuario administrador no debe exceder los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'email.max' => 'El correo electrónico no debe exceder los 255 caracteres.',
            'email.unique' => 'El correo electrónico ya ha sido registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser una cadena de texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
