<?php

namespace App\Services\PDF;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenant;

class PDFReportService
{
    /**
     * Genera un reporte profesional en PDF con la marca del inquilino.
     * Implementa la Fase 5.2 (Trazabilidad CRM).
     */
    public function generateDashboardReport(Tenant $tenant, string $type, array $data)
    {
        $view = match($type) {
            'dizteku' => 'reports.financial',
            'piblo' => 'reports.sales',
            'seo' => 'reports.seo',
            default => 'reports.generic'
        };

        $pdf = Pdf::loadView($view, [
            'tenant' => $tenant,
            'data' => $data,
            'colors' => [
                'primary' => $tenant->primary_color ?? '#673DE6',
                'secondary' => $tenant->secondary_color ?? '#00A9A5'
            ],
            'date' => now()->format('d/m/Y')
        ]);

        return $pdf->download("Reporte_{$type}_{$tenant->slug}.pdf");
    }
}
