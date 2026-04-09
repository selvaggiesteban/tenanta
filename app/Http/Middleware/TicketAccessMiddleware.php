<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Matriz V4:
     * - Superadmin & Gerente: Solo Lectura (L)
     * - Administrador: Lectura y Respuesta (R)
     * - Inquilino & Profesor: CRUD Completo
     * - Cliente: Gestión Propia (manejado a nivel de controlador/query)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $isRead = $request->isMethodSafe(); // GET, HEAD, OPTIONS
        $isReply = $request->is('*/reply');

        // Superadmin y Gerente: Solo Lectura (L)
        if (in_array($user->role, ['super_admin', 'manager'])) {
            if ($isRead) {
                return $next($request);
            }
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Tu rol solo tiene permisos de lectura sobre los tickets.'
            ], 403);
        }

        // Administrador: Solo Lectura y Respuesta (R)
        if ($user->role === 'admin') {
            if ($isRead || $isReply) {
                return $next($request);
            }
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Los Administradores solo pueden leer o responder tickets, no crearlos ni cerrarlos.'
            ], 403);
        }

        // Inquilino (member) y Profesor (teacher): CRUD Completo
        if (in_array($user->role, ['member', 'teacher'])) {
            return $next($request);
        }

        return response()->json(['message' => 'No autorizado'], 403);
    }
}
