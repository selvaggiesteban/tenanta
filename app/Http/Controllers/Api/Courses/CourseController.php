<?php

namespace App\Http\Controllers\Api\Courses;

use App\Actions\Courses\CreateCourseAction;
use App\Actions\Courses\PublishCourseAction;
use App\Actions\Courses\UpdateCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CreateCourseRequest;
use App\Http\Requests\Courses\UpdateCourseRequest;
use App\Http\Resources\Courses\CourseListResource;
use App\Http\Resources\Courses\CourseResource;
use App\Models\Courses\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController extends Controller
{
    public function __construct(
        private CreateCourseAction $createAction,
        private UpdateCourseAction $updateAction,
        private PublishCourseAction $publishAction
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Course::query()
            ->with(['instructor'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->level, fn($q, $level) => $q->where('level', $level))
            ->when($request->search, fn($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }));

        // Sort
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $courses = $request->has('per_page')
            ? $query->paginate($request->per_page)
            : $query->get();

        return CourseListResource::collection($courses);
    }

    public function store(CreateCourseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = auth()->user()->tenant_id;

        $course = $this->createAction->execute($data);

        return response()->json([
            'message' => 'Curso creado exitosamente.',
            'data' => new CourseResource($course->load(['blocks.topics'])),
        ], 201);
    }

    public function show(Course $course): CourseResource
    {
        return new CourseResource(
            $course->load(['instructor', 'blocks.topics'])
        );
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $course = $this->updateAction->execute($course, $request->validated());

        return response()->json([
            'message' => 'Curso actualizado exitosamente.',
            'data' => new CourseResource($course->load(['blocks.topics'])),
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        // Check for active enrollments
        if ($course->enrollments()->where('status', 'active')->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un curso con inscripciones activas.',
            ], 422);
        }

        $course->delete();

        return response()->json([
            'message' => 'Curso eliminado exitosamente.',
        ]);
    }

    public function publish(Course $course): JsonResponse
    {
        try {
            $course = $this->publishAction->execute($course);

            return response()->json([
                'message' => 'Curso publicado exitosamente.',
                'data' => new CourseResource($course),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function unpublish(Course $course): JsonResponse
    {
        $course = $this->publishAction->unpublish($course);

        return response()->json([
            'message' => 'Curso despublicado exitosamente.',
            'data' => new CourseResource($course),
        ]);
    }

    public function archive(Course $course): JsonResponse
    {
        $course = $this->publishAction->archive($course);

        return response()->json([
            'message' => 'Curso archivado exitosamente.',
            'data' => new CourseResource($course),
        ]);
    }

    // Public endpoint for catalog
    public function catalog(Request $request): AnonymousResourceCollection
    {
        $query = Course::query()
            ->where('status', Course::STATUS_PUBLISHED)
            ->with(['instructor'])
            ->when($request->level, fn($q, $level) => $q->where('level', $level))
            ->when($request->search, fn($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($request->min_price !== null, fn($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price !== null, fn($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->free, fn($q) => $q->where('price', 0));

        // Sort
        $sortField = match ($request->sort_by) {
            'price' => 'price',
            'rating' => 'rating',
            'popular' => 'enrolled_count',
            default => 'published_at',
        };
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $courses = $query->paginate($request->per_page ?? 12);

        return CourseListResource::collection($courses);
    }

    public function showBySlug(string $slug): CourseResource
    {
        $course = Course::where('slug', $slug)
            ->where('status', Course::STATUS_PUBLISHED)
            ->with(['instructor', 'blocks.topics'])
            ->firstOrFail();

        return new CourseResource($course);
    }
}
