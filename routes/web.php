<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IdeaController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\RatingController;


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/', function () {
    return view('home');
})->name('home');

// Rota para abrir a tela de criação da sala
Route::get('/rooms/create', function () {
    return view('rooms.create');
})->name('rooms.create');

Route::get('/rooms/{uuid}', function ($uuid) {
    return view('rooms.show', ['uuid' => $uuid]);
})->name('rooms.show');



// Rotas de UI (Retornam HTML/Views)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [RoomController::class, 'index'])->name('dashboard');
});

// Endpoints Internos de UI (Retornam JSON para o Alpine.js)
Route::middleware(['ensure.guest'])->prefix('api')->group(function () {
    Route::post('/rooms/{uuid}/ideas', [IdeaController::class, 'store'])->whereUuid('uuid');
    
    Route::get('/ideas', [IdeaController::class, 'index']);
    Route::post('/ideas/{id}/comments', [CommentController::class, 'store'])->whereNumber('id');
    Route::get('/ideas/{id}/comments', [CommentController::class, 'index'])->whereNumber('id');

    Route::post('/ideas/{id}/ratings', [RatingController::class, 'store'])->whereNumber('id');
    Route::get('/ideas/{id}/myrating', [RatingController::class, 'myRating'])->whereNumber('id');
});

require __DIR__.'/auth.php';
