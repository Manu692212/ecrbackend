<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CareerController extends Controller
{
    public function index()
    {
        $careers = Career::orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($careers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'location' => 'required|string|max:255',
            'employment_type' => 'required|string|in:full-time,part-time,contract',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date|after:today',
            'category' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $career = Career::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'requirements' => $request->requirements,
            'location' => $request->location,
            'employment_type' => $request->employment_type,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'deadline' => $request->deadline,
            'category' => $request->category,
            'is_featured' => $request->is_featured ?? false,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Career created successfully',
            'career' => $career
        ], 201);
    }

    public function show(string $id)
    {
        $career = Career::findOrFail($id);
        return response()->json($career);
    }

    public function update(Request $request, string $id)
    {
        $career = Career::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'requirements' => 'nullable|string',
            'location' => 'sometimes|string|max:255',
            'employment_type' => 'sometimes|string|in:full-time,part-time,contract',
            'salary_min' => 'sometimes|numeric|min:0',
            'salary_max' => 'sometimes|numeric|min:0',
            'deadline' => 'nullable|date|after:today',
            'category' => 'nullable|string|max:255',
            'is_featured' => 'sometimes|boolean',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($request->has('title')) {
            $request->merge(['slug' => Str::slug($request->title)]);
        }

        $career->update($request->all());

        return response()->json([
            'message' => 'Career updated successfully',
            'career' => $career
        ]);
    }

    public function destroy(string $id)
    {
        $career = Career::findOrFail($id);
        $career->delete();

        return response()->json(['message' => 'Career deleted successfully']);
    }
}
