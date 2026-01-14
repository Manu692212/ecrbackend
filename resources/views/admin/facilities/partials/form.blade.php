@csrf

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
        <input type="text" name="name" value="{{ old('name', $facility->name ?? '') }}" required
               class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <input type="text" name="category" value="{{ old('category', $facility->category ?? '') }}"
               class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
        <input type="text" name="location" value="{{ old('location', $facility->location ?? '') }}"
               class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
            <input type="number" name="capacity" value="{{ old('capacity', $facility->capacity ?? '') }}"
                   class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500" min="1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
            <input type="number" name="order" value="{{ old('order', $facility->order ?? 0) }}"
                   class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500" min="0">
        </div>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1 mt-6">Description</label>
    <textarea name="description" rows="4"
              class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $facility->description ?? '') }}</textarea>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700 mb-1">Features (comma or newline separated)</label>
    <textarea name="features_text" rows="3"
              class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"
              placeholder="Wi-Fi&#10;Projector&#10;AC">{{ old('features_text', isset($facility) && $facility->features ? implode("\n", $facility->features) : '') }}</textarea>
</div>

<div class="mt-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">Image {{ isset($facility) ? '(leave empty to keep existing)' : '*' }}</label>
    <input type="file" name="image" {{ isset($facility) ? '' : 'required' }}
           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-700">
    @if(isset($facility) && $facility->image_url)
        <div class="mt-3 flex items-center gap-4">
            <img src="{{ $facility->image_url }}" alt="{{ $facility->name }}" class="h-20 w-20 object-cover rounded-lg border">
            <p class="text-sm text-gray-500">Current image preview</p>
        </div>
    @endif
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
    <label class="flex items-center gap-3 bg-gray-50 px-4 py-3 rounded-lg border border-gray-200 cursor-pointer">
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $facility->is_featured ?? false) ? 'checked' : '' }}
               class="rounded text-emerald-600 focus:ring-emerald-500">
        <span class="text-sm font-medium text-gray-700">Mark as featured</span>
    </label>
    <label class="flex items-center gap-3 bg-gray-50 px-4 py-3 rounded-lg border border-gray-200 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $facility->is_active ?? true) ? 'checked' : '' }}
               class="rounded text-emerald-600 focus:ring-emerald-500">
        <span class="text-sm font-medium text-gray-700">Show on website</span>
    </label>
</div>

@error('image')
    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
@enderror
