<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'provider',
        'model',
        'metadata',
        'total_input_tokens',
        'total_output_tokens',
        'last_message_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Get messages formatted for AI provider.
     */
    public function getMessagesForAI(): array
    {
        return $this->messages
            ->filter(fn($m) => $m->role !== 'system')
            ->map(function ($message) {
                $formatted = [
                    'role' => $message->role,
                    'content' => $message->content,
                ];

                if ($message->tool_calls) {
                    $formatted['tool_calls'] = $message->tool_calls;
                }

                if ($message->tool_results) {
                    $formatted['tool_results'] = $message->tool_results;
                }

                if ($message->tool_call_id) {
                    $formatted['tool_call_id'] = $message->tool_call_id;
                }

                return $formatted;
            })
            ->values()
            ->toArray();
    }

    /**
     * Add a message to the conversation.
     */
    public function addMessage(array $data): Message
    {
        $message = $this->messages()->create($data);

        $this->update([
            'last_message_at' => now(),
            'total_input_tokens' => $this->total_input_tokens + ($data['input_tokens'] ?? 0),
            'total_output_tokens' => $this->total_output_tokens + ($data['output_tokens'] ?? 0),
        ]);

        return $message;
    }

    /**
     * Generate a title from the first user message.
     */
    public function generateTitle(): void
    {
        if ($this->title) {
            return;
        }

        $firstUserMessage = $this->messages()->where('role', 'user')->first();

        if ($firstUserMessage) {
            $this->update([
                'title' => str($firstUserMessage->content)->limit(50)->toString(),
            ]);
        }
    }

    /**
     * Get total tokens used.
     */
    public function getTotalTokens(): int
    {
        return $this->total_input_tokens + $this->total_output_tokens;
    }
}
