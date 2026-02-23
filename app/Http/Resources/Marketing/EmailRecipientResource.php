<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailRecipientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'status' => $this->status,
            'merge_fields' => $this->merge_fields,
            'sent_at' => $this->sent_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'opened_at' => $this->opened_at?->toISOString(),
            'clicked_at' => $this->clicked_at?->toISOString(),
            'bounced_at' => $this->bounced_at?->toISOString(),
            'unsubscribed_at' => $this->unsubscribed_at?->toISOString(),
            'open_count' => $this->open_count,
            'click_count' => $this->click_count,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
        ];
    }
}
