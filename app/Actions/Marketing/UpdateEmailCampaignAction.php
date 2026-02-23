<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailCampaign;

class UpdateEmailCampaignAction
{
    public function execute(EmailCampaign $campaign, array $data): EmailCampaign
    {
        if ($campaign->status !== EmailCampaign::STATUS_DRAFT) {
            throw new \Exception('Solo se pueden editar campañas en estado borrador');
        }

        $campaign->update([
            'template_id' => $data['template_id'] ?? $campaign->template_id,
            'name' => $data['name'] ?? $campaign->name,
            'subject' => $data['subject'] ?? $campaign->subject,
            'from_name' => $data['from_name'] ?? $campaign->from_name,
            'from_email' => $data['from_email'] ?? $campaign->from_email,
            'reply_to' => $data['reply_to'] ?? $campaign->reply_to,
            'content_html' => $data['content_html'] ?? $campaign->content_html,
            'content_text' => $data['content_text'] ?? $campaign->content_text,
            'type' => $data['type'] ?? $campaign->type,
            'settings' => $data['settings'] ?? $campaign->settings,
            'scheduled_at' => $data['scheduled_at'] ?? $campaign->scheduled_at,
        ]);

        return $campaign->fresh();
    }
}
