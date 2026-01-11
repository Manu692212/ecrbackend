<?php

use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Models\Facility;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $facilities = Facility::query()
        ->where('is_active', true)
        ->orderBy('order')
        ->orderBy('name')
        ->take(8)
        ->get();

    return view('welcome', compact('facilities'));
})->name('home');

Route::get('/test', function () {
    return 'Test route is working!';
});

Route::get('/media/{path}', function (string $path) {
    $normalized = ltrim($path, '/');

    if (!Storage::disk('public')->exists($normalized)) {
        abort(404);
    }

    $response = Storage::disk('public')->response($normalized);

    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, HEAD, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept');

    return $response;
})->where('path', '.*');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin')->name('dashboard');
    Route::resource('facilities', AdminFacilityController::class)->except(['show']);
});

Route::get('/login', function () {
    return response()->json([
        'message' => 'Login route placeholder',
        'instructions' => 'Call POST /api/admins/login with credentials to authenticate.'
    ], 200);
})->name('login');
