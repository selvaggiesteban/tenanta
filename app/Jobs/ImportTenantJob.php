<?php

namespace App\Jobs;

use App\Services\TenantDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $data
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TenantDataService $tenantService): void
    {
        try {
            $tenantService->createTenantWithAdmin($this->data);
            
            Log::info("Importación exitosa para: " . ($this->data['business_name'] ?? 'Desconocido'));
        } catch (\Exception $e) {
            Log::error("Error importando inquilino: " . $e->getMessage(), [
                'data' => $this->data
            ]);
        }
    }
}
