<?php

namespace App\Services\Omnichannel;

use App\Models\Omnichannel\Channel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaApiService
{
    protected string $baseUrl = 'https://graph.facebook.com/v19.0';

    /**
     * Envía un mensaje de texto vía WhatsApp Cloud API.
     */
    public function sendWhatsAppMessage(Channel $channel, string $to, string $text): array
    {
        $credentials = $channel->credentials;
        $phoneNumberId = $channel->provider_id;
        $accessToken = $credentials['access_token'] ?? null;

        if (!$phoneNumberId || !$accessToken) {
            throw new \Exception("Configuración de WhatsApp incompleta para el canal: {$channel->name}");
        }

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $text],
            ]);

        if ($response->failed()) {
            Log::error("Error enviando WhatsApp: " . $response->body());
            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error'
            ];
        }

        return [
            'success' => true,
            'message_id' => $response->json()['messages'][0]['id'] ?? null
        ];
    }

    /**
     * Envía un mensaje de plantilla (Template) vía WhatsApp.
     * Requerido para iniciar conversaciones después de 24h.
     */
    public function sendWhatsAppTemplate(Channel $channel, string $to, string $templateName, string $languageCode = 'es', array $components = []): array
    {
        $credentials = $channel->credentials;
        $phoneNumberId = $channel->provider_id;
        $accessToken = $credentials['access_token'] ?? null;

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => $languageCode],
                    'components' => $components
                ],
            ]);

        if ($response->failed()) {
            Log::error("Error enviando WhatsApp Template: " . $response->body());
            return ['success' => false, 'error' => $response->json()['error']['message'] ?? 'Unknown error'];
        }

        return ['success' => true, 'message_id' => $response->json()['messages'][0]['id'] ?? null];
    }

    /**
     * Envía un mensaje vía Messenger API.
     */
    public function sendMessengerMessage(Channel $channel, string $recipientId, string $text): array
    {
        $credentials = $channel->credentials;
        $accessToken = $credentials['page_access_token'] ?? null;

        if (!$accessToken) {
            throw new \Exception("Falta Page Access Token para el canal Messenger: {$channel->name}");
        }

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/me/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $text],
                'messaging_type' => 'RESPONSE',
            ]);

        if ($response->failed()) {
            Log::error("Error enviando Messenger: " . $response->body());
            return ['success' => false, 'error' => $response->json()['error']['message'] ?? 'Unknown error'];
        }

        return [
            'success' => true,
            'message_id' => $response->json()['message_id'] ?? null
        ];
    }

    /**
     * Envía un mensaje vía Instagram Graph API (DMs).
     */
    public function sendInstagramMessage(Channel $channel, string $recipientId, string $text): array
    {
        $credentials = $channel->credentials;
        $accessToken = $credentials['page_access_token'] ?? null;

        if (!$accessToken) {
            throw new \Exception("Falta Page Access Token para el canal Instagram: {$channel->name}");
        }

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/me/messages", [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $text],
                'tag' => 'POST_PURCHASE', // Opcional, dependiendo de la política de ventana de 24h
            ]);

        if ($response->failed()) {
            Log::error("Error enviando Instagram: " . $response->body());
            return ['success' => false, 'error' => $response->json()['error']['message'] ?? 'Unknown error'];
        }

        return [
            'success' => true,
            'message_id' => $response->json()['message_id'] ?? null
        ];
    }
}
