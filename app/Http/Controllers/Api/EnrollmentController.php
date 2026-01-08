<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($enrollments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0',
            'payment_status' => 'string|in:pending,paid,failed,refunded',
            'enrollment_status' => 'string|in:active,completed,dropped,suspended',
            'completion_date' => 'nullable|date|after_or_equal:enrollment_date',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $existingEnrollment = Enrollment::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->where('enrollment_status', '!=', 'completed')
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'message' => 'Student is already enrolled in this course'
            ], 422);
        }

        $enrollment = Enrollment::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'enrollment_date' => $request->enrollment_date,
            'amount_paid' => $request->amount_paid,
            'payment_status' => $request->payment_status ?? 'pending',
            'enrollment_status' => $request->enrollment_status ?? 'active',
            'completion_date' => $request->completion_date,
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'message' => 'Enrollment created successfully',
            'enrollment' => $enrollment->load(['student', 'course'])
        ], 201);
    }

    public function show(string $id)
    {
        $enrollment = Enrollment::with(['student', 'course'])
            ->findOrFail($id);
        return response()->json($enrollment);
    }

    public function update(Request $request, string $id)
    {
        $enrollment = Enrollment::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'enrollment_date' => 'sometimes|date',
            'amount_paid' => 'sometimes|numeric|min:0',
            'payment_status' => 'sometimes|string|in:pending,paid,failed,refunded',
            'enrollment_status' => 'sometimes|string|in:active,completed,dropped,suspended',
            'completion_date' => 'nullable|date|after_or_equal:enrollment_date',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $enrollment->update($request->all());

        return response()->json([
            'message' => 'Enrollment updated successfully',
            'enrollment' => $enrollment->load(['student', 'course'])
        ]);
    }

    public function destroy(string $id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->delete();

        return response()->json(['message' => 'Enrollment deleted successfully']);
    }

    public function getByStudent($student_id)
    {
        $student = Student::findOrFail($student_id);
        $enrollments = Enrollment::with('course')
            ->where('student_id', $student_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json($enrollments);
    }

    public function getByCourse($course_id)
    {
        $course = Course::findOrFail($course_id);
        $enrollments = Enrollment::with('student')
            ->where('course_id', $course_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json($enrollments);
    }
}
