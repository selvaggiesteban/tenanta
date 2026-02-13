<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $action = 'updated'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->ticket->tenant_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket' => [
                'id' => $this->ticket->id,
                'number' => $this->ticket->number,
                'subject' => $this->ticket->subject,
                'status' => $this->ticket->status,
                'priority' => $this->ticket->priority,
                'assigned_to' => $this->ticket->assigned_to,
            ],
            'action' => $this->action,
        ];
    }
}
