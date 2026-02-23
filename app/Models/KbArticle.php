<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KbArticle extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'is_featured',
        'is_public',
        'author_id',
        'views',
        'helpful_yes',
        'helpful_no',
        'tags',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (KbArticle $article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function markHelpful(bool $helpful): void
    {
        if ($helpful) {
            $this->increment('helpful_yes');
        } else {
            $this->increment('helpful_no');
        }
    }

    public function getHelpfulPercentageAttribute(): ?float
    {
        $total = $this->helpful_yes + $this->helpful_no;
        if ($total === 0) {
            return null;
        }

        return round(($this->helpful_yes / $total) * 100, 1);
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($wordCount / 200));
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%");
        });
    }
}
