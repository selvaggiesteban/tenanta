<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmailTemplateCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn($template) => [
                'id' => $template->id,
                'name' => $template->name,
                'subject' => $template->subject,
                'type' => $template->type,
                'category' => $template->category,
                'is_active' => $template->is_active,
                'created_at' => $template->created_at?->toISOString(),
            ]),
        ];
    }
}
