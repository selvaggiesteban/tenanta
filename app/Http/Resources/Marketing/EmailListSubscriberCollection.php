<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmailListSubscriberCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn($subscriber) => [
                'id' => $subscriber->id,
                'email' => $subscriber->email,
                'name' => $subscriber->name,
                'status' => $subscriber->status,
                'source' => $subscriber->source,
                'subscribed_at' => $subscriber->subscribed_at?->toISOString(),
            ]),
        ];
    }
}
