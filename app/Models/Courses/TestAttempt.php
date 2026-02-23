<?php

namespace App\Models\Courses;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_id',
        'enrollment_id',
        'score',
        'total_points',
        'percentage',
        'passed',
        'answers',
        'results',
        'started_at',
        'completed_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'score' => 'integer',
        'total_points' => 'integer',
        'percentage' => 'integer',
        'passed' => 'boolean',
        'answers' => 'array',
        'results' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(CourseTest::class, 'test_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'enrollment_id');
    }

    // Scopes

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    public function getIsInProgressAttribute(): bool
    {
        return $this->completed_at === null;
    }

    public function getFormattedTimeSpentAttribute(): string
    {
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getTimeRemainingSecondsAttribute(): ?int
    {
        if (!$this->is_in_progress) {
            return null;
        }

        $timeLimit = $this->test->time_limit_minutes;

        if (!$timeLimit) {
            return null;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        $remaining = ($timeLimit * 60) - $elapsed;

        return max(0, $remaining);
    }

    public function getIsTimedOutAttribute(): bool
    {
        $remaining = $this->time_remaining_seconds;

        return $remaining !== null && $remaining <= 0;
    }

    // Methods

    public function submitAnswers(array $answers): void
    {
        $test = $this->test;
        $questions = $test->questions()->with('options')->get();

        $score = 0;
        $totalPoints = 0;
        $results = [];

        foreach ($questions as $question) {
            $selectedOptionIds = $answers[$question->id] ?? [];

            if (!is_array($selectedOptionIds)) {
                $selectedOptionIds = [$selectedOptionIds];
            }

            $selectedOptionIds = array_map('intval', $selectedOptionIds);
            $pointsEarned = $question->calculateScore($selectedOptionIds);

            $results[$question->id] = [
                'correct' => $pointsEarned > 0,
                'points' => $pointsEarned,
                'selected' => $selectedOptionIds,
                'correct_options' => $question->correct_option_ids,
            ];

            $score += $pointsEarned;
            $totalPoints += $question->points;
        }

        $percentage = $totalPoints > 0 ? (int) round(($score / $totalPoints) * 100) : 0;
        $passed = $percentage >= $test->passing_score;

        $this->update([
            'answers' => $answers,
            'results' => $results,
            'score' => $score,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'passed' => $passed,
            'completed_at' => now(),
            'time_spent_seconds' => now()->diffInSeconds($this->started_at),
        ]);
    }
}
