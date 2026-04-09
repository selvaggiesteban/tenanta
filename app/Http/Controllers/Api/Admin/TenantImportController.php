<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\TenantDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Csv\Reader;
use Illuminate\Support\Facades\Storage;

class TenantImportController extends Controller
{
    /**
     * Paso 1: Subir archivo y devolver encabezados para mapeo.
     */
    public function uploadAndPreview(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|mimes:csv,txt|max:10240']);

        $path = $request->file('file')->store('temp_imports');
        $csv = Reader::createFromPath(Storage::path($path), 'r');
        $csv->setHeaderOffset(0);

        return response()->json([
            'success' => true,
            'temp_path' => $path,
            'headers' => $csv->getHeader(),
            'preview_rows' => array_slice(iterator_to_array($csv->getRecords()), 0, 3)
        ]);
    }

    /**
     * Paso 2: Procesar la importación con el mapeo definido por el usuario.
     */
    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'temp_path' => 'required|string',
            'mapping' => 'required|array', // ['business_name' => 'col_1', 'email' => 'col_3', ...]
        ]);

        $csv = Reader::createFromPath(Storage::path($validated['temp_path']), 'r');
        $csv->setHeaderOffset(0);
        
        $tenantService = app(TenantDataService::class);
        $imported = 0;
        $errors = [];

        foreach ($csv->getRecords() as $record) {
            try {
                $data = [];
                foreach ($validated['mapping'] as $targetField => $sourceHeader) {
                    $data[$targetField] = $record[$sourceHeader] ?? null;
                }
                
                // Forzar campos necesarios si no vienen en el mapeo
                $data['scraping_source'] = 'ui_bulk_import';
                $data['password'] = 'Tenanta2026*';

                $tenantService->createTenantWithAdmin($data);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error en fila: " . ($record[$validated['mapping']['business_name']] ?? 'Desconocido') . " - " . $e->getMessage();
            }
        }

        Storage::delete($validated['temp_path']);

        return response()->json([
            'success' => true,
            'message' => "Importación completada: {$imported} negocios creados.",
            'errors' => $errors
        ]);
    }
}
