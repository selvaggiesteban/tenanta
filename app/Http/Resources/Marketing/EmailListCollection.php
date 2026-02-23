<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmailListCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn($list) => [
                'id' => $list->id,
                'name' => $list->name,
                'description' => $list->description,
                'type' => $list->type,
                'subscriber_count' => $list->subscriber_count,
                'active_count' => $list->active_count,
                'is_active' => $list->is_active,
                'is_default' => $list->is_default,
                'created_at' => $list->created_at?->toISOString(),
            ]),
        ];
    }
}
