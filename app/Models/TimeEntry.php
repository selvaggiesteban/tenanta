<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TimeEntry extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'project_id',
        'task_id',
        'description',
        'started_at',
        'ended_at',
        'duration_minutes',
        'is_billable',
        'hourly_rate',
        'is_running',
        'is_manual',
        'is_overtime',
        'overtime_authorized_by',
        'overtime_authorized_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'overtime_authorized_at' => 'datetime',
        'is_billable' => 'boolean',
        'is_running' => 'boolean',
        'is_manual' => 'boolean',
        'is_overtime' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($entry) {
            // Calculate duration if both times are set and not running
            if ($entry->started_at && $entry->ended_at && !$entry->is_running) {
                $entry->duration_minutes = $entry->started_at->diffInMinutes($entry->ended_at);
            }
        });
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function overtimeAuthorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overtime_authorized_by');
    }

    // Scopes

    public function scopeRunning($query)
    {
        return $query->where('is_running', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForTask($query, int $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    public function scopeInDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('started_at', [$start, $end]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('started_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('started_at', now()->month)
            ->whereYear('started_at', now()->year);
    }

    // Helpers

    public function stop(): void
    {
        if (!$this->is_running) {
            return;
        }

        $this->update([
            'ended_at' => now(),
            'is_running' => false,
        ]);
    }

    public function cancel(): void
    {
        if (!$this->is_running) {
            throw new \Exception('Solo se pueden cancelar entradas en ejecución');
        }

        $this->delete();
    }

    public function getDurationAttribute(): string
    {
        $minutes = $this->is_running
            ? $this->started_at->diffInMinutes(now())
            : $this->duration_minutes;

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function getDurationHoursAttribute(): float
    {
        $minutes = $this->is_running
            ? $this->started_at->diffInMinutes(now())
            : $this->duration_minutes;

        return round($minutes / 60, 2);
    }

    public function getCostAttribute(): float
    {
        $rate = $this->hourly_rate ?? 0;
        return $this->duration_hours * $rate;
    }

    // Static helpers for timer

    public static function startTimer(
        int $userId,
        ?int $projectId = null,
        ?int $taskId = null,
        ?string $description = null
    ): self {
        // Check if user already has a running timer
        $running = static::where('user_id', $userId)->running()->first();
        if ($running) {
            throw new \Exception('Ya tienes un timer en ejecución');
        }

        return static::create([
            'user_id' => $userId,
            'project_id' => $projectId,
            'task_id' => $taskId,
            'description' => $description,
            'started_at' => now(),
            'is_running' => true,
            'is_billable' => $projectId ? Project::find($projectId)?->is_billable ?? true : true,
        ]);
    }

    public static function getCurrentTimer(int $userId): ?self
    {
        return static::where('user_id', $userId)->running()->first();
    }

    public static function createManualEntry(array $data): self
    {
        $data['is_manual'] = true;
        $data['is_running'] = false;

        return static::create($data);
    }
}
