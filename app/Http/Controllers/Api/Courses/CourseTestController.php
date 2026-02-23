<?php

namespace App\Http\Controllers\Api\Courses;

use App\Actions\Courses\GradeTestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CreateTestRequest;
use App\Http\Resources\Courses\CourseTestResource;
use App\Models\Courses\Course;
use App\Models\Courses\CourseTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CourseTestController extends Controller
{
    public function __construct(
        private GradeTestAction $gradeAction
    ) {}

    public function index(Course $course): AnonymousResourceCollection
    {
        $tests = $course->tests()
            ->with('questions.options')
            ->ordered()
            ->get();

        return CourseTestResource::collection($tests);
    }

    public function store(CreateTestRequest $request, Course $course): JsonResponse
    {
        $data = $request->validated();

        $test = DB::transaction(function () use ($course, $data) {
            // Auto-set sort order if not provided
            if (!isset($data['sort_order'])) {
                $data['sort_order'] = $course->tests()->max('sort_order') + 1;
            }

            $test = $course->tests()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'passing_score' => $data['passing_score'],
                'max_attempts' => $data['max_attempts'] ?? 0,
                'show_answers_after' => $data['show_answers_after'] ?? true,
                'shuffle_questions' => $data['shuffle_questions'] ?? false,
                'shuffle_options' => $data['shuffle_options'] ?? false,
                'is_required' => $data['is_required'] ?? false,
                'sort_order' => $data['sort_order'],
            ]);

            // Create questions
            if (!empty($data['questions'])) {
                foreach ($data['questions'] as $qIndex => $questionData) {
                    $question = $test->questions()->create([
                        'question' => $questionData['question'],
                        'explanation' => $questionData['explanation'] ?? null,
                        'type' => $questionData['type'],
                        'points' => $questionData['points'] ?? 1,
                        'sort_order' => $questionData['sort_order'] ?? $qIndex + 1,
                    ]);

                    // Create options
                    foreach ($questionData['options'] as $oIndex => $optionData) {
                        $question->options()->create([
                            'text' => $optionData['text'],
                            'is_correct' => $optionData['is_correct'],
                            'sort_order' => $optionData['sort_order'] ?? $oIndex + 1,
                        ]);
                    }
                }
            }

            return $test;
        });

        return response()->json([
            'message' => 'Examen creado exitosamente.',
            'data' => new CourseTestResource($test->load('questions.options')),
        ], 201);
    }

    public function show(Course $course, CourseTest $test): CourseTestResource
    {
        $this->ensureTestBelongsToCourse($course, $test);

        return new CourseTestResource($test->load('questions.options'));
    }

    public function update(CreateTestRequest $request, Course $course, CourseTest $test): JsonResponse
    {
        $this->ensureTestBelongsToCourse($course, $test);

        $data = $request->validated();

        DB::transaction(function () use ($test, $data) {
            $test->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'passing_score' => $data['passing_score'],
                'max_attempts' => $data['max_attempts'] ?? 0,
                'show_answers_after' => $data['show_answers_after'] ?? true,
                'shuffle_questions' => $data['shuffle_questions'] ?? false,
                'shuffle_options' => $data['shuffle_options'] ?? false,
                'is_required' => $data['is_required'] ?? false,
            ]);

            // Update questions if provided
            if (isset($data['questions'])) {
                // Delete existing questions and options
                $test->questions()->each(function ($question) {
                    $question->options()->delete();
                    $question->delete();
                });

                // Create new questions
                foreach ($data['questions'] as $qIndex => $questionData) {
                    $question = $test->questions()->create([
                        'question' => $questionData['question'],
                        'explanation' => $questionData['explanation'] ?? null,
                        'type' => $questionData['type'],
                        'points' => $questionData['points'] ?? 1,
                        'sort_order' => $questionData['sort_order'] ?? $qIndex + 1,
                    ]);

                    foreach ($questionData['options'] as $oIndex => $optionData) {
                        $question->options()->create([
                            'text' => $optionData['text'],
                            'is_correct' => $optionData['is_correct'],
                            'sort_order' => $optionData['sort_order'] ?? $oIndex + 1,
                        ]);
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Examen actualizado exitosamente.',
            'data' => new CourseTestResource($test->fresh()->load('questions.options')),
        ]);
    }

    public function destroy(Course $course, CourseTest $test): JsonResponse
    {
        $this->ensureTestBelongsToCourse($course, $test);

        // Check for completed attempts
        if ($test->attempts()->completed()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar un examen con intentos completados.',
            ], 422);
        }

        DB::transaction(function () use ($test) {
            $test->questions()->each(function ($question) {
                $question->options()->delete();
                $question->delete();
            });
            $test->attempts()->delete();
            $test->delete();
        });

        return response()->json([
            'message' => 'Examen eliminado exitosamente.',
        ]);
    }

    public function statistics(Course $course, CourseTest $test): JsonResponse
    {
        $this->ensureTestBelongsToCourse($course, $test);

        $stats = $this->gradeAction->getTestStatistics($test);

        return response()->json($stats);
    }

    private function ensureTestBelongsToCourse(Course $course, CourseTest $test): void
    {
        if ($test->course_id !== $course->id) {
            abort(404, 'El examen no pertenece a este curso.');
        }
    }
}
