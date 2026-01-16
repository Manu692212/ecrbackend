<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
            'description' => 'nullable|string',
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

        $imagePayload = [];
        if ($request->hasFile('image')) {
            $imagePayload = $this->buildImagePayloadFromFile($request->file('image'));
        } elseif ($request->filled('image')) {
            $imagePayload = [
                'image' => $request->input('image'),
                'image_data' => null,
                'image_mime' => null,
            ];
        }

        $facility = Facility::create(array_merge([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'category' => $request->category,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'features' => $request->features,
            'is_featured' => $request->is_featured ?? false,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ], $imagePayload));

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
            'description' => 'sometimes|nullable|string',
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
            $data = array_merge(
                $data,
                $this->buildImagePayloadFromFile($request->file('image'), $facility)
            );
        } elseif ($request->filled('image')) {
            $data['image_data'] = null;
            $data['image_mime'] = null;
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

    public function uploadImage(Request $request, string $id)
    {
        $facility = Facility::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $facility->update(
            $this->buildImagePayloadFromFile($request->file('image'), $facility)
        );

        return response()->json([
            'message' => 'Facility image uploaded successfully',
            'facility' => $facility,
        ]);
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

    /**
     * @return array{image_data:string,image_mime:string|null,image:?string}
     */
    private function buildImagePayloadFromFile(UploadedFile $image, ?Facility $existing = null): array
    {
        if ($existing && $existing->image) {
            Storage::disk('public')->delete($existing->image);
        }

        $contents = file_get_contents($image->getRealPath());

        return [
            'image_data' => base64_encode($contents ?: ''),
            'image_mime' => $image->getMimeType() ?: $image->getClientMimeType(),
            'image' => null,
        ];
    }
}
