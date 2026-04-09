<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\TenantDataService;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use App\Models\User;

class TenantBulkImportSeeder extends Seeder
{
    /**
     * Sube la base de inquilinos desde el archivo unificado (versión CSV).
     * Mapea encabezados a perfiles editables y crea accesos de Administrador.
     */
    public function run(TenantDataService $tenantService): void
    {
        $csvPath = base_path('../traslado.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("Archivo no encontrado en: {$csvPath}. Por favor, exporta el Excel a CSV con ese nombre.");
            return;
        }

        try {
            $csv = Reader::createFromPath($csvPath, 'r');
            $csv->setHeaderOffset(0);

            $this->command->info("Iniciando carga de la base de inquilinos...");

            foreach ($csv as $record) {
                // Mapeo de encabezados del archivo unificado a la estructura de Tenanta
                $data = [
                    'business_name' => $record['title'] ?? $record['nombre'] ?? 'Negocio Nuevo',
                    'category'      => $record['category'] ?? $record['categoria'] ?? 'Servicios',
                    'email'         => $this->getValidEmail($record),
                    'phone'         => $record['phone'] ?? $record['telefono'] ?? null,
                    'address'       => $record['address'] ?? $record['direccion'] ?? null,
                    'descriptions'  => $record['descriptions'] ?? $record['descripcion'] ?? null,
                    'open_hours'    => $record['open_hours'] ?? $record['horarios'] ?? null,
                    'link'          => $record['link'] ?? $record['google_maps'] ?? null,
                    'scraping_source' => 'importacion_masiva_unificada',
                    
                    // Credenciales para el Administrador del Inquilino
                    'admin_name'    => $record['title'] ?? 'Administrador',
                    'password'      => 'Tenanta2026*', // Contraseña temporal
                ];

                if (empty($data['email'])) {
                    continue;
                }

                // Usamos el servicio para crear el Tenant y el Usuario Administrador
                try {
                    [$tenant, $admin] = $tenantService->createTenantWithAdmin($data);
                    
                    // Si el archivo sugiere un segundo rol de 'inquilino' (usuario estándar), se puede crear aquí
                    // Por defecto, el Admin ya tiene acceso a todo.
                    
                    $this->command->line("✅ Importado: {$tenant->name} (Admin: {$admin->email})");
                } catch (\Exception $e) {
                    $this->command->error("❌ Error en {$data['business_name']}: " . $e->getMessage());
                }
            }

            $this->command->info("Proceso de importación masiva finalizado.");

        } catch (\Exception $e) {
            $this->command->error("Error crítico: " . $e->getMessage());
        }
    }

    /**
     * Extrae el primer email válido de la columna de emails.
     */
    private function getValidEmail(array $record): ?string
    {
        $emailSource = $record['emails'] ?? $record['email'] ?? '';
        $emails = explode(',', $emailSource);
        return trim($emails[0]) ?: null;
    }
}
