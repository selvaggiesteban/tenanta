<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'content_type' => ['sometimes', 'string', Rule::in(['video', 'text', 'pdf', 'quiz'])],
            'video_url' => ['nullable', 'string', 'max:500'],
            'video_provider' => ['nullable', 'string', Rule::in(['youtube', 'vimeo', 'bunny', 'cloudflare', 'local'])],
            'video_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'content' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.name' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.url' => ['required_with:attachments', 'string', 'max:500'],
            'attachments.*.type' => ['nullable', 'string', 'max:50'],
            'attachments.*.size' => ['nullable', 'integer', 'min:0'],
            'is_free_preview' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'content_type.in' => 'El tipo de contenido no es válido.',
            'video_provider.in' => 'El proveedor de video no es válido.',
        ];
    }
}
