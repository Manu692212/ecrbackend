<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

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

