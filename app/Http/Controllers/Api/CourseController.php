<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'required|string|max:50|unique:courses',
            'duration_hours' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'level' => 'string|in:beginner,intermediate,advanced',
            'instructor' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'code' => $request->code,
            'duration_hours' => $request->duration_hours,
            'price' => $request->price,
            'level' => $request->level ?? 'beginner',
            'instructor' => $request->instructor,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course
        ], 201);
    }

    public function show(string $id)
    {
        $course = Course::withCount('enrollments')
            ->findOrFail($id);
        return response()->json($course);
    }

    public function update(Request $request, string $id)
    {
        $course = Course::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'code' => 'sometimes|string|max:50|unique:courses,code,' . $id,
            'duration_hours' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'level' => 'sometimes|string|in:beginner,intermediate,advanced',
            'instructor' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $course->update($request->all());

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course
        ]);
    }

    public function destroy(string $id)
    {
        $course = Course::findOrFail($id);
        
        if ($course->enrollments()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete course with existing enrollments'
            ], 422);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }
}
