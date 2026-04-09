<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LMSAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Profesores, Admins y Super Admins: Gestión total.
     * Otros: Lectura.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Profesores, Admins y Super Admins tienen acceso total
        if (in_array($user->role, ['super_admin', 'admin', 'teacher'])) {
            return $next($request);
        }

        // Otros roles (Inquilinos, Gerentes) solo pueden leer (GET)
        if (!$request->isMethodSafe()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo los Profesores pueden modificar el contenido educativo.'
            ], 403);
        }

        return $next($request);
    }
}
