<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

class AddCampaignRecipientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', 'string', 'in:list,users,emails'],
            'list_id' => ['required_if:source,list', 'integer', 'exists:email_lists,id'],
            'user_ids' => ['required_if:source,users', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'emails' => ['required_if:source,emails', 'array'],
            'emails.*.email' => ['required', 'email'],
            'emails.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'source.required' => 'Debe especificar la fuente de destinatarios',
            'source.in' => 'La fuente debe ser: list, users o emails',
            'list_id.required_if' => 'Debe seleccionar una lista',
            'user_ids.required_if' => 'Debe seleccionar al menos un usuario',
            'emails.required_if' => 'Debe proporcionar al menos un email',
        ];
    }
}
