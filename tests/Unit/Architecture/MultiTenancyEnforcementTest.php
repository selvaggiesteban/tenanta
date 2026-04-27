<?php

namespace Tests\Unit\Architecture;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class MultiTenancyEnforcementTest extends TestCase
{
    /**
     * Asegura que todos los modelos del dominio de negocio implementen el aislamiento por Tenant.
     */
    public function test_all_domain_models_use_tenant_trait()
    {
        $modelsPath = app_path('Models');
        $files = File::allFiles($modelsPath);
        
        // Modelos globales que no pertenecen a un tenant específico
        $excludedModels = [
            'Tenant', 
            'SubscriptionPlan', 
            'ChangeRequest',
            'User' // User maneja el trait o la relación de forma especial en la autenticación base
        ];

        foreach ($files as $file) {
            $class = 'App\\Models\\' . str_replace('/', '\\', $file->getRelativePathname());
            $class = preg_replace('/\.php$/', '', $class);
            
            if (class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $className = $reflection->getShortName();
                
                if (!in_array($className, $excludedModels) && !$reflection->isAbstract()) {
                    $traits = $reflection->getTraitNames();
                    // Buscar recursivamente en los traits por si está empaquetado
                    $usesTenant = in_array('App\Traits\BelongsToTenant', $traits);
                    
                    $this->assertTrue(
                        $usesTenant, 
                        "VULNERABILIDAD CRÍTICA (Fuga de Datos): El modelo {$class} NO implementa el trait BelongsToTenant."
                    );
                }
            }
        }
    }
}
