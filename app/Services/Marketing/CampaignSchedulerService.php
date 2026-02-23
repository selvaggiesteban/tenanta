<?php

namespace App\Services\Marketing;

use App\Actions\Marketing\SendCampaignAction;
use App\Models\Marketing\EmailCampaign;
use Illuminate\Support\Facades\Log;

class CampaignSchedulerService
{
    public function __construct(
        protected SendCampaignAction $sendCampaignAction
    ) {}

    /**
     * Process scheduled campaigns that are due.
     * This should be called from a scheduled command (e.g., every minute).
     */
    public function processScheduledCampaigns(): int
    {
        $campaigns = EmailCampaign::where('status', EmailCampaign::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now())
            ->get();

        $processed = 0;

        foreach ($campaigns as $campaign) {
            try {
                Log::info('Processing scheduled campaign', [
                    'campaign_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'scheduled_at' => $campaign->scheduled_at,
                ]);

                $this->sendCampaignAction->execute($campaign);
                $processed++;

                Log::info('Scheduled campaign sent successfully', [
                    'campaign_id' => $campaign->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send scheduled campaign', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processed;
    }

    /**
     * Get upcoming scheduled campaigns.
     */
    public function getUpcomingCampaigns(int $tenantId, int $limit = 10): array
    {
        return EmailCampaign::where('tenant_id', $tenantId)
            ->where('status', EmailCampaign::STATUS_SCHEDULED)
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'scheduled_at' => $c->scheduled_at,
                'recipient_count' => $c->recipient_count,
            ])
            ->toArray();
    }
}
