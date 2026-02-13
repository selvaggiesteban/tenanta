<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'name',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
        'completed_at',
        'budget',
        'hourly_rate',
        'is_billable',
        'manager_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'date',
        'budget' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_billable' => 'boolean',
    ];

    const STATUSES = [
        'planning' => 'En Planificación',
        'active' => 'Activo',
        'on_hold' => 'En Pausa',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
    ];

    const PRIORITIES = [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ];

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role', 'hourly_rate'])
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForManager($query, int $userId)
    {
        return $query->where('manager_id', $userId);
    }

    public function scopeForMember($query, int $userId)
    {
        return $query->whereHas('members', fn($q) => $q->where('user_id', $userId));
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    // Helpers

    public function addMember(User $user, string $role = 'member', ?float $hourlyRate = null): void
    {
        $this->members()->syncWithoutDetaching([
            $user->id => [
                'role' => $role,
                'hourly_rate' => $hourlyRate,
            ],
        ]);
    }

    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function isManager(User $user): bool
    {
        return $this->manager_id === $user->id;
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Cancel any open tasks
        $this->tasks()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update(['status' => 'cancelled']);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'active',
            'completed_at' => null,
        ]);
    }

    public function isEditable(): bool
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->tasks()->where('status', 'completed')->count();
        return (int) round(($completed / $total) * 100);
    }

    public function getTotalHoursAttribute(): float
    {
        return $this->timeEntries()->sum('duration_minutes') / 60;
    }

    public function getTotalCostAttribute(): float
    {
        // Sum up cost based on user hourly rates
        $total = 0;
        foreach ($this->timeEntries()->with('user')->get() as $entry) {
            $rate = $this->getMemberRate($entry->user) ?? $this->hourly_rate ?? 0;
            $total += ($entry->duration_minutes / 60) * $rate;
        }
        return $total;
    }

    public function getMemberRate(User $user): ?float
    {
        $pivot = $this->members()->where('user_id', $user->id)->first()?->pivot;
        return $pivot?->hourly_rate;
    }
}
