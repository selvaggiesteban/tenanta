<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResellerTenantController extends Controller
{
    /**
     * Listar inquilinos del Distribuidor.
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $tenants = $user->managedTenants()->get(['id', 'name', 'slug', 'trial_ends_at']);

        return response()->json(['success' => true, 'data' => $tenants]);
    }

    /**
     * Crear un nuevo inquilino bajo la red del Distribuidor.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        
        $data = $request->validate([
            'business_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_name' => 'required|string|max:255',
        ]);

        $data['reseller_id'] = $user->id;
        $data['scraping_source'] = 'reseller_panel';

        [$tenant, $admin] = app(\App\Services\TenantDataService::class)->createTenantWithAdmin($data);

        return response()->json([
            'success' => true,
            'message' => 'Inquilino creado exitosamente en tu red.',
            'data' => $tenant
        ], 201);
    }
}
