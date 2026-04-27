<?php

namespace App\Services\Omnichannel;

use App\Models\Omnichannel\Conversation;
use App\Models\Omnichannel\Message;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConversationAnalyticsService
{
    /**
     * Calcula métricas clave para el dashboard de un inquilino.
     */
    public function getTenantMetrics(int $tenantId): array
    {
        return [
            'avg_frt' => $this->calculateAverageFRT($tenantId),
            'avg_resolution_time' => $this->calculateAverageResolutionTime($tenantId),
            'conversations_by_status' => $this->getConversationsCountByStatus($tenantId),
            'messages_count' => $this->getMessagesCount($tenantId),
            'calls_count' => $this->getCallsCount($tenantId),
        ];
    }

    /**
     * FRT: Tiempo promedio entre el primer mensaje inbound y la primera respuesta outbound.
     */
    private function calculateAverageFRT(int $tenantId): float
    {
        $conversations = Conversation::where('tenant_id', $tenantId)
            ->whereHas('messages', fn($q) => $q->where('direction', 'inbound'))
            ->whereHas('messages', fn($q) => $q->where('direction', 'outbound'))
            ->with(['messages' => fn($q) => $q->orderBy('created_at', 'asc')])
            ->get();

        if ($conversations->isEmpty()) {
            return 0;
        }

        $totalFrt = 0;
        $count = 0;

        foreach ($conversations as $conversation) {
            $firstInbound = $conversation->messages->firstWhere('direction', 'inbound');
            $firstOutbound = $conversation->messages->firstWhere(function($msg) use ($firstInbound) {
                return $msg->direction === 'outbound' && $msg->created_at > $firstInbound->created_at;
            });

            if ($firstInbound && $firstOutbound) {
                $totalFrt += $firstInbound->created_at->diffInSeconds($firstOutbound->created_at);
                $count++;
            }
        }

        return $count > 0 ? round($totalFrt / $count, 2) : 0;
    }

    /**
     * Resolution Time: Tiempo promedio desde el inicio hasta el cierre de la conversación.
     */
    private function calculateAverageResolutionTime(int $tenantId): float
    {
        $conversations = Conversation::where('tenant_id', $tenantId)
            ->whereNotNull('closed_at')
            ->with(['messages' => fn($q) => $q->orderBy('created_at', 'asc')])
            ->get();

        if ($conversations->isEmpty()) {
            return 0;
        }

        $totalRes = 0;
        $count = 0;

        foreach ($conversations as $conversation) {
            $firstInbound = $conversation->messages->firstWhere('direction', 'inbound');
            
            if ($firstInbound) {
                $totalRes += $firstInbound->created_at->diffInSeconds($conversation->closed_at);
                $count++;
            }
        }

        return $count > 0 ? round($totalRes / $count, 2) : 0;
    }

    private function getConversationsCountByStatus(int $tenantId): array
    {
        return Conversation::where('tenant_id', $tenantId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getMessagesCount(int $tenantId): array
    {
        return Message::whereHas('conversation', fn($q) => $q->where('tenant_id', $tenantId))
            ->select('direction', DB::raw('count(*) as count'))
            ->groupBy('direction')
            ->pluck('count', 'direction')
            ->toArray();
    }

    private function getCallsCount(int $tenantId): int
    {
        return DB::table('omnichannel_calls')->where('tenant_id', $tenantId)->count();
    }
}
