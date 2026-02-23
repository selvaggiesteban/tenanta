<?php

namespace App\Http\Controllers\Api\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimeEntryResource;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TimeEntryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TimeEntry::query()
            ->with(['user:id,name', 'project:id,name', 'task:id,title'])
            ->where('is_running', false);

        // Filter by user
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter my entries
        if ($request->boolean('my_entries')) {
            $query->where('user_id', auth('api')->id());
        }

        // Filter by project
        if ($projectId = $request->get('project_id')) {
            $query->where('project_id', $projectId);
        }

        // Filter by task
        if ($taskId = $request->get('task_id')) {
            $query->where('task_id', $taskId);
        }

        // Filter by date range
        if ($start = $request->get('start_date')) {
            $query->where('started_at', '>=', Carbon::parse($start)->startOfDay());
        }
        if ($end = $request->get('end_date')) {
            $query->where('started_at', '<=', Carbon::parse($end)->endOfDay());
        }

        // Preset date filters
        if ($request->boolean('today')) {
            $query->today();
        }
        if ($request->boolean('this_week')) {
            $query->thisWeek();
        }
        if ($request->boolean('this_month')) {
            $query->thisMonth();
        }

        // Filter billable
        if ($request->has('billable')) {
            $query->where('is_billable', $request->boolean('billable'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'started_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $entries = $query->paginate($perPage);

        return TimeEntryResource::collection($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'description' => 'nullable|string|max:500',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'is_billable' => 'nullable|boolean',
        ]);

        $validated['user_id'] = auth('api')->id();

        $entry = TimeEntry::createManualEntry($validated);
        $entry->load(['project:id,name', 'task:id,title']);

        return response()->json([
            'success' => true,
            'message' => 'Entrada de tiempo creada',
            'data' => new TimeEntryResource($entry),
        ], 201);
    }

    public function show(TimeEntry $entry): JsonResponse
    {
        $entry->load(['user:id,name,email', 'project:id,name', 'task:id,title']);

        return response()->json([
            'success' => true,
            'data' => new TimeEntryResource($entry),
        ]);
    }

    public function update(Request $request, TimeEntry $entry): JsonResponse
    {
        // Only allow updates to own entries or if admin
        if ($entry->user_id !== auth('api')->id()) {
            // TODO: Check if user is admin
            return response()->json([
                'success' => false,
                'message' => 'No puedes editar entradas de otros usuarios',
            ], 403);
        }

        if ($entry->is_running) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes editar una entrada en ejecución',
            ], 422);
        }

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'description' => 'nullable|string|max:500',
            'started_at' => 'sometimes|date',
            'ended_at' => 'sometimes|date|after:started_at',
            'is_billable' => 'nullable|boolean',
        ]);

        $entry->update($validated);
        $entry->load(['project:id,name', 'task:id,title']);

        return response()->json([
            'success' => true,
            'message' => 'Entrada actualizada',
            'data' => new TimeEntryResource($entry),
        ]);
    }

    public function destroy(TimeEntry $entry): JsonResponse
    {
        // Only allow deletion of own entries
        if ($entry->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar entradas de otros usuarios',
            ], 403);
        }

        if ($entry->is_running) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar una entrada en ejecución',
            ], 422);
        }

        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Entrada eliminada',
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $userId = $request->get('user_id', auth('api')->id());

        $startDate = $request->has('start_date')
            ? Carbon::parse($request->get('start_date'))
            : now()->startOfWeek();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->get('end_date'))
            : now()->endOfWeek();

        $query = TimeEntry::where('user_id', $userId)
            ->where('is_running', false)
            ->inDateRange($startDate, $endDate);

        $totalMinutes = $query->sum('duration_minutes');
        $billableMinutes = (clone $query)->billable()->sum('duration_minutes');

        $byProject = TimeEntry::where('user_id', $userId)
            ->where('is_running', false)
            ->inDateRange($startDate, $endDate)
            ->selectRaw('project_id, SUM(duration_minutes) as total_minutes')
            ->groupBy('project_id')
            ->with('project:id,name')
            ->get()
            ->map(fn($row) => [
                'project' => $row->project ? [
                    'id' => $row->project->id,
                    'name' => $row->project->name,
                ] : null,
                'total_minutes' => $row->total_minutes,
                'total_hours' => round($row->total_minutes / 60, 2),
            ]);

        $byDay = TimeEntry::where('user_id', $userId)
            ->where('is_running', false)
            ->inDateRange($startDate, $endDate)
            ->selectRaw('DATE(started_at) as date, SUM(duration_minutes) as total_minutes')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'total_minutes' => $row->total_minutes,
                'total_hours' => round($row->total_minutes / 60, 2),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'total_minutes' => $totalMinutes,
                'total_hours' => round($totalMinutes / 60, 2),
                'billable_minutes' => $billableMinutes,
                'billable_hours' => round($billableMinutes / 60, 2),
                'by_project' => $byProject,
                'by_day' => $byDay,
            ],
        ]);
    }
}
