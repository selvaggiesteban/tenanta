<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PipelineStageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pipeline_id' => $this->pipeline_id,
            'name' => $this->name,
            'color' => $this->color,
            'sort_order' => $this->sort_order,
            'probability' => $this->probability,
            'is_won' => $this->is_won,
            'is_lost' => $this->is_lost,
            'is_terminal' => $this->isTerminal(),
            'leads_count' => $this->when($this->leads_count !== null, $this->leads_count),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
