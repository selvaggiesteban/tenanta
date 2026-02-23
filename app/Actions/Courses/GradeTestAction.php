<?php

namespace App\Actions\Courses;

use App\Models\Courses\CourseTest;
use App\Models\Courses\TestAttempt;

class GradeTestAction
{
    public function getResults(TestAttempt $attempt): array
    {
        if (!$attempt->is_completed) {
            return [
                'error' => 'El intento aún no ha sido completado.',
            ];
        }

        $test = $attempt->test;
        $questions = $test->questions()->with('options')->get();

        $questionResults = [];

        foreach ($questions as $question) {
            $result = $attempt->results[$question->id] ?? null;

            $questionData = [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'points' => $question->points,
                'points_earned' => $result['points'] ?? 0,
                'is_correct' => $result['correct'] ?? false,
                'selected_options' => $result['selected'] ?? [],
            ];

            // Include correct answers and explanation if test allows showing after completion
            if ($test->show_answers_after) {
                $questionData['correct_options'] = $result['correct_options'] ?? [];
                $questionData['explanation'] = $question->explanation;

                // Include option details
                $questionData['options'] = $question->options->map(fn($opt) => [
                    'id' => $opt->id,
                    'text' => $opt->text,
                    'is_correct' => $opt->is_correct,
                    'was_selected' => in_array($opt->id, $result['selected'] ?? []),
                ])->toArray();
            }

            $questionResults[] = $questionData;
        }

        return [
            'attempt_id' => $attempt->id,
            'test_id' => $test->id,
            'test_title' => $test->title,
            'score' => $attempt->score,
            'total_points' => $attempt->total_points,
            'percentage' => $attempt->percentage,
            'passed' => $attempt->passed,
            'passing_score' => $test->passing_score,
            'time_spent' => $attempt->formatted_time_spent,
            'time_spent_seconds' => $attempt->time_spent_seconds,
            'started_at' => $attempt->started_at,
            'completed_at' => $attempt->completed_at,
            'show_answers' => $test->show_answers_after,
            'questions' => $questionResults,
            'summary' => [
                'total_questions' => count($questionResults),
                'correct_answers' => collect($questionResults)->where('is_correct', true)->count(),
                'incorrect_answers' => collect($questionResults)->where('is_correct', false)->count(),
            ],
        ];
    }

    public function getTestStatistics(CourseTest $test): array
    {
        $attempts = $test->attempts()->completed()->get();

        if ($attempts->isEmpty()) {
            return [
                'total_attempts' => 0,
                'unique_users' => 0,
                'average_score' => 0,
                'pass_rate' => 0,
                'average_time_seconds' => 0,
            ];
        }

        $totalAttempts = $attempts->count();
        $uniqueUsers = $attempts->pluck('user_id')->unique()->count();
        $averageScore = $attempts->avg('percentage');
        $passedAttempts = $attempts->where('passed', true)->count();
        $passRate = ($passedAttempts / $totalAttempts) * 100;
        $averageTime = $attempts->avg('time_spent_seconds');

        // Question-level statistics
        $questionStats = $this->calculateQuestionStatistics($test, $attempts);

        return [
            'total_attempts' => $totalAttempts,
            'unique_users' => $uniqueUsers,
            'average_score' => round($averageScore, 1),
            'pass_rate' => round($passRate, 1),
            'average_time_seconds' => (int) $averageTime,
            'score_distribution' => $this->calculateScoreDistribution($attempts),
            'question_statistics' => $questionStats,
        ];
    }

    private function calculateScoreDistribution($attempts): array
    {
        $ranges = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
        ];

        foreach ($attempts as $attempt) {
            $score = $attempt->percentage;

            if ($score <= 20) {
                $ranges['0-20']++;
            } elseif ($score <= 40) {
                $ranges['21-40']++;
            } elseif ($score <= 60) {
                $ranges['41-60']++;
            } elseif ($score <= 80) {
                $ranges['61-80']++;
            } else {
                $ranges['81-100']++;
            }
        }

        return $ranges;
    }

    private function calculateQuestionStatistics(CourseTest $test, $attempts): array
    {
        $questions = $test->questions()->with('options')->get();
        $stats = [];

        foreach ($questions as $question) {
            $totalAnswers = 0;
            $correctAnswers = 0;
            $optionSelections = [];

            foreach ($question->options as $option) {
                $optionSelections[$option->id] = 0;
            }

            foreach ($attempts as $attempt) {
                $result = $attempt->results[$question->id] ?? null;

                if ($result) {
                    $totalAnswers++;

                    if ($result['correct']) {
                        $correctAnswers++;
                    }

                    foreach ($result['selected'] ?? [] as $selectedId) {
                        if (isset($optionSelections[$selectedId])) {
                            $optionSelections[$selectedId]++;
                        }
                    }
                }
            }

            $stats[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'total_answers' => $totalAnswers,
                'correct_answers' => $correctAnswers,
                'correct_rate' => $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0,
                'option_selections' => $question->options->map(fn($opt) => [
                    'id' => $opt->id,
                    'text' => $opt->text,
                    'is_correct' => $opt->is_correct,
                    'selection_count' => $optionSelections[$opt->id] ?? 0,
                    'selection_percentage' => $totalAnswers > 0
                        ? round(($optionSelections[$opt->id] / $totalAnswers) * 100, 1)
                        : 0,
                ])->toArray(),
            ];
        }

        return $stats;
    }

    public function getUserTestHistory(int $userId, CourseTest $test): array
    {
        $attempts = TestAttempt::where('user_id', $userId)
            ->where('test_id', $test->id)
            ->orderByDesc('created_at')
            ->get();

        return [
            'test_id' => $test->id,
            'test_title' => $test->title,
            'max_attempts' => $test->max_attempts,
            'attempts_used' => $attempts->count(),
            'remaining_attempts' => $test->getRemainingAttempts($userId),
            'best_score' => $attempts->where('completed_at', '!=', null)->max('percentage'),
            'has_passed' => $attempts->where('passed', true)->isNotEmpty(),
            'attempts' => $attempts->map(fn($attempt) => [
                'id' => $attempt->id,
                'score' => $attempt->score,
                'total_points' => $attempt->total_points,
                'percentage' => $attempt->percentage,
                'passed' => $attempt->passed,
                'time_spent' => $attempt->formatted_time_spent,
                'started_at' => $attempt->started_at,
                'completed_at' => $attempt->completed_at,
                'is_completed' => $attempt->is_completed,
            ])->toArray(),
        ];
    }
}
