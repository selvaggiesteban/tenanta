<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subject' => $this->subject,
            'from_name' => $this->from_name,
            'from_email' => $this->from_email,
            'reply_to' => $this->reply_to,
            'content_html' => $this->content_html,
            'content_text' => $this->content_text,
            'type' => $this->type,
            'status' => $this->status,
            'settings' => $this->settings,
            'template_id' => $this->template_id,
            'template' => $this->whenLoaded('template', fn() => [
                'id' => $this->template->id,
                'name' => $this->template->name,
            ]),
            'stats' => [
                'recipient_count' => $this->recipient_count,
                'sent_count' => $this->sent_count,
                'delivered_count' => $this->delivered_count,
                'opened_count' => $this->opened_count,
                'clicked_count' => $this->clicked_count,
                'bounced_count' => $this->bounced_count,
                'unsubscribed_count' => $this->unsubscribed_count,
            ],
            'rates' => $this->when($this->sent_count > 0, fn() => [
                'delivery_rate' => $this->delivery_rate,
                'open_rate' => $this->open_rate,
                'click_rate' => $this->click_rate,
            ]),
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
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
