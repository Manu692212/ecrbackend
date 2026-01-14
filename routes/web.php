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

    $headers = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
        'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept',
    ];

    if (!Storage::disk('public')->exists($normalized)) {
        return response('File not found', 404)->withHeaders($headers);
    }

    $response = Storage::disk('public')->response($normalized);

    foreach ($headers as $key => $value) {
        $response->headers->set($key, $value);
    }

    return $response;
})->where('path', '.*');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.facilities.index');
    })->name('dashboard');
    Route::resource('facilities', AdminFacilityController::class)->except(['show']);
});

Route::get('/login', function () {
    return response()->json([
        'message' => 'Login route placeholder',
        'instructions' => 'Call POST /api/admins/login with credentials to authenticate.'
    ], 200);
})->name('login');
