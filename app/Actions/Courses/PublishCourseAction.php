<?php

namespace App\Actions\Courses;

use App\Models\Courses\Course;
use InvalidArgumentException;

class PublishCourseAction
{
    public function execute(Course $course): Course
    {
        $this->validateCourseCanBePublished($course);

        $course->update([
            'status' => Course::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return $course->fresh();
    }

    public function unpublish(Course $course): Course
    {
        $course->update([
            'status' => Course::STATUS_DRAFT,
        ]);

        return $course->fresh();
    }

    public function archive(Course $course): Course
    {
        $course->update([
            'status' => Course::STATUS_ARCHIVED,
        ]);

        return $course->fresh();
    }

    private function validateCourseCanBePublished(Course $course): void
    {
        $errors = [];

        // Must have at least one block
        if ($course->blocks()->count() === 0) {
            $errors[] = 'El curso debe tener al menos un módulo.';
        }

        // Must have at least one topic
        if ($course->topics()->count() === 0) {
            $errors[] = 'El curso debe tener al menos un tema/lección.';
        }

        // Must have a title and description
        if (empty($course->title)) {
            $errors[] = 'El curso debe tener un título.';
        }

        if (empty($course->description)) {
            $errors[] = 'El curso debe tener una descripción.';
        }

        // Validate all topics have content
        $topicsWithoutContent = $course->topics()
            ->where(function ($query) {
                $query->whereNull('video_url')
                    ->whereNull('content');
            })
            ->count();

        if ($topicsWithoutContent > 0) {
            $errors[] = "Hay {$topicsWithoutContent} temas sin contenido (video o texto).";
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }
    }
}
