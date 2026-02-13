<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tool_calls',
        'tool_results',
        'tool_call_id',
        'input_tokens',
        'output_tokens',
        'model',
        'metadata',
    ];

    protected $casts = [
        'tool_calls' => 'array',
        'tool_results' => 'array',
        'metadata' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Check if this is a user message.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if this is an assistant message.
     */
    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Check if this message has tool calls.
     */
    public function hasToolCalls(): bool
    {
        return !empty($this->tool_calls);
    }

    /**
     * Check if this is a tool result message.
     */
    public function isToolResult(): bool
    {
        return $this->role === 'tool' || !empty($this->tool_results);
    }
}
