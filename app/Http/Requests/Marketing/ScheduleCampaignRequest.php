<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.required' => 'La fecha de programación es obligatoria',
            'scheduled_at.date' => 'La fecha debe ser válida',
            'scheduled_at.after' => 'La fecha debe ser en el futuro',
        ];
    }
}
