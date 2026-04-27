<?php

namespace App\Http\Controllers\Api\Omnichannel;

use App\Http\Controllers\Controller;
use App\Models\Omnichannel\CannedResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CannedResponseController extends Controller
{
    public function index(): JsonResponse
    {
        $responses = CannedResponse::orderBy('shortcut')->get();
        return response()->json(['success' => true, 'data' => $responses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shortcut' => 'required|string|max:50',
            'content' => 'required|string',
            'is_active' => 'boolean',
        ]);

        // Evitar duplicados por tenant (aunque el model ya tiene el trait, el request valida antes)
        $exists = CannedResponse::where('shortcut', $validated['shortcut'])->exists();
        if ($exists) {
            return response()->json(['message' => 'El shortcut ya existe para tu cuenta'], 422);
        }

        $response = CannedResponse::create($validated);
        return response()->json(['success' => true, 'data' => $response], 201);
    }

    public function update(Request $request, CannedResponse $cannedResponse): JsonResponse
    {
        $validated = $request->validate([
            'shortcut' => 'sometimes|required|string|max:50',
            'content' => 'sometimes|required|string',
            'is_active' => 'boolean',
        ]);

        $cannedResponse->update($validated);
        return response()->json(['success' => true, 'data' => $cannedResponse]);
    }

    public function destroy(CannedResponse $cannedResponse): JsonResponse
    {
        $cannedResponse->delete();
        return response()->json(['success' => true, 'message' => 'Respuesta eliminada']);
    }
}
