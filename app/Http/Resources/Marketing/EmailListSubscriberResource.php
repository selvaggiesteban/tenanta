<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailListSubscriberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'status' => $this->status,
            'custom_fields' => $this->custom_fields,
            'source' => $this->source,
            'subscribed_at' => $this->subscribed_at?->toISOString(),
            'unsubscribed_at' => $this->unsubscribed_at?->toISOString(),
            'unsubscribe_reason' => $this->unsubscribe_reason,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
