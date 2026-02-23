<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'provider' => $this->provider,
            'model' => $this->model,
            'total_tokens' => $this->getTotalTokens(),
            'usage' => [
                'input_tokens' => $this->total_input_tokens,
                'output_tokens' => $this->total_output_tokens,
            ],
            'last_message_at' => $this->last_message_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
