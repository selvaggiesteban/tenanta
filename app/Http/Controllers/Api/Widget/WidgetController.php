<?php

namespace App\Http\Controllers\Api\Widget;

use App\Http\Controllers\Controller;
use App\Models\Omnichannel\Channel;
use App\Models\Omnichannel\Conversation;
use App\Models\Omnichannel\Message;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    /**
     * Devuelve la configuración pública del widget para un token específico.
     */
    public function settings(Request $request): JsonResponse
    {
        $token = $request->get('token');
        $channel = Channel::where('type', 'web_widget')
            ->where('credentials->widget_token', $token)
            ->firstOrFail();

        // Validación de Dominio
        $referer = $request->headers->get('referer');
        $allowedDomains = $channel->settings['allowed_domains'] ?? [];
        
        if (!empty($allowedDomains)) {
            $isAllowed = false;
            foreach ($allowedDomains as $domain) {
                if (str_contains($referer, $domain)) {
                    $isAllowed = true;
                    break;
                }
            }
            if (!$isAllowed) {
                return response()->json(['message' => 'Domain not authorized'], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $channel->name,
                'primary_color' => $channel->settings['primary_color'] ?? '#3f51b5',
                'welcome_message' => $channel->settings['welcome_message'] ?? '¿En qué podemos ayudarte?',
                'logo_url' => $channel->settings['logo_url'] ?? null,
            ]
        ]);
    }

    /**
     * Inicializa una sesión de invitado (Pre-chat form).
     */
    public function init(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'nullable|string',
        ]);

        $channel = Channel::where('credentials->widget_token', $validated['token'])->firstOrFail();

        // 1. Crear Lead automáticamente
        $lead = Lead::create([
            'tenant_id' => $channel->tenant_id,
            'first_name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'source' => 'web_widget',
            'status' => 'new'
        ]);

        // 2. Crear Conversación
        $conversation = Conversation::create([
            'tenant_id' => $channel->tenant_id,
            'channel_id' => $channel->id,
            'external_id' => (string) Str::uuid(),
            'status' => 'open',
            'last_message_at' => now(),
            'metadata' => [
                'guest_name' => $validated['name'],
                'guest_email' => $validated['email'],
                'lead_id' => $lead->id
            ]
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $conversation->external_id,
            'conversation_id' => $conversation->id
        ]);
    }
}
