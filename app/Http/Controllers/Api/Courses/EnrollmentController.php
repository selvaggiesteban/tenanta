<?php

namespace App\Http\Controllers\Api\Courses;

use App\Actions\Courses\CalculateCourseProgressAction;
use App\Actions\Courses\CheckSubscriptionAccessAction;
use App\Actions\Courses\EnrollUserAction;
use App\Actions\Courses\MarkTopicCompletedAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\EnrollUserRequest;
use App\Http\Requests\Courses\UpdateTopicProgressRequest;
use App\Http\Resources\Courses\CourseEnrollmentResource;
use App\Http\Resources\Courses\CourseResource;
use App\Http\Resources\Courses\TopicProgressResource;
use App\Models\Courses\Course;
use App\Models\Courses\CourseEnrollment;
use App\Models\Courses\CourseTopic;
use App\Models\Courses\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EnrollmentController extends Controller
{
    public function __construct(
        private EnrollUserAction $enrollAction,
        private MarkTopicCompletedAction $markTopicCompletedAction,
        private CalculateCourseProgressAction $calculateProgressAction,
        private CheckSubscriptionAccessAction $accessAction
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $enrollments = CourseEnrollment::query()
            ->where('user_id', auth()->id())
            ->with(['course.instructor'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderByDesc('enrolled_at')
            ->paginate($request->per_page ?? 10);

        return CourseEnrollmentResource::collection($enrollments);
    }

    public function store(EnrollUserRequest $request): JsonResponse
    {
        $user = auth()->user();
        $course = Course::findOrFail($request->course_id);
        $subscription = $request->subscription_id
            ? Subscription::findOrFail($request->subscription_id)
            : null;

        try {
            $enrollment = $this->enrollAction->execute($user, $course, $subscription);

            return response()->json([
                'message' => 'Inscripción realizada exitosamente.',
                'data' => new CourseEnrollmentResource($enrollment->load('course')),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(CourseEnrollment $enrollment): JsonResponse
    {
        $this->authorizeEnrollment($enrollment);

        $enrollment->load(['course.blocks.topics', 'topicProgress']);

        // Get detailed progress
        $progress = $this->calculateProgressAction->getDetailedProgress($enrollment);

        return response()->json([
            'enrollment' => new CourseEnrollmentResource($enrollment),
            'progress' => $progress,
        ]);
    }

    public function courseContent(CourseEnrollment $enrollment): JsonResponse
    {
        $this->authorizeEnrollment($enrollment);

        if (!$enrollment->isActive()) {
            return response()->json([
                'message' => 'Tu inscripción no está activa.',
            ], 403);
        }

        $course = $enrollment->course->load(['blocks.topics.progress' => function ($query) use ($enrollment) {
            $query->where('enrollment_id', $enrollment->id);
        }]);

        // Add flag to show full content
        request()->merge(['_show_full_content' => true]);

        return response()->json([
            'course' => new CourseResource($course),
            'enrollment' => new CourseEnrollmentResource($enrollment),
        ]);
    }

    public function markTopicCompleted(CourseEnrollment $enrollment, CourseTopic $topic): JsonResponse
    {
        $this->authorizeEnrollment($enrollment);

        try {
            $progress = $this->markTopicCompletedAction->execute($enrollment, $topic);

            return response()->json([
                'message' => 'Tema marcado como completado.',
                'data' => new TopicProgressResource($progress),
                'enrollment_progress' => $enrollment->fresh()->progress_percentage,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateTopicProgress(
        UpdateTopicProgressRequest $request,
        CourseEnrollment $enrollment,
        CourseTopic $topic
    ): JsonResponse {
        $this->authorizeEnrollment($enrollment);

        try {
            $progress = $this->markTopicCompletedAction->updateWatchProgress(
                $enrollment,
                $topic,
                $request->position_seconds,
                $request->watched_seconds
            );

            return response()->json([
                'data' => new TopicProgressResource($progress),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function checkAccess(Course $course): JsonResponse
    {
        $user = auth()->user();
        $accessDetails = $this->accessAction->getAccessDetails($user, $course);

        return response()->json($accessDetails);
    }

    public function unenroll(CourseEnrollment $enrollment): JsonResponse
    {
        $this->authorizeEnrollment($enrollment);

        // Only allow unenroll if not completed
        if ($enrollment->status === CourseEnrollment::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'No se puede cancelar una inscripción completada.',
            ], 422);
        }

        $this->enrollAction->unenroll($enrollment);

        return response()->json([
            'message' => 'Inscripción cancelada exitosamente.',
        ]);
    }

    private function authorizeEnrollment(CourseEnrollment $enrollment): void
    {
        if ($enrollment->user_id !== auth()->id()) {
            abort(403, 'No tienes acceso a esta inscripción.');
        }
    }
}
