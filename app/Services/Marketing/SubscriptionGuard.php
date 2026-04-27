<?php

namespace App\Services\Marketing;

use App\Models\Tenant;

class SubscriptionGuard
{
    /**
     * Valida si un Tenant tiene acceso a un módulo de automatización específico.
     */
    public function canUseAutomation(Tenant $tenant, string $automationType): bool
    {
        // El esquema 'addons' en la tabla tenants permite habilitar microservicios
        $addons = $tenant->addons ?? [];
        
        return isset($addons['automations']) && 
               (in_array($automationType, $addons['automations']) || in_array('*', $addons['automations']));
    }

    /**
     * Valida si un Tenant tiene habilitado un canal de comunicación.
     */
    public function canUseChannel(Tenant $tenant, string $channelType): bool
    {
        $addons = $tenant->addons ?? [];
        
        return isset($addons['channels']) && 
               (in_array($channelType, $addons['channels']) || in_array('*', $addons['channels']));
    }

    /**
     * Verifica límites de uso (ej: cantidad de mensajes enviados).
     */
    public function hasQuota(Tenant $tenant, string $feature): bool
    {
        // Lógica para verificar cuotas mensuales
        return true;
    }
}
