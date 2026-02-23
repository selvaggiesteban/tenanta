<?php

namespace App\Http\Requests\Courses;

use App\Models\Courses\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSubscriptionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'billing_cycle' => ['required', 'string', Rule::in(array_keys(SubscriptionPlan::BILLING_CYCLES))],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'course_access' => ['required', 'string', Rule::in(['all', 'specific', 'category'])],
            'course_ids' => ['nullable', 'required_if:course_access,specific', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
            'category_ids' => ['nullable', 'required_if:course_access,category', 'array'],
            'category_ids.*' => ['integer'],
            'max_courses' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del plan es obligatorio.',
            'price.required' => 'El precio es obligatorio.',
            'price.min' => 'El precio no puede ser negativo.',
            'currency.required' => 'La moneda es obligatoria.',
            'billing_cycle.required' => 'El ciclo de facturación es obligatorio.',
            'billing_cycle.in' => 'El ciclo de facturación no es válido.',
            'course_access.required' => 'El tipo de acceso a cursos es obligatorio.',
            'course_access.in' => 'El tipo de acceso no es válido.',
            'course_ids.required_if' => 'Debe seleccionar al menos un curso para acceso específico.',
            'course_ids.*.exists' => 'Uno de los cursos seleccionados no existe.',
            'category_ids.required_if' => 'Debe seleccionar al menos una categoría.',
        ];
    }
}
