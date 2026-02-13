<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function overview(): JsonResponse
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfWeek = Carbon::now()->startOfWeek();

        return response()->json([
            'data' => [
                'clients' => [
                    'total' => Client::count(),
                    'active' => Client::where('status', 'active')->count(),
                    'new_this_month' => Client::where('created_at', '>=', $startOfMonth)->count(),
                ],
                'leads' => [
                    'total' => Lead::count(),
                    'new_this_month' => Lead::where('created_at', '>=', $startOfMonth)->count(),
                    'pipeline_value' => Lead::sum('value'),
                ],
                'quotes' => [
                    'total' => Quote::count(),
                    'pending' => Quote::whereIn('status', ['draft', 'sent'])->count(),
                    'accepted_this_month' => Quote::where('status', 'accepted')
                        ->where('updated_at', '>=', $startOfMonth)
                        ->count(),
                    'total_value_accepted' => Quote::where('status', 'accepted')
                        ->where('updated_at', '>=', $startOfMonth)
                        ->sum('total'),
                ],
                'tasks' => [
                    'total' => Task::count(),
                    'pending' => Task::where('status', 'pending')->count(),
                    'in_progress' => Task::where('status', 'in_progress')->count(),
                    'completed_this_week' => Task::where('status', 'completed')
                        ->where('updated_at', '>=', $startOfWeek)
                        ->count(),
                ],
                'tickets' => [
                    'open' => Ticket::where('status', 'open')->count(),
                    'in_progress' => Ticket::where('status', 'in_progress')->count(),
                ],
            ],
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        // Sales by status
        $quotesByStatus = Quote::select('status', DB::raw('count(*) as count'), DB::raw('sum(total) as total'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('status')
            ->get();

        // Sales trend
        $salesTrend = Quote::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count'),
            DB::raw('sum(total) as total')
        )
            ->where('created_at', '>=', $startDate)
            ->where('status', 'accepted')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Leads by stage
        $leadsByStage = Lead::select('pipeline_stages.name as stage', DB::raw('count(*) as count'), DB::raw('sum(leads.value) as value'))
            ->join('pipeline_stages', 'leads.pipeline_stage_id', '=', 'pipeline_stages.id')
            ->groupBy('pipeline_stages.name')
            ->get();

        // Conversion rate
        $totalLeads = Lead::where('created_at', '>=', $startDate)->count();
        $convertedLeads = Lead::where('created_at', '>=', $startDate)->whereNotNull('converted_at')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        return response()->json([
            'data' => [
                'quotes_by_status' => $quotesByStatus,
                'sales_trend' => $salesTrend,
                'leads_by_stage' => $leadsByStage,
                'conversion_rate' => $conversionRate,
                'period' => $period,
            ],
        ]);
    }

    public function operations(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        // Projects summary
        $projectStats = [
            'total' => Project::count(),
            'active' => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
        ];

        // Tasks by status
        $tasksByStatus = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Tasks by priority
        $tasksByPriority = Task::select('priority', DB::raw('count(*) as count'))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority');

        // Overdue tasks
        $overdueTasks = Task::where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        // Completion trend
        $completionTrend = Task::select(
            DB::raw('DATE(updated_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('status', 'completed')
            ->where('updated_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'data' => [
                'projects' => $projectStats,
                'tasks_by_status' => $tasksByStatus,
                'tasks_by_priority' => $tasksByPriority,
                'overdue_tasks' => $overdueTasks,
                'completion_trend' => $completionTrend,
                'period' => $period,
            ],
        ]);
    }

    public function team(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        // Time logged by user
        $timeByUser = TimeEntry::select('users.name', DB::raw('sum(duration) as total_minutes'))
            ->join('users', 'time_entries.user_id', '=', 'users.id')
            ->where('time_entries.created_at', '>=', $startDate)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_minutes')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name' => $item->name,
                'hours' => round($item->total_minutes / 60, 1),
            ]);

        // Tasks completed by user
        $tasksByUser = Task::select('users.name', DB::raw('count(*) as count'))
            ->join('users', 'tasks.assigned_to', '=', 'users.id')
            ->where('tasks.status', 'completed')
            ->where('tasks.updated_at', '>=', $startDate)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Time by project
        $timeByProject = TimeEntry::select('projects.name', DB::raw('sum(duration) as total_minutes'))
            ->join('projects', 'time_entries.project_id', '=', 'projects.id')
            ->where('time_entries.created_at', '>=', $startDate)
            ->groupBy('projects.id', 'projects.name')
            ->orderByDesc('total_minutes')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name' => $item->name,
                'hours' => round($item->total_minutes / 60, 1),
            ]);

        return response()->json([
            'data' => [
                'time_by_user' => $timeByUser,
                'tasks_by_user' => $tasksByUser,
                'time_by_project' => $timeByProject,
                'period' => $period,
            ],
        ]);
    }

    public function support(): JsonResponse
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        // Tickets by status
        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Tickets by priority
        $ticketsByPriority = Ticket::select('priority', DB::raw('count(*) as count'))
            ->whereNotIn('status', ['resolved', 'closed'])
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority');

        // Average response time (in minutes)
        $avgResponseTime = Ticket::whereNotNull('first_response_at')
            ->where('created_at', '>=', $startOfMonth)
            ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, first_response_at)'));

        // Average resolution time (in hours)
        $avgResolutionTime = Ticket::whereNotNull('resolved_at')
            ->where('created_at', '>=', $startOfMonth)
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolved_at)'));

        // Tickets trend
        $ticketsTrend = Ticket::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as created'),
            DB::raw('sum(case when status in ("resolved", "closed") then 1 else 0 end) as resolved')
        )
            ->where('created_at', '>=', $startOfMonth)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'data' => [
                'tickets_by_status' => $ticketsByStatus,
                'tickets_by_priority' => $ticketsByPriority,
                'avg_response_time_minutes' => round($avgResponseTime ?? 0),
                'avg_resolution_time_hours' => round($avgResolutionTime ?? 0, 1),
                'tickets_trend' => $ticketsTrend,
            ],
        ]);
    }

    protected function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->startOfQuarter(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
    }
}
