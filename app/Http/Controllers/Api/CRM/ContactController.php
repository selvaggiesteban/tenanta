<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Contact::query()->with('client:id,name');

        // Filter by client
        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        // Search
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $contacts = $query->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify client belongs to same tenant
        $client = Client::findOrFail($validated['client_id']);

        $contact = Contact::create($validated);

        // If marked as primary, make it primary
        if ($validated['is_primary'] ?? false) {
            $contact->makePrimary();
        }

        return response()->json([
            'success' => true,
            'message' => 'Contacto creado exitosamente',
            'data' => new ContactResource($contact->load('client:id,name')),
        ], 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $contact->load('client:id,name');

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
        ]);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $contact->update($validated);

        // If marked as primary, make it primary
        if ($validated['is_primary'] ?? false) {
            $contact->makePrimary();
        }

        return response()->json([
            'success' => true,
            'message' => 'Contacto actualizado exitosamente',
            'data' => new ContactResource($contact),
        ]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contacto eliminado exitosamente',
        ]);
    }

    public function makePrimary(Contact $contact): JsonResponse
    {
        $contact->makePrimary();

        return response()->json([
            'success' => true,
            'message' => 'Contacto marcado como principal',
            'data' => new ContactResource($contact),
        ]);
    }
}
