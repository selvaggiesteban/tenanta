<?php

namespace App\Http\Controllers\Api\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CreateTopicRequest;
use App\Http\Requests\Courses\UpdateTopicRequest;
use App\Http\Resources\Courses\CourseTopicResource;
use App\Models\Courses\Course;
use App\Models\Courses\CourseBlock;
use App\Models\Courses\CourseTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseTopicController extends Controller
{
    public function index(Course $course, CourseBlock $block): AnonymousResourceCollection
    {
        $this->ensureBlockBelongsToCourse($course, $block);

        $topics = $block->topics()->ordered()->get();

        return CourseTopicResource::collection($topics);
    }

    public function store(CreateTopicRequest $request, Course $course, CourseBlock $block): JsonResponse
    {
        $this->ensureBlockBelongsToCourse($course, $block);

        $data = $request->validated();
        $data['course_id'] = $course->id;

        // Auto-set sort order if not provided
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = $block->topics()->max('sort_order') + 1;
        }

        $topic = $block->topics()->create($data);

        return response()->json([
            'message' => 'Tema creado exitosamente.',
            'data' => new CourseTopicResource($topic),
        ], 201);
    }

    public function show(Course $course, CourseBlock $block, CourseTopic $topic): CourseTopicResource
    {
        $this->ensureTopicBelongsToBlock($course, $block, $topic);

        return new CourseTopicResource($topic);
    }

    public function update(UpdateTopicRequest $request, Course $course, CourseBlock $block, CourseTopic $topic): JsonResponse
    {
        $this->ensureTopicBelongsToBlock($course, $block, $topic);

        $topic->update($request->validated());

        return response()->json([
            'message' => 'Tema actualizado exitosamente.',
            'data' => new CourseTopicResource($topic),
        ]);
    }

    public function destroy(Course $course, CourseBlock $block, CourseTopic $topic): JsonResponse
    {
        $this->ensureTopicBelongsToBlock($course, $block, $topic);

        // Delete associated progress records
        $topic->progress()->delete();
        $topic->delete();

        return response()->json([
            'message' => 'Tema eliminado exitosamente.',
        ]);
    }

    public function reorder(Request $request, Course $course, CourseBlock $block): JsonResponse
    {
        $this->ensureBlockBelongsToCourse($course, $block);

        $request->validate([
            'topics' => ['required', 'array'],
            'topics.*.id' => ['required', 'integer', 'exists:course_topics,id'],
            'topics.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->topics as $topicData) {
            CourseTopic::where('id', $topicData['id'])
                ->where('block_id', $block->id)
                ->update(['sort_order' => $topicData['sort_order']]);
        }

        return response()->json([
            'message' => 'Orden de temas actualizado.',
        ]);
    }

    public function moveTopic(Request $request, Course $course, CourseTopic $topic): JsonResponse
    {
        if ($topic->course_id !== $course->id) {
            abort(404, 'El tema no pertenece a este curso.');
        }

        $request->validate([
            'block_id' => ['required', 'integer', 'exists:course_blocks,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $newBlock = CourseBlock::findOrFail($request->block_id);

        if ($newBlock->course_id !== $course->id) {
            return response()->json([
                'message' => 'El módulo de destino no pertenece a este curso.',
            ], 422);
        }

        $sortOrder = $request->sort_order ?? $newBlock->topics()->max('sort_order') + 1;

        $topic->update([
            'block_id' => $newBlock->id,
            'sort_order' => $sortOrder,
        ]);

        return response()->json([
            'message' => 'Tema movido exitosamente.',
            'data' => new CourseTopicResource($topic),
        ]);
    }

    private function ensureBlockBelongsToCourse(Course $course, CourseBlock $block): void
    {
        if ($block->course_id !== $course->id) {
            abort(404, 'El módulo no pertenece a este curso.');
        }
    }

    private function ensureTopicBelongsToBlock(Course $course, CourseBlock $block, CourseTopic $topic): void
    {
        $this->ensureBlockBelongsToCourse($course, $block);

        if ($topic->block_id !== $block->id) {
            abort(404, 'El tema no pertenece a este módulo.');
        }
    }
}
