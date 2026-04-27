<?php

namespace App\Services\Omnichannel;

use App\Models\Omnichannel\Channel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramApiService
{
    protected string $baseUrl = 'https://api.telegram.org/bot';

    /**
     * Envía un mensaje de texto vía Telegram Bot API.
     */
    public function sendMessage(Channel $channel, string $chatId, string $text): array
    {
        $credentials = $channel->credentials;
        $botToken = $credentials['bot_token'] ?? null;

        if (!$botToken) {
            throw new \Exception("Falta Bot Token para el canal Telegram: {$channel->name}");
        }

        $response = Http::post("{$this->baseUrl}{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);

        if ($response->failed()) {
            Log::error("Error enviando Telegram: " . $response->body());
            return [
                'success' => false,
                'error' => $response->json()['description'] ?? 'Unknown error'
            ];
        }

        return [
            'success' => true,
            'message_id' => $response->json()['result']['message_id'] ?? null
        ];
    }

    /**
     * Configura el webhook para el bot de Telegram.
     */
    public function setWebhook(Channel $channel, string $url): bool
    {
        $credentials = $channel->credentials;
        $botToken = $credentials['bot_token'] ?? null;

        if (!$botToken) return false;

        $response = Http::post("{$this->baseUrl}{$botToken}/setWebhook", [
            'url' => $url,
        ]);

        return $response->successful();
    }
}
