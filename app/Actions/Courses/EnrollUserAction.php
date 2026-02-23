<?php

namespace App\Actions\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseEnrollment;
use App\Models\Courses\Subscription;
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class EnrollUserAction
{
    public function execute(User $user, Course $course, ?Subscription $subscription = null): CourseEnrollment
    {
        // Check if user is already enrolled
        $existingEnrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingEnrollment) {
            // Reactivate if expired
            if ($existingEnrollment->status === CourseEnrollment::STATUS_EXPIRED) {
                $existingEnrollment->update([
                    'status' => CourseEnrollment::STATUS_ACTIVE,
                    'subscription_id' => $subscription?->id,
                    'expires_at' => $subscription?->ends_at,
                ]);
                return $existingEnrollment->fresh();
            }

            throw new InvalidArgumentException('El usuario ya está inscrito en este curso.');
        }

        // Validate course is published
        if ($course->status !== Course::STATUS_PUBLISHED) {
            throw new InvalidArgumentException('No se puede inscribir en un curso no publicado.');
        }

        // Validate subscription access if course requires it
        if ($course->price > 0 && !$subscription) {
            throw new InvalidArgumentException('Se requiere una suscripción activa para inscribirse en este curso.');
        }

        if ($subscription && !$subscription->isActive()) {
            throw new InvalidArgumentException('La suscripción no está activa.');
        }

        return DB::transaction(function () use ($user, $course, $subscription) {
            $enrollment = CourseEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'subscription_id' => $subscription?->id,
                'status' => CourseEnrollment::STATUS_ACTIVE,
                'enrolled_at' => now(),
                'expires_at' => $subscription?->ends_at,
                'progress_percentage' => 0,
                'completed_topics' => 0,
                'total_topics' => $course->total_topics,
            ]);

            // Update course enrollment count
            $course->increment('enrolled_count');

            return $enrollment;
        });
    }

    public function unenroll(CourseEnrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment) {
            $course = $enrollment->course;

            // Delete topic progress
            $enrollment->topicProgress()->delete();

            // Delete test attempts
            $enrollment->testAttempts()->delete();

            // Delete enrollment
            $enrollment->delete();

            // Update course enrollment count
            $course->decrement('enrolled_count');
        });
    }

    public function complete(CourseEnrollment $enrollment): CourseEnrollment
    {
        if ($enrollment->status === CourseEnrollment::STATUS_COMPLETED) {
            return $enrollment;
        }

        $enrollment->update([
            'status' => CourseEnrollment::STATUS_COMPLETED,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        return $enrollment->fresh();
    }

    public function expire(CourseEnrollment $enrollment): CourseEnrollment
    {
        $enrollment->update([
            'status' => CourseEnrollment::STATUS_EXPIRED,
        ]);

        return $enrollment->fresh();
    }
}
