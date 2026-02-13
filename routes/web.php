<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Tenanta SPA - All routes handled by Vue Router
|
*/

// SPA catch-all route
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
