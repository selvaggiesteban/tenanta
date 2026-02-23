<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'logo_light',
        'logo_dark',
        'favicon',
        'primary_color',
        'secondary_color',
        'plan_id',
        'trial_ends_at',
        'settings',
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_links',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'locale',
        'timezone',
        'currency',
        'date_format',
    ];

    protected $casts = [
        'settings' => 'array',
        'social_links' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
