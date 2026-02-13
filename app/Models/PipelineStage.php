<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'color',
        'sort_order',
        'probability',
        'is_won',
        'is_lost',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'probability' => 'decimal:2',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
    ];

    // Relationships

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'pipeline_stage_id');
    }

    // Helpers

    public function moveUp(): bool
    {
        $previousStage = PipelineStage::where('pipeline_id', $this->pipeline_id)
            ->where('sort_order', '<', $this->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if (!$previousStage) {
            return false;
        }

        return $this->swapOrderWith($previousStage);
    }

    public function moveDown(): bool
    {
        $nextStage = PipelineStage::where('pipeline_id', $this->pipeline_id)
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();

        if (!$nextStage) {
            return false;
        }

        return $this->swapOrderWith($nextStage);
    }

    public function swapOrderWith(PipelineStage $other): bool
    {
        $thisOrder = $this->sort_order;
        $otherOrder = $other->sort_order;

        $this->update(['sort_order' => $otherOrder]);
        $other->update(['sort_order' => $thisOrder]);

        return true;
    }

    public function isTerminal(): bool
    {
        return $this->is_won || $this->is_lost;
    }
}
