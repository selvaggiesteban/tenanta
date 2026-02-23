<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_id',
        'campaign_id',
        'event_type',
        'url',
        'ip_address',
        'user_agent',
        'device_type',
        'client_name',
        'client_os',
        'country',
        'city',
        'raw_data',
        'occurred_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'occurred_at' => 'datetime',
    ];

    const TYPE_SENT = 'sent';
    const TYPE_DELIVERED = 'delivered';
    const TYPE_OPENED = 'opened';
    const TYPE_CLICKED = 'clicked';
    const TYPE_BOUNCED = 'bounced';
    const TYPE_COMPLAINED = 'complained';
    const TYPE_UNSUBSCRIBED = 'unsubscribed';

    // Relationships

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(EmailRecipient::class, 'recipient_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    // Scopes

    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeOpens($query)
    {
        return $query->where('event_type', self::TYPE_OPENED);
    }

    public function scopeClicks($query)
    {
        return $query->where('event_type', self::TYPE_CLICKED);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    // Static factory methods

    public static function recordSent(EmailRecipient $recipient): self
    {
        return static::create([
            'recipient_id' => $recipient->id,
            'campaign_id' => $recipient->campaign_id,
            'event_type' => self::TYPE_SENT,
            'occurred_at' => now(),
        ]);
    }

    public static function recordDelivered(EmailRecipient $recipient, array $data = []): self
    {
        return static::create([
            'recipient_id' => $recipient->id,
            'campaign_id' => $recipient->campaign_id,
            'event_type' => self::TYPE_DELIVERED,
            'raw_data' => $data,
            'occurred_at' => now(),
        ]);
    }

    public static function recordOpen(EmailRecipient $recipient, array $data = []): self
    {
        return static::create([
            'recipient_id' => $recipient->id,
            'campaign_id' => $recipient->campaign_id,
            'event_type' => self::TYPE_OPENED,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'device_type' => $data['device_type'] ?? null,
            'client_name' => $data['client_name'] ?? null,
            'client_os' => $data['client_os'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'raw_data' => $data,
            'occurred_at' => now(),
        ]);
    }

    public static function recordClick(EmailRecipient $recipient, string $url, array $data = []): self
    {
        return static::create([
            'recipient_id' => $recipient->id,
            'campaign_id' => $recipient->campaign_id,
            'event_type' => self::TYPE_CLICKED,
            'url' => $url,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'device_type' => $data['device_type'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'raw_data' => $data,
            'occurred_at' => now(),
        ]);
    }

    public static function recordBounce(EmailRecipient $recipient, array $data = []): self
    {
        return static::create([
            'recipient_id' => $recipient->id,
            'campaign_id' => $recipient->campaign_id,
            'event_type' => self::TYPE_BOUNCED,
            'raw_data' => $data,
            'occurred_at' => now(),
        ]);
    }
}
