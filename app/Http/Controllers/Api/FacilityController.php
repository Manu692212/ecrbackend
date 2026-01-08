<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($facilities);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'is_featured' => 'boolean',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $facility = Facility::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $request->image,
            'icon' => $request->icon,
            'category' => $request->category,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'features' => $request->features,
            'is_featured' => $request->is_featured ?? false,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Facility created successfully',
            'facility' => $facility
        ], 201);
    }

    public function show(string $id)
    {
        $facility = Facility::findOrFail($id);
        return response()->json($facility);
    }

    public function update(Request $request, string $id)
    {
        $facility = Facility::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'location' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'is_featured' => 'sometimes|boolean',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ($request->has('name')) {
            $request->merge(['slug' => Str::slug($request->name)]);
        }

        $facility->update($request->all());

        return response()->json([
            'message' => 'Facility updated successfully',
            'facility' => $facility
        ]);
    }

    public function destroy(string $id)
    {
        $facility = Facility::findOrFail($id);
        $facility->delete();

        return response()->json(['message' => 'Facility deleted successfully']);
    }
}
