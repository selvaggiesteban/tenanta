<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailTemplate;

class CreateEmailTemplateAction
{
    public function execute(int $tenantId, int $userId, array $data): EmailTemplate
    {
        return EmailTemplate::create([
            'tenant_id' => $tenantId,
            'created_by' => $userId,
            'name' => $data['name'],
            'subject' => $data['subject'],
            'content_html' => $data['content_html'],
            'content_text' => $data['content_text'] ?? strip_tags($data['content_html']),
            'type' => $data['type'] ?? EmailTemplate::TYPE_MARKETING,
            'category' => $data['category'] ?? null,
            'variables' => $data['variables'] ?? [],
            'settings' => $data['settings'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
