<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailList;
use App\Models\Marketing\EmailRecipient;
use App\Models\Marketing\EmailUnsubscribe;
use App\Models\User;
use Illuminate\Support\Collection;

class AddCampaignRecipientsAction
{
    public function fromList(EmailCampaign $campaign, EmailList $list): int
    {
        $subscribers = $list->activeSubscribers()->get();
        return $this->addRecipients($campaign, $subscribers->map(fn($s) => [
            'email' => $s->email,
            'name' => $s->name,
            'user_id' => $s->user_id,
        ]));
    }

    public function fromUsers(EmailCampaign $campaign, Collection $users): int
    {
        return $this->addRecipients($campaign, $users->map(fn($u) => [
            'email' => $u->email,
            'name' => $u->name,
            'user_id' => $u->id,
        ]));
    }

    public function fromEmails(EmailCampaign $campaign, array $emails): int
    {
        return $this->addRecipients($campaign, collect($emails)->map(fn($e) => [
            'email' => is_array($e) ? $e['email'] : $e,
            'name' => is_array($e) ? ($e['name'] ?? null) : null,
            'user_id' => null,
        ]));
    }

    protected function addRecipients(EmailCampaign $campaign, Collection $recipients): int
    {
        $tenantId = $campaign->tenant_id;
        $added = 0;

        foreach ($recipients as $recipient) {
            // Skip if globally unsubscribed
            if (EmailUnsubscribe::isUnsubscribed($tenantId, $recipient['email'])) {
                continue;
            }

            // Skip if already a recipient
            if ($campaign->recipients()->where('email', $recipient['email'])->exists()) {
                continue;
            }

            EmailRecipient::create([
                'campaign_id' => $campaign->id,
                'user_id' => $recipient['user_id'],
                'email' => $recipient['email'],
                'name' => $recipient['name'],
                'status' => EmailRecipient::STATUS_PENDING,
            ]);

            $added++;
        }

        // Update campaign recipient count
        $campaign->update([
            'recipient_count' => $campaign->recipients()->count(),
        ]);

        return $added;
    }

    public function removeRecipient(EmailCampaign $campaign, string $email): bool
    {
        $deleted = $campaign->recipients()->where('email', $email)->delete();

        if ($deleted) {
            $campaign->update([
                'recipient_count' => $campaign->recipients()->count(),
            ]);
        }

        return $deleted > 0;
    }
}
