<?php

namespace App\Actions\Marketing;

use App\Models\Marketing\EmailCampaign;
use Carbon\Carbon;

class ScheduleCampaignAction
{
    public function execute(EmailCampaign $campaign, Carbon $scheduledAt): EmailCampaign
    {
        if ($campaign->status !== EmailCampaign::STATUS_DRAFT) {
            throw new \Exception('Solo se pueden programar campañas en estado borrador');
        }

        if ($campaign->recipient_count === 0) {
            throw new \Exception('La campaña debe tener al menos un destinatario');
        }

        if (!$campaign->content_html) {
            throw new \Exception('La campaña debe tener contenido');
        }

        if ($scheduledAt->isPast()) {
            throw new \Exception('La fecha programada debe ser en el futuro');
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_SCHEDULED,
            'scheduled_at' => $scheduledAt,
        ]);

        return $campaign->fresh();
    }

    public function cancel(EmailCampaign $campaign): EmailCampaign
    {
        if ($campaign->status !== EmailCampaign::STATUS_SCHEDULED) {
            throw new \Exception('Solo se pueden cancelar campañas programadas');
        }

        $campaign->update([
            'status' => EmailCampaign::STATUS_DRAFT,
            'scheduled_at' => null,
        ]);

        return $campaign->fresh();
    }
}
