<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\PipelineResource;
use App\Http\Resources\PipelineStageResource;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PipelineController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pipeline::query()
            ->with('stages')
            ->withCount('leads');

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by active
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $pipelines = $query->get();

        return PipelineResource::collection($pipelines);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:leads,deals,projects,custom',
            'is_default' => 'nullable|boolean',
        ]);

        $pipeline = Pipeline::create($validated);
        $pipeline->load('stages');

        return response()->json([
            'success' => true,
            'message' => 'Pipeline creado exitosamente',
            'data' => new PipelineResource($pipeline),
        ], 201);
    }

    public function show(Pipeline $pipeline): JsonResponse
    {
        $pipeline->load('stages');
        $pipeline->loadCount('leads');

        return response()->json([
            'success' => true,
            'data' => new PipelineResource($pipeline),
        ]);
    }

    public function update(Request $request, Pipeline $pipeline): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $pipeline->update($validated);
        $pipeline->load('stages');

        return response()->json([
            'success' => true,
            'message' => 'Pipeline actualizado exitosamente',
            'data' => new PipelineResource($pipeline),
        ]);
    }

    public function destroy(Pipeline $pipeline): JsonResponse
    {
        // Check if pipeline has leads
        if ($pipeline->leads()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un pipeline con leads asociados',
            ], 422);
        }

        $pipeline->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pipeline eliminado exitosamente',
        ]);
    }

    public function makeDefault(Pipeline $pipeline): JsonResponse
    {
        $pipeline->makeDefault();
        $pipeline->load('stages');

        return response()->json([
            'success' => true,
            'message' => 'Pipeline establecido como predeterminado',
            'data' => new PipelineResource($pipeline),
        ]);
    }

    // Stage management

    public function storeStage(Request $request, Pipeline $pipeline): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'probability' => 'nullable|numeric|min:0|max:100',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
        ]);

        // Get max sort order
        $maxOrder = $pipeline->stages()->max('sort_order') ?? -1;
        $validated['sort_order'] = $maxOrder + 1;

        $stage = $pipeline->stages()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Etapa creada exitosamente',
            'data' => new PipelineStageResource($stage),
        ], 201);
    }

    public function updateStage(Request $request, Pipeline $pipeline, PipelineStage $stage): JsonResponse
    {
        if ($stage->pipeline_id !== $pipeline->id) {
            return response()->json([
                'success' => false,
                'message' => 'La etapa no pertenece a este pipeline',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:7',
            'probability' => 'nullable|numeric|min:0|max:100',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
        ]);

        $stage->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Etapa actualizada exitosamente',
            'data' => new PipelineStageResource($stage),
        ]);
    }

    public function destroyStage(Pipeline $pipeline, PipelineStage $stage): JsonResponse
    {
        if ($stage->pipeline_id !== $pipeline->id) {
            return response()->json([
                'success' => false,
                'message' => 'La etapa no pertenece a este pipeline',
            ], 404);
        }

        // Check if stage has leads
        if ($stage->leads()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una etapa con leads asociados',
            ], 422);
        }

        $stage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Etapa eliminada exitosamente',
        ]);
    }

    public function reorderStages(Request $request, Pipeline $pipeline): JsonResponse
    {
        $validated = $request->validate([
            'stages' => 'required|array',
            'stages.*' => 'required|integer|exists:pipeline_stages,id',
        ]);

        return DB::transaction(function () use ($pipeline, $validated) {
            foreach ($validated['stages'] as $order => $stageId) {
                PipelineStage::where('id', $stageId)
                    ->where('pipeline_id', $pipeline->id)
                    ->update(['sort_order' => $order]);
            }

            $pipeline->load('stages');

            return response()->json([
                'success' => true,
                'message' => 'Etapas reordenadas exitosamente',
                'data' => new PipelineResource($pipeline),
            ]);
        });
    }
}
