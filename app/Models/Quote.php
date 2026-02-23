<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'quote_number',
        'title',
        'description',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'currency',
        'valid_until',
        'terms',
        'notes',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    const STATUSES = [
        'draft' => 'Borrador',
        'sent' => 'Enviado',
        'viewed' => 'Visto',
        'accepted' => 'Aceptado',
        'rejected' => 'Rechazado',
        'expired' => 'Expirado',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (!$quote->quote_number) {
                $quote->quote_number = static::generateQuoteNumber($quote->tenant_id);
            }
        });
    }

    public static function generateQuoteNumber(int $tenantId): string
    {
        $year = date('Y');
        $lastQuote = static::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastQuote ? (int) substr($lastQuote->quote_number, -4) + 1 : 1;

        return sprintf('PRE-%s-%04d', $year, $number);
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    // Helpers

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum('subtotal');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $total = $subtotal + $taxAmount - $this->discount_amount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => max(0, $total),
        ]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now(),
            ]);
        }
    }

    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft']);
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }
}
