<?php

namespace App\Http\Controllers\Api\Omnichannel;

use App\Http\Controllers\Controller;
use App\Models\Omnichannel\Channel;
use App\Jobs\Omnichannel\ProcessInboundMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookOrchestratorController extends Controller
{
    /**
     * Punto de entrada único para Webhooks de Meta (Messenger/WhatsApp).
     */
    public function handleMeta(Request $request)
    {
        // 1. Verificación de reto (Challenge) para configuración inicial
        if ($request->isMethod('get') && $request->has('hub_mode')) {
            return $this->verifyWebhook($request);
        }

        $payload = $request->all();
        
        // 2. Orquestación: Identificar el Canal
        $providerId = $payload['entry'][0]['id'] ?? null;
        
        // Buscamos el canal por provider_id (ID de teléfono o ID de página)
        $channel = Channel::where('provider_id', $providerId)->first();

        if (!$channel) {
            Log::warning("Webhook Meta recibido para provider_id desconocido: {$providerId}");
            return response()->json(['status' => 'ignored'], 200);
        }

        // 3. Despachar Job para procesamiento asíncrono
        ProcessInboundMessage::dispatch($channel, $payload);

        return response()->json(['status' => 'accepted'], 202);
    }

    /**
     * Maneja Webhooks de Telegram.
     * Se espera una URL del tipo: /webhooks/telegram/{token}
     */
    public function handleTelegram(Request $request, $token)
    {
        $payload = $request->all();

        // Buscamos el canal por el token de seguridad (podría ser el provider_id o un campo en credentials)
        $channel = Channel::where('type', 'telegram')
            ->where('credentials->webhook_token', $token)
            ->first();

        if (!$channel) {
            Log::warning("Webhook Telegram recibido con token inválido: {$token}");
            return response()->json(['status' => 'invalid_token'], 403);
        }

        // Despachar Job para procesamiento asíncrono
        ProcessInboundMessage::dispatch($channel, $payload);

        return response()->json(['status' => 'accepted'], 202);
    }

    /**
     * Maneja Webhooks de Twilio (SMS).
     */
    public function handleTwilio(Request $request)
    {
        $payload = $request->all();
        $to = $payload['To'] ?? null;

        // Buscamos el canal por el número de teléfono (provider_id)
        $channel = Channel::where('type', 'sms')
            ->where('provider_id', $to)
            ->first();

        if (!$channel) {
            Log::warning("Webhook Twilio recibido para número desconocido: {$to}");
            return response('Unknown number', 200);
        }

        // Despachar Job para procesamiento asíncrono
        ProcessInboundMessage::dispatch($channel, $payload);

        // Twilio espera una respuesta TwiML válida
        return response('<Response></Response>', 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Maneja Webhooks de Google Business Messages.
     */
    public function handleGoogleBusinessMessages(Request $request)
    {
        $payload = $request->all();
        
        // Identificar el canal (usualmente por el agent_id o similar en el payload)
        $agentId = $payload['agent'] ?? null;
        
        $channel = Channel::where('type', 'google_business')
            ->where('provider_id', $agentId)
            ->first();

        if (!$channel) {
            Log::warning("Webhook GBM recibido para agente desconocido: {$agentId}");
            return response()->json(['status' => 'ignored'], 200);
        }

        ProcessInboundMessage::dispatch($channel, $payload);

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Maneja Webhooks de X (Twitter) incluyendo el CRC check.
     */
    public function handleX(Request $request, \App\Services\Omnichannel\XApiService $xService)
    {
        // 1. Manejo de CRC (Challenge-Response Check)
        if ($request->isMethod('get') && $request->has('crc_token')) {
            $crcToken = $request->get('crc_token');
            // Nota: En un entorno real, el consumer_secret debería ser dinámico por canal o global
            $consumerSecret = config('services.x.consumer_secret'); 
            
            return response()->json([
                'response_token' => $xService->handleCrc($crcToken, $consumerSecret)
            ]);
        }

        $payload = $request->all();
        $userId = $payload['for_user_id'] ?? null;

        $channel = Channel::where('type', 'x')
            ->where('provider_id', $userId)
            ->first();

        if (!$channel) {
            Log::warning("Webhook X recibido para usuario desconocido: {$userId}");
            return response()->json(['status' => 'ignored'], 200);
        }

        ProcessInboundMessage::dispatch($channel, $payload);

        return response()->json(['status' => 'accepted'], 202);
    }

    /**
     * Maneja el callback de OAuth de Google para GBM.
     */
    public function handleGoogleOAuthCallback(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state'); // Generalmente contiene el tenant_id o channel_id

        // Intercambiar código por tokens
        $response = \Illuminate\Support\Facades\Http::post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => route('webhooks.google.callback'),
            'grant_type' => 'authorization_code',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Aquí deberías guardar los tokens en el canal correspondiente
            // Por simplicidad, asumimos que el 'state' nos ayuda a identificarlo
            $channel = Channel::find($state);
            if ($channel) {
                $credentials = $channel->credentials;
                $credentials['access_token'] = $data['access_token'];
                $credentials['refresh_token'] = $data['refresh_token'] ?? ($credentials['refresh_token'] ?? null);
                $channel->update(['credentials' => $credentials]);
                
                return response()->json(['success' => true, 'message' => 'Google Business Messages vinculado correctamente']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Error en la vinculación con Google'], 400);
    }

    /**
     * Valida el Token de verificación enviado por Meta durante el setup del Webhook.
     */
    protected function verifyWebhook(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode === 'subscribe') {
            // Buscamos si algún canal tiene este verify_token en sus credenciales
            $channel = Channel::where('credentials->verify_token', $token)->first();

            if ($channel) {
                Log::info("Webhook Meta verificado para el canal: {$channel->name}");
                return response($challenge, 200);
            }
        }

        Log::error("Falla en verificación de Webhook Meta. Token inválido: {$token}");
        return response('Forbidden', 403);
    }
}
