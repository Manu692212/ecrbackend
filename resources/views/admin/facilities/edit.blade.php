<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Facility - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-slate-900 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <i class="fas fa-pen-to-square text-2xl text-amber-300"></i>
                <div>
                    <p class="text-sm uppercase tracking-widest text-slate-300">Facilities</p>
                    <h1 class="text-2xl font-bold">Edit Facility</h1>
                </div>
            </div>
            <a href="{{ route('admin.facilities.index') }}" class="px-4 py-2 rounded-full bg-slate-800 hover:bg-slate-700 transition text-sm font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Back to list
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <p class="text-red-800 font-semibold mb-2">Please fix the following:</p>
                <ul class="text-red-700 list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.facilities.update', $facility) }}" method="POST" enctype="multipart/form-data"
              class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
            @method('PUT')
            <h2 class="text-2xl font-semibold text-gray-900">Update details</h2>

            @include('admin.facilities.partials.form', ['facility' => $facility])

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.facilities.index') }}" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 rounded-lg bg-amber-500 text-white font-semibold hover:bg-amber-600">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </main>
</body>
</html>
