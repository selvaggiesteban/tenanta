<?php

namespace App\Http\Requests\Marketing;

use App\Models\Marketing\EmailList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmailListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', 'string', Rule::in([
                EmailList::TYPE_STATIC,
                EmailList::TYPE_DYNAMIC,
            ])],
            'filters' => ['nullable', 'array'],
            'filters.roles' => ['nullable', 'array'],
            'filters.created_after' => ['nullable', 'date'],
            'filters.created_before' => ['nullable', 'date'],
            'filters.has_subscription' => ['nullable', 'boolean'],
            'filters.has_enrollment' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la lista es obligatorio',
        ];
    }
}
