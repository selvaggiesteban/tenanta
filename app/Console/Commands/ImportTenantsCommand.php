<?php

namespace App\Console\Commands;

use App\Jobs\ImportTenantJob;
use Illuminate\Console\Command;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;

class ImportTenantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenanta:import-tenants {file} {--queue : Dispatch to queue instead of processing synchronously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa inquilinos masivamente desde un archivo CSV (Localización LATAM)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: {$filePath}");
            return 1;
        }

        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
            
            $headers = $csv->getHeader();
            $this->info("Iniciando importación masiva de " . count($csv) . " registros...");

            $imported = 0;
            $failed = 0;

            foreach ($csv as $record) {
                // Mapeo dinámico basado en las columnas esperadas del scrap de Google Business
                $data = $this->mapRecordToTenantData($record);

                if (empty($data['admin_email'])) {
                    $this->warn("Registro omitido por falta de email: " . ($record['title'] ?? 'Sin título'));
                    $failed++;
                    continue;
                }

                if ($this->option('queue')) {
                    ImportTenantJob::dispatch($data);
                } else {
                    // Procesamiento síncrono para depuración o lotes pequeños
                    try {
                        app(\App\Services\TenantDataService::class)->createTenantWithAdmin($data);
                    } catch (\Exception $e) {
                        $this->error("Error en registro: " . ($data['business_name'] ?? '---') . " - " . $e->getMessage());
                        $failed++;
                        continue;
                    }
                }

                $imported++;
                
                if ($imported % 50 === 0) {
                    $this->line("Procesados {$imported} registros...");
                }
            }

            $this->info("=== Importación Finalizada ===");
            $this->info("Exitosos: {$imported}");
            $this->error("Fallidos: {$failed}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Ocurrió un error crítico durante la importación: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Mapea una fila del CSV de scrap de Google Business al formato del TenantDataService.
     */
    protected function mapRecordToTenantData(array $record): array
    {
        // Limpieza básica de emails
        $emails = explode(',', $record['emails'] ?? '');
        $primaryEmail = trim($emails[0] ?? '');

        // Construir datos de negocio
        return [
            'business_name' => $record['title'] ?? 'Negocio sin nombre',
            'category' => $record['category'] ?? 'Servicios',
            'email' => $primaryEmail,
            'phone' => $record['phone'] ?? null,
            'address' => $record['address'] ?? null,
            'descriptions' => $record['descriptions'] ?? $record['title'] ?? null,
            'open_hours' => $record['open_hours'] ?? null,
            'link' => $record['link'] ?? null,
            'scraping_source' => 'bulk_import_google_business',
            
            // Datos del admin (usar el mismo email y nombre por defecto)
            'admin_name' => $record['title'] ?? 'Administrador',
            'admin_email' => $primaryEmail,
            'password' => 'Tenanta2026*', // Contraseña genérica inicial
            
            // Metadatos adicionales
            'hero_title' => "Somos " . ($record['title'] ?? 'expertos'),
            'hero_subtitle' => $record['category'] ?? 'Servicios Profesionales',
            'faqs' => [], // Podrían generarse dinámicamente después
            'reviews' => [
                'rating' => $record['review_rating'] ?? 5,
                'count' => $record['review_count'] ?? 0
            ]
        ];
    }
}
