<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'content',
        'status',
        'metadata',
    ];

    protected $casts = [
        'content' => 'array',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
