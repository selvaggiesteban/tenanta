<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\BrandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function __construct(
        protected BrandingService $brandingService
    ) {}

    /**
     * Listar todos los inquilinos para gestión de landings.
     */
    public function index(): JsonResponse
    {
        $tenants = Tenant::all(['id', 'name', 'slug', 'category', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $tenants
        ]);
    }

    /**
     * Obtener datos actuales de la landing de un inquilino.
     */
    public function show(Tenant $tenant): JsonResponse
    {
        $branding = $this->brandingService->resolve($tenant);

        return response()->json([
            'success' => true,
            'data' => $branding
        ]);
    }

    /**
     * Editar manualmente la landing de un inquilino por el Superadmin.
     */
    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'about_text' => 'nullable|string|max:5000',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'contact_address' => 'nullable|string|max:500',
            // ... otros campos del branding
        ]);

        $tenant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Landing actualizada exitosamente por el Superadministrador.',
            'data' => $this->brandingService->resolve($tenant)
        ]);
    }

    /**
     * Regenerar la landing a partir del template y los datos registrados.
     */
    public function regenerate(Tenant $tenant): JsonResponse
    {
        // Lógica para resetear campos a partir de los ajustes de perfil 
        // o disparar un nuevo procesamiento de scraping si fuera necesario.
        
        // Por ahora, simulamos el refresco de metadatos SEO y Branding.
        $tenant->update([
            'hero_title' => "Bienvenido a {$tenant->name}",
            'hero_subtitle' => $tenant->category ?? "Servicios profesionales de alta calidad",
            'seo_metadata' => [] // Esto disparará una nueva auditoría en el Dashboard
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Landing regenerada correctamente con la información base del inquilino.',
            'data' => $this->brandingService->resolve($tenant)
        ]);
    }

    /**
     * Eliminar (resetear) la landing de un inquilino.
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        $tenant->update([
            'logo_url' => null,
            'hero_image' => null,
            'about_text' => null,
            'features' => [],
            'services' => [],
            'faqs' => [],
            'reviews' => []
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contenido de la landing eliminado/reseteado exitosamente.'
        ]);
    }
}
