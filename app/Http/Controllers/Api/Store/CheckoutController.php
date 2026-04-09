<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Recibe el plan seleccionado y prepara la sesión de pago (Lógica heredada de Academicus)
     */
    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|in:inicial,crecimiento,dominacion',
            'billing_cycle' => 'required|in:monthly,yearly'
        ]);

        $plans = config('plans');
        $plan = $plans[$validated['plan_id']];
        $price = $validated['billing_cycle'] === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];

        // Aquí iría la integración con MercadoPago / Stripe heredada de PaymentController de Academicus
        // Por ahora simulamos la creación de la orden para el Frontend.

        return response()->json([
            'success' => true,
            'message' => 'Redirigiendo a la pasarela de pagos...',
            'data' => [
                'order_id' => 'ORD-' . strtoupper(uniqid()),
                'plan_name' => $plan['name'],
                'amount' => $price,
                'checkout_url' => '/finalizar-compra/simulacion' // URL de la pasarela
            ]
        ]);
    }
}
