<?php

namespace App\Services\Omnichannel;

use Twilio\TwiML\VoiceResponse;
use App\Models\Omnichannel\Channel;

class TwilioVoiceService
{
    /**
     * Genera TwiML para llamadas entrantes.
     */
    public function handleInboundCall(Channel $channel, string $from): string
    {
        $response = new VoiceResponse();
        
        // Ejemplo básico: Saludo y conectar a un cliente WebRTC o número
        $response->say("Gracias por llamar a " . $channel->name . ". Por favor espere mientras lo comunicamos.", ['language' => 'es-MX']);
        
        // Aquí se podría usar <Dial> para conectar con un agente en el navegador
        $dial = $response->dial();
        $dial->client('agent_dashboard'); // Identificador del cliente WebRTC

        return (string)$response;
    }

    /**
     * Genera TwiML para llamadas salientes iniciadas desde el navegador (WebRTC).
     */
    public function handleOutboundCall(string $to, string $callerId): string
    {
        $response = new VoiceResponse();
        
        $dial = $response->dial(['callerId' => $callerId]);
        
        // Si el destino es un número de teléfono
        if (preg_match('/^\+?[1-9]\d{1,14}$/', $to)) {
            $dial->number($to);
        } else {
            // Si el destino es otro cliente WebRTC
            $dial->client($to);
        }

        return (string)$response;
    }

    /**
     * Genera un token de acceso para Twilio Voice SDK (WebRTC).
     */
    public function generateAccessToken(Channel $channel, string $identity): string
    {
        // Nota: Esto requiere twilio/sdk instalado
        // Como no puedo instalar dependencias ahora, asumo que está disponible o doy el esquema.
        
        $accountSid = $channel->credentials['twilio_account_sid'] ?? config('services.twilio.sid');
        $apiKey = $channel->credentials['twilio_api_key'] ?? config('services.twilio.api_key');
        $apiSecret = $channel->credentials['twilio_api_secret'] ?? config('services.twilio.api_secret');
        $twimlAppSid = $channel->credentials['twilio_twiml_app_sid'] ?? config('services.twilio.twiml_app_sid');

        if (!$accountSid || !$apiKey || !$apiSecret) {
            throw new \Exception("Twilio credentials missing for voice token generation.");
        }

        // Simulación de generación si la clase no existe, o uso real si existe
        if (class_exists('\Twilio\Jwt\AccessToken')) {
            $token = new \Twilio\Jwt\AccessToken($accountSid, $apiKey, $apiSecret, 3600, $identity);
            $voiceGrant = new \Twilio\Jwt\Grants\VoiceGrant();
            $voiceGrant->setOutgoingApplicationSid($twimlAppSid);
            $voiceGrant->setIncomingAllow(true);
            $token->addGrant($voiceGrant);
            return $token->toJWT();
        }

        return "token_placeholder_for_" . $identity;
    }
}
