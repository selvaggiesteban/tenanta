<?php

namespace App\Actions\Courses;

use App\Models\Courses\CourseEnrollment;
use App\Models\Courses\CourseTest;
use App\Models\Courses\TestAttempt;
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class SubmitTestAction
{
    public function __construct(
        private GradeTestAction $gradeTestAction,
        private CalculateCourseProgressAction $calculateProgressAction
    ) {}

    public function startAttempt(User $user, CourseTest $test, CourseEnrollment $enrollment): TestAttempt
    {
        // Validate enrollment
        if ($enrollment->course_id !== $test->course_id) {
            throw new InvalidArgumentException('El examen no pertenece al curso inscrito.');
        }

        if (!$enrollment->isActive()) {
            throw new InvalidArgumentException('La inscripción no está activa.');
        }

        // Check if user can attempt this test
        if (!$test->canUserAttempt($user->id)) {
            $maxAttempts = $test->max_attempts;
            throw new InvalidArgumentException("Has alcanzado el máximo de {$maxAttempts} intentos para este examen.");
        }

        // Check for in-progress attempt
        $inProgressAttempt = TestAttempt::where('user_id', $user->id)
            ->where('test_id', $test->id)
            ->whereNull('completed_at')
            ->first();

        if ($inProgressAttempt) {
            // Check if timed out
            if ($inProgressAttempt->is_timed_out) {
                // Auto-submit with current answers
                $this->submit($inProgressAttempt, $inProgressAttempt->answers ?? []);
            } else {
                // Return existing attempt
                return $inProgressAttempt;
            }
        }

        // Create new attempt
        return TestAttempt::create([
            'user_id' => $user->id,
            'test_id' => $test->id,
            'enrollment_id' => $enrollment->id,
            'started_at' => now(),
            'answers' => [],
        ]);
    }

    public function saveProgress(TestAttempt $attempt, array $answers): TestAttempt
    {
        if ($attempt->is_completed) {
            throw new InvalidArgumentException('Este intento ya fue completado.');
        }

        if ($attempt->is_timed_out) {
            // Auto-submit on timeout
            return $this->submit($attempt, $answers);
        }

        $attempt->update([
            'answers' => $answers,
        ]);

        return $attempt->fresh();
    }

    public function submit(TestAttempt $attempt, array $answers): TestAttempt
    {
        if ($attempt->is_completed) {
            throw new InvalidArgumentException('Este intento ya fue completado.');
        }

        return DB::transaction(function () use ($attempt, $answers) {
            // Grade the test
            $attempt->submitAnswers($answers);

            // Recalculate course progress if test affects completion
            if ($attempt->test->is_required) {
                $this->calculateProgressAction->execute($attempt->enrollment);
            }

            return $attempt->fresh();
        });
    }

    public function getAttemptState(TestAttempt $attempt): array
    {
        $test = $attempt->test;
        $questions = $test->questions()
            ->with('options')
            ->when($test->shuffle_questions, fn($q) => $q->inRandomOrder())
            ->get();

        $questionsData = $questions->map(function ($question) use ($test, $attempt) {
            $options = $question->options;

            if ($test->shuffle_options) {
                $options = $options->shuffle();
            }

            return [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'points' => $question->points,
                'options' => $options->map(fn($opt) => [
                    'id' => $opt->id,
                    'text' => $opt->text,
                ])->toArray(),
                'selected' => $attempt->answers[$question->id] ?? null,
            ];
        });

        return [
            'attempt_id' => $attempt->id,
            'test_id' => $test->id,
            'test_title' => $test->title,
            'time_limit_minutes' => $test->time_limit_minutes,
            'time_remaining_seconds' => $attempt->time_remaining_seconds,
            'started_at' => $attempt->started_at,
            'is_timed_out' => $attempt->is_timed_out,
            'questions' => $questionsData,
            'total_questions' => $questions->count(),
            'answered_questions' => count(array_filter($attempt->answers ?? [])),
        ];
    }
}
