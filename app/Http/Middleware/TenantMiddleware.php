<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found',
            ], 404);
        }

        if ($tenant->trashed()) {
            return response()->json([
                'message' => 'Tenant account has been deactivated',
            ], 403);
        }

        // Set tenant in container for global scopes
        app()->instance('current_tenant', $tenant);

        // Add tenant to request for easy access
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
