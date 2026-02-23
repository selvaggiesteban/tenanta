<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Lead $lead,
        public int $fromStageId,
        public int $toStageId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->lead->tenant_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'lead.moved';
    }

    public function broadcastWith(): array
    {
        return [
            'lead' => [
                'id' => $this->lead->id,
                'title' => $this->lead->title,
                'value' => $this->lead->value,
            ],
            'from_stage_id' => $this->fromStageId,
            'to_stage_id' => $this->toStageId,
        ];
    }
}
