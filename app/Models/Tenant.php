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
        'category',
        'slug',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'features',
        'services',
        'faqs',
        'reviews',
        'seo_metadata',
        'about_text',
        'cta_text',
        'cta_url',
        'logo_url',
        'logo_light',
        'logo_dark',
        'favicon',
        'primary_color',
        'secondary_color',
        'plan_id',
        'trial_ends_at',
        'settings',
        'gemini_key',
        'openai_key',
        'api_key',
        'contact_email',
        'contact_phone',
        'whatsapp_number',
        'contact_address',
        'business_hours',
        'google_map_url',
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
        'features' => 'array',
        'services' => 'array',
        'faqs' => 'array',
        'reviews' => 'array',
        'seo_metadata' => 'array',
        'business_hours' => 'array',
        'social_links' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
