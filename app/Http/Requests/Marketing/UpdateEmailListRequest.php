<?php

namespace App\Http\Requests\Marketing;

use App\Models\Marketing\EmailList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', 'string', Rule::in([
                EmailList::TYPE_STATIC,
                EmailList::TYPE_DYNAMIC,
            ])],
            'filters' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
