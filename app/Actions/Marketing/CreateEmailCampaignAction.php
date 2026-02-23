<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailTemplate;

class CreateEmailCampaignAction
{
    public function execute(int $tenantId, int $userId, array $data): EmailCampaign
    {
        $campaign = EmailCampaign::create([
            'tenant_id' => $tenantId,
            'created_by' => $userId,
            'template_id' => $data['template_id'] ?? null,
            'name' => $data['name'],
            'subject' => $data['subject'],
            'from_name' => $data['from_name'],
            'from_email' => $data['from_email'],
            'reply_to' => $data['reply_to'] ?? null,
            'content_html' => $data['content_html'] ?? null,
            'content_text' => $data['content_text'] ?? null,
            'type' => $data['type'] ?? EmailCampaign::TYPE_REGULAR,
            'status' => EmailCampaign::STATUS_DRAFT,
            'settings' => $data['settings'] ?? [],
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        // If template provided, copy content from template
        if (isset($data['template_id']) && !isset($data['content_html'])) {
            $template = EmailTemplate::find($data['template_id']);
            if ($template) {
                $campaign->update([
                    'content_html' => $template->content_html,
                    'content_text' => $template->content_text,
                ]);
            }
        }

        return $campaign;
    }
}
