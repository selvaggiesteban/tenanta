<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailRecipient;
use App\Models\Marketing\EmailUnsubscribe;

class HandleUnsubscribeAction
{
    public function execute(
        int $recipientId,
        string $hash,
        ?string $reason = null,
        ?string $feedback = null,
        array $metadata = []
    ): bool {
        $recipient = EmailRecipient::find($recipientId);

        if (!$recipient) {
            return false;
        }

        // Verify hash
        $expectedHash = hash_hmac('sha256', $recipient->id . $recipient->email, config('app.key'));
        if (!hash_equals($expectedHash, $hash)) {
            return false;
        }

        $campaign = $recipient->campaign;

        // Record global unsubscribe
        EmailUnsubscribe::recordUnsubscribe(
            $campaign->tenant_id,
            $recipient->email,
            $recipient->user_id,
            $campaign->id,
            $reason,
            $feedback,
            $metadata
        );

        // Update recipient
        $recipient->recordUnsubscribe();

        return true;
    }

    public function resubscribe(int $tenantId, string $email): bool
    {
        $unsubscribe = EmailUnsubscribe::where('tenant_id', $tenantId)
            ->where('email', $email)
            ->first();

        if (!$unsubscribe) {
            return false;
        }

        $unsubscribe->delete();

        return true;
    }

    public function getUnsubscribeReasons(): array
    {
        return EmailUnsubscribe::REASONS;
    }
}
