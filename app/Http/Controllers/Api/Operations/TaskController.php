<?php

namespace App\Http\Controllers\Api\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Task::query()
            ->with(['project:id,name', 'assignee:id,name', 'pipelineStage:id,name,color']);

        // Filter by project
        if ($projectId = $request->get('project_id')) {
            $query->where('project_id', $projectId);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by assignee
        if ($assigneeId = $request->get('assignee_id')) {
            $query->where('assignee_id', $assigneeId);
        }

        // Filter my tasks
        if ($request->boolean('my_tasks')) {
            $query->where('assignee_id', auth('api')->id());
        }

        // Filter tasks to review (for current user as reviewer)
        if ($request->boolean('to_review')) {
            $query->where('reviewer_id', auth('api')->id())
                  ->where('status', 'review');
        }

        // Filter overdue
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $tasks = $query->paginate($perPage);

        return TaskResource::collection($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assignee_id' => 'nullable|exists:users,id',
            'reviewer_id' => 'nullable|exists:users,id',
            'pipeline_stage_id' => 'nullable|exists:pipeline_stages,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:1',
            'dependencies' => 'nullable|array',
            'dependencies.*' => 'exists:tasks,id',
        ]);

        $validated['created_by'] = auth('api')->id();

        // Get max sort order for project
        $maxOrder = Task::where('project_id', $validated['project_id'])->max('sort_order') ?? -1;
        $validated['sort_order'] = $maxOrder + 1;

        $task = Task::create($validated);

        // Add dependencies
        if (!empty($validated['dependencies'])) {
            foreach ($validated['dependencies'] as $depId) {
                $dependency = Task::find($depId);
                if ($dependency && $dependency->project_id === $task->project_id) {
                    $task->addDependency($dependency);
                }
            }
        }

        $task->load(['project:id,name', 'assignee:id,name', 'pipelineStage:id,name,color']);

        return response()->json([
            'success' => true,
            'message' => 'Tarea creada exitosamente',
            'data' => new TaskResource($task),
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        $task->load([
            'project:id,name',
            'assignee:id,name,email',
            'reviewer:id,name,email',
            'pipelineStage:id,name,color',
            'createdBy:id,name',
            'dependencies:id,title,status',
            'dependents:id,title,status',
        ]);

        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        if (!$task->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta tarea no puede ser editada',
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assignee_id' => 'nullable|exists:users,id',
            'reviewer_id' => 'nullable|exists:users,id',
            'pipeline_stage_id' => 'nullable|exists:pipeline_stages,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:1',
        ]);

        $task->update($validated);
        $task->load(['project:id,name', 'assignee:id,name', 'pipelineStage:id,name,color']);

        return response()->json([
            'success' => true,
            'message' => 'Tarea actualizada exitosamente',
            'data' => new TaskResource($task),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tarea eliminada exitosamente',
        ]);
    }

    public function start(Task $task): JsonResponse
    {
        try {
            $task->start();
            return response()->json([
                'success' => true,
                'message' => 'Tarea iniciada',
                'data' => new TaskResource($task),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(Task $task): JsonResponse
    {
        try {
            $task->submit();
            return response()->json([
                'success' => true,
                'message' => 'Tarea enviada a revisión',
                'data' => new TaskResource($task),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function approve(Task $task): JsonResponse
    {
        try {
            $task->approve();
            return response()->json([
                'success' => true,
                'message' => 'Tarea aprobada',
                'data' => new TaskResource($task),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reject(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $task->reject($validated['reason']);
            return response()->json([
                'success' => true,
                'message' => 'Tarea rechazada',
                'data' => new TaskResource($task),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function complete(Task $task): JsonResponse
    {
        try {
            $task->complete();
            return response()->json([
                'success' => true,
                'message' => 'Tarea completada',
                'data' => new TaskResource($task),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'tasks' => 'required|array',
            'tasks.*' => 'required|integer|exists:tasks,id',
        ]);

        foreach ($validated['tasks'] as $order => $taskId) {
            Task::where('id', $taskId)
                ->where('project_id', $validated['project_id'])
                ->update(['sort_order' => $order]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tareas reordenadas',
        ]);
    }

    public function addDependency(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'depends_on_id' => 'required|exists:tasks,id',
        ]);

        $dependency = Task::find($validated['depends_on_id']);

        if ($dependency->project_id !== $task->project_id) {
            return response()->json([
                'success' => false,
                'message' => 'La dependencia debe pertenecer al mismo proyecto',
            ], 422);
        }

        try {
            $task->addDependency($dependency);
            $task->load('dependencies:id,title,status');

            return response()->json([
                'success' => true,
                'message' => 'Dependencia agregada',
                'data' => new TaskResource($task),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function removeDependency(Task $task, Task $dependency): JsonResponse
    {
        $task->removeDependency($dependency);
        $task->load('dependencies:id,title,status');

        return response()->json([
            'success' => true,
            'message' => 'Dependencia removida',
            'data' => new TaskResource($task),
        ]);
    }
}
