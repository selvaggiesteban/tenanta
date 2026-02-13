<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'subtotal',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateSubtotal();
        });

        static::saved(function ($item) {
            $item->quote->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->quote->calculateTotals();
        });
    }

    // Relationships

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    // Helpers

    public function calculateSubtotal(): void
    {
        $baseAmount = $this->quantity * $this->unit_price;
        $discountAmount = $baseAmount * ($this->discount_percent / 100);
        $this->subtotal = $baseAmount - $discountAmount;
    }
}
