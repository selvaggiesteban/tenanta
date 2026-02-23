<?php

namespace App\Http\Controllers\Api\Courses;

use App\Actions\Courses\GradeTestAction;
use App\Actions\Courses\SubmitTestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\SubmitTestRequest;
use App\Http\Resources\Courses\TestAttemptResource;
use App\Models\Courses\CourseEnrollment;
use App\Models\Courses\CourseTest;
use App\Models\Courses\TestAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestAttemptController extends Controller
{
    public function __construct(
        private SubmitTestAction $submitAction,
        private GradeTestAction $gradeAction
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $attempts = TestAttempt::query()
            ->where('user_id', auth()->id())
            ->with('test')
            ->when($request->test_id, fn($q, $testId) => $q->where('test_id', $testId))
            ->when($request->completed, fn($q) => $q->completed())
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 10);

        return TestAttemptResource::collection($attempts);
    }

    public function start(Request $request, CourseTest $test): JsonResponse
    {
        $user = auth()->user();

        // Find the user's enrollment for this course
        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $test->course_id)
            ->whereIn('status', [
                CourseEnrollment::STATUS_ACTIVE,
                CourseEnrollment::STATUS_COMPLETED,
            ])
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'Debes estar inscrito en el curso para realizar este examen.',
            ], 403);
        }

        try {
            $attempt = $this->submitAction->startAttempt($user, $test, $enrollment);
            $attemptState = $this->submitAction->getAttemptState($attempt);

            return response()->json([
                'message' => $attempt->wasRecentlyCreated
                    ? 'Nuevo intento iniciado.'
                    : 'Continuando intento en progreso.',
                'data' => $attemptState,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function saveProgress(Request $request, TestAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($attempt);

        $request->validate([
            'answers' => ['required', 'array'],
        ]);

        try {
            $attempt = $this->submitAction->saveProgress($attempt, $request->answers);

            return response()->json([
                'message' => 'Progreso guardado.',
                'data' => new TestAttemptResource($attempt),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function submit(SubmitTestRequest $request, TestAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($attempt);

        try {
            $attempt = $this->submitAction->submit($attempt, $request->answers);

            return response()->json([
                'message' => $attempt->passed
                    ? '¡Felicitaciones! Has aprobado el examen.'
                    : 'Examen completado. No alcanzaste la puntuación mínima.',
                'data' => new TestAttemptResource($attempt),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(TestAttempt $attempt): TestAttemptResource
    {
        $this->authorizeAttempt($attempt);

        return new TestAttemptResource($attempt->load('test'));
    }

    public function results(TestAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($attempt);

        if (!$attempt->is_completed) {
            return response()->json([
                'message' => 'El intento aún no ha sido completado.',
            ], 422);
        }

        $results = $this->gradeAction->getResults($attempt);

        return response()->json($results);
    }

    public function history(CourseTest $test): JsonResponse
    {
        $history = $this->gradeAction->getUserTestHistory(auth()->id(), $test);

        return response()->json($history);
    }

    public function state(TestAttempt $attempt): JsonResponse
    {
        $this->authorizeAttempt($attempt);

        if ($attempt->is_completed) {
            return response()->json([
                'message' => 'Este intento ya fue completado.',
                'completed' => true,
                'data' => new TestAttemptResource($attempt),
            ]);
        }

        $state = $this->submitAction->getAttemptState($attempt);

        return response()->json([
            'completed' => false,
            'data' => $state,
        ]);
    }

    private function authorizeAttempt(TestAttempt $attempt): void
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403, 'No tienes acceso a este intento.');
        }
    }
}
