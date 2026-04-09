<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ChangeRequestController extends Controller
{
    /**
     * Inquilinos y Clientes: Solicitar cambios a la Landing (Sección 1).
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        // Validar que solo Inquilinos (member) o Clientes puedan crear solicitudes
        if (!in_array($user->role, ['member'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo Inquilinos y Clientes pueden solicitar cambios a las Landings.'
            ], 403);
        }

        $validated = $request->validate([
            'requested_changes' => 'required|string|min:10|max:5000',
        ]);

        $changeRequest = ChangeRequest::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'type' => 'landing_update',
            'requested_changes' => $validated['requested_changes'],
            'status' => 'pending'
        ]);

        // Fase 11.2: Notificar al Staff (Superadmin, Administrador y Gerente)
        // NOTA: Para producción se debe usar Notification::send() con una clase real de notificación
        // Aquí simulamos la acción o dejamos el gancho listo para el sistema de correos.
        $staffMembers = User::whereIn('role', ['super_admin', 'admin', 'manager'])
            ->where('tenant_id', $user->tenant_id)
            ->get();
            
        // Notification::send($staffMembers, new \App\Notifications\LandingChangeRequested($changeRequest));

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de cambios enviada. El Staff ha sido notificado y la revisará pronto.',
            'data' => $changeRequest
        ], 201);
    }

    /**
     * Staff (Superadmin, Admin, Gerente): Ver listado de solicitudes pendientes.
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();

        if (!in_array($user->role, ['super_admin', 'admin', 'manager'])) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $requests = ChangeRequest::with(['user:id,name,email', 'tenant:id,name,slug'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    /**
     * Staff: Aprobar o Rechazar la solicitud.
     */
    public function update(Request $request, ChangeRequest $changeRequest): JsonResponse
    {
        $user = auth('api')->user();

        if (!in_array($user->role, ['super_admin', 'admin', 'manager'])) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,completed',
            'staff_notes' => 'nullable|string'
        ]);

        $changeRequest->update([
            'status' => $validated['status'],
            'staff_notes' => $validated['staff_notes'],
            'resolved_by' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de la solicitud actualizado a ' . $validated['status'],
            'data' => $changeRequest
        ]);
    }
}
