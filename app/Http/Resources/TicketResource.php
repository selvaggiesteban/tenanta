<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'category' => $this->category,
            'is_overdue' => $this->isOverdue(),
            'creator' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn() => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ] : null),
            'client' => $this->whenLoaded('client', fn() => $this->client ? [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ] : null),
            'replies' => $this->whenLoaded('replies', fn() => $this->replies->map(fn($r) => [
                'id' => $r->id,
                'content' => $r->content,
                'is_internal' => $r->is_internal,
                'user' => [
                    'id' => $r->user->id,
                    'name' => $r->user->name,
                ],
                'created_at' => $r->created_at->toISOString(),
            ])),
            'replies_count' => $this->when(isset($this->replies_count), $this->replies_count),
            'response_time' => $this->response_time,
            'resolution_time' => $this->resolution_time,
            'first_response_at' => $this->first_response_at?->toISOString(),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'due_at' => $this->due_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
