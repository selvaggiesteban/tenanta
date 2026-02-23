<?php

namespace App\Http\Resources\Courses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseInstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ?? null,
            'bio' => $this->bio ?? null,
        ];
    }
}
