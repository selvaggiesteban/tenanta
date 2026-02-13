<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Super admin bypasses all role checks
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Check if user has one of the required roles
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Unauthorized. Required role: ' . implode(' or ', $roles),
            ], 403);
        }

        return $next($request);
    }
}
