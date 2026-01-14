<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('order')->orderBy('name')->paginate(10);
        $activeCount = Facility::where('is_active', true)->count();

        return view('admin.facilities.index', compact('facilities', 'activeCount'));
    }

    public function create()
    {
        return view('admin.facilities.create', [
            'facility' => new Facility(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateFacility($request);

        if ($request->hasFile('image')) {
            $data = array_merge($data, $this->buildImagePayloadFromFile($request->file('image')));
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['order'] = $data['order'] ?? 0;

        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['features'] = $this->normalizeFeatures($request->input('features_text'));

        Facility::create($data);

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Facility created successfully.');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $data = $this->validateFacility($request, true);

        if ($request->hasFile('image')) {
            $data = array_merge(
                $data,
                $this->buildImagePayloadFromFile($request->file('image'))
            );
        }

        if ($request->filled('name') && $facility->name !== $request->name) {
            $data['slug'] = $this->generateUniqueSlug($request->name, $facility->id);
        }

        if ($request->filled('features_text')) {
            $data['features'] = $this->normalizeFeatures($request->input('features_text'));
        } elseif ($request->has('features_text')) {
            $data['features'] = null;
        }

        $data['is_featured'] = $request->boolean('is_featured', $facility->is_featured);
        $data['is_active'] = $request->boolean('is_active', $facility->is_active);
        $data['order'] = $data['order'] ?? $facility->order;

        $facility->update($data);

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Facility updated successfully.');
    }

    public function destroy(Facility $facility)
    {
        $facility->delete();

        return redirect()
            ->route('admin.facilities.index')
            ->with('success', 'Facility deleted successfully.');
    }

    private function validateFacility(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);
    }

    private function normalizeFeatures(?string $rawFeatures): ?array
    {
        if (!$rawFeatures) {
            return null;
        }

        $features = array_filter(array_map(
            fn ($feature) => trim($feature),
            preg_split('/[\r\n,]+/', $rawFeatures) ?: []
        ));

        return $features ? array_values($features) : null;
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: Str::random(8);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Facility::where('slug', $slug)
                ->when($ignoreId, fn ($query, $id) => $query->where('id', '!=', $id))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * @return array{image_data:string,image_mime:string|null,image:?string}
     */
    private function buildImagePayloadFromFile(UploadedFile $image): array
    {
        $contents = file_get_contents($image->getRealPath());

        return [
            'image_data' => base64_encode($contents ?: ''),
            'image_mime' => $image->getMimeType() ?: $image->getClientMimeType(),
            'image' => null,
        ];
    }
}
