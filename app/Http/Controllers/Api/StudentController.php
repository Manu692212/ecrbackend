<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $student = Student::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'education_level' => $request->education_level,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student
        ], 201);
    }

    public function show(string $id)
    {
        $student = Student::with(['enrollments.course'])
            ->withCount('enrollments')
            ->findOrFail($id);
        return response()->json($student);
    }

    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:students,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $student->update($request->all());

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student
        ]);
    }

    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        
        if ($student->enrollments()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete student with existing enrollments'
            ], 422);
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    public function uploadResume(Request $request, string $id)
    {
        $student = Student::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            $filename = 'resume_' . $student->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            if ($student->resume_path) {
                Storage::disk('public')->delete($student->resume_path);
            }
            
            $path = $file->store('resumes', 'public');
            $student->update(['resume_path' => $path]);

            return response()->json([
                'message' => 'Resume uploaded successfully',
                'resume_path' => $path
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function downloadResume(string $id)
    {
        $student = Student::findOrFail($id);
        
        if (!$student->resume_path) {
            return response()->json(['message' => 'No resume found'], 404);
        }

        $filePath = storage_path('app/public/' . $student->resume_path);
        
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->download($filePath, 'resume_' . $student->first_name . '_' . $student->last_name . '.pdf');
    }

    public function deleteResume(string $id)
    {
        $student = Student::findOrFail($id);
        
        if (!$student->resume_path) {
            return response()->json(['message' => 'No resume found'], 404);
        }

        Storage::disk('public')->delete($student->resume_path);
        $student->update(['resume_path' => null]);

        return response()->json(['message' => 'Resume deleted successfully']);
    }
}
