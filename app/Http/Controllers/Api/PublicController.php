<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Courses\Course;
use App\Models\Courses\SubscriptionPlan;
use App\Models\Tenant;
use App\Services\BrandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __construct(
        private readonly BrandingService $brandingService
    ) {}

    /**
     * Get branding for the current tenant.
     */
    public function branding(Request $request): JsonResponse
    {
        // For public pages, we might not have a tenant in session if not using subdomains
        // So we fallback to a default or require a slug
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (!$tenant) {
            return response()->json(['message' => 'Tenant context not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant_name' => $tenant->name,
                'branding' => $this->brandingService->resolve($tenant),
            ]
        ]);
    }

    /**
     * Get public courses for the catalog.
     */
    public function courses(Request $request): JsonResponse
    {
        $tenant = app('current_tenant');
        
        $query = Course::where('tenant_id', $tenant->id)
            ->where('is_active', true);

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $courses = $query->limit($request->input('limit', 12))->get();

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Get public subscription plans.
     */
    public function plans(Request $request): JsonResponse
    {
        $tenant = app('current_tenant');
        
        $plans = SubscriptionPlan::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }
}
