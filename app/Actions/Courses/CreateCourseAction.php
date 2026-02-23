<?php

namespace App\Actions\Courses;

use App\Models\Courses\Course;
use Illuminate\Support\Facades\DB;

class CreateCourseAction
{
    public function execute(array $data): Course
    {
        return DB::transaction(function () use ($data) {
            $course = Course::create([
                'tenant_id' => $data['tenant_id'],
                'instructor_id' => $data['instructor_id'] ?? null,
                'title' => $data['title'],
                'slug' => $data['slug'] ?? \Str::slug($data['title']),
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'thumbnail' => $data['thumbnail'] ?? null,
                'trailer_video_url' => $data['trailer_video_url'] ?? null,
                'level' => $data['level'] ?? Course::LEVEL_BEGINNER,
                'language' => $data['language'] ?? 'es',
                'duration_hours' => $data['duration_hours'] ?? 0,
                'price' => $data['price'] ?? 0,
                'currency' => $data['currency'] ?? 'ARS',
                'status' => Course::STATUS_DRAFT,
                'requirements' => $data['requirements'] ?? null,
                'what_you_learn' => $data['what_you_learn'] ?? null,
                'target_audience' => $data['target_audience'] ?? null,
            ]);

            // Create blocks if provided
            if (!empty($data['blocks'])) {
                foreach ($data['blocks'] as $index => $blockData) {
                    $block = $course->blocks()->create([
                        'title' => $blockData['title'],
                        'description' => $blockData['description'] ?? null,
                        'sort_order' => $blockData['sort_order'] ?? $index + 1,
                    ]);

                    // Create topics for each block
                    if (!empty($blockData['topics'])) {
                        foreach ($blockData['topics'] as $topicIndex => $topicData) {
                            $block->topics()->create([
                                'course_id' => $course->id,
                                'title' => $topicData['title'],
                                'description' => $topicData['description'] ?? null,
                                'content_type' => $topicData['content_type'] ?? 'video',
                                'video_url' => $topicData['video_url'] ?? null,
                                'video_provider' => $topicData['video_provider'] ?? null,
                                'video_duration_seconds' => $topicData['video_duration_seconds'] ?? 0,
                                'content' => $topicData['content'] ?? null,
                                'attachments' => $topicData['attachments'] ?? null,
                                'is_free_preview' => $topicData['is_free_preview'] ?? false,
                                'sort_order' => $topicData['sort_order'] ?? $topicIndex + 1,
                            ]);
                        }
                    }
                }
            }

            return $course->fresh(['blocks.topics']);
        });
    }
}
