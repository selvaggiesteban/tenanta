<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'assignee_id',
        'reviewer_id',
        'pipeline_stage_id',
        'due_date',
        'estimated_hours',
        'sort_order',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'completed_at',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUSES = [
        'pending' => 'Pendiente',
        'in_progress' => 'En Progreso',
        'review' => 'En Revisión',
        'approved' => 'Aprobada',
        'rejected' => 'Rechazada',
        'completed' => 'Completada',
        'cancelled' => 'Cancelada',
    ];

    const PRIORITIES = [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ];

    // Relationships

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_id')
            ->withTimestamps();
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_id', 'task_id')
            ->withTimestamps();
    }

    // Scopes

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForAssignee($query, int $userId)
    {
        return $query->where('assignee_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress', 'rejected']);
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'review');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['approved', 'completed']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled', 'approved']);
    }

    // Workflow Methods

    public function start(): void
    {
        if (!$this->canStart()) {
            throw new \Exception('Esta tarea no puede ser iniciada');
        }

        $this->update(['status' => 'in_progress']);
    }

    public function submit(): void
    {
        if (!$this->canSubmit()) {
            throw new \Exception('Esta tarea no puede ser enviada a revisión');
        }

        $this->update([
            'status' => 'review',
            'submitted_at' => now(),
        ]);
    }

    public function approve(): void
    {
        if (!$this->canApprove()) {
            throw new \Exception('Esta tarea no puede ser aprobada');
        }

        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(string $reason): void
    {
        if (!$this->canReject()) {
            throw new \Exception('Esta tarea no puede ser rechazada');
        }

        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function complete(): void
    {
        if (!$this->canComplete()) {
            throw new \Exception('Esta tarea no puede ser completada');
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'pending',
            'submitted_at' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'completed_at' => null,
            'rejection_reason' => null,
        ]);
    }

    // Permission Checks

    public function canStart(): bool
    {
        if (!in_array($this->status, ['pending', 'rejected'])) {
            return false;
        }

        // Check if dependencies are completed
        foreach ($this->dependencies as $dependency) {
            if (!in_array($dependency->status, ['completed', 'approved'])) {
                return false;
            }
        }

        return true;
    }

    public function canSubmit(): bool
    {
        return in_array($this->status, ['in_progress', 'rejected']);
    }

    public function canApprove(): bool
    {
        return $this->status === 'review';
    }

    public function canReject(): bool
    {
        return $this->status === 'review';
    }

    public function canComplete(): bool
    {
        return in_array($this->status, ['approved', 'in_progress']);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'in_progress', 'rejected']);
    }

    // Helpers

    public function addDependency(Task $task): void
    {
        if ($this->wouldCreateCircularDependency($task)) {
            throw new \Exception('Esto crearía una dependencia circular');
        }

        $this->dependencies()->attach($task->id);
    }

    public function removeDependency(Task $task): void
    {
        $this->dependencies()->detach($task->id);
    }

    public function wouldCreateCircularDependency(Task $task): bool
    {
        // Check if adding this dependency would create a circle
        if ($task->id === $this->id) {
            return true;
        }

        $visited = [];
        return $this->hasPathTo($task, $visited);
    }

    private function hasPathTo(Task $target, array &$visited): bool
    {
        if (in_array($target->id, $visited)) {
            return false;
        }

        $visited[] = $target->id;

        foreach ($target->dependencies as $dep) {
            if ($dep->id === $this->id) {
                return true;
            }
            if ($this->hasPathTo($dep, $visited)) {
                return true;
            }
        }

        return false;
    }

    public function getTotalHoursAttribute(): float
    {
        return $this->timeEntries()->sum('duration_minutes') / 60;
    }
}
