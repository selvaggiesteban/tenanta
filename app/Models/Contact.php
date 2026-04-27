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
        'company',
        'job_title',
        'industry',
        'activity',
        'linkedin_url',
        'maps_url',
        'address_details',
        'city',
        'province',
        'country',
        'deliverability_status',
        'whatsapp_received_at',
        'entity_type',
        'assigned_sender',
        'custom_fields',
        'phone',
        'mobile',
        'position',
        'department',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'custom_fields' => 'array',
        'whatsapp_received_at' => 'datetime',
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
