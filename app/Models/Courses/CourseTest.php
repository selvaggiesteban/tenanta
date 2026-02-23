<?php

namespace App\Models\Courses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'block_id',
        'title',
        'description',
        'type',
        'time_limit_minutes',
        'passing_score',
        'max_attempts',
        'show_answers_after',
        'shuffle_questions',
        'shuffle_options',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'time_limit_minutes' => 'integer',
        'passing_score' => 'integer',
        'max_attempts' => 'integer',
        'show_answers_after' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    const TYPE_QUIZ = 'quiz';
    const TYPE_EXAM = 'exam';
    const TYPE_PRACTICE = 'practice';

    const TYPES = [
        self::TYPE_QUIZ => 'Quiz',
        self::TYPE_EXAM => 'Examen',
        self::TYPE_PRACTICE => 'Práctica',
    ];

    // Relationships

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(CourseBlock::class, 'block_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class, 'test_id')->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'test_id');
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    // Accessors

    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->questions()->sum('points');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getHasTimeLimitAttribute(): bool
    {
        return $this->time_limit_minutes !== null && $this->time_limit_minutes > 0;
    }

    public function getHasUnlimitedAttemptsAttribute(): bool
    {
        return $this->max_attempts === 0;
    }

    // Methods

    public function getAttemptsForUser(int $userId): int
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->count();
    }

    public function canUserAttempt(int $userId): bool
    {
        if ($this->has_unlimited_attempts) {
            return true;
        }

        return $this->getAttemptsForUser($userId) < $this->max_attempts;
    }

    public function getRemainingAttempts(int $userId): ?int
    {
        if ($this->has_unlimited_attempts) {
            return null;
        }

        return max(0, $this->max_attempts - $this->getAttemptsForUser($userId));
    }

    public function getBestAttempt(int $userId): ?TestAttempt
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->orderByDesc('percentage')
            ->first();
    }

    public function hasUserPassed(int $userId): bool
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->where('passed', true)
            ->exists();
    }
}
