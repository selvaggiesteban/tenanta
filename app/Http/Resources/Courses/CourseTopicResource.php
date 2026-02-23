<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseTopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content_type' => $this->content_type,
            'video_duration_seconds' => $this->video_duration_seconds,
            'formatted_duration' => $this->formatted_duration,
            'is_free_preview' => $this->is_free_preview,
            'sort_order' => $this->sort_order,

            // Only include full content for authorized users
            'video_url' => $this->when(
                $this->shouldShowContent($request),
                $this->video_url
            ),
            'embed_url' => $this->when(
                $this->shouldShowContent($request),
                $this->embed_url
            ),
            'content' => $this->when(
                $this->shouldShowContent($request),
                $this->content
            ),
            'attachments' => $this->when(
                $this->shouldShowContent($request),
                $this->attachments
            ),

            // Progress (when loaded)
            'progress' => $this->when(
                $this->relationLoaded('progress') && $this->progress->first(),
                fn() => new TopicProgressResource($this->progress->first())
            ),
        ];
    }

    private function shouldShowContent(Request $request): bool
    {
        // Free preview topics always show content
        if ($this->is_free_preview) {
            return true;
        }

        // Check if request has enrollment context
        return $request->has('_show_full_content') ||
               $request->route('enrollment') !== null;
    }
}
