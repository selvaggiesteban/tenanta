<?php

namespace App\Http\Requests\Courses;

use App\Models\Courses\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'description' => ['nullable', 'string', 'max:10000'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'trailer_video_url' => ['nullable', 'string', 'max:500'],
            'instructor_id' => ['nullable', 'integer', 'exists:users,id'],
            'level' => ['nullable', 'string', Rule::in(array_keys(Course::LEVELS))],
            'language' => ['nullable', 'string', 'max:10'],
            'duration_hours' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['string', 'max:500'],
            'what_you_learn' => ['nullable', 'array'],
            'what_you_learn.*' => ['string', 'max:500'],
            'target_audience' => ['nullable', 'array'],
            'target_audience.*' => ['string', 'max:500'],

            // Nested blocks
            'blocks' => ['nullable', 'array'],
            'blocks.*.title' => ['required_with:blocks', 'string', 'max:255'],
            'blocks.*.description' => ['nullable', 'string', 'max:1000'],
            'blocks.*.sort_order' => ['nullable', 'integer', 'min:0'],

            // Nested topics within blocks
            'blocks.*.topics' => ['nullable', 'array'],
            'blocks.*.topics.*.title' => ['required_with:blocks.*.topics', 'string', 'max:255'],
            'blocks.*.topics.*.description' => ['nullable', 'string', 'max:2000'],
            'blocks.*.topics.*.content_type' => ['nullable', 'string', Rule::in(['video', 'text', 'pdf', 'quiz'])],
            'blocks.*.topics.*.video_url' => ['nullable', 'string', 'max:500'],
            'blocks.*.topics.*.video_provider' => ['nullable', 'string', 'max:50'],
            'blocks.*.topics.*.video_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'blocks.*.topics.*.content' => ['nullable', 'string'],
            'blocks.*.topics.*.is_free_preview' => ['nullable', 'boolean'],
            'blocks.*.topics.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título del curso es obligatorio.',
            'title.max' => 'El título no puede exceder 255 caracteres.',
            'slug.unique' => 'Ya existe un curso con este slug.',
            'level.in' => 'El nivel seleccionado no es válido.',
            'price.min' => 'El precio no puede ser negativo.',
            'instructor_id.exists' => 'El instructor seleccionado no existe.',
            'blocks.*.title.required_with' => 'El título del módulo es obligatorio.',
            'blocks.*.topics.*.title.required_with' => 'El título del tema es obligatorio.',
        ];
    }
}
