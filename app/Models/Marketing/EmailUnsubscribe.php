<?php

namespace App\Models\Marketing;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailUnsubscribe extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'campaign_id',
        'email',
        'reason',
        'feedback',
        'scope',
        'ip_address',
        'user_agent',
    ];

    const SCOPE_ALL = 'all';
    const SCOPE_CAMPAIGN_TYPE = 'campaign_type';
    const SCOPE_LIST = 'list';

    const REASONS = [
        'too_frequent' => 'Recibo demasiados emails',
        'not_relevant' => 'El contenido no es relevante',
        'never_subscribed' => 'No recuerdo haberme suscrito',
        'spam' => 'Esto es spam',
        'other' => 'Otro motivo',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    // Scopes

    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    // Static methods

    public static function isUnsubscribed(int $tenantId, string $email): bool
    {
        return static::where('tenant_id', $tenantId)
            ->where('email', $email)
            ->where('scope', self::SCOPE_ALL)
            ->exists();
    }

    public static function recordUnsubscribe(
        int $tenantId,
        string $email,
        ?int $userId = null,
        ?int $campaignId = null,
        ?string $reason = null,
        ?string $feedback = null,
        array $metadata = []
    ): self {
        return static::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'email' => $email,
            ],
            [
                'user_id' => $userId,
                'campaign_id' => $campaignId,
                'reason' => $reason,
                'feedback' => $feedback,
                'scope' => self::SCOPE_ALL,
                'ip_address' => $metadata['ip_address'] ?? null,
                'user_agent' => $metadata['user_agent'] ?? null,
            ]
        );
    }
}
