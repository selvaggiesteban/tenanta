<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailCampaign;
use App\Models\Marketing\EmailEvent;
use App\Models\Marketing\EmailRecipient;
use Illuminate\Support\Facades\DB;

class GetCampaignStatsAction
{
    public function execute(EmailCampaign $campaign): array
    {
        $recipientCount = $campaign->recipient_count;

        if ($recipientCount === 0) {
            return $this->emptyStats();
        }

        return [
            'overview' => [
                'recipient_count' => $recipientCount,
                'sent_count' => $campaign->sent_count,
                'delivered_count' => $campaign->delivered_count,
                'opened_count' => $campaign->opened_count,
                'clicked_count' => $campaign->clicked_count,
                'bounced_count' => $campaign->bounced_count,
                'unsubscribed_count' => $campaign->unsubscribed_count,
            ],
            'rates' => [
                'delivery_rate' => $this->percentage($campaign->delivered_count, $campaign->sent_count),
                'open_rate' => $this->percentage($campaign->opened_count, $campaign->delivered_count),
                'click_rate' => $this->percentage($campaign->clicked_count, $campaign->delivered_count),
                'bounce_rate' => $this->percentage($campaign->bounced_count, $campaign->sent_count),
                'unsubscribe_rate' => $this->percentage($campaign->unsubscribed_count, $campaign->delivered_count),
                'click_to_open_rate' => $this->percentage($campaign->clicked_count, $campaign->opened_count),
            ],
            'engagement' => $this->getEngagementStats($campaign),
            'top_links' => $this->getTopLinks($campaign),
            'device_stats' => $this->getDeviceStats($campaign),
            'geo_stats' => $this->getGeoStats($campaign),
            'timeline' => $this->getTimeline($campaign),
        ];
    }

    protected function emptyStats(): array
    {
        return [
            'overview' => [
                'recipient_count' => 0,
                'sent_count' => 0,
                'delivered_count' => 0,
                'opened_count' => 0,
                'clicked_count' => 0,
                'bounced_count' => 0,
                'unsubscribed_count' => 0,
            ],
            'rates' => [
                'delivery_rate' => 0,
                'open_rate' => 0,
                'click_rate' => 0,
                'bounce_rate' => 0,
                'unsubscribe_rate' => 0,
                'click_to_open_rate' => 0,
            ],
            'engagement' => [],
            'top_links' => [],
            'device_stats' => [],
            'geo_stats' => [],
            'timeline' => [],
        ];
    }

    protected function percentage(int $part, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($part / $total) * 100, 2);
    }

    protected function getEngagementStats(EmailCampaign $campaign): array
    {
        $recipients = $campaign->recipients;

        return [
            'total_opens' => $recipients->sum('open_count'),
            'total_clicks' => $recipients->sum('click_count'),
            'unique_opens' => $recipients->whereNotNull('opened_at')->count(),
            'unique_clicks' => $recipients->whereNotNull('clicked_at')->count(),
            'avg_opens_per_recipient' => $recipients->whereNotNull('opened_at')->count() > 0
                ? round($recipients->sum('open_count') / $recipients->whereNotNull('opened_at')->count(), 2)
                : 0,
            'avg_clicks_per_recipient' => $recipients->whereNotNull('clicked_at')->count() > 0
                ? round($recipients->sum('click_count') / $recipients->whereNotNull('clicked_at')->count(), 2)
                : 0,
        ];
    }

    protected function getTopLinks(EmailCampaign $campaign, int $limit = 10): array
    {
        return EmailEvent::where('campaign_id', $campaign->id)
            ->where('event_type', EmailEvent::TYPE_CLICKED)
            ->whereNotNull('url')
            ->select('url', DB::raw('COUNT(*) as click_count'), DB::raw('COUNT(DISTINCT recipient_id) as unique_clicks'))
            ->groupBy('url')
            ->orderByDesc('click_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function getDeviceStats(EmailCampaign $campaign): array
    {
        return EmailEvent::where('campaign_id', $campaign->id)
            ->where('event_type', EmailEvent::TYPE_OPENED)
            ->whereNotNull('device_type')
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();
    }

    protected function getGeoStats(EmailCampaign $campaign): array
    {
        return EmailEvent::where('campaign_id', $campaign->id)
            ->where('event_type', EmailEvent::TYPE_OPENED)
            ->whereNotNull('country')
            ->select('country', DB::raw('COUNT(*) as count'))
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(20)
            ->get()
            ->pluck('count', 'country')
            ->toArray();
    }

    protected function getTimeline(EmailCampaign $campaign): array
    {
        $events = EmailEvent::where('campaign_id', $campaign->id)
            ->whereIn('event_type', [EmailEvent::TYPE_OPENED, EmailEvent::TYPE_CLICKED])
            ->select(
                DB::raw('DATE(occurred_at) as date'),
                'event_type',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date', 'event_type')
            ->orderBy('date')
            ->get();

        $timeline = [];
        foreach ($events as $event) {
            $date = $event->date;
            if (!isset($timeline[$date])) {
                $timeline[$date] = ['date' => $date, 'opens' => 0, 'clicks' => 0];
            }
            if ($event->event_type === EmailEvent::TYPE_OPENED) {
                $timeline[$date]['opens'] = $event->count;
            } else {
                $timeline[$date]['clicks'] = $event->count;
            }
        }

        return array_values($timeline);
    }
}
