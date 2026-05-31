<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AskController;

Route::inertia('/', 'Welcome')->name('home');

// 'auth' -> user must be logged in
// 'verified' -> user must have verified his/her email address
Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('/ask', [AskController::class, 'index'])
    ->name('ask.index');
    Route::post('/ask', [AskController::class, 'ask'])
    ->name('ask.post');
});

require __DIR__.'/settings.php';
