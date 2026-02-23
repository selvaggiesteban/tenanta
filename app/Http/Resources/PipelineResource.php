<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PipelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->resource::TYPES[$this->type] ?? $this->type,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'stages' => PipelineStageResource::collection($this->whenLoaded('stages')),
            'leads_count' => $this->when($this->leads_count !== null, $this->leads_count),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
