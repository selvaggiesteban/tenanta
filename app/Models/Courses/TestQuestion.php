<?php

namespace App\Models\Courses;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'question',
        'explanation',
        'type',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    const TYPE_SINGLE = 'single';
    const TYPE_MULTIPLE = 'multiple';
    const TYPE_TRUE_FALSE = 'true_false';

    const TYPES = [
        self::TYPE_SINGLE => 'Opción única',
        self::TYPE_MULTIPLE => 'Opción múltiple',
        self::TYPE_TRUE_FALSE => 'Verdadero/Falso',
    ];

    // Relationships

    public function test(): BelongsTo
    {
        return $this->belongsTo(CourseTest::class, 'test_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(TestOption::class, 'question_id')->orderBy('sort_order');
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accessors

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getCorrectOptionsAttribute()
    {
        return $this->options()->where('is_correct', true)->get();
    }

    public function getCorrectOptionIdsAttribute(): array
    {
        return $this->options()
            ->where('is_correct', true)
            ->pluck('id')
            ->toArray();
    }

    // Methods

    public function isAnswerCorrect(array $selectedOptionIds): bool
    {
        $correctIds = $this->correct_option_ids;

        sort($selectedOptionIds);
        sort($correctIds);

        return $selectedOptionIds === $correctIds;
    }

    public function calculateScore(array $selectedOptionIds): int
    {
        if ($this->isAnswerCorrect($selectedOptionIds)) {
            return $this->points;
        }

        return 0;
    }
}
