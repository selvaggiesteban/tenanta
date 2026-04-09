<?php

namespace App\Models\Courses;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'status',
        'payment_id',
        'payment_provider',
        'payment_provider_id',
        'payment_method',
        'payment_data',
        'last_payment_at',
        'next_payment_at',
        'amount',
        'currency',
        'external_reference',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'next_payment_at' => 'datetime',
        'payment_data' => 'array',
        'amount' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAST_DUE = 'past_due';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    const STATUSES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_TRIAL => 'Prueba',
        self::STATUS_ACTIVE => 'Activa',
        self::STATUS_PAST_DUE => 'Vencida',
        self::STATUS_CANCELLED => 'Cancelada',
        self::STATUS_EXPIRED => 'Expirada',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('ends_at', '>', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_EXPIRED)
                ->orWhere(function ($q2) {
                    $q2->where('status', self::STATUS_ACTIVE)
                        ->where('ends_at', '<=', now());
                });
        });
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->ends_at->isFuture();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->ends_at->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->is_expired) {
            return 0;
        }

        return (int) now()->diffInDays($this->ends_at, false);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_expired && $this->status === self::STATUS_ACTIVE) {
            return self::STATUSES[self::STATUS_EXPIRED];
        }

        return self::STATUSES[$this->status] ?? $this->status;
    }

    // Methods

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    public function activate(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public function canAccessCourse(int $courseId): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->plan->includesCourse($courseId);
    }
}
