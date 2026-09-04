<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;


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


require __DIR__.'/auth.php';
