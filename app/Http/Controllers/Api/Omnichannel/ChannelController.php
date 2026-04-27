<?php

namespace App\Http\Controllers\Api\Omnichannel;

use App\Http\Controllers\Controller;
use App\Models\Omnichannel\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChannelController extends Controller
{
    /**
     * Display a listing of the channels for the current tenant.
     */
    public function index(): JsonResponse
    {
        $channels = Channel::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $channels
        ]);
    }

    /**
     * Store a newly created channel.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['whatsapp', 'messenger', 'email_smtp', 'email_gmail', 'web_widget', 'telegram', 'instagram'])],
            'name' => 'required|string|max:255',
            'provider_id' => 'nullable|string|max:255',
            'credentials' => 'required|array',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        $channel = Channel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Canal creado exitosamente',
            'data' => $channel
        ], 201);
    }

    /**
     * Display the specified channel.
     */
    public function show(Channel $channel): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $channel
        ]);
    }

    /**
     * Update the specified channel.
     */
    public function update(Request $request, Channel $channel): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'provider_id' => 'nullable|string|max:255',
            'credentials' => 'sometimes|required|array',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        $channel->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Canal actualizado exitosamente',
            'data' => $channel
        ]);
    }

    /**
     * Remove the specified channel.
     */
    public function destroy(Channel $channel): JsonResponse
    {
        $channel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Canal eliminado exitosamente'
        ]);
    }

    /**
     * Toggle active status of a channel.
     */
    public function toggleActive(Channel $channel): JsonResponse
    {
        $channel->update(['is_active' => !$channel->is_active]);

        return response()->json([
            'success' => true,
            'message' => $channel->is_active ? 'Canal activado' : 'Canal desactivado',
            'data' => $channel
        ]);
    }
}
