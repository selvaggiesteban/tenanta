<?php

namespace App\Models\Courses;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'duration_days',
        'features',
        'course_ids',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'array',
        'course_ids' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    // Relationships

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', Subscription::STATUS_ACTIVE);
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // Accessors

    public function getFormattedPriceAttribute(): string
    {
        $symbol = match ($this->currency) {
            'USD' => 'US$',
            'EUR' => '€',
            default => '$',
        };

        return $symbol . number_format($this->price, 2, ',', '.');
    }

    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_days >= 365) {
            $years = floor($this->duration_days / 365);
            return $years === 1 ? '1 año' : "{$years} años";
        }

        if ($this->duration_days >= 30) {
            $months = floor($this->duration_days / 30);
            return $months === 1 ? '1 mes' : "{$months} meses";
        }

        return $this->duration_days === 1 ? '1 día' : "{$this->duration_days} días";
    }

    public function getIsUnlimitedCoursesAttribute(): bool
    {
        return empty($this->course_ids);
    }

    // Methods

    public function includesCourse(int $courseId): bool
    {
        // null or empty = all courses
        if (empty($this->course_ids)) {
            return true;
        }

        return in_array($courseId, $this->course_ids);
    }

    public function getIncludedCourses()
    {
        if (empty($this->course_ids)) {
            return Course::active()->get();
        }

        return Course::whereIn('id', $this->course_ids)->active()->get();
    }
}
