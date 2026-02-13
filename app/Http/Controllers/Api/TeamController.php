<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $teams = Team::with('members:id,name,email,avatar_url')
            ->orderBy('name')
            ->get();

        return TeamResource::collection($teams);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team = Team::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Equipo creado exitosamente',
            'data' => new TeamResource($team),
        ], 201);
    }

    public function show(Team $team): JsonResponse
    {
        $team->load('members:id,name,email,avatar_url,role');

        return response()->json([
            'success' => true,
            'data' => new TeamResource($team),
        ]);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Equipo actualizado exitosamente',
            'data' => new TeamResource($team),
        ]);
    }

    public function destroy(Team $team): JsonResponse
    {
        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Equipo eliminado exitosamente',
        ]);
    }

    public function addMember(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Verify user belongs to same tenant
        if ($user->tenant_id !== $team->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no pertenece a este tenant',
            ], 403);
        }

        $team->addMember($user);

        return response()->json([
            'success' => true,
            'message' => 'Miembro agregado exitosamente',
            'data' => new TeamResource($team->load('members:id,name,email,avatar_url')),
        ]);
    }

    public function removeMember(Team $team, User $user): JsonResponse
    {
        if (!$team->hasMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es miembro de este equipo',
            ], 404);
        }

        $team->removeMember($user);

        return response()->json([
            'success' => true,
            'message' => 'Miembro removido exitosamente',
            'data' => new TeamResource($team->load('members:id,name,email,avatar_url')),
        ]);
    }
}
