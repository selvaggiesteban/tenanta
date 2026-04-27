<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeIndex extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'source_type',
        'source_id',
        'title',
        'content',
        'metadata',
        'hash',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
