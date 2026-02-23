<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailEvent;
use App\Models\Marketing\EmailRecipient;
use App\Models\Marketing\EmailUnsubscribe;
use Illuminate\Support\Facades\Log;

class ProcessEmailWebhookAction
{
    public function execute(string $provider, array $payload): void
    {
        $eventType = $this->normalizeEventType($provider, $payload);
        $messageId = $this->extractMessageId($provider, $payload);

        if (!$eventType || !$messageId) {
            Log::warning('Could not process email webhook', [
                'provider' => $provider,
                'payload' => $payload,
            ]);
            return;
        }

        $recipient = EmailRecipient::where('message_id', $messageId)->first();

        if (!$recipient) {
            Log::info('Recipient not found for webhook', [
                'message_id' => $messageId,
                'event_type' => $eventType,
            ]);
            return;
        }

        $this->processEvent($recipient, $eventType, $payload);
    }

    protected function normalizeEventType(string $provider, array $payload): ?string
    {
        $typeMap = [
            'ses' => [
                'Delivery' => EmailEvent::TYPE_DELIVERED,
                'Open' => EmailEvent::TYPE_OPENED,
                'Click' => EmailEvent::TYPE_CLICKED,
                'Bounce' => EmailEvent::TYPE_BOUNCED,
                'Complaint' => EmailEvent::TYPE_COMPLAINED,
            ],
            'sendgrid' => [
                'delivered' => EmailEvent::TYPE_DELIVERED,
                'open' => EmailEvent::TYPE_OPENED,
                'click' => EmailEvent::TYPE_CLICKED,
                'bounce' => EmailEvent::TYPE_BOUNCED,
                'spamreport' => EmailEvent::TYPE_COMPLAINED,
            ],
            'mailgun' => [
                'delivered' => EmailEvent::TYPE_DELIVERED,
                'opened' => EmailEvent::TYPE_OPENED,
                'clicked' => EmailEvent::TYPE_CLICKED,
                'failed' => EmailEvent::TYPE_BOUNCED,
                'complained' => EmailEvent::TYPE_COMPLAINED,
            ],
        ];

        $providerType = $this->extractEventType($provider, $payload);

        return $typeMap[$provider][$providerType] ?? null;
    }

    protected function extractEventType(string $provider, array $payload): ?string
    {
        return match ($provider) {
            'ses' => $payload['eventType'] ?? $payload['notificationType'] ?? null,
            'sendgrid' => $payload['event'] ?? null,
            'mailgun' => $payload['event'] ?? null,
            default => null,
        };
    }

    protected function extractMessageId(string $provider, array $payload): ?string
    {
        return match ($provider) {
            'ses' => $payload['mail']['messageId'] ?? null,
            'sendgrid' => $payload['sg_message_id'] ?? null,
            'mailgun' => $payload['message-id'] ?? null,
            default => null,
        };
    }

    protected function processEvent(EmailRecipient $recipient, string $eventType, array $payload): void
    {
        $data = [
            'ip_address' => $payload['ip'] ?? $payload['clientIp'] ?? null,
            'user_agent' => $payload['user_agent'] ?? $payload['userAgent'] ?? null,
            'country' => $payload['country'] ?? $payload['geo']?['country'] ?? null,
            'city' => $payload['city'] ?? $payload['geo']?['city'] ?? null,
        ];

        switch ($eventType) {
            case EmailEvent::TYPE_DELIVERED:
                $recipient->markAsDelivered();
                EmailEvent::recordDelivered($recipient, $payload);
                break;

            case EmailEvent::TYPE_OPENED:
                $recipient->recordOpen();
                EmailEvent::recordOpen($recipient, $data);
                break;

            case EmailEvent::TYPE_CLICKED:
                $url = $payload['url'] ?? $payload['link'] ?? '';
                $recipient->recordClick();
                EmailEvent::recordClick($recipient, $url, $data);
                break;

            case EmailEvent::TYPE_BOUNCED:
                $errorCode = $payload['bounce']?['bounceType'] ?? $payload['reason'] ?? null;
                $errorMessage = $payload['bounce']?['bouncedRecipients'][0]?['diagnosticCode'] ?? null;
                $recipient->markAsBounced($errorCode, $errorMessage);
                EmailEvent::recordBounce($recipient, $payload);
                break;

            case EmailEvent::TYPE_COMPLAINED:
                $this->handleComplaint($recipient);
                break;
        }
    }

    protected function handleComplaint(EmailRecipient $recipient): void
    {
        $campaign = $recipient->campaign;

        // Record the complaint as an unsubscribe
        EmailUnsubscribe::recordUnsubscribe(
            $campaign->tenant_id,
            $recipient->email,
            $recipient->user_id,
            $campaign->id,
            'spam',
            'Marked as spam by recipient'
        );

        $recipient->recordUnsubscribe();
    }
}
