<?php

namespace App\Models\Marketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailListSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'user_id',
        'email',
        'name',
        'custom_fields',
        'status',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_reason',
        'source',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUS_CLEANED = 'cleaned';
    const STATUS_PENDING = 'pending';

    // Relationships

    public function list(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeSubscribed($query)
    {
        return $query->where('status', self::STATUS_SUBSCRIBED);
    }

    public function scopeUnsubscribed($query)
    {
        return $query->where('status', self::STATUS_UNSUBSCRIBED);
    }

    // Accessors

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->email;
    }

    public function getIsSubscribedAttribute(): bool
    {
        return $this->status === self::STATUS_SUBSCRIBED;
    }

    // Methods

    public function unsubscribe(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
            'unsubscribe_reason' => $reason,
        ]);

        $this->list->refreshCounts();
    }

    public function resubscribe(): void
    {
        $this->update([
            'status' => self::STATUS_SUBSCRIBED,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'unsubscribe_reason' => null,
        ]);

        $this->list->refreshCounts();
    }
}
