<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\SEO\SEOAnalyzerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AuditTenantSEOJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Tenant $tenant
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SEOAnalyzerService $seoService): void
    {
        try {
            $analysisResults = $seoService->analyze($this->tenant);
            
            // Guardar resultados en el modelo Tenant
            $this->tenant->update([
                'seo_metadata' => $analysisResults
            ]);
            
            Log::info("Auditoría SEO completada para el inquilino: " . $this->tenant->name);
        } catch (\Exception $e) {
            Log::error("Error en auditoría SEO: " . $e->getMessage());
        }
    }
}
