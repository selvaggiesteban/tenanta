<?php

namespace App\Models\Marketing;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'slug',
        'subject',
        'preview_text',
        'html_content',
        'text_content',
        'type',
        'category',
        'design_settings',
        'thumbnail',
        'variables',
        'is_active',
        'is_default',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'design_settings' => 'array',
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    const TYPE_MARKETING = 'marketing';
    const TYPE_TRANSACTIONAL = 'transactional';
    const TYPE_NOTIFICATION = 'notification';

    const TYPES = [
        self::TYPE_MARKETING => 'Marketing',
        self::TYPE_TRANSACTIONAL => 'Transaccional',
        self::TYPE_NOTIFICATION => 'Notificación',
    ];

    const CATEGORIES = [
        'welcome' => 'Bienvenida',
        'newsletter' => 'Newsletter',
        'promotion' => 'Promoción',
        'reminder' => 'Recordatorio',
        'announcement' => 'Anuncio',
        'course_update' => 'Actualización de Curso',
        'subscription' => 'Suscripción',
        'invoice' => 'Factura',
        'other' => 'Otro',
    ];

    // Default variables available in all templates
    const DEFAULT_VARIABLES = [
        '{{user.name}}' => 'Nombre del usuario',
        '{{user.email}}' => 'Email del usuario',
        '{{user.first_name}}' => 'Primer nombre',
        '{{tenant.name}}' => 'Nombre de la empresa',
        '{{tenant.email}}' => 'Email de la empresa',
        '{{unsubscribe_url}}' => 'Link para desuscribirse',
        '{{current_year}}' => 'Año actual',
    ];

    // Relationships

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeMarketing($query)
    {
        return $query->where('type', self::TYPE_MARKETING);
    }

    // Accessors

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getAvailableVariablesAttribute(): array
    {
        return array_merge(self::DEFAULT_VARIABLES, $this->variables ?? []);
    }

    // Methods

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function duplicate(): self
    {
        $clone = $this->replicate(['slug', 'is_default', 'usage_count', 'last_used_at']);
        $clone->name = $this->name . ' (Copia)';
        $clone->slug = \Str::slug($clone->name) . '-' . uniqid();
        $clone->is_default = false;
        $clone->usage_count = 0;
        $clone->save();

        return $clone;
    }

    public function render(array $data = []): string
    {
        $content = $this->html_content;

        // Replace variables
        foreach ($data as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        // Replace default variables with empty if not provided
        foreach (array_keys(self::DEFAULT_VARIABLES) as $variable) {
            $content = str_replace($variable, '', $content);
        }

        return $content;
    }
}
