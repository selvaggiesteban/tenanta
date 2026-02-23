<?php

namespace App\Models\Courses;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'description',
        'short_description',
        'price',
        'currency',
        'image_path',
        'video_preview_url',
        'level',
        'duration_hours',
        'is_active',
        'is_featured',
        'sort_order',
        'requirements',
        'objectives',
        'meta',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_hours' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'requirements' => 'array',
        'objectives' => 'array',
        'meta' => 'array',
    ];

    const LEVEL_BEGINNER = 'beginner';
    const LEVEL_INTERMEDIATE = 'intermediate';
    const LEVEL_ADVANCED = 'advanced';

    const LEVELS = [
        self::LEVEL_BEGINNER => 'Principiante',
        self::LEVEL_INTERMEDIATE => 'Intermedio',
        self::LEVEL_ADVANCED => 'Avanzado',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    // Relationships

    public function blocks(): HasMany
    {
        return $this->hasMany(CourseBlock::class)->orderBy('sort_order');
    }

    public function topics(): HasMany
    {
        return $this->hasManyThrough(
            CourseTopic::class,
            CourseBlock::class,
            'course_id',
            'block_id'
        );
    }

    public function tests(): HasMany
    {
        return $this->hasMany(CourseTest::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot('enrolled_at', 'started_at', 'completed_at', 'progress_percentage')
            ->withTimestamps();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%");
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
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

    public function getIsFreeAttribute(): bool
    {
        return $this->price <= 0;
    }

    // Methods

    public function isEnrolled(User $user): bool
    {
        return $this->enrollments()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function getEnrollment(User $user): ?CourseEnrollment
    {
        return $this->enrollments()
            ->where('user_id', $user->id)
            ->first();
    }

    public function calculateProgress(User $user): int
    {
        $enrollment = $this->getEnrollment($user);

        if (!$enrollment) {
            return 0;
        }

        $totalTopics = $this->topics()->count();

        if ($totalTopics === 0) {
            return 0;
        }

        $completedTopics = TopicProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        return (int) round(($completedTopics / $totalTopics) * 100);
    }
}
