<?php

namespace App\Models\Marketing;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'template_id',
        'created_by',
        'name',
        'subject',
        'preview_text',
        'html_content',
        'text_content',
        'type',
        'audience_type',
        'audience_filters',
        'recipient_ids',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'from_name',
        'from_email',
        'reply_to',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'unsubscribed_count',
        'complained_count',
        'track_opens',
        'track_clicks',
    ];

    protected $casts = [
        'audience_filters' => 'array',
        'recipient_ids' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'bounced_count' => 'integer',
        'unsubscribed_count' => 'integer',
        'complained_count' => 'integer',
        'track_opens' => 'boolean',
        'track_clicks' => 'boolean',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_SENDING = 'sending';
    const STATUS_SENT = 'sent';
    const STATUS_PAUSED = 'paused';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_DRAFT => 'Borrador',
        self::STATUS_SCHEDULED => 'Programada',
        self::STATUS_SENDING => 'Enviando',
        self::STATUS_SENT => 'Enviada',
        self::STATUS_PAUSED => 'Pausada',
        self::STATUS_CANCELLED => 'Cancelada',
    ];

    const TYPE_REGULAR = 'regular';
    const TYPE_AUTOMATED = 'automated';
    const TYPE_AB_TEST = 'ab_test';

    const AUDIENCE_ALL = 'all';
    const AUDIENCE_SEGMENT = 'segment';
    const AUDIENCE_MANUAL = 'manual';
    const AUDIENCE_LIST = 'list';

    // Relationships

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class, 'campaign_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(EmailEvent::class, 'campaign_id');
    }

    // Scopes

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeReadyToSend($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now());
    }

    // Accessors

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->delivered_count === 0) return 0;
        return round(($this->opened_count / $this->delivered_count) * 100, 2);
    }

    public function getClickRateAttribute(): float
    {
        if ($this->delivered_count === 0) return 0;
        return round(($this->clicked_count / $this->delivered_count) * 100, 2);
    }

    public function getBounceRateAttribute(): float
    {
        if ($this->sent_count === 0) return 0;
        return round(($this->bounced_count / $this->sent_count) * 100, 2);
    }

    public function getDeliveryRateAttribute(): float
    {
        if ($this->sent_count === 0) return 0;
        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getIsSendingAttribute(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    public function getIsSentAttribute(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function getCanEditAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    public function getCanSendAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->total_recipients > 0;
    }

    // Methods

    public function schedule(\DateTime $scheduledAt): void
    {
        $this->update([
            'status' => self::STATUS_SCHEDULED,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    public function pause(): void
    {
        if ($this->status === self::STATUS_SENDING) {
            $this->update(['status' => self::STATUS_PAUSED]);
        }
    }

    public function resume(): void
    {
        if ($this->status === self::STATUS_PAUSED) {
            $this->update(['status' => self::STATUS_SENDING]);
        }
    }

    public function cancel(): void
    {
        if (in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_PAUSED])) {
            $this->update(['status' => self::STATUS_CANCELLED]);
        }
    }

    public function markAsSending(): void
    {
        $this->update([
            'status' => self::STATUS_SENDING,
            'started_at' => now(),
        ]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'completed_at' => now(),
        ]);
    }

    public function incrementStat(string $stat, int $amount = 1): void
    {
        $validStats = ['sent_count', 'delivered_count', 'opened_count', 'clicked_count', 'bounced_count', 'unsubscribed_count', 'complained_count'];

        if (in_array($stat, $validStats)) {
            $this->increment($stat, $amount);
        }
    }

    public function duplicate(): self
    {
        $clone = $this->replicate([
            'status', 'scheduled_at', 'started_at', 'completed_at',
            'total_recipients', 'sent_count', 'delivered_count',
            'opened_count', 'clicked_count', 'bounced_count',
            'unsubscribed_count', 'complained_count'
        ]);

        $clone->name = $this->name . ' (Copia)';
        $clone->status = self::STATUS_DRAFT;
        $clone->save();

        return $clone;
    }
}
