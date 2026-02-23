<?php

namespace App\Http\Controllers\Api\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CreateBlockRequest;
use App\Http\Resources\Courses\CourseBlockResource;
use App\Models\Courses\Course;
use App\Models\Courses\CourseBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseBlockController extends Controller
{
    public function index(Course $course): AnonymousResourceCollection
    {
        $blocks = $course->blocks()
            ->with('topics')
            ->ordered()
            ->get();

        return CourseBlockResource::collection($blocks);
    }

    public function store(CreateBlockRequest $request, Course $course): JsonResponse
    {
        $data = $request->validated();

        // Auto-set sort order if not provided
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = $course->blocks()->max('sort_order') + 1;
        }

        $block = $course->blocks()->create($data);

        return response()->json([
            'message' => 'Módulo creado exitosamente.',
            'data' => new CourseBlockResource($block),
        ], 201);
    }

    public function show(Course $course, CourseBlock $block): CourseBlockResource
    {
        $this->ensureBlockBelongsToCourse($course, $block);

        return new CourseBlockResource($block->load('topics'));
    }

    public function update(CreateBlockRequest $request, Course $course, CourseBlock $block): JsonResponse
    {
        $this->ensureBlockBelongsToCourse($course, $block);

        $block->update($request->validated());

        return response()->json([
            'message' => 'Módulo actualizado exitosamente.',
            'data' => new CourseBlockResource($block),
        ]);
    }

    public function destroy(Course $course, CourseBlock $block): JsonResponse
    {
        $this->ensureBlockBelongsToCourse($course, $block);

        // Delete associated topics
        $block->topics()->delete();
        $block->delete();

        return response()->json([
            'message' => 'Módulo eliminado exitosamente.',
        ]);
    }

    public function reorder(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            'blocks' => ['required', 'array'],
            'blocks.*.id' => ['required', 'integer', 'exists:course_blocks,id'],
            'blocks.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->blocks as $blockData) {
            CourseBlock::where('id', $blockData['id'])
                ->where('course_id', $course->id)
                ->update(['sort_order' => $blockData['sort_order']]);
        }

        return response()->json([
            'message' => 'Orden de módulos actualizado.',
        ]);
    }

    private function ensureBlockBelongsToCourse(Course $course, CourseBlock $block): void
    {
        if ($block->course_id !== $course->id) {
            abort(404, 'El módulo no pertenece a este curso.');
        }
    }
}
