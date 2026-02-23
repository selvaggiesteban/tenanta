<?php

namespace App\Http\Requests\Marketing;

use App\Models\Marketing\EmailCampaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmailCampaignRequest extends FormRequest
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
            'from_name' => ['required', 'string', 'max:255'],
            'from_email' => ['required', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'template_id' => ['nullable', 'integer', 'exists:email_templates,id'],
            'content_html' => ['nullable', 'string'],
            'content_text' => ['nullable', 'string'],
            'type' => ['nullable', 'string', Rule::in([
                EmailCampaign::TYPE_REGULAR,
                EmailCampaign::TYPE_AUTOMATED,
                EmailCampaign::TYPE_AB_TEST,
            ])],
            'settings' => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la campaña es obligatorio',
            'subject.required' => 'El asunto es obligatorio',
            'from_name.required' => 'El nombre del remitente es obligatorio',
            'from_email.required' => 'El email del remitente es obligatorio',
            'from_email.email' => 'El email del remitente debe ser válido',
            'scheduled_at.after' => 'La fecha programada debe ser en el futuro',
        ];
    }
}
