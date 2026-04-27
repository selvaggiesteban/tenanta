<?php

namespace App\Models\Omnichannel;

use App\Traits\BelongsToTenant;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use BelongsToTenant, HasUuids;

    protected $table = 'omnichannel_conversations';

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'contact_id',
        'external_id',
        'subject',
        'status',
        'assigned_to',
        'last_message_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'json',
        'last_message_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
