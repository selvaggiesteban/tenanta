<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class ProcessAccountlyTranscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Tenant $tenant,
        protected string $filePath
    ) {}

    /**
     * Ejecuta el script de Python de Accountly de la manera más eficiente (Local VPS).
     */
    public function handle(): void
    {
        try {
            // Comando para ejecutar el script de Accountly
            // Se asume que el script reside en la carpeta de scripts del proyecto
            $scriptPath = base_path('scripts/accountly_transcriber.py');
            
            $result = Process::run("python3 {$scriptPath} --file={$this->filePath} --tenant={$this->tenant->id}");

            if ($result->successful()) {
                Log::info("Transcripción de Accountly exitosa para: " . $this->tenant->name);
                
                // Actualizar el estado del dashboard para que el inquilino vea los nuevos datos
                $this->tenant->update(['settings->last_finance_sync' => now()]);
            } else {
                Log::error("Error en script de Accountly: " . $result->errorOutput());
            }
        } catch (\Exception $e) {
            Log::error("Excepción en Job Accountly: " . $e->getMessage());
        }
    }
}
