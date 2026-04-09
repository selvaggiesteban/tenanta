<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseTopic extends Model
{
    protected $fillable = ['course_block_id', 'title', 'content', 'video_url', 'display_order', 'is_free'];

    public function block(): BelongsTo
    {
        return $this->belongsTo(CourseBlock::class, 'course_block_id');
    }
}
