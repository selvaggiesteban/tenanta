<?php

namespace App\Models\Omnichannel;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasUuids; // No usa BelongsToTenant porque hereda el scope de la conversación

    protected $table = 'omnichannel_messages';

    protected $fillable = [
        'conversation_id',
        'external_id',
        'type',
        'direction',
        'sender_name',
        'sender_identifier',
        'content',
        'content_type',
        'attachment_url',
        'status',
        'sentiment',
        'raw_payload'
    ];

    protected $casts = [
        'sentiment' => 'float',
        'raw_payload' => 'json',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
