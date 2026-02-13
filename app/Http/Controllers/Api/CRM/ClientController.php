<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ContactResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::query()
            ->withCount(['contacts', 'projects']);

        // Search
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $clients = $query->paginate($perPage);

        return ClientResource::collection($clients);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive,archived',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['created_by'] = auth('api')->id();

        $client = Client::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado exitosamente',
            'data' => new ClientResource($client),
        ], 201);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load(['contacts', 'createdBy']);
        $client->loadCount(['contacts', 'projects', 'quotes', 'tickets']);

        return response()->json([
            'success' => true,
            'data' => new ClientResource($client),
        ]);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive,archived',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente',
            'data' => new ClientResource($client),
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $client = Client::withTrashed()->findOrFail($id);
        $client->restore();

        return response()->json([
            'success' => true,
            'message' => 'Cliente restaurado exitosamente',
            'data' => new ClientResource($client),
        ]);
    }

    public function contacts(Client $client): AnonymousResourceCollection
    {
        $contacts = $client->contacts()->orderBy('name')->get();

        return ContactResource::collection($contacts);
    }

    public function archive(Client $client): JsonResponse
    {
        $client->archive();

        return response()->json([
            'success' => true,
            'message' => 'Cliente archivado exitosamente',
            'data' => new ClientResource($client),
        ]);
    }

    public function activate(Client $client): JsonResponse
    {
        $client->activate();

        return response()->json([
            'success' => true,
            'message' => 'Cliente activado exitosamente',
            'data' => new ClientResource($client),
        ]);
    }
}
