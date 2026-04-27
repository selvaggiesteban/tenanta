<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMercadoPagoSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('x-signature');
        $requestId = $request->header('x-request-id');
        $secret = config('services.mercadopago.webhook_secret');

        if (!$signature || !$requestId || !$secret) {
            return response()->json(['message' => 'Invalid signature headers'], 401);
        }

        // Logic to verify HMAC signature (simplification for MP V2)
        // Extract ts and v1 from signature header
        $parts = explode(',', $signature);
        $ts = null;
        $v1 = null;

        foreach ($parts as $part) {
            if (strpos($part, 'ts=') === 0) $ts = substr($part, 3);
            if (strpos($part, 'v1=') === 0) $v1 = substr($part, 3);
        }

        if (!$ts || !$v1) {
            return response()->json(['message' => 'Incomplete signature'], 401);
        }

        $manifest = "id:{$requestId};ts:{$ts};";
        $computedV1 = hash_hmac('sha256', $manifest, $secret);

        if (!hash_equals($v1, $computedV1)) {
            return response()->json(['message' => 'Signature mismatch'], 403);
        }

        return $next($request);
    }
}
