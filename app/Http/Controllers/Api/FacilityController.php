<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096'
                : 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'is_featured' => 'boolean',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('facilities', 'public');
        } elseif ($request->filled('image')) {
            $imagePath = $request->input('image');
        }

        $facility = Facility::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $imagePath,
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
        
        $rules = [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => $request->hasFile('image')
                ? 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096'
                : 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'location' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'is_featured' => 'sometimes|boolean',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $request->all();

        if ($request->has('name')) {
            $data['slug'] = Str::slug($request->name);
        }

        if ($request->hasFile('image')) {
            if ($facility->image) {
                Storage::disk('public')->delete($facility->image);
            }
            $data['image'] = $request->file('image')->store('facilities', 'public');
        }

        $facility->update($data);

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

    public function publicList()
    {
        try {
            $facilities = Facility::where('is_active', true)
                ->orderBy('order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($facilities);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
