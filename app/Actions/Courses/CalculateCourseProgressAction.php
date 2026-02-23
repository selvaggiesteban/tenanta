<?php

namespace App\Actions\Courses;

use App\Models\Courses\CourseEnrollment;
use App\Models\Courses\TopicProgress;

class CalculateCourseProgressAction
{
    public function execute(CourseEnrollment $enrollment): CourseEnrollment
    {
        $course = $enrollment->course;

        // Get total topics count
        $totalTopics = $course->topics()->count();

        if ($totalTopics === 0) {
            $enrollment->update([
                'progress_percentage' => 0,
                'completed_topics' => 0,
                'total_topics' => 0,
            ]);
            return $enrollment->fresh();
        }

        // Count completed topics
        $completedTopics = TopicProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        // Calculate percentage
        $percentage = (int) round(($completedTopics / $totalTopics) * 100);

        // Check if required tests are passed
        $requiredTestsPassed = $this->checkRequiredTestsPassed($enrollment);

        // Update enrollment
        $updateData = [
            'progress_percentage' => $percentage,
            'completed_topics' => $completedTopics,
            'total_topics' => $totalTopics,
        ];

        // Mark as completed if 100% progress and all required tests passed
        if ($percentage >= 100 && $requiredTestsPassed) {
            if ($enrollment->status !== CourseEnrollment::STATUS_COMPLETED) {
                $updateData['status'] = CourseEnrollment::STATUS_COMPLETED;
                $updateData['completed_at'] = now();
            }
        }

        $enrollment->update($updateData);

        return $enrollment->fresh();
    }

    private function checkRequiredTestsPassed(CourseEnrollment $enrollment): bool
    {
        $requiredTests = $enrollment->course->tests()->required()->get();

        if ($requiredTests->isEmpty()) {
            return true;
        }

        foreach ($requiredTests as $test) {
            $passed = $test->hasUserPassed($enrollment->user_id);
            if (!$passed) {
                return false;
            }
        }

        return true;
    }

    public function getDetailedProgress(CourseEnrollment $enrollment): array
    {
        $course = $enrollment->course;
        $blocks = $course->blocks()->with(['topics.progress' => function ($query) use ($enrollment) {
            $query->where('enrollment_id', $enrollment->id);
        }])->get();

        $blocksProgress = [];

        foreach ($blocks as $block) {
            $blockTopics = $block->topics;
            $completedInBlock = $blockTopics->filter(function ($topic) {
                return $topic->progress->first()?->is_completed ?? false;
            })->count();

            $blocksProgress[] = [
                'block_id' => $block->id,
                'block_title' => $block->title,
                'total_topics' => $blockTopics->count(),
                'completed_topics' => $completedInBlock,
                'percentage' => $blockTopics->count() > 0
                    ? (int) round(($completedInBlock / $blockTopics->count()) * 100)
                    : 0,
                'topics' => $blockTopics->map(function ($topic) {
                    $progress = $topic->progress->first();
                    return [
                        'topic_id' => $topic->id,
                        'topic_title' => $topic->title,
                        'is_completed' => $progress?->is_completed ?? false,
                        'completed_at' => $progress?->completed_at,
                        'watch_percentage' => $progress?->watch_percentage ?? 0,
                        'last_position_seconds' => $progress?->last_position_seconds ?? 0,
                    ];
                })->toArray(),
            ];
        }

        // Get test results
        $testsProgress = [];
        $tests = $course->tests()->get();

        foreach ($tests as $test) {
            $bestAttempt = $test->getBestAttempt($enrollment->user_id);
            $testsProgress[] = [
                'test_id' => $test->id,
                'test_title' => $test->title,
                'is_required' => $test->is_required,
                'passing_score' => $test->passing_score,
                'attempts_used' => $test->getAttemptsForUser($enrollment->user_id),
                'max_attempts' => $test->max_attempts,
                'best_score' => $bestAttempt?->percentage,
                'passed' => $bestAttempt?->passed ?? false,
            ];
        }

        return [
            'enrollment_id' => $enrollment->id,
            'course_id' => $course->id,
            'overall_percentage' => $enrollment->progress_percentage,
            'completed_topics' => $enrollment->completed_topics,
            'total_topics' => $enrollment->total_topics,
            'status' => $enrollment->status,
            'blocks' => $blocksProgress,
            'tests' => $testsProgress,
        ];
    }
}
