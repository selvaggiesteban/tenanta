<?php

namespace App\Http\Controllers\Api\Operations;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Project::query()
            ->with(['client:id,name', 'manager:id,name'])
            ->withCount(['tasks', 'members']);

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by client
        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        // Filter by manager
        if ($managerId = $request->get('manager_id')) {
            $query->where('manager_id', $managerId);
        }

        // Filter by member (user is part of project)
        if ($memberId = $request->get('member_id')) {
            $query->forMember($memberId);
        }

        // Filter my projects (user is member or manager)
        if ($request->boolean('my_projects')) {
            $userId = auth('api')->id();
            $query->where(function ($q) use ($userId) {
                $q->where('manager_id', $userId)
                  ->orWhereHas('members', fn($sub) => $sub->where('user_id', $userId));
            });
        }

        // Filter overdue
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $projects = $query->paginate($perPage);

        return ProjectResource::collection($projects);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:planning,active,on_hold',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_billable' => 'nullable|boolean',
            'manager_id' => 'nullable|exists:users,id',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role' => 'nullable|in:member,lead,reviewer',
            'members.*.hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = auth('api')->id();

        $project = Project::create($validated);

        // Add members if provided
        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $member) {
                $project->addMember(
                    User::find($member['user_id']),
                    $member['role'] ?? 'member',
                    $member['hourly_rate'] ?? null
                );
            }
        }

        $project->load(['client:id,name', 'manager:id,name', 'members:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Proyecto creado exitosamente',
            'data' => new ProjectResource($project),
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $project->load([
            'client:id,name',
            'manager:id,name,email',
            'members:id,name,email',
            'createdBy:id,name',
        ]);
        $project->loadCount(['tasks', 'timeEntries']);

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project),
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        if (!$project->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Este proyecto no puede ser editado',
            ], 422);
        }

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:planning,active,on_hold',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_billable' => 'nullable|boolean',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $project->update($validated);
        $project->load(['client:id,name', 'manager:id,name', 'members:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Proyecto actualizado exitosamente',
            'data' => new ProjectResource($project),
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto eliminado exitosamente',
        ]);
    }

    public function complete(Project $project): JsonResponse
    {
        if ($project->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'El proyecto ya está completado',
            ], 422);
        }

        $project->complete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto completado exitosamente',
            'data' => new ProjectResource($project),
        ]);
    }

    public function reopen(Project $project): JsonResponse
    {
        if ($project->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Solo proyectos completados pueden ser reabiertos',
            ], 422);
        }

        $project->reopen();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto reabierto exitosamente',
            'data' => new ProjectResource($project),
        ]);
    }

    public function tasks(Project $project): AnonymousResourceCollection
    {
        $tasks = $project->tasks()
            ->with(['assignee:id,name', 'pipelineStage:id,name,color'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return TaskResource::collection($tasks);
    }

    public function addMember(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|in:member,lead,reviewer',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $user = User::find($validated['user_id']);

        if ($project->isMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario ya es miembro del proyecto',
            ], 422);
        }

        $project->addMember(
            $user,
            $validated['role'] ?? 'member',
            $validated['hourly_rate'] ?? null
        );

        $project->load('members:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Miembro agregado exitosamente',
            'data' => new ProjectResource($project),
        ]);
    }

    public function removeMember(Project $project, User $user): JsonResponse
    {
        if (!$project->isMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es miembro del proyecto',
            ], 404);
        }

        $project->removeMember($user);
        $project->load('members:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Miembro removido exitosamente',
            'data' => new ProjectResource($project),
        ]);
    }

    public function updateMember(Request $request, Project $project, User $user): JsonResponse
    {
        if (!$project->isMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es miembro del proyecto',
            ], 404);
        }

        $validated = $request->validate([
            'role' => 'nullable|in:member,lead,reviewer',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $project->members()->updateExistingPivot($user->id, $validated);
        $project->load('members:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Miembro actualizado exitosamente',
            'data' => new ProjectResource($project),
        ]);
    }
}
