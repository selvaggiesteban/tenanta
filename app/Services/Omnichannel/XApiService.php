<?php

namespace App\Services\Omnichannel;

use App\Models\Omnichannel\Channel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XApiService
{
    protected string $baseUrl = 'https://api.twitter.com/2';

    /**
     * Envía un mensaje directo vía X (Twitter) API v2.
     */
    public function sendDirectMessage(Channel $channel, string $recipientId, string $text): array
    {
        $credentials = $channel->credentials;
        $accessToken = $credentials['access_token'] ?? null;

        if (!$accessToken) {
            throw new \Exception("Falta Access Token para el canal X (Twitter): {$channel->name}");
        }

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/dm_conversations/with/{$recipientId}/messages", [
                'text' => $text,
            ]);

        if ($response->failed()) {
            Log::error("Error enviando X DM: " . $response->body());
            return [
                'success' => false,
                'error' => $response->json()['errors'][0]['message'] ?? 'Unknown error'
            ];
        }

        return [
            'success' => true,
            'message_id' => $response->json()['data']['id'] ?? null
        ];
    }

    /**
     * Maneja el CRC (Challenge-Response Check) para webhooks de X.
     */
    public function handleCrc(string $crcToken, string $consumerSecret): string
    {
        $hash = hash_hmac('sha256', $crcToken, $consumerSecret, true);
        return 'sha256=' . base64_encode($hash);
    }
}
