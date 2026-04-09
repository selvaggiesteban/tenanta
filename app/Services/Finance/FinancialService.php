<?php

namespace App\Services\Finance;

use App\Models\Quote;
use App\Models\Project;
use App\Models\Courses\Subscription;
use Illuminate\Support\Facades\DB;

class FinancialService
{
    /**
     * Obtiene las métricas para el Dashboard Dizteku (General).
     * Implementa la Fase 2.7.1 (Localización LATAM).
     */
    public function getDiztekuMetrics(): array
    {
        $tenant = app('current_tenant');
        
        $totalRevenue = Quote::where('status', 'accepted')->sum('total');
        $recurringRevenue = Subscription::where('status', 'active')->sum('amount');
        
        // Simulación de gastos operativos (30% de los ingresos para demostración)
        $simulatedExpenses = ($totalRevenue + $recurringRevenue) * 0.3;
        
        return [
            'ingresos_totales' => $totalRevenue + $recurringRevenue,
            'ingresos_recurrentes' => $recurringRevenue,
            'gastos_estimados' => $simulatedExpenses,
            'beneficio_neto' => ($totalRevenue + $recurringRevenue) - $simulatedExpenses,
            'margen_operativo' => 70, // Porcentaje simulado
            'moneda' => $tenant->currency ?? 'ARS',
        ];
    }

    /**
     * Obtiene las métricas para el Dashboard Piblo (Ventas).
     * Implementa la Fase 2.7.2.
     */
    public function getPibloMetrics(): array
    {
        $quotes = Quote::select('status', DB::raw('count(*) as count'), DB::raw('sum(total) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $accepted = $quotes->get('accepted');
        $sent = $quotes->get('sent');

        return [
            'ventas_cerradas' => $accepted ? $accepted->total : 0,
            'cantidad_ventas' => $accepted ? $accepted->count : 0,
            'en_proceso' => $sent ? $sent->total : 0,
            'tasa_conversion' => $sent && $sent->count > 0 ? ($accepted->count / ($accepted->count + $sent->count)) * 100 : 0,
            'promedio_ticket' => $accepted && $accepted->count > 0 ? $accepted->total / $accepted->count : 0,
        ];
    }

    /**
     * Obtiene las métricas para el Dashboard CMO (Marketing).
     * Implementa la Fase 2.7.3.
     */
    public function getCMOMetrics(): array
    {
        // Métricas simuladas basadas en lógica de marketing
        $inversionMarketing = 5000; // Simulado
        $nuevosClientes = Quote::where('status', 'accepted')
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        return [
            'roi_marketing' => $nuevosClientes > 0 ? (($nuevosClientes * 1000) / $inversionMarketing) * 100 : 0,
            'cac' => $nuevosClientes > 0 ? $inversionMarketing / $nuevosClientes : 0, // Costo de Adquisición
            'ltv' => 15000, // Valor de vida del cliente simulado
            'presupuesto_utilizado' => $inversionMarketing,
            'proyeccion_crecimiento' => 15, // Porcentaje
        ];
    }

    /**
     * Obtiene el resumen de cuentas mensuales (Fase 2.7.4).
     */
    public function getMonthlyAccounts(): array
    {
        return Quote::where('status', 'accepted')
            ->where('accepted_at', '>=', now()->startOfMonth())
            ->orderBy('accepted_at', 'desc')
            ->get(['title', 'total', 'accepted_at'])
            ->toArray();
    }
}
