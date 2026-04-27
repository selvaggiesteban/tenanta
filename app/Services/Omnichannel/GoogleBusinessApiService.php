<?php

namespace App\Services\Omnichannel;

use App\Models\Omnichannel\Channel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBusinessApiService
{
    protected string $baseUrl = 'https://businessmessages.googleapis.com/v1';

    /**
     * Envía un mensaje vía Google Business Messages API.
     */
    public function sendMessage(Channel $channel, string $conversationId, string $text): array
    {
        $credentials = $channel->credentials;
        $accessToken = $credentials['access_token'] ?? null;

        if (!$accessToken) {
            throw new \Exception("Falta Access Token para el canal Google Business Messages: {$channel->name}");
        }

        $messageId = uniqid('gbm_');

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/conversations/{$conversationId}/messages", [
                'messageId' => $messageId,
                'representative' => [
                    'representativeType' => 'HUMAN',
                    'displayName' => auth()->user()->name ?? 'Support Agent',
                ],
                'text' => $text,
                'fallback' => $text,
            ]);

        if ($response->failed()) {
            // Si el token expiró, podrías intentar refrescarlo aquí si tienes el refresh_token
            Log::error("Error enviando Google Business Message: " . $response->body());
            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error'
            ];
        }

        return [
            'success' => true,
            'message_id' => $messageId
        ];
    }

    /**
     * Refresca el Access Token usando el Refresh Token.
     */
    public function refreshToken(Channel $channel): bool
    {
        $credentials = $channel->credentials;
        $refreshToken = $credentials['refresh_token'] ?? null;
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (!$refreshToken || !$clientId || !$clientSecret) {
            return false;
        }

        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $credentials['access_token'] = $data['access_token'];
            if (isset($data['expires_in'])) {
                $credentials['expires_at'] = now()->addSeconds($data['expires_in'])->toDateTimeString();
            }
            $channel->update(['credentials' => $credentials]);
            return true;
        }

        return false;
    }
}
