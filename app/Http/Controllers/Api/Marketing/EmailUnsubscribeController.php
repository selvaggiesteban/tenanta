<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Actions\Marketing\HandleUnsubscribeAction;
use App\Http\Controllers\Controller;
use App\Models\Marketing\EmailUnsubscribe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailUnsubscribeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EmailUnsubscribe::query()
            ->with('user', 'campaign');

        if ($request->filled('search')) {
            $query->where('email', 'like', "%{$request->search}%");
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        $unsubscribes = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 50));

        return response()->json([
            'data' => $unsubscribes->map(fn($u) => [
                'id' => $u->id,
                'email' => $u->email,
                'reason' => $u->reason,
                'reason_label' => EmailUnsubscribe::REASONS[$u->reason] ?? $u->reason,
                'feedback' => $u->feedback,
                'scope' => $u->scope,
                'user' => $u->user ? [
                    'id' => $u->user->id,
                    'name' => $u->user->name,
                ] : null,
                'campaign' => $u->campaign ? [
                    'id' => $u->campaign->id,
                    'name' => $u->campaign->name,
                ] : null,
                'created_at' => $u->created_at?->toISOString(),
            ]),
            'meta' => [
                'current_page' => $unsubscribes->currentPage(),
                'last_page' => $unsubscribes->lastPage(),
                'per_page' => $unsubscribes->perPage(),
                'total' => $unsubscribes->total(),
            ],
        ]);
    }

    public function resubscribe(
        Request $request,
        HandleUnsubscribeAction $action
    ): JsonResponse {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $success = $action->resubscribe(
            auth()->user()->tenant_id,
            $request->email
        );

        if (!$success) {
            return response()->json([
                'message' => 'El email no está en la lista de desuscripciones',
            ], 404);
        }

        return response()->json([
            'message' => 'Email reactivado exitosamente',
        ]);
    }

    public function reasons(): JsonResponse
    {
        return response()->json([
            'data' => collect(EmailUnsubscribe::REASONS)->map(fn($label, $key) => [
                'key' => $key,
                'label' => $label,
            ])->values(),
        ]);
    }

    public function stats(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $total = EmailUnsubscribe::where('tenant_id', $tenantId)->count();

        $byReason = EmailUnsubscribe::where('tenant_id', $tenantId)
            ->selectRaw('reason, COUNT(*) as count')
            ->groupBy('reason')
            ->pluck('count', 'reason')
            ->toArray();

        $recent = EmailUnsubscribe::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'total' => $total,
            'by_reason' => $byReason,
            'last_30_days' => $recent,
        ]);
    }
}
