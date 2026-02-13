<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->resource::STATUSES[$this->status] ?? $this->status,
            'priority' => $this->priority,
            'priority_label' => $this->resource::PRIORITIES[$this->priority] ?? $this->priority,
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toDateString(),
            'budget' => $this->budget,
            'hourly_rate' => $this->hourly_rate,
            'is_billable' => $this->is_billable,
            'is_editable' => $this->isEditable(),
            'progress' => $this->progress,
            'total_hours' => $this->when($this->relationLoaded('timeEntries'), fn() => round($this->total_hours, 2)),
            'total_cost' => $this->when($this->relationLoaded('timeEntries'), fn() => round($this->total_cost, 2)),
            'client' => $this->when($this->relationLoaded('client'), function () {
                return $this->client ? [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                ] : null;
            }),
            'manager' => $this->when($this->relationLoaded('manager'), function () {
                return $this->manager ? [
                    'id' => $this->manager->id,
                    'name' => $this->manager->name,
                    'email' => $this->manager->email ?? null,
                ] : null;
            }),
            'members' => $this->when($this->relationLoaded('members'), function () {
                return $this->members->map(fn($member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email ?? null,
                    'role' => $member->pivot->role,
                    'hourly_rate' => $member->pivot->hourly_rate,
                ]);
            }),
            'tasks_count' => $this->when($this->tasks_count !== null, $this->tasks_count),
            'members_count' => $this->when($this->members_count !== null, $this->members_count),
            'time_entries_count' => $this->when($this->time_entries_count !== null, $this->time_entries_count),
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
