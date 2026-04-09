<?php

namespace App\Console\Commands;

use App\Models\Courses\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRecurringBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenanta:billing-process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa cobros recurrentes de suscripciones vencidas (Mercado Pago / PayPal)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Iniciando proceso de facturación recurrente...");

        // Buscar suscripciones activas cuya fecha de próximo cobro haya expirado
        $dueSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '<=', now())
            ->get();

        if ($dueSubscriptions->isEmpty()) {
            $this->info("No hay cobros pendientes el día de hoy.");
            return 0;
        }

        $processed = 0;
        $failed = 0;

        foreach ($dueSubscriptions as $subscription) {
            try {
                // Simulación de cargo a la pasarela (Mercado Pago / PayPal)
                // Aquí se invocaría el PaymentManager heredado de Academicus
                Log::info("Procesando cobro de $ {$subscription->amount} para la suscripción ID: {$subscription->id}...");
                
                // Si el cobro es exitoso, renovar por un mes más (simulado)
                $subscription->update([
                    'ends_at' => now()->addMonth(),
                    'last_payment_at' => now()
                ]);

                $processed++;
                $this->line("✅ Suscripción {$subscription->id} renovada exitosamente.");
                
            } catch (\Exception $e) {
                Log::error("Fallo al cobrar suscripción ID: {$subscription->id}. Motivo: " . $e->getMessage());
                
                // En un escenario real, cambiar a estado 'past_due' o notificar al cliente
                $subscription->update(['status' => 'past_due']);
                $failed++;
                $this->error("❌ Error en suscripción {$subscription->id}");
            }
        }

        $this->info("=== Proceso Finalizado ===");
        $this->info("Suscripciones renovadas: {$processed}");
        $this->error("Cobros fallidos: {$failed}");

        return 0;
    }
}
