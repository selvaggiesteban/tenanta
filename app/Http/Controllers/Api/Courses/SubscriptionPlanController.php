<?php

namespace App\Http\Controllers\Api\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CreateSubscriptionPlanRequest;
use App\Http\Resources\Courses\SubscriptionPlanResource;
use App\Models\Courses\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SubscriptionPlan::query()
            ->when(!$request->user()?->hasRole(['admin', 'super_admin']), function ($q) {
                $q->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('price');

        $plans = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return SubscriptionPlanResource::collection($plans);
    }

    public function store(CreateSubscriptionPlanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;

        $plan = SubscriptionPlan::create($data);

        return response()->json([
            'message' => 'Plan de suscripción creado exitosamente.',
            'data' => new SubscriptionPlanResource($plan),
        ], 201);
    }

    public function show(SubscriptionPlan $plan): SubscriptionPlanResource
    {
        return new SubscriptionPlanResource($plan);
    }

    public function update(CreateSubscriptionPlanRequest $request, SubscriptionPlan $plan): JsonResponse
    {
        $plan->update($request->validated());

        return response()->json([
            'message' => 'Plan actualizado exitosamente.',
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }

    public function destroy(SubscriptionPlan $plan): JsonResponse
    {
        // Check for active subscriptions
        if ($plan->subscriptions()->whereIn('status', ['active', 'trial'])->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un plan con suscripciones activas.',
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Plan eliminado exitosamente.',
        ]);
    }

    public function toggleActive(SubscriptionPlan $plan): JsonResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);

        $status = $plan->is_active ? 'activado' : 'desactivado';

        return response()->json([
            'message' => "Plan {$status} exitosamente.",
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }

    public function featured(): AnonymousResourceCollection
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->get();

        return SubscriptionPlanResource::collection($plans);
    }
}
