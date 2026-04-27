<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'title', 'slug', 'description', 'image_url', 'is_active'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(CourseBlock::class)->orderBy('display_order');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CourseSchedule::class)->orderBy('start_date');
    }
}
