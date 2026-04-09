<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Courses\Subscription;
use App\Models\Courses\SubscriptionPlan;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentManager $paymentManager
    ) {}

    public function createCheckoutSession(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'gateway' => 'nullable|string|in:mercadopago',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $gateway = $request->input('gateway', 'mercadopago');
        $user = $request->user();
        
        $reference = Str::uuid()->toString();

        // Create pending subscription
        $subscription = Subscription::create([
            'tenant_id' => $plan->tenant_id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_days ?: 30),
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'payment_provider' => $gateway,
            'external_reference' => $reference,
        ]);

        $paymentService = $this->paymentManager->driver($gateway);

        try {
            $initPoint = $paymentService->createPreference([
                'title' => $plan->name,
                'price' => $plan->price,
                'reference' => $reference,
                'success_url' => url('/payment/success?reference=' . $reference),
                'failure_url' => url('/payment/failure?reference=' . $reference),
                'pending_url' => url('/payment/pending?reference=' . $reference),
            ]);

            return response()->json([
                'url' => $initPoint,
                'reference' => $reference,
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            $subscription->delete(); // Rollback subscription creation if preference fails
            return response()->json(['message' => 'Error creating checkout session: ' . $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $gateway = $request->query('gateway', 'mercadopago');
        $paymentService = $this->paymentManager->driver($gateway);
        
        try {
            $paymentService->handleWebhook($request->all());
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
