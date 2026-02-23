<?php

namespace App\Http\Controllers\Api\Courses;

use App\Actions\Courses\CancelSubscriptionAction;
use App\Actions\Courses\SubscribeUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\SubscribeUserRequest;
use App\Http\Resources\Courses\SubscriptionResource;
use App\Models\Courses\Subscription;
use App\Models\Courses\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscribeUserAction $subscribeAction,
        private CancelSubscriptionAction $cancelAction
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $subscriptions = Subscription::query()
            ->where('user_id', auth()->id())
            ->with('plan')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 10);

        return SubscriptionResource::collection($subscriptions);
    }

    public function store(SubscribeUserRequest $request): JsonResponse
    {
        $user = auth()->user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        try {
            $subscription = $this->subscribeAction->execute($user, $plan, [
                'provider' => $request->payment_provider,
                'provider_id' => $request->payment_provider_id,
                'method' => $request->payment_method,
            ]);

            return response()->json([
                'message' => 'Suscripción creada exitosamente.',
                'data' => new SubscriptionResource($subscription->load('plan')),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Subscription $subscription): SubscriptionResource
    {
        $this->authorizeSubscription($subscription);

        return new SubscriptionResource($subscription->load('plan'));
    }

    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorizeSubscription($subscription);

        $immediately = $request->boolean('immediately', false);

        try {
            $subscription = $this->cancelAction->execute($subscription, $immediately);

            $message = $immediately
                ? 'Suscripción cancelada inmediatamente.'
                : 'Suscripción cancelada. Tendrás acceso hasta el final del período de facturación.';

            return response()->json([
                'message' => $message,
                'data' => new SubscriptionResource($subscription),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reactivate(Subscription $subscription): JsonResponse
    {
        $this->authorizeSubscription($subscription);

        try {
            $subscription = $this->cancelAction->reactivate($subscription);

            return response()->json([
                'message' => 'Suscripción reactivada exitosamente.',
                'data' => new SubscriptionResource($subscription),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function changePlan(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorizeSubscription($subscription);

        $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
        ]);

        $newPlan = SubscriptionPlan::findOrFail($request->plan_id);

        try {
            $subscription = $this->subscribeAction->changePlan($subscription, $newPlan);

            return response()->json([
                'message' => 'Plan cambiado exitosamente.',
                'data' => new SubscriptionResource($subscription->load('plan')),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function current(): JsonResponse
    {
        $subscription = Subscription::where('user_id', auth()->id())
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL])
            ->with('plan')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No tienes una suscripción activa.',
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => new SubscriptionResource($subscription),
        ]);
    }

    private function authorizeSubscription(Subscription $subscription): void
    {
        if ($subscription->user_id !== auth()->id()) {
            abort(403, 'No tienes acceso a esta suscripción.');
        }
    }
}
