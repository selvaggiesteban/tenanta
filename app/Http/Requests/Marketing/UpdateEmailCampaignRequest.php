<?php

namespace App\Http\Requests\Marketing;

use App\Models\Marketing\EmailCampaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'from_name' => ['sometimes', 'string', 'max:255'],
            'from_email' => ['sometimes', 'email', 'max:255'],
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
}
