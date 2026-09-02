<?php

use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\GuestConversionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['ensure.guest'])->group(function () {
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{uuid}', [RoomController::class, 'show'])->whereUuid('uuid');
    Route::get('/rooms/public', [RoomController::class, 'publicRooms']);
    Route::post('/guest/register', [GuestConversionController::class, 'convert']);

});