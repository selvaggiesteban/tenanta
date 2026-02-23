<?php

namespace App\Models\Courses;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseEnrollment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'course_id',
        'subscription_id',
        'enrolled_at',
        'started_at',
        'completed_at',
        'progress_percentage',
        'last_activity_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function topicProgress(): HasMany
    {
        return $this->hasMany(TopicProgress::class, 'enrollment_id');
    }

    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'enrollment_id');
    }

    // Scopes

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at')
            ->where('progress_percentage', '>', 0);
    }

    public function scopeNotStarted($query)
    {
        return $query->whereNull('started_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('last_activity_at');
    }

    // Accessors

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    public function getIsStartedAttribute(): bool
    {
        return $this->started_at !== null;
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_completed) {
            return 'completed';
        }

        if ($this->progress_percentage > 0) {
            return 'in_progress';
        }

        return 'not_started';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'Completado',
            'in_progress' => 'En progreso',
            'not_started' => 'No iniciado',
            default => 'Desconocido',
        };
    }

    // Methods

    public function markAsStarted(): void
    {
        if (!$this->started_at) {
            $this->update([
                'started_at' => now(),
                'last_activity_at' => now(),
            ]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'progress_percentage' => 100,
            'last_activity_at' => now(),
        ]);
    }

    public function updateProgress(int $percentage): void
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
            'last_activity_at' => now(),
        ]);

        if ($percentage >= 100 && !$this->completed_at) {
            $this->markAsCompleted();
        }
    }

    public function updateLastActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
        ]);
    }

    public function calculateAndUpdateProgress(): int
    {
        $totalTopics = $this->course->topics()->count();

        if ($totalTopics === 0) {
            return 0;
        }

        $completedTopics = $this->topicProgress()
            ->where('is_completed', true)
            ->count();

        $percentage = (int) round(($completedTopics / $totalTopics) * 100);

        $this->updateProgress($percentage);

        return $percentage;
    }
}
