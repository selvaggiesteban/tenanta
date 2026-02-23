<?php

namespace App\Actions\Courses;

use App\Models\Courses\CourseEnrollment;
use App\Models\Courses\CourseTopic;
use App\Models\Courses\TopicProgress;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class MarkTopicCompletedAction
{
    public function __construct(
        private CalculateCourseProgressAction $calculateProgressAction
    ) {}

    public function execute(CourseEnrollment $enrollment, CourseTopic $topic): TopicProgress
    {
        // Validate topic belongs to enrolled course
        if ($topic->course_id !== $enrollment->course_id) {
            throw new InvalidArgumentException('El tema no pertenece al curso inscrito.');
        }

        // Validate enrollment is active
        if (!$enrollment->isActive()) {
            throw new InvalidArgumentException('La inscripción no está activa.');
        }

        return DB::transaction(function () use ($enrollment, $topic) {
            // Get or create topic progress
            $progress = TopicProgress::firstOrCreate(
                [
                    'user_id' => $enrollment->user_id,
                    'topic_id' => $topic->id,
                    'enrollment_id' => $enrollment->id,
                ],
                [
                    'is_completed' => false,
                    'watch_time_seconds' => 0,
                    'last_position_seconds' => 0,
                ]
            );

            // Mark as completed if not already
            if (!$progress->is_completed) {
                $progress->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);

                // Recalculate course progress
                $this->calculateProgressAction->execute($enrollment);
            }

            return $progress->fresh();
        });
    }

    public function markIncomplete(CourseEnrollment $enrollment, CourseTopic $topic): TopicProgress
    {
        $progress = TopicProgress::where('enrollment_id', $enrollment->id)
            ->where('topic_id', $topic->id)
            ->first();

        if (!$progress) {
            throw new InvalidArgumentException('No hay progreso registrado para este tema.');
        }

        $progress->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);

        // Recalculate course progress
        $this->calculateProgressAction->execute($enrollment);

        return $progress->fresh();
    }

    public function updateWatchProgress(
        CourseEnrollment $enrollment,
        CourseTopic $topic,
        int $positionSeconds,
        int $watchedSeconds
    ): TopicProgress {
        // Validate topic belongs to enrolled course
        if ($topic->course_id !== $enrollment->course_id) {
            throw new InvalidArgumentException('El tema no pertenece al curso inscrito.');
        }

        // Get or create topic progress
        $progress = TopicProgress::firstOrCreate(
            [
                'user_id' => $enrollment->user_id,
                'topic_id' => $topic->id,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'is_completed' => false,
                'watch_time_seconds' => 0,
                'last_position_seconds' => 0,
            ]
        );

        // Update progress
        $progress->updateWatchProgress($positionSeconds, $watchedSeconds);

        // Update enrollment last activity
        $enrollment->updateLastActivity();

        return $progress->fresh();
    }
}
