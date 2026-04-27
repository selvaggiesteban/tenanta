<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class VerifyTenantApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Tenant-Api-Key') ?? $request->query('api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API Key is missing'
            ], 401);
        }

        $tenant = Tenant::where('api_key', $apiKey)->first();

        if (!$tenant) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid API Key'
            ], 401);
        }

        // Set the tenant globally for the request context if needed
        // App::instance(Tenant::class, $tenant);
        
        // Or just attach it to the request
        $request->merge(['current_tenant' => $tenant]);

        return $next($request);
    }
}
