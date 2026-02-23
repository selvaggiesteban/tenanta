<?php

namespace App\Actions\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseEnrollment;
use App\Models\Courses\CourseTopic;
use App\Models\Courses\Subscription;
use App\Models\User;

class CheckSubscriptionAccessAction
{
    public function canAccessCourse(User $user, Course $course): bool
    {
        // Free courses are always accessible
        if ($course->price <= 0) {
            return true;
        }

        // Check for active enrollment
        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [
                CourseEnrollment::STATUS_ACTIVE,
                CourseEnrollment::STATUS_COMPLETED,
            ])
            ->first();

        if ($enrollment) {
            // Check if enrollment hasn't expired
            if ($enrollment->expires_at === null || $enrollment->expires_at->isFuture()) {
                return true;
            }
        }

        // Check for active subscription with course access
        return $this->hasActiveSubscriptionWithCourseAccess($user, $course);
    }

    public function canAccessTopic(User $user, CourseTopic $topic): bool
    {
        // Free preview topics are always accessible
        if ($topic->is_free_preview) {
            return true;
        }

        return $this->canAccessCourse($user, $topic->course);
    }

    public function hasActiveSubscriptionWithCourseAccess(User $user, Course $course): bool
    {
        $subscriptions = Subscription::where('user_id', $user->id)
            ->whereIn('status', [
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_TRIAL,
            ])
            ->with('plan')
            ->get();

        foreach ($subscriptions as $subscription) {
            $plan = $subscription->plan;

            // Check if plan grants access to all courses
            if ($plan->course_access === 'all') {
                return true;
            }

            // Check if plan grants access to specific courses
            if ($plan->course_access === 'specific') {
                $courseIds = $plan->course_ids ?? [];
                if (in_array($course->id, $courseIds)) {
                    return true;
                }
            }

            // Check by course category if plan has category restrictions
            if ($plan->course_access === 'category' && $course->category_id) {
                $categoryIds = $plan->category_ids ?? [];
                if (in_array($course->category_id, $categoryIds)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getAccessibleCourses(User $user): array
    {
        $accessibleCourseIds = [];

        // Get all active subscriptions
        $subscriptions = Subscription::where('user_id', $user->id)
            ->whereIn('status', [
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_TRIAL,
            ])
            ->with('plan')
            ->get();

        foreach ($subscriptions as $subscription) {
            $plan = $subscription->plan;

            if ($plan->course_access === 'all') {
                // Return all published courses
                return Course::where('status', Course::STATUS_PUBLISHED)
                    ->pluck('id')
                    ->toArray();
            }

            if ($plan->course_access === 'specific') {
                $accessibleCourseIds = array_merge($accessibleCourseIds, $plan->course_ids ?? []);
            }

            if ($plan->course_access === 'category') {
                $categoryIds = $plan->category_ids ?? [];
                $coursesInCategories = Course::whereIn('category_id', $categoryIds)
                    ->where('status', Course::STATUS_PUBLISHED)
                    ->pluck('id')
                    ->toArray();
                $accessibleCourseIds = array_merge($accessibleCourseIds, $coursesInCategories);
            }
        }

        // Add directly enrolled courses
        $enrolledCourseIds = CourseEnrollment::where('user_id', $user->id)
            ->whereIn('status', [
                CourseEnrollment::STATUS_ACTIVE,
                CourseEnrollment::STATUS_COMPLETED,
            ])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->pluck('course_id')
            ->toArray();

        $accessibleCourseIds = array_merge($accessibleCourseIds, $enrolledCourseIds);

        // Add free courses
        $freeCourseIds = Course::where('status', Course::STATUS_PUBLISHED)
            ->where('price', '<=', 0)
            ->pluck('id')
            ->toArray();

        $accessibleCourseIds = array_merge($accessibleCourseIds, $freeCourseIds);

        return array_unique($accessibleCourseIds);
    }

    public function getAccessDetails(User $user, Course $course): array
    {
        $canAccess = $this->canAccessCourse($user, $course);
        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $activeSubscription = Subscription::where('user_id', $user->id)
            ->whereIn('status', [Subscription::STATUS_ACTIVE, Subscription::STATUS_TRIAL])
            ->with('plan')
            ->first();

        return [
            'can_access' => $canAccess,
            'access_type' => $this->determineAccessType($user, $course, $enrollment, $activeSubscription),
            'enrollment' => $enrollment ? [
                'id' => $enrollment->id,
                'status' => $enrollment->status,
                'progress_percentage' => $enrollment->progress_percentage,
                'expires_at' => $enrollment->expires_at,
            ] : null,
            'subscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'plan_name' => $activeSubscription->plan->name,
                'status' => $activeSubscription->status,
                'ends_at' => $activeSubscription->ends_at,
            ] : null,
            'is_free' => $course->price <= 0,
            'price' => $course->price,
            'currency' => $course->currency,
        ];
    }

    private function determineAccessType(User $user, Course $course, ?CourseEnrollment $enrollment, ?Subscription $subscription): string
    {
        if ($course->price <= 0) {
            return 'free';
        }

        if ($enrollment && $enrollment->isActive()) {
            if ($enrollment->subscription_id) {
                return 'subscription';
            }
            return 'direct_enrollment';
        }

        if ($subscription && $this->hasActiveSubscriptionWithCourseAccess($user, $course)) {
            return 'subscription';
        }

        return 'none';
    }
}
