<?php

namespace App\Jobs\Omnichannel;

use App\Models\Omnichannel\Channel;
use App\Models\Omnichannel\Conversation;
use App\Models\Omnichannel\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInboundMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Channel $channel;
    protected array $payload;

    public function __construct(Channel $channel, array $payload)
    {
        $this->channel = $channel;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        try {
            if ($this->channel->type === 'whatsapp') {
                $this->processWhatsApp();
            } elseif ($this->channel->type === 'messenger') {
                $this->processMessenger();
            } elseif ($this->channel->type === 'instagram') {
                $this->processInstagram();
            } elseif ($this->channel->type === 'telegram') {
                $this->processTelegram();
            } elseif ($this->channel->type === 'sms') {
                $this->processSms();
            } elseif ($this->channel->type === 'google_business') {
                $this->processGoogleBusinessMessages();
            } elseif ($this->channel->type === 'x') {
                $this->processX();
            }
        } catch (\Exception $e) {
            Log::error("Error procesando mensaje entrante: " . $e->getMessage(), [
                'channel_id' => $this->channel->id,
                'payload' => $this->payload
            ]);
        }
    }

    protected function processSms(): void
    {
        $senderId = $this->payload['From'] ?? null;
        if (!$senderId) return;

        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $this->channel->tenant_id,
                'channel_id' => $this->channel->id,
                'external_id' => $senderId,
            ],
            [
                'status' => 'open',
                'last_message_at' => now(),
            ]
        );

        $content = $this->payload['Body'] ?? '[Sin contenido]';
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'external_id' => $this->payload['MessageSid'] ?? uniqid('sms_'),
            'direction' => 'inbound',
            'sender_name' => 'Usuario SMS',
            'sender_identifier' => $senderId,
            'content' => $content,
            'content_type' => 'text',
            'status' => 'delivered',
            'sentiment' => $this->getSentiment($content),
            'raw_payload' => $this->payload
        ]);

        $conversation->update(['last_message_at' => now()]);

        event(new \App\Events\Omnichannel\MessageReceived($message, $this->channel->tenant_id));
    }

    protected function processInstagram(): void
    {
        $entry = $this->payload['entry'][0]['messaging'][0] ?? null;
        if (!$entry || !isset($entry['message'])) return;

        $senderId = $entry['sender']['id'];

        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $this->channel->tenant_id,
                'channel_id' => $this->channel->id,
                'external_id' => $senderId,
            ],
            [
                'status' => 'open',
                'last_message_at' => now(),
            ]
        );

        $content = $entry['message']['text'] ?? '[Adjunto/Multimedia]';
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'external_id' => $entry['message']['mid'],
            'direction' => 'inbound',
            'sender_name' => 'Usuario Instagram',
            'sender_identifier' => $senderId,
            'content' => $content,
            'content_type' => isset($entry['message']['attachments']) ? 'document' : 'text',
            'status' => 'delivered',
            'sentiment' => $this->getSentiment($content),
            'raw_payload' => $entry
        ]);

        $conversation->update(['last_message_at' => now()]);

        event(new \App\Events\Omnichannel\MessageReceived($message, $this->channel->tenant_id));
    }

    protected function processTelegram(): void
    {
        $messageData = $this->payload['message'] ?? null;
        if (!$messageData) return;

        $senderId = $messageData['from']['id'];
        $senderName = trim(($messageData['from']['first_name'] ?? '') . ' ' . ($messageData['from']['last_name'] ?? ''));
        if (empty($senderName)) {
            $senderName = $messageData['from']['username'] ?? 'Usuario Telegram';
        }

        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $this->channel->tenant_id,
                'channel_id' => $this->channel->id,
                'external_id' => (string) $senderId,
            ],
            [
                'status' => 'open',
                'last_message_at' => now(),
                'metadata' => ['sender_name' => $senderName]
            ]
        );

        $content = $messageData['text'] ?? '[Multimedia/Comando]';
        $contentType = 'text';
        if (isset($messageData['photo'])) $contentType = 'image';
        if (isset($messageData['document'])) $contentType = 'document';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'external_id' => (string) $messageData['message_id'],
            'direction' => 'inbound',
            'sender_name' => $senderName,
            'sender_identifier' => (string) $senderId,
            'content' => $content,
            'content_type' => $contentType,
            'status' => 'delivered',
            'sentiment' => $this->getSentiment($content),
            'raw_payload' => $messageData
        ]);

        $conversation->update(['last_message_at' => now()]);

        event(new \App\Events\Omnichannel\MessageReceived($message, $this->channel->tenant_id));
    }

    protected function processWhatsApp(): void
    {
        $entry = $this->payload['entry'][0]['changes'][0]['value'] ?? null;
        if (!$entry || !isset($entry['messages'])) return;

        foreach ($entry['messages'] as $waMsg) {
            $senderId = $waMsg['from'];
            $senderName = $entry['contacts'][0]['profile']['name'] ?? 'Usuario WhatsApp';

            $conversation = Conversation::firstOrCreate(
                [
                    'tenant_id' => $this->channel->tenant_id,
                    'channel_id' => $this->channel->id,
                    'external_id' => $senderId,
                ],
                [
                    'status' => 'open',
                    'last_message_at' => now(),
                    'metadata' => ['sender_name' => $senderName]
                ]
            );

            $content = $this->getWhatsAppContent($waMsg);

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'external_id' => $waMsg['id'],
                'direction' => 'inbound',
                'sender_name' => $senderName,
                'sender_identifier' => $senderId,
                'content' => $content,
                'content_type' => $waMsg['type'] ?? 'text',
                'status' => 'delivered',
                'sentiment' => $this->getSentiment($content),
                'raw_payload' => $waMsg
            ]);

            $conversation->update(['last_message_at' => now()]);

            // Broadcast via Laravel Reverb
            event(new \App\Events\Omnichannel\MessageReceived($message, $this->channel->tenant_id));
        }
    }

    protected function processMessenger(): void
    {
        $entry = $this->payload['entry'][0]['messaging'][0] ?? null;
        if (!$entry || !isset($entry['message'])) return;

        $senderId = $entry['sender']['id'];

        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $this->channel->tenant_id,
                'channel_id' => $this->channel->id,
                'external_id' => $senderId,
            ],
            [
                'status' => 'open',
                'last_message_at' => now(),
            ]
        );

        $content = $entry['message']['text'] ?? '[Adjunto/Multimedia]';
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'external_id' => $entry['message']['mid'],
            'direction' => 'inbound',
            'sender_name' => 'Usuario Messenger',
            'sender_identifier' => $senderId,
            'content' => $content,
            'content_type' => isset($entry['message']['attachments']) ? 'document' : 'text',
            'status' => 'delivered',
            'sentiment' => $this->getSentiment($content),
            'raw_payload' => $entry
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Broadcast via Laravel Reverb
        event(new \App\Events\Omnichannel\MessageReceived($message, $this->channel->tenant_id));
    }

    protected function processGoogleBusinessMessages(): void
    {
        $conversationId = $this->payload['conversationId'] ?? null;
        $messageData = $this->payload['message'] ?? null;
        if (!$conversationId || !$messageData) return;

        $senderName = $this->payload['context']['userInfo']['displayName'] ?? 'Usuario Google';

        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $this->channel->tenant_id,
                'channel_id' => $this->channel->id,
                'external_id' => $conversationId,
            ],
            [
                'status' => 'open',
                'last_message_at' => now(),
                'metadata' => ['sender_name' => $senderName]
            ]
        );

        $content = $messageData['text'] ?? '[Contenido no soportado]';
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'external_id' => $messageData['messageId'],
            'direction' => 'inbound',
            'sender_name' => $senderName,
            'sender_identifier' => $conversationId,
            'content' => $content,
            'content_type' => 'text',
            'status' => 'delivered',
            'sentiment' => $this->getSentiment($content),
            'raw_payload' => $this->payload
        ]);

        $conversation->update(['last_message_at' => now()]);

        event(new \App\Events\Omnichannel\MessageReceived($message, $this->channel->tenant_id));
    }

    protected function processX(): void
    {
        $dmEvent = $this->payload['direct_message_events'][0] ?? null;
        if (!$dmEvent || $dmEvent['type'] !== 'message_create') return;

        $senderId = $dmEvent['message_create']['sender_id'];
        
        // Evitar procesar mensajes propios
        if ($senderId === $this->channel->provider_id) return;

        $conversation = Conversation::firstOrCreate(
            [
                'tenant_id' => $this->channel->tenant_id,
                'channel_id' => $this->channel->id,
                'external_id' => $senderId,
            ],
            [
                'status' => 'open',
                'last_message_at' => now(),
            ]
        );

        $content = $dmEvent['message_create']['message_data']['text'] ?? '[Multimedia]';
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'external_id' => $dmEvent['id'],
            'direction' => 'inbound',
            'sender_name' => 'Usuario X',
            'sender_identifier' => $senderId,
            'content' => $content,
            'content_type' => 'text',
            'status' => 'delivered',
            'sentiment' => $this->getSentiment($content),
            'raw_payload' => $dmEvent
        ]);

        $conversation->update(['last_message_at' => now()]);

        event(new \App\Events\Omnichannel\MessageReceived($message, $this->channel->tenant_id));
    }

    protected function getWhatsAppContent(array $msg): string
    {
        return match ($msg['type']) {
            'text' => $msg['text']['body'],
            'image' => '[Imagen]',
            'audio' => '[Audio]',
            'video' => '[Video]',
            'document' => '[Documento]',
            'sticker' => '[Sticker]',
            default => '[Mensaje no soportado]',
        };
    }

    protected function getSentiment(string $content): float
    {
        // Solo analizar sentimiento si hay contenido de texto real y no es un placeholder
        if (empty($content) || str_starts_with($content, '[')) {
            return 0;
        }

        try {
            return app(\App\Services\AI\IACopilotService::class)->detectSentiment($content);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
