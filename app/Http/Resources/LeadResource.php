<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'status' => $this->status,
            'status_label' => $this->resource::STATUSES[$this->status] ?? $this->status,
            'source' => $this->source,
            'source_label' => $this->resource::SOURCES[$this->source] ?? $this->source,
            'estimated_value' => $this->estimated_value,
            'notes' => $this->notes,
            'is_converted' => $this->isConverted(),
            'can_be_converted' => $this->canBeConverted(),
            'assigned_to' => $this->when($this->relationLoaded('assignedTo'), function () {
                return $this->assignedTo ? [
                    'id' => $this->assignedTo->id,
                    'name' => $this->assignedTo->name,
                ] : null;
            }),
            'pipeline_id' => $this->pipeline_id,
            'pipeline' => $this->when($this->relationLoaded('pipeline'), function () {
                return $this->pipeline ? [
                    'id' => $this->pipeline->id,
                    'name' => $this->pipeline->name,
                ] : null;
            }),
            'pipeline_stage_id' => $this->pipeline_stage_id,
            'pipeline_stage' => $this->when($this->relationLoaded('pipelineStage'), function () {
                return $this->pipelineStage ? [
                    'id' => $this->pipelineStage->id,
                    'name' => $this->pipelineStage->name,
                    'color' => $this->pipelineStage->color,
                ] : null;
            }),
            'converted_client' => $this->when($this->relationLoaded('convertedClient'), function () {
                return $this->convertedClient ? [
                    'id' => $this->convertedClient->id,
                    'name' => $this->convertedClient->name,
                ] : null;
            }),
            'converted_at' => $this->converted_at?->toISOString(),
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
