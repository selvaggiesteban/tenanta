<?php

namespace App\Models\Omnichannel;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CannedResponse extends Model
{
    use BelongsToTenant;

    protected $table = 'omnichannel_canned_responses';

    protected $fillable = [
        'tenant_id',
        'shortcut',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
