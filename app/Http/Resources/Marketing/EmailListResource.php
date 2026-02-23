<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'filters' => $this->filters,
            'subscriber_count' => $this->subscriber_count,
            'active_count' => $this->active_count,
            'unsubscribed_count' => $this->unsubscribed_count,
            'is_active' => $this->is_active,
            'is_default' => $this->is_default,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'subscribers' => EmailListSubscriberCollection::make($this->whenLoaded('subscribers')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
