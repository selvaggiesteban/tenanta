<?php

namespace App\Http\Controllers\Api\Omnichannel;

use App\Http\Controllers\Controller;
use App\Models\Omnichannel\Conversation;
use App\Models\Omnichannel\Message;
use App\Services\Omnichannel\MetaApiService;
use App\Services\Omnichannel\ConversationAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnifiedInboxController extends Controller
{
    /**
     * Devuelve todas las conversaciones del inquilino actual.
     */
    public function index(): JsonResponse
    {
        $conversations = Conversation::with(['channel', 'contact', 'assignee'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $conversations->items(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'total' => $conversations->total(),
            ]
        ]);
    }

    /**
     * Obtiene métricas de análisis para el dashboard.
     */
    public function analytics(ConversationAnalyticsService $analyticsService): JsonResponse
    {
        $metrics = $analyticsService->getTenantMetrics(auth()->user()->tenant_id);

        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Obtiene el historial de mensajes de una conversación específica.
     */
    public function messages(Conversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Envía un mensaje a cualquier red usando la API oficial correspondiente.
     */
    public function sendMessage(
        Request $request, 
        MetaApiService $metaService, 
        \App\Services\Omnichannel\TelegramApiService $telegramService, 
        \App\Services\Omnichannel\TwilioApiService $twilioService,
        \App\Services\Omnichannel\GoogleBusinessApiService $gbmService,
        \App\Services\Omnichannel\XApiService $xService
    ): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:omnichannel_conversations,id',
            'message' => 'required|string',
            'type' => 'sometimes|string|in:message,note',
        ]);

        $type = $validated['type'] ?? 'message';
        $conversation = Conversation::with('channel')->findOrFail($validated['conversation_id']);
        $channel = $conversation->channel;
        
        $result = ['success' => true]; // Default success for notes

        // Solo enviamos a la red externa si es un mensaje público
        if ($type === 'message') {
            if ($channel->type === 'whatsapp') {
                $result = $metaService->sendWhatsAppMessage($channel, $conversation->external_id, $validated['message']);
            } elseif ($channel->type === 'messenger') {
                $result = $metaService->sendMessengerMessage($channel, $conversation->external_id, $validated['message']);
            } elseif ($channel->type === 'instagram') {
                $result = $metaService->sendInstagramMessage($channel, $conversation->external_id, $validated['message']);
            } elseif ($channel->type === 'telegram') {
                $result = $telegramService->sendMessage($channel, $conversation->external_id, $validated['message']);
            } elseif ($channel->type === 'sms') {
                $result = $twilioService->sendSms($channel, $conversation->external_id, $validated['message']);
            } elseif ($channel->type === 'google_business') {
                $result = $gbmService->sendMessage($channel, $conversation->external_id, $validated['message']);
            } elseif ($channel->type === 'x') {
                $result = $xService->sendDirectMessage($channel, $conversation->external_id, $validated['message']);
            } else {
                $result = ['success' => false, 'error' => 'Canal no soportado para envío externo'];
            }
        }

        if ($result['success']) {
            // Registrar el mensaje (o nota) en la DB
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'external_id' => $result['message_id'] ?? null,
                'type' => $type,
                'direction' => 'outbound',
                'sender_name' => auth()->user()->name,
                'content' => $validated['message'],
                'content_type' => 'text',
                'status' => $type === 'note' ? 'read' : 'sent',
            ]);

            $conversation->update(['last_message_at' => now()]);

            // Broadcast via Laravel Reverb for real-time UI updates
            event(new \App\Events\Omnichannel\MessageReceived($message, $conversation->tenant_id));

            return response()->json([
                'success' => true,
                'data' => $message
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Error desconocido al enviar mensaje'
        ], 422);
    }

    /**
     * Vincula una conversación a un contacto del CRM.
     */
    public function linkContact(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $conversation->update([
            'contact_id' => $validated['contact_id']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversación vinculada al contacto correctamente',
            'data' => $conversation->load('contact')
        ]);
    }

    /**
     * Emite un evento de que el agente está escribiendo.
     */
    public function emitTyping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|string',
            'is_typing' => 'required|boolean',
        ]);

        event(new \App\Events\Omnichannel\AgentTyping(
            $validated['conversation_id'],
            auth()->user()->name,
            auth()->user()->tenant_id,
            $validated['is_typing']
        ));

        return response()->json(['success' => true]);
    }

    /**
     * Asigna un agente a la conversación.
     */
    public function assignAgent(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $conversation->update([
            'assigned_to' => $validated['user_id']
        ]);

        // Asegurarse de que el usuario sea un participante
        \App\Models\Omnichannel\Participant::firstOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => $validated['user_id']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agente asignado correctamente',
            'data' => $conversation->load('assignee')
        ]);
    }

    /**
     * Obtiene una sugerencia de la IA basada en el conocimiento indexado.
     */
    public function suggestResponse(Conversation $conversation, \App\Services\AI\IACopilotService $copilotService): JsonResponse
    {
        $suggestion = $copilotService->suggestResponse($conversation);

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion
        ]);
    }
}
