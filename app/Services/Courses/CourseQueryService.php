<?php

namespace App\Services\Courses;

use App\Models\Courses\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CourseQueryService
{
    /**
     * Build the course query based on request parameters.
     */
    public function query(Request $request): Builder
    {
        $query = Course::query()
            ->with(['instructor'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->level, fn($q, $level) => $q->where('level', $level))
            ->when($request->search, fn($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($request->featured, fn($q) => $q->where('is_featured', true))
            ->when($request->min_price !== null, fn($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price !== null, fn($q) => $q->where('price', '<=', $request->max_price));

        // Sorting logic
        $sortField = match ($request->sort_by) {
            'price' => 'price',
            'rating' => 'rating',
            'popular' => 'enrolled_count',
            default => 'created_at',
        };
        
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query;
    }
}
