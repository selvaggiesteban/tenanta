<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'name',
        'email',
        'phone',
        'mobile',
        'position',
        'department',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Helpers

    public function makePrimary(): void
    {
        // Remove primary from other contacts of same client
        static::where('client_id', $this->client_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }
}
