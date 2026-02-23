<?php

namespace App\Models\Courses;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'title',
        'content',
        'video_url',
        'video_provider',
        'video_duration_seconds',
        'pdf_url',
        'attachments',
        'sort_order',
        'is_free_preview',
    ];

    protected $casts = [
        'video_duration_seconds' => 'integer',
        'attachments' => 'array',
        'sort_order' => 'integer',
        'is_free_preview' => 'boolean',
    ];

    const VIDEO_PROVIDER_YOUTUBE = 'youtube';
    const VIDEO_PROVIDER_VIMEO = 'vimeo';
    const VIDEO_PROVIDER_WISTIA = 'wistia';
    const VIDEO_PROVIDER_LOCAL = 'local';

    // Relationships

    public function block(): BelongsTo
    {
        return $this->belongsTo(CourseBlock::class, 'block_id');
    }

    public function course(): BelongsTo
    {
        return $this->block->course();
    }

    public function progress(): HasMany
    {
        return $this->hasMany(TopicProgress::class, 'topic_id');
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeFreePreview($query)
    {
        return $query->where('is_free_preview', true);
    }

    // Accessors

    public function getFormattedDurationAttribute(): string
    {
        $totalSeconds = $this->video_duration_seconds;
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getHasVideoAttribute(): bool
    {
        return !empty($this->video_url);
    }

    public function getHasPdfAttribute(): bool
    {
        return !empty($this->pdf_url);
    }

    public function getHasAttachmentsAttribute(): bool
    {
        return !empty($this->attachments) && count($this->attachments) > 0;
    }

    // Methods

    public function getProgressForUser(User $user): ?TopicProgress
    {
        return $this->progress()
            ->where('user_id', $user->id)
            ->first();
    }

    public function isCompletedByUser(User $user): bool
    {
        $progress = $this->getProgressForUser($user);

        return $progress && $progress->is_completed;
    }

    public function getVideoEmbedUrl(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        return match ($this->video_provider) {
            self::VIDEO_PROVIDER_YOUTUBE => $this->getYoutubeEmbedUrl(),
            self::VIDEO_PROVIDER_VIMEO => $this->getVimeoEmbedUrl(),
            default => $this->video_url,
        };
    }

    protected function getYoutubeEmbedUrl(): string
    {
        // Extract video ID from various YouTube URL formats
        $videoId = $this->extractYoutubeId($this->video_url);

        return "https://www.youtube.com/embed/{$videoId}";
    }

    protected function getVimeoEmbedUrl(): string
    {
        // Extract video ID from Vimeo URL
        preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $matches);
        $videoId = $matches[1] ?? '';

        return "https://player.vimeo.com/video/{$videoId}";
    }

    protected function extractYoutubeId(string $url): string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([^&]+)/',
            '/youtu\.be\/([^?]+)/',
            '/youtube\.com\/embed\/([^?]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return $url;
    }
}
