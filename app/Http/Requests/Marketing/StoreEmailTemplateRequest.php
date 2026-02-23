<?php

namespace App\Http\Requests\Marketing;

use App\Models\Marketing\EmailTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content_html' => ['required', 'string'],
            'content_text' => ['nullable', 'string'],
            'type' => ['nullable', 'string', Rule::in([
                EmailTemplate::TYPE_MARKETING,
                EmailTemplate::TYPE_TRANSACTIONAL,
                EmailTemplate::TYPE_NOTIFICATION,
            ])],
            'category' => ['nullable', 'string', 'max:100'],
            'variables' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la plantilla es obligatorio',
            'subject.required' => 'El asunto es obligatorio',
            'content_html.required' => 'El contenido HTML es obligatorio',
        ];
    }
}
