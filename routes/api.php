<?php

use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\GuestConversionController;
use App\Http\Controllers\Api\IdeaController;
use App\Http\Controllers\Api\RatingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['ensure.guest'])->group(function () {
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{uuid}', [RoomController::class, 'show'])->whereUuid('uuid');
    Route::get('/rooms/public', [RoomController::class, 'publicRooms']);
    Route::post('/guest/register', [GuestConversionController::class, 'convert']);
    Route::post('/rooms/{uuid}/ideas', [IdeaController::class, 'store'])->whereUuid('uuid');
    Route::post('/ideas/{id}/ratings', [RatingController::class, 'store'])->whereNumber('id');
    Route::get('/ideas', [IdeaController::class, 'index']);

});