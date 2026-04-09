<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantDataService
{
    /**
     * Crea un nuevo Inquilino con sus datos de negocio y un usuario administrador inicial.
     * Implementa la lógica de la Fase 3.3 y 4.2 (Localización LATAM).
     *
     * @param array $data Datos del negocio y administrador.
     * @return array [Tenant, User]
     */
    public function createTenantWithAdmin(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Generar slug único basado en el nombre del negocio
            $businessName = $data['business_name'] ?? $data['company_name'];
            $slug = Str::slug($businessName);
            
            // Asegurar unicidad del slug si ya existe
            if (Tenant::where('slug', $slug)->exists()) {
                $slug .= '-' . Str::lower(Str::random(4));
            }

            // Crear el Inquilino (Perfil de Negocio)
            $tenant = Tenant::create([
                'name' => $businessName,
                'category' => $data['category'] ?? null,
                'slug' => $slug,
                'hero_title' => $data['hero_title'] ?? "Bienvenido a {$businessName}",
                'hero_subtitle' => $data['hero_subtitle'] ?? $data['category'] ?? "Servicios profesionales",
                'about_text' => $data['about_text'] ?? $data['descriptions'] ?? null,
                'contact_email' => $data['contact_email'] ?? $data['email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? $data['phone'] ?? null,
                'contact_address' => $data['contact_address'] ?? $data['address'] ?? null,
                'google_map_url' => $data['google_map_url'] ?? $data['link'] ?? null,
                'business_hours' => $data['business_hours'] ?? $this->parseHours($data['open_hours'] ?? null),
                'features' => $data['features'] ?? [],
                'services' => $data['services'] ?? [],
                'faqs' => $data['faqs'] ?? [],
                'reviews' => $data['reviews'] ?? [],
                'trial_ends_at' => now()->addDays(14), // 14 días de prueba por defecto
                'locale' => 'es_AR',
                'timezone' => 'America/Argentina/Buenos_Aires',
                'currency' => 'ARS',
                'settings' => [
                    'marketing_enabled' => true,
                    'seo_audit_pending' => true,
                    'scraping_source' => $data['scraping_source'] ?? 'manual'
                ],
            ]);

            // Crear el Usuario Administrador asociado al Tenant
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['admin_name'] ?? $data['name'] ?? $businessName,
                'email' => $data['admin_email'] ?? $data['email'],
                'password' => Hash::make($data['password'] ?? Str::random(16)), // Password aleatorio inicial
                'role' => 'admin',
                'status' => 'pending_activation',
                'email_verified_at' => null, // Obligatorio verificar
                'timezone' => 'America/Argentina/Buenos_Aires',
            ]);

            // Enviar notificación de bienvenida y activación
            $user->notify(new \App\Notifications\WelcomeAndActivateNotification());

            return [$tenant, $user];
        });
    }

    /**
     * Parsea los horarios de apertura desde un formato JSON o texto (Fase 3.3).
     */
    private function parseHours($hoursData): array
    {
        if (empty($hoursData)) return [];
        
        if (is_array($hoursData)) return $hoursData;

        try {
            if (Str::startsWith($hoursData, '[')) {
                return json_decode($hoursData, true);
            }
        } catch (\Exception $e) {
            // Fallback a retornar el string original envuelto
        }

        return ['horario_general' => $hoursData];
    }
}
