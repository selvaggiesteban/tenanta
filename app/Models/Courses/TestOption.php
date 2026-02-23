<?php

namespace App\Models\Courses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'text',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships

    public function question(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }
}
