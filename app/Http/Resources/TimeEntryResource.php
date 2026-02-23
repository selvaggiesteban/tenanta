<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'started_at' => $this->started_at->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'duration' => $this->duration,
            'duration_minutes' => $this->is_running
                ? $this->started_at->diffInMinutes(now())
                : $this->duration_minutes,
            'duration_hours' => $this->duration_hours,
            'is_billable' => $this->is_billable,
            'is_running' => $this->is_running,
            'is_manual' => $this->is_manual,
            'is_overtime' => $this->is_overtime,
            'hourly_rate' => $this->hourly_rate,
            'cost' => $this->cost,
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email ?? null,
                ];
            }),
            'project' => $this->when($this->relationLoaded('project'), function () {
                return $this->project ? [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                ] : null;
            }),
            'task' => $this->when($this->relationLoaded('task'), function () {
                return $this->task ? [
                    'id' => $this->task->id,
                    'title' => $this->task->title,
                ] : null;
            }),
            'overtime_authorized_by' => $this->when($this->is_overtime && $this->relationLoaded('overtimeAuthorizedBy'), function () {
                return $this->overtimeAuthorizedBy ? [
                    'id' => $this->overtimeAuthorizedBy->id,
                    'name' => $this->overtimeAuthorizedBy->name,
                ] : null;
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
