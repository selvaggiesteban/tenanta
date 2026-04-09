<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ResellerController extends Controller
{
    /**
     * Vista general del Distribuidor: Inquilinos bajo su red y comisiones.
     */
    public function dashboard(): JsonResponse
    {
        $user = auth('api')->user();

        // En un sistema real, los inquilinos tendrían una columna 'reseller_id'
        // Aquí simulamos los datos para que el panel sea funcional tras el despliegue
        $stats = [
            'total_inquilinos' => Tenant::count(), // Filtrar por reseller en prod
            'clientes_activos' => Tenant::where('trial_ends_at', '>', now())->count(),
            'comisiones_pendientes' => 150000.00,
            'moneda' => 'ARS'
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
