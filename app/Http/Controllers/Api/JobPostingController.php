<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class JobPostingController extends Controller
{
    public function index()
    {
        $jobs = JobPosting::orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($jobs);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'location' => 'required|string|max:255',
            'employment_type' => 'required|string|in:full-time,part-time,contract',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date|after:today',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job = JobPosting::create([
            'title' => $request->title,
            'department' => $request->department,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'location' => $request->location,
            'employment_type' => $request->employment_type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'deadline' => $request->deadline,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Job posting created successfully',
            'job' => $job
        ], 201);
    }

    public function show(string $id)
    {
        $job = JobPosting::findOrFail($id);
        return response()->json($job);
    }

    public function update(Request $request, string $id)
    {
        $job = JobPosting::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'department' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'requirements' => 'nullable|string',
            'location' => 'sometimes|string|max:255',
            'employment_type' => 'sometimes|string|in:full-time,part-time,contract',
            'salary_min' => 'sometimes|numeric|min:0',
            'salary_max' => 'sometimes|numeric|min:0',
            'deadline' => 'nullable|date|after:today',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $job->update($request->all());

        return response()->json([
            'message' => 'Job posting updated successfully',
            'job' => $job
        ]);
    }

    public function destroy(string $id)
    {
        $job = JobPosting::findOrFail($id);
        $job->delete();

        return response()->json(['message' => 'Job posting deleted successfully']);
    }
}
