<?php

namespace App\Models\Marketing;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailList extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'description',
        'type',
        'filters',
        'subscriber_count',
        'active_count',
        'unsubscribed_count',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'filters' => 'array',
        'subscriber_count' => 'integer',
        'active_count' => 'integer',
        'unsubscribed_count' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    const TYPE_STATIC = 'static';
    const TYPE_DYNAMIC = 'dynamic';

    // Relationships

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(EmailListSubscriber::class, 'list_id');
    }

    public function activeSubscribers(): HasMany
    {
        return $this->hasMany(EmailListSubscriber::class, 'list_id')
            ->where('status', 'subscribed');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeStatic($query)
    {
        return $query->where('type', self::TYPE_STATIC);
    }

    public function scopeDynamic($query)
    {
        return $query->where('type', self::TYPE_DYNAMIC);
    }

    // Methods

    public function addSubscriber(User $user, string $source = 'manual'): EmailListSubscriber
    {
        $subscriber = $this->subscribers()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $user->email,
                'name' => $user->name,
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'source' => $source,
            ]
        );

        $this->refreshCounts();

        return $subscriber;
    }

    public function addSubscriberByEmail(string $email, string $name = null, string $source = 'manual'): EmailListSubscriber
    {
        $subscriber = $this->subscribers()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'source' => $source,
            ]
        );

        $this->refreshCounts();

        return $subscriber;
    }

    public function removeSubscriber(int $userId): void
    {
        $this->subscribers()->where('user_id', $userId)->delete();
        $this->refreshCounts();
    }

    public function refreshCounts(): void
    {
        $this->update([
            'subscriber_count' => $this->subscribers()->count(),
            'active_count' => $this->subscribers()->where('status', 'subscribed')->count(),
            'unsubscribed_count' => $this->subscribers()->where('status', 'unsubscribed')->count(),
        ]);
    }

    public function getSubscriberEmails(): array
    {
        return $this->activeSubscribers()->pluck('email')->toArray();
    }

    public function isUserSubscribed(int $userId): bool
    {
        return $this->subscribers()
            ->where('user_id', $userId)
            ->where('status', 'subscribed')
            ->exists();
    }
}
