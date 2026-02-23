<?php

namespace App\Models\Courses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'sort_order',
        'is_free_preview',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_free_preview' => 'boolean',
    ];

    // Relationships

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(CourseTopic::class, 'block_id')->orderBy('sort_order');
    }

    public function tests(): HasMany
    {
        return $this->hasMany(CourseTest::class, 'block_id');
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accessors

    public function getTotalTopicsAttribute(): int
    {
        return $this->topics()->count();
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->topics()->sum('video_duration_seconds');
    }

    public function getFormattedDurationAttribute(): string
    {
        $totalSeconds = $this->total_duration;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
