<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'type',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    const TYPES = [
        'leads' => 'Leads',
        'deals' => 'Negocios',
        'projects' => 'Proyectos',
        'custom' => 'Personalizado',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pipeline) {
            // If this is set as default, unset other defaults of same type
            if ($pipeline->is_default) {
                static::where('tenant_id', $pipeline->tenant_id)
                    ->where('type', $pipeline->type)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        static::updating(function ($pipeline) {
            // If this is being set as default, unset other defaults of same type
            if ($pipeline->isDirty('is_default') && $pipeline->is_default) {
                static::where('tenant_id', $pipeline->tenant_id)
                    ->where('type', $pipeline->type)
                    ->where('id', '!=', $pipeline->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        // Create default stages when pipeline is created
        static::created(function ($pipeline) {
            $pipeline->createDefaultStages();
        });
    }

    // Relationships

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('sort_order');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
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

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Helpers

    public function createDefaultStages(): void
    {
        $defaultStages = match ($this->type) {
            'leads' => [
                ['name' => 'Nuevo', 'color' => '#6366f1', 'probability' => 10],
                ['name' => 'Contactado', 'color' => '#8b5cf6', 'probability' => 25],
                ['name' => 'Calificado', 'color' => '#a855f7', 'probability' => 50],
                ['name' => 'Propuesta', 'color' => '#d946ef', 'probability' => 75],
                ['name' => 'Ganado', 'color' => '#22c55e', 'probability' => 100, 'is_won' => true],
                ['name' => 'Perdido', 'color' => '#ef4444', 'probability' => 0, 'is_lost' => true],
            ],
            'deals' => [
                ['name' => 'Descubrimiento', 'color' => '#3b82f6', 'probability' => 10],
                ['name' => 'Propuesta', 'color' => '#6366f1', 'probability' => 30],
                ['name' => 'Negociación', 'color' => '#8b5cf6', 'probability' => 60],
                ['name' => 'Contrato', 'color' => '#a855f7', 'probability' => 90],
                ['name' => 'Cerrado Ganado', 'color' => '#22c55e', 'probability' => 100, 'is_won' => true],
                ['name' => 'Cerrado Perdido', 'color' => '#ef4444', 'probability' => 0, 'is_lost' => true],
            ],
            'projects' => [
                ['name' => 'Backlog', 'color' => '#64748b', 'probability' => 0],
                ['name' => 'Por Hacer', 'color' => '#3b82f6', 'probability' => 0],
                ['name' => 'En Progreso', 'color' => '#f59e0b', 'probability' => 0],
                ['name' => 'En Revisión', 'color' => '#8b5cf6', 'probability' => 0],
                ['name' => 'Completado', 'color' => '#22c55e', 'probability' => 100, 'is_won' => true],
            ],
            default => [
                ['name' => 'Pendiente', 'color' => '#6366f1', 'probability' => 0],
                ['name' => 'En Proceso', 'color' => '#f59e0b', 'probability' => 50],
                ['name' => 'Completado', 'color' => '#22c55e', 'probability' => 100, 'is_won' => true],
            ],
        };

        foreach ($defaultStages as $index => $stage) {
            $this->stages()->create([
                ...$stage,
                'sort_order' => $index,
            ]);
        }
    }

    public function makeDefault(): void
    {
        $this->update(['is_default' => true]);
    }

    public static function getDefaultForType(string $type): ?self
    {
        return static::ofType($type)->default()->first();
    }
}
