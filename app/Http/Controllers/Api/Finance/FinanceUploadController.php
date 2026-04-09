<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAccountlyTranscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FinanceUploadController extends Controller
{
    /**
     * Recibe el resumen bancario para transcripción vía Accountly (Sección 2).
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,xlsx,csv|max:10240',
        ]);

        $tenant = app('current_tenant');
        $path = $request->file('file')->store("tenants/{$tenant->id}/finance_uploads");

        // Disparar el Job que ejecuta el script de Python
        ProcessAccountlyTranscription::dispatch($tenant, Storage::path($path));

        return response()->json([
            'success' => true,
            'message' => '¡Archivo recibido! La inteligencia Accountly está transcribiendo tus datos. Los gráficos se actualizarán en breve.'
        ]);
    }
}
