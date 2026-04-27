<?php

namespace App\Events\Omnichannel;

use App\Models\Omnichannel\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public int $tenantId;

    public function __construct(Message $message, int $tenantId)
    {
        $this->message = $message;
        $this->tenantId = $tenantId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenants.{$this->tenantId}.omnichannel"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message->load('conversation'),
        ];
    }
}
