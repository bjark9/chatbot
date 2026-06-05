<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AskController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;

Route::inertia('/', 'Welcome')->name('home');

// 'auth' -> user must be logged in
// 'verified' -> user must have verified his/her email address
Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('/conversations', [ConversationController::class, 'index'])
    ->name('conversations.index');
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::get('ask/{conversation}', [MessageController::class, 'index']);
    Route::post('ask/{conversation}/messages', [MessageController::class, 'store']);
    Route::get('/ask', [AskController::class, 'index'])
    ->name('ask.index');
    Route::post('/ask', [AskController::class, 'ask'])
    ->name('ask.post');
});



require __DIR__.'/settings.php';
