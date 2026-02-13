<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quote_number' => $this->quote_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->resource::STATUSES[$this->status] ?? $this->status,
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total' => $this->total,
            'currency' => $this->currency,
            'valid_until' => $this->valid_until?->toDateString(),
            'is_expired' => $this->isExpired(),
            'is_editable' => $this->isEditable(),
            'terms' => $this->terms,
            'notes' => $this->notes,
            'sent_at' => $this->sent_at?->toISOString(),
            'viewed_at' => $this->viewed_at?->toISOString(),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'client' => $this->when($this->relationLoaded('client'), function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                    'email' => $this->client->email,
                ];
            }),
            'items' => QuoteItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->when($this->items_count !== null, $this->items_count),
            'created_by' => $this->when($this->relationLoaded('createdBy'), function () {
                return $this->createdBy ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ] : null;
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
