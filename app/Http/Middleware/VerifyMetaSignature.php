<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMetaSignature
{
    /**
     * Valida la firma X-Hub-Signature-256 enviada por Meta (WhatsApp/Messenger).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // En un entorno multi-tenant, el 'app_secret' podría variar por canal, 
        // pero usualmente se usa un App de Meta global para la plataforma.
        $appSecret = config('services.meta.app_secret');
        $signature = $request->header('X-Hub-Signature-256');

        if (!$signature) {
            return response()->json(['message' => 'No signature provided'], 401);
        }

        $signature = str_replace('sha256=', '', $signature);
        $expectedSignature = hash_hmac('sha256', $request->getContent(), $appSecret);

        if (!hash_equals($signature, $expectedSignature)) {
            return response()->json(['message' => 'Invalid Meta signature'], 403);
        }

        return $next($request);
    }
}
