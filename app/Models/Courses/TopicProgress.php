<?php

namespace App\Models\Courses;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicProgress extends Model
{
    use HasFactory;

    protected $table = 'topic_progress';

    protected $fillable = [
        'user_id',
        'topic_id',
        'enrollment_id',
        'is_completed',
        'completed_at',
        'watch_time_seconds',
        'last_position_seconds',
        'last_watched_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'watch_time_seconds' => 'integer',
        'last_position_seconds' => 'integer',
        'last_watched_at' => 'datetime',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CourseTopic::class, 'topic_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'enrollment_id');
    }

    // Scopes

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEnrollment($query, int $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId);
    }

    // Accessors

    public function getWatchPercentageAttribute(): int
    {
        $topicDuration = $this->topic->video_duration_seconds;

        if ($topicDuration <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->watch_time_seconds / $topicDuration) * 100));
    }

    // Methods

    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Update enrollment progress
        $this->enrollment->calculateAndUpdateProgress();
    }

    public function updateWatchProgress(int $positionSeconds, int $watchedSeconds): void
    {
        $this->update([
            'last_position_seconds' => $positionSeconds,
            'watch_time_seconds' => max($this->watch_time_seconds, $watchedSeconds),
            'last_watched_at' => now(),
        ]);

        // Update enrollment last activity
        $this->enrollment->updateLastActivity();

        // Auto-complete if watched enough (e.g., 90% of video)
        $topicDuration = $this->topic->video_duration_seconds;
        if ($topicDuration > 0 && $watchedSeconds >= ($topicDuration * 0.9)) {
            if (!$this->is_completed) {
                $this->markAsCompleted();
            }
        }
    }
}
