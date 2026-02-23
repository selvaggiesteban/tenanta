<?php

namespace App\Http\Requests\Courses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'content_type' => ['required', 'string', Rule::in(['video', 'text', 'pdf', 'quiz'])],
            'video_url' => ['nullable', 'required_if:content_type,video', 'string', 'max:500'],
            'video_provider' => ['nullable', 'string', Rule::in(['youtube', 'vimeo', 'bunny', 'cloudflare', 'local'])],
            'video_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'content' => ['nullable', 'required_if:content_type,text', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.name' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.url' => ['required_with:attachments', 'string', 'max:500'],
            'attachments.*.type' => ['nullable', 'string', 'max:50'],
            'attachments.*.size' => ['nullable', 'integer', 'min:0'],
            'is_free_preview' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título del tema es obligatorio.',
            'content_type.required' => 'El tipo de contenido es obligatorio.',
            'content_type.in' => 'El tipo de contenido no es válido.',
            'video_url.required_if' => 'La URL del video es obligatoria para contenido de tipo video.',
            'content.required_if' => 'El contenido es obligatorio para tipo texto.',
            'video_provider.in' => 'El proveedor de video no es válido.',
        ];
    }
}
