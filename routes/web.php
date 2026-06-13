<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AskController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PersonnalisationController;

Route::inertia('/', 'Welcome')->name('home');

// 'auth' -> user must be logged in
// 'verified' -> user must have verified his/her email address
Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('/conversations', [ConversationController::class, 'index'])
    ->name('conversations.index');
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::get('ask/{conversation}', [MessageController::class, 'index'])
        ->name('ask.conversation');
    Route::post('ask/{conversation}/messages', [MessageController::class, 'store']);
    Route::get('/ask', [AskController::class, 'index'])
    ->name('ask.index');
    Route::get('/ask/{conversation}', [AskController::class, 'show'])->name('ask.show');
    Route::post('/ask', [AskController::class, 'ask'])
    ->name('ask.post');
    // Personnalisation
    Route::get('/personnalisation', [PersonnalisationController::class, 'index'])
    ->name('personnalisation.instructions');
    Route::post('/personnalisation/instructions', [PersonnalisationController::class, 'store']);
    // AskStream
    Route::get('/ask-stream', [\App\Http\Controllers\AskStreamController::class, 'index'])
    ->name('stream.index');
    Route::post('/ask-stream', [\App\Http\Controllers\AskStreamController::class, 'stream'])
    ->name('stream.post');
    // Route for a brand new prompt
    Route::post('/ask', [AskController::class, 'ask'])->name('ask.new');
    // Route for adding to an existing chat history
    Route::post('/ask/{conversation}/messages', [AskController::class, 'ask'])->name('ask.append');
});



require __DIR__.'/settings.php';
