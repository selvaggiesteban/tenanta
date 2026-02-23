<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subject' => $this->subject,
            'content_html' => $this->content_html,
            'content_text' => $this->content_text,
            'type' => $this->type,
            'category' => $this->category,
            'variables' => $this->variables,
            'settings' => $this->settings,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
