<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

class ImportListSubscribersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Debe subir un archivo CSV',
            'file.mimes' => 'El archivo debe ser CSV',
            'file.max' => 'El archivo no debe superar 5MB',
        ];
    }
}
