<?php

namespace App\Models\Marketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'email',
        'name',
        'merge_fields',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'unsubscribed_at',
        'open_count',
        'click_count',
        'error_code',
        'error_message',
        'message_id',
        'provider',
    ];

    protected $casts = [
        'merge_fields' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'open_count' => 'integer',
        'click_count' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_OPENED = 'opened';
    const STATUS_CLICKED = 'clicked';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_FAILED = 'failed';

    // Relationships

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(EmailEvent::class, 'recipient_id');
    }

    // Scopes

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSent($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_DELIVERED, self::STATUS_OPENED, self::STATUS_CLICKED]);
    }

    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }

    public function scopeClicked($query)
    {
        return $query->whereNotNull('clicked_at');
    }

    public function scopeBounced($query)
    {
        return $query->where('status', self::STATUS_BOUNCED);
    }

    // Accessors

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->email;
    }

    public function getHasOpenedAttribute(): bool
    {
        return $this->opened_at !== null;
    }

    public function getHasClickedAttribute(): bool
    {
        return $this->clicked_at !== null;
    }

    // Methods

    public function markAsSent(string $messageId, string $provider): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'message_id' => $messageId,
            'provider' => $provider,
        ]);

        $this->campaign->incrementStat('sent_count');
    }

    public function markAsDelivered(): void
    {
        if ($this->status === self::STATUS_SENT) {
            $this->update([
                'status' => self::STATUS_DELIVERED,
                'delivered_at' => now(),
            ]);

            $this->campaign->incrementStat('delivered_count');
        }
    }

    public function recordOpen(): void
    {
        $this->increment('open_count');

        if (!$this->opened_at) {
            $this->update([
                'status' => self::STATUS_OPENED,
                'opened_at' => now(),
            ]);

            $this->campaign->incrementStat('opened_count');
        }
    }

    public function recordClick(): void
    {
        $this->increment('click_count');

        if (!$this->clicked_at) {
            $this->update([
                'status' => self::STATUS_CLICKED,
                'clicked_at' => now(),
            ]);

            $this->campaign->incrementStat('clicked_count');
        }
    }

    public function markAsBounced(string $errorCode = null, string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_BOUNCED,
            'bounced_at' => now(),
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);

        $this->campaign->incrementStat('bounced_count');
    }

    public function markAsFailed(string $errorCode = null, string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }

    public function recordUnsubscribe(): void
    {
        $this->update(['unsubscribed_at' => now()]);
        $this->campaign->incrementStat('unsubscribed_count');
    }
}
