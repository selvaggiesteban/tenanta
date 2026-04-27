<?php

namespace App\Models\Omnichannel;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use BelongsToTenant, HasUuids, SoftDeletes;

    protected $table = 'omnichannel_channels';

    protected $fillable = [
        'tenant_id',
        'type',
        'name',
        'provider_id',
        'credentials',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'credentials' => 'encrypted:json',
        'settings' => 'json',
        'is_active' => 'boolean',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
