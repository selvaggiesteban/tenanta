<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CRMReadAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Restringe a 'manager' a solo lectura en CRM.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Super Admin y Admin tienen acceso total
        if (in_array($user->role, ['super_admin', 'admin'])) {
            return $next($request);
        }

        // Manager solo puede leer (GET, HEAD, OPTIONS)
        if ($user->role === 'manager' && !$request->isMethodSafe()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Los Gerentes solo tienen permisos de lectura en el CRM.'
            ], 403);
        }

        return $next($request);
    }
}
