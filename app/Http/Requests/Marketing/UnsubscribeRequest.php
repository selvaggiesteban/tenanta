<?php

namespace App\Http\Requests\Marketing;

use App\Models\Marketing\EmailUnsubscribe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnsubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', Rule::in(array_keys(EmailUnsubscribe::REASONS))],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
