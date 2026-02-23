<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailRecipient;
use App\Services\Marketing\EmailSenderService;
use Illuminate\Support\Facades\Log;

class SendCampaignAction
{
    public function __construct(
        protected EmailSenderService $emailSender
    ) {}

    public function execute(EmailCampaign $campaign): void
    {
        if (!in_array($campaign->status, [EmailCampaign::STATUS_DRAFT, EmailCampaign::STATUS_SCHEDULED])) {
            throw new \Exception('La campaña no puede ser enviada en su estado actual');
        }

        if ($campaign->recipient_count === 0) {
            throw new \Exception('La campaña debe tener al menos un destinatario');
        }

        // Mark as sending
        $campaign->update([
            'status' => EmailCampaign::STATUS_SENDING,
            'started_at' => now(),
        ]);

        try {
            $this->processRecipients($campaign);

            // Mark as sent
            $campaign->update([
                'status' => EmailCampaign::STATUS_SENT,
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            // Check if any emails were sent
            $sentCount = $campaign->recipients()->whereNotNull('sent_at')->count();

            if ($sentCount > 0) {
                // Partial send - mark as sent with errors
                $campaign->update([
                    'status' => EmailCampaign::STATUS_SENT,
                    'completed_at' => now(),
                ]);
            } else {
                // No emails sent - revert to draft
                $campaign->update([
                    'status' => EmailCampaign::STATUS_DRAFT,
                    'started_at' => null,
                ]);
            }

            throw $e;
        }
    }

    protected function processRecipients(EmailCampaign $campaign): void
    {
        $recipients = $campaign->recipients()
            ->where('status', EmailRecipient::STATUS_PENDING)
            ->cursor();

        foreach ($recipients as $recipient) {
            try {
                $this->sendToRecipient($campaign, $recipient);
            } catch (\Exception $e) {
                Log::warning('Failed to send to recipient', [
                    'recipient_id' => $recipient->id,
                    'email' => $recipient->email,
                    'error' => $e->getMessage(),
                ]);

                $recipient->markAsFailed(
                    $e->getCode() ?: 'SEND_ERROR',
                    $e->getMessage()
                );
            }
        }
    }

    protected function sendToRecipient(EmailCampaign $campaign, EmailRecipient $recipient): void
    {
        // Render content with merge fields
        $html = $this->renderContent($campaign->content_html, $recipient);
        $text = $campaign->content_text
            ? $this->renderContent($campaign->content_text, $recipient)
            : strip_tags($html);

        // Add tracking pixel
        $html = $this->addTrackingPixel($html, $recipient);

        // Replace links with tracking links
        $html = $this->addLinkTracking($html, $recipient);

        // Send via email service
        $result = $this->emailSender->send([
            'to' => $recipient->email,
            'to_name' => $recipient->name,
            'from' => $campaign->from_email,
            'from_name' => $campaign->from_name,
            'reply_to' => $campaign->reply_to,
            'subject' => $this->renderContent($campaign->subject, $recipient),
            'html' => $html,
            'text' => $text,
            'headers' => [
                'X-Campaign-ID' => $campaign->id,
                'X-Recipient-ID' => $recipient->id,
            ],
        ]);

        // Mark as sent
        $recipient->markAsSent($result['message_id'], $result['provider']);
    }

    protected function renderContent(string $content, EmailRecipient $recipient): string
    {
        $mergeFields = array_merge(
            [
                'email' => $recipient->email,
                'name' => $recipient->name ?? '',
                'first_name' => explode(' ', $recipient->name ?? '')[0] ?? '',
            ],
            $recipient->merge_fields ?? []
        );

        foreach ($mergeFields as $key => $value) {
            $content = str_replace(
                ['{{' . $key . '}}', '{{ ' . $key . ' }}'],
                $value,
                $content
            );
        }

        return $content;
    }

    protected function addTrackingPixel(string $html, EmailRecipient $recipient): string
    {
        $trackingUrl = route('email.track.open', [
            'recipient' => $recipient->id,
            'hash' => $this->generateTrackingHash($recipient),
        ]);

        $pixel = '<img src="' . $trackingUrl . '" width="1" height="1" alt="" />';

        // Insert before closing body tag
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $pixel . '</body>', $html);
        } else {
            $html .= $pixel;
        }

        return $html;
    }

    protected function addLinkTracking(string $html, EmailRecipient $recipient): string
    {
        return preg_replace_callback(
            '/<a\s+([^>]*href=["\'])([^"\']+)(["\'][^>]*)>/i',
            function ($matches) use ($recipient) {
                $url = $matches[2];

                // Skip tracking for unsubscribe links
                if (str_contains($url, 'unsubscribe')) {
                    return $matches[0];
                }

                $trackingUrl = route('email.track.click', [
                    'recipient' => $recipient->id,
                    'hash' => $this->generateTrackingHash($recipient),
                    'url' => base64_encode($url),
                ]);

                return '<a ' . $matches[1] . $trackingUrl . $matches[3] . '>';
            },
            $html
        );
    }

    protected function generateTrackingHash(EmailRecipient $recipient): string
    {
        return hash_hmac('sha256', $recipient->id . $recipient->email, config('app.key'));
    }
}
