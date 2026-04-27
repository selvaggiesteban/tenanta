<?php

namespace App\Events\Omnichannel;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $conversationId;
    public string $agentName;
    public int $tenantId;
    public bool $isTyping;

    public function __construct(string $conversationId, string $agentName, int $tenantId, bool $isTyping = true)
    {
        $this->conversationId = $conversationId;
        $this->agentName = $agentName;
        $this->tenantId = $tenantId;
        $this->isTyping = $isTyping;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenants.{$this->tenantId}.omnichannel"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'agent.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'agent_name' => $this->agentName,
            'is_typing' => $this->isTyping,
        ];
    }
}
