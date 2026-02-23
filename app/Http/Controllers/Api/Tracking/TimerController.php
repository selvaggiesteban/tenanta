<?php

namespace App\Http\Controllers\Api\Tracking;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimeEntryResource;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    public function current(): JsonResponse
    {
        $userId = auth('api')->id();
        $timer = TimeEntry::getCurrentTimer($userId);

        if (!$timer) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        $timer->load(['project:id,name', 'task:id,title']);

        return response()->json([
            'success' => true,
            'data' => new TimeEntryResource($timer),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'description' => 'nullable|string|max:500',
        ]);

        // If task_id is provided, get project_id from task
        if (!empty($validated['task_id']) && empty($validated['project_id'])) {
            $task = \App\Models\Task::find($validated['task_id']);
            $validated['project_id'] = $task?->project_id;
        }

        try {
            $timer = TimeEntry::startTimer(
                auth('api')->id(),
                $validated['project_id'] ?? null,
                $validated['task_id'] ?? null,
                $validated['description'] ?? null
            );

            $timer->load(['project:id,name', 'task:id,title']);

            return response()->json([
                'success' => true,
                'message' => 'Timer iniciado',
                'data' => new TimeEntryResource($timer),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function stop(Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $timer = TimeEntry::getCurrentTimer($userId);

        if (!$timer) {
            return response()->json([
                'success' => false,
                'message' => 'No hay timer en ejecución',
            ], 404);
        }

        // Allow updating description on stop
        if ($request->has('description')) {
            $timer->description = $request->get('description');
        }

        $timer->stop();
        $timer->load(['project:id,name', 'task:id,title']);

        return response()->json([
            'success' => true,
            'message' => 'Timer detenido',
            'data' => new TimeEntryResource($timer),
        ]);
    }

    public function cancel(): JsonResponse
    {
        $userId = auth('api')->id();
        $timer = TimeEntry::getCurrentTimer($userId);

        if (!$timer) {
            return response()->json([
                'success' => false,
                'message' => 'No hay timer en ejecución',
            ], 404);
        }

        try {
            $timer->cancel();
            return response()->json([
                'success' => true,
                'message' => 'Timer cancelado',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request): JsonResponse
    {
        $userId = auth('api')->id();
        $timer = TimeEntry::getCurrentTimer($userId);

        if (!$timer) {
            return response()->json([
                'success' => false,
                'message' => 'No hay timer en ejecución',
            ], 404);
        }

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'description' => 'nullable|string|max:500',
        ]);

        $timer->update($validated);
        $timer->load(['project:id,name', 'task:id,title']);

        return response()->json([
            'success' => true,
            'message' => 'Timer actualizado',
            'data' => new TimeEntryResource($timer),
        ]);
    }
}
