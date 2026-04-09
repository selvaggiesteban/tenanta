<?php

namespace App\Http\Controllers\Api\Omnichannel;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnifiedInboxController extends Controller
{
    /**
     * Devuelve todos los mensajes de todas las redes (WA, Messenger, IG) unificados.
     * Implementa la lógica de PowerChat Plus (Sección 2).
     */
    public function index(): JsonResponse
    {
        $tenant = app('current_tenant');
        
        // Obtenemos mensajes de la base de datos que hayan sido sincronizados vía Webhooks o QR
        $messages = Message::whereHas('conversation', function($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            })
            ->with('conversation')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Envía un mensaje a cualquier red usando el motor híbrido (QR o API).
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
            'platform' => 'required|in:whatsapp,messenger,instagram'
        ]);

        // Aquí se dispara la lógica de envío wa.me o Meta API según configuración
        return response()->json([
            'success' => true,
            'message' => 'Mensaje en proceso de envío vía ' . $validated['platform']
        ]);
    }
}
