<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'vat_number',
        'status',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'website',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('vat_number', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Helpers

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }
}
