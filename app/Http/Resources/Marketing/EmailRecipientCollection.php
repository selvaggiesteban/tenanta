<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmailRecipientCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn($recipient) => [
                'id' => $recipient->id,
                'email' => $recipient->email,
                'name' => $recipient->name,
                'status' => $recipient->status,
                'sent_at' => $recipient->sent_at?->toISOString(),
                'opened_at' => $recipient->opened_at?->toISOString(),
                'clicked_at' => $recipient->clicked_at?->toISOString(),
                'open_count' => $recipient->open_count,
                'click_count' => $recipient->click_count,
            ]),
        ];
    }
}
