@php
    $statusPill = fn(bool $state) => $state
        ? 'bg-green-100 text-green-800'
        : 'bg-red-100 text-red-800';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-slate-900 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <i class="fas fa-tools text-2xl text-emerald-300"></i>
                <div>
                    <p class="text-sm uppercase tracking-widest text-slate-300">ECR Wings Academy</p>
                    <h1 class="text-2xl font-bold">Facilities Manager</h1>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-full bg-slate-800 hover:bg-slate-700 transition text-sm font-semibold">
                    <i class="fas fa-home mr-2"></i>Admin Dashboard
                </a>
                <a href="{{ route('admin.facilities.create') }}" class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-600 transition text-sm font-semibold">
                    <i class="fas fa-plus mr-2"></i>Add Facility
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded">
                <p class="text-emerald-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex flex-wrap justify-between items-center gap-4">
                <div>
                    <p class="text-sm text-gray-500 uppercase tracking-widest">Overview</p>
                    <h2 class="text-3xl font-semibold text-gray-900">Facilities</h2>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 mb-1">Active facilities</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ $facilities->where('is_active', true)->count() }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Facility</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Featured</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($facilities as $facility)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="h-16 w-16 rounded-lg overflow-hidden border border-gray-200 bg-gray-100">
                                            @if($facility->image_url)
                                                <img src="{{ $facility->image_url }}" alt="{{ $facility->name }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="h-full w-full flex items-center justify-center text-gray-400 text-xs uppercase tracking-wide">No Image</div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-base font-semibold text-gray-900">{{ $facility->name }}</p>
                                            <p class="text-sm text-gray-500 line-clamp-2">{{ Str::limit($facility->description, 90) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $facility->category ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $facility->location ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $facility->order ?? 0 }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusPill($facility->is_featured) }}">
                                        {{ $facility->is_featured ? 'Featured' : 'Regular' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusPill($facility->is_active) }}">
                                        {{ $facility->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium space-x-3">
                                    <a href="{{ route('admin.facilities.edit', $facility) }}" class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-pen-to-square mr-1"></i>Edit
                                    </a>
                                    <form action="{{ route('admin.facilities.destroy', $facility) }}" method="POST" class="inline" onsubmit="return confirm('Delete this facility?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-image text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-semibold">No facilities yet</p>
                                    <p class="text-sm mb-4">Start by creating your first facility.</p>
                                    <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition">
                                        <i class="fas fa-plus mr-2"></i>Add Facility
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100">
                {{ $facilities->links('pagination::tailwind') }}
            </div>
        </div>
    </main>
</body>
</html>
