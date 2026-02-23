<?php

namespace App\Actions\Courses;

use App\Models\Courses\Course;
use Illuminate\Support\Facades\DB;

class UpdateCourseAction
{
    public function execute(Course $course, array $data): Course
    {
        return DB::transaction(function () use ($course, $data) {
            $updateData = array_filter([
                'instructor_id' => $data['instructor_id'] ?? null,
                'title' => $data['title'] ?? null,
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'thumbnail' => $data['thumbnail'] ?? null,
                'trailer_video_url' => $data['trailer_video_url'] ?? null,
                'level' => $data['level'] ?? null,
                'language' => $data['language'] ?? null,
                'duration_hours' => $data['duration_hours'] ?? null,
                'price' => $data['price'] ?? null,
                'currency' => $data['currency'] ?? null,
                'requirements' => $data['requirements'] ?? null,
                'what_you_learn' => $data['what_you_learn'] ?? null,
                'target_audience' => $data['target_audience'] ?? null,
            ], fn($value) => $value !== null);

            if (!empty($updateData)) {
                $course->update($updateData);
            }

            // Update blocks if provided
            if (isset($data['blocks'])) {
                $existingBlockIds = [];

                foreach ($data['blocks'] as $index => $blockData) {
                    if (isset($blockData['id'])) {
                        // Update existing block
                        $block = $course->blocks()->find($blockData['id']);
                        if ($block) {
                            $block->update([
                                'title' => $blockData['title'],
                                'description' => $blockData['description'] ?? null,
                                'sort_order' => $blockData['sort_order'] ?? $index + 1,
                            ]);
                            $existingBlockIds[] = $block->id;
                        }
                    } else {
                        // Create new block
                        $block = $course->blocks()->create([
                            'title' => $blockData['title'],
                            'description' => $blockData['description'] ?? null,
                            'sort_order' => $blockData['sort_order'] ?? $index + 1,
                        ]);
                        $existingBlockIds[] = $block->id;
                    }

                    // Handle topics for this block
                    if (isset($blockData['topics'])) {
                        $this->updateBlockTopics($course, $block, $blockData['topics']);
                    }
                }

                // Delete blocks not in the update
                $course->blocks()->whereNotIn('id', $existingBlockIds)->delete();
            }

            return $course->fresh(['blocks.topics']);
        });
    }

    private function updateBlockTopics(Course $course, $block, array $topics): void
    {
        $existingTopicIds = [];

        foreach ($topics as $index => $topicData) {
            if (isset($topicData['id'])) {
                // Update existing topic
                $topic = $block->topics()->find($topicData['id']);
                if ($topic) {
                    $topic->update([
                        'title' => $topicData['title'],
                        'description' => $topicData['description'] ?? null,
                        'content_type' => $topicData['content_type'] ?? 'video',
                        'video_url' => $topicData['video_url'] ?? null,
                        'video_provider' => $topicData['video_provider'] ?? null,
                        'video_duration_seconds' => $topicData['video_duration_seconds'] ?? 0,
                        'content' => $topicData['content'] ?? null,
                        'attachments' => $topicData['attachments'] ?? null,
                        'is_free_preview' => $topicData['is_free_preview'] ?? false,
                        'sort_order' => $topicData['sort_order'] ?? $index + 1,
                    ]);
                    $existingTopicIds[] = $topic->id;
                }
            } else {
                // Create new topic
                $topic = $block->topics()->create([
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
                    'sort_order' => $topicData['sort_order'] ?? $index + 1,
                ]);
                $existingTopicIds[] = $topic->id;
            }
        }

        // Delete topics not in the update
        $block->topics()->whereNotIn('id', $existingTopicIds)->delete();
    }
}
