<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\TenantCreateRequest; 
use App\Services\TenantDataService;

class TenantController extends Controller
{
    protected TenantDataService $tenantService;

    public function __construct(TenantDataService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Endpoint para la creación manual de un nuevo Tenant con su Administrador inicial.
     * Implementa Localización LATAM y lógica de la Fase 3.3.
     */
    public function store(TenantCreateRequest $request): JsonResponse
    {
        try {
            // Mapeo de campos del Request al formato esperado por el Servicio
            $data = [
                'business_name' => $request->company_name,
                'admin_name' => $request->name,
                'admin_email' => $request->email,
                'password' => $request->password,
                'scraping_source' => 'web_registration'
            ];

            [$tenant, $user] = $this->tenantService->createTenantWithAdmin($data);

            return response()->json([
                'success' => true,
                'message' => '¡Éxito! Negocio y Usuario Administrador creados correctamente.',
                'data' => [
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                    ],
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el inquilino: ' . $e->getMessage(),
            ], 500);
        }
    }
}
