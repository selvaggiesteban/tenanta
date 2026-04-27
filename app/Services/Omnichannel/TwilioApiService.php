<?php

namespace App\Services\Omnichannel;

use App\Models\Omnichannel\Channel;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioApiService
{
    /**
     * Envía un mensaje SMS vía Twilio.
     */
    public function sendSms(Channel $channel, string $to, string $text): array
    {
        $credentials = $channel->credentials;
        $sid = $credentials['twilio_sid'] ?? null;
        $token = $credentials['twilio_token'] ?? null;
        $from = $channel->provider_id; // El número de Twilio

        if (!$sid || !$token || !$from) {
            throw new \Exception("Configuración de Twilio incompleta para el canal: {$channel->name}");
        }

        try {
            $client = new Client($sid, $token);
            $message = $client->messages->create($to, [
                'from' => $from,
                'body' => $text,
            ]);

            return [
                'success' => true,
                'message_id' => $message->sid
            ];
        } catch (\Exception $e) {
            Log::error("Error enviando SMS vía Twilio: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
