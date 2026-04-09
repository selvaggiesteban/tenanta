<?php

namespace App\Http\Controllers\Api\LMS;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Listado de cursos del inquilino.
     */
    public function index(): JsonResponse
    {
        $courses = Course::withCount('blocks')->get();
        return response()->json(['success' => true, 'data' => $courses]);
    }

    /**
     * Detalle del curso con su cronograma (Sección 8).
     */
    public function show(Course $course): JsonResponse
    {
        $course->load(['blocks.topics', 'schedules']);
        return response()->json(['success' => true, 'data' => $course]);
    }

    /**
     * Gestión del Cronograma por el Profesor (Fase 7).
     */
    public function updateSchedule(Request $request, Course $course): JsonResponse
    {
        $this->authorize('isTeacher', User::class); // Middleware ya protege esto

        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.event_name' => 'required|string',
            'schedules.*.start_date' => 'required|date',
        ]);

        $course->schedules()->delete();
        $course->schedules()->createMany($validated['schedules']);

        return response()->json(['success' => true, 'message' => 'Cronograma actualizado correctamente.']);
    }
}
