<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailTemplate;

class UpdateEmailTemplateAction
{
    public function execute(EmailTemplate $template, array $data): EmailTemplate
    {
        $template->update([
            'name' => $data['name'] ?? $template->name,
            'subject' => $data['subject'] ?? $template->subject,
            'content_html' => $data['content_html'] ?? $template->content_html,
            'content_text' => $data['content_text'] ?? $template->content_text,
            'type' => $data['type'] ?? $template->type,
            'category' => $data['category'] ?? $template->category,
            'variables' => $data['variables'] ?? $template->variables,
            'settings' => $data['settings'] ?? $template->settings,
            'is_active' => $data['is_active'] ?? $template->is_active,
        ]);

        return $template->fresh();
    }
}
