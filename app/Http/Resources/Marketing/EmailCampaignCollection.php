<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmailCampaignCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn($campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'subject' => $campaign->subject,
                'type' => $campaign->type,
                'status' => $campaign->status,
                'recipient_count' => $campaign->recipient_count,
                'sent_count' => $campaign->sent_count,
                'open_rate' => $campaign->sent_count > 0 ? $campaign->open_rate : null,
                'click_rate' => $campaign->sent_count > 0 ? $campaign->click_rate : null,
                'scheduled_at' => $campaign->scheduled_at?->toISOString(),
                'completed_at' => $campaign->completed_at?->toISOString(),
                'created_at' => $campaign->created_at?->toISOString(),
            ]),
        ];
    }
}
