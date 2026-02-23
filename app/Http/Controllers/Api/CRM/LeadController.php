<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Lead::query()
            ->with(['assignedTo:id,name', 'pipelineStage:id,name,color']);

        // Search
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by source
        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        // Filter by assigned user
        if ($assignedTo = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        // Filter by pipeline stage
        if ($stageId = $request->get('pipeline_stage_id')) {
            $query->where('pipeline_stage_id', $stageId);
        }

        // Filter converted/unconverted
        if ($request->has('converted')) {
            $request->boolean('converted')
                ? $query->converted()
                : $query->whereNull('converted_client_id');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $leads = $query->paginate($perPage);

        return LeadResource::collection($leads);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'status' => 'nullable|in:new,contacted,qualified,proposal,negotiation',
            'source' => 'nullable|string|max:100',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'pipeline_stage_id' => 'nullable|exists:pipeline_stages,id',
        ]);

        $validated['created_by'] = auth('api')->id();

        $lead = Lead::create($validated);
        $lead->load(['assignedTo:id,name', 'pipelineStage:id,name,color']);

        return response()->json([
            'success' => true,
            'message' => 'Lead creado exitosamente',
            'data' => new LeadResource($lead),
        ], 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        $lead->load(['assignedTo:id,name', 'pipelineStage:id,name,color', 'convertedClient:id,name', 'createdBy:id,name']);

        return response()->json([
            'success' => true,
            'data' => new LeadResource($lead),
        ]);
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'contact_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'status' => 'nullable|in:new,contacted,qualified,proposal,negotiation,lost',
            'source' => 'nullable|string|max:100',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'pipeline_stage_id' => 'nullable|exists:pipeline_stages,id',
        ]);

        $lead->update($validated);
        $lead->load(['assignedTo:id,name', 'pipelineStage:id,name,color']);

        return response()->json([
            'success' => true,
            'message' => 'Lead actualizado exitosamente',
            'data' => new LeadResource($lead),
        ]);
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead eliminado exitosamente',
        ]);
    }

    /**
     * Convert lead to client
     */
    public function convert(Request $request, Lead $lead): JsonResponse
    {
        if (!$lead->canBeConverted()) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead no puede ser convertido',
            ], 422);
        }

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
        ]);

        try {
            $client = $lead->convertToClient($validated['company_name'] ?? null);
            $lead->refresh()->load('convertedClient:id,name');

            return response()->json([
                'success' => true,
                'message' => 'Lead convertido a cliente exitosamente',
                'data' => [
                    'lead' => new LeadResource($lead),
                    'client' => new ClientResource($client),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mark lead as lost
     */
    public function markAsLost(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $lead->markAsLost($validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Lead marcado como perdido',
            'data' => new LeadResource($lead),
        ]);
    }

    /**
     * Move lead to a different pipeline stage (for Kanban drag-drop)
     */
    public function moveStage(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        // Verify the stage belongs to the lead's pipeline
        $stage = \App\Models\PipelineStage::find($validated['pipeline_stage_id']);

        if ($lead->pipeline_id && $stage->pipeline_id !== $lead->pipeline_id) {
            return response()->json([
                'success' => false,
                'message' => 'La etapa no pertenece al pipeline del lead',
            ], 422);
        }

        $lead->update([
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
            'pipeline_id' => $stage->pipeline_id,
        ]);

        // Auto-update status based on stage properties
        if ($stage->is_won) {
            $lead->update(['status' => 'won']);
        } elseif ($stage->is_lost) {
            $lead->update(['status' => 'lost']);
        }

        $lead->load(['assignedTo:id,name', 'pipelineStage:id,name,color']);

        return response()->json([
            'success' => true,
            'message' => 'Lead movido exitosamente',
            'data' => new LeadResource($lead),
        ]);
    }
}
