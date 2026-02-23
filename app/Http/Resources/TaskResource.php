<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->resource::STATUSES[$this->status] ?? $this->status,
            'priority' => $this->priority,
            'priority_label' => $this->resource::PRIORITIES[$this->priority] ?? $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'estimated_hours' => $this->estimated_hours,
            'total_hours' => round($this->total_hours, 2),
            'sort_order' => $this->sort_order,
            'is_editable' => $this->isEditable(),
            'can_start' => $this->canStart(),
            'can_submit' => $this->canSubmit(),
            'can_approve' => $this->canApprove(),
            'can_complete' => $this->canComplete(),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
            'project' => $this->when($this->relationLoaded('project'), function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                ];
            }),
            'assignee' => $this->when($this->relationLoaded('assignee'), function () {
                return $this->assignee ? [
                    'id' => $this->assignee->id,
                    'name' => $this->assignee->name,
                    'email' => $this->assignee->email ?? null,
                ] : null;
            }),
            'reviewer' => $this->when($this->relationLoaded('reviewer'), function () {
                return $this->reviewer ? [
                    'id' => $this->reviewer->id,
                    'name' => $this->reviewer->name,
                    'email' => $this->reviewer->email ?? null,
                ] : null;
            }),
            'pipeline_stage' => $this->when($this->relationLoaded('pipelineStage'), function () {
                return $this->pipelineStage ? [
                    'id' => $this->pipelineStage->id,
                    'name' => $this->pipelineStage->name,
                    'color' => $this->pipelineStage->color,
                ] : null;
            }),
            'dependencies' => $this->when($this->relationLoaded('dependencies'), function () {
                return $this->dependencies->map(fn($dep) => [
                    'id' => $dep->id,
                    'title' => $dep->title,
                    'status' => $dep->status,
                ]);
            }),
            'dependents' => $this->when($this->relationLoaded('dependents'), function () {
                return $this->dependents->map(fn($dep) => [
                    'id' => $dep->id,
                    'title' => $dep->title,
                    'status' => $dep->status,
                ]);
            }),
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
