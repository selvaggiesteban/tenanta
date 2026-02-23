<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'number',
        'subject',
        'description',
        'status',
        'priority',
        'category',
        'created_by',
        'assigned_to',
        'client_id',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'due_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->number)) {
                $ticket->number = static::generateNumber($ticket->tenant_id);
            }
        });
    }

    public static function generateNumber(int $tenantId): string
    {
        $count = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->count();

        return 'TKT-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at');
    }

    public function assign(User $user): void
    {
        $this->update([
            'assigned_to' => $user->id,
            'status' => $this->status === 'open' ? 'in_progress' : $this->status,
        ]);
    }

    public function addReply(string $content, User $user, bool $isInternal = false): TicketReply
    {
        $reply = $this->replies()->create([
            'user_id' => $user->id,
            'content' => $content,
            'is_internal' => $isInternal,
        ]);

        if (!$this->first_response_at && $user->id === $this->assigned_to) {
            $this->update(['first_response_at' => now()]);
        }

        return $reply;
    }

    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast() && !in_array($this->status, ['resolved', 'closed']);
    }

    public function getResponseTimeAttribute(): ?int
    {
        if (!$this->first_response_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->first_response_at);
    }

    public function getResolutionTimeAttribute(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffInMinutes($this->resolved_at);
    }
}
