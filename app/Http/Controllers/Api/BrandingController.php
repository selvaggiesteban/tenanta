<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BrandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    public function __construct(
        protected BrandingService $brandingService
    ) {}

    /**
     * Get branding for current tenant (authenticated user).
     *
     * GET /api/v1/branding
     */
    public function index(): JsonResponse
    {
        $branding = $this->brandingService->resolve();

        return response()->json([
            'success' => true,
            'data' => $branding,
        ]);
    }

    /**
     * Get branding for a specific tenant by slug (public).
     *
     * GET /api/v1/public/branding/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $branding = $this->brandingService->resolveBySlug($slug);

        if (!$branding) {
            return response()->json([
                'success' => false,
                'message' => __('messages.not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $branding,
        ]);
    }

    /**
     * Get available locales.
     *
     * GET /api/v1/branding/locales
     */
    public function locales(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->brandingService->getAvailableLocales(),
        ]);
    }

    /**
     * Get available timezones.
     *
     * GET /api/v1/branding/timezones
     */
    public function timezones(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->brandingService->getAvailableTimezones(),
        ]);
    }

    /**
     * Get available currencies.
     *
     * GET /api/v1/branding/currencies
     */
    public function currencies(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->brandingService->getAvailableCurrencies(),
        ]);
    }

    /**
     * Update tenant branding (admin only).
     *
     * PUT /api/v1/branding
     */
    public function update(Request $request): JsonResponse
    {
        $tenant = app('current_tenant');

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => __('messages.unauthorized'),
            ], 401);
        }

        $validated = $request->validate([
            'logo_light' => 'nullable|string|max:500',
            'logo_dark' => 'nullable|string|max:500',
            'favicon' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:500',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
            'social_links.linkedin' => 'nullable|url',
            'social_links.youtube' => 'nullable|url',
            'meta_title' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string|max:300',
            'meta_keywords' => 'nullable|string|max:200',
            'locale' => 'nullable|string|in:es_AR,en_US',
            'timezone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'date_format' => 'nullable|string|max:20',
        ]);

        $tenant->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.updated', ['resource' => __('messages.resources.tenant')]),
            'data' => $this->brandingService->resolve($tenant),
        ]);
    }
}
