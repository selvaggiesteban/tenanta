<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenant = app('current_tenant')) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
            }
        });

        static::creating(function ($model) {
            if (!$model->tenant_id && $tenant = app('current_tenant')) {
                $model->tenant_id = $tenant->id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeWithoutTenantScope(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('tenant');
    }
}
