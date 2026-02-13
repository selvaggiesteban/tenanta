<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public string $action = 'updated'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->task->tenant_id}"),
            new PrivateChannel("project.{$this->task->project_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'status' => $this->task->status,
                'priority' => $this->task->priority,
                'assigned_to' => $this->task->assigned_to,
                'project_id' => $this->task->project_id,
            ],
            'action' => $this->action,
        ];
    }
}
