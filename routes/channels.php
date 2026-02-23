<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User private channel
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return $user->id === $userId;
});

// Tenant private channel
Broadcast::channel('tenant.{tenantId}', function (User $user, int $tenantId) {
    return $user->tenant_id === $tenantId;
});

// Project private channel
Broadcast::channel('project.{projectId}', function (User $user, int $projectId) {
    $project = \App\Models\Project::find($projectId);

    if (!$project || $project->tenant_id !== $user->tenant_id) {
        return false;
    }

    return $project->members()->where('user_id', $user->id)->exists()
        || $user->hasRole(['admin', 'manager']);
});

// Conversation private channel
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);

    return $conversation
        && $conversation->user_id === $user->id
        && $conversation->tenant_id === $user->tenant_id;
});
