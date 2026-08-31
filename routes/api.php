<?php

use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::post('/rooms', [RoomController::class, 'store']);
Route::get('/rooms/{uuid}', [RoomController::class, 'show'])->whereUuid('uuid');
Route::get('/rooms/public', [RoomController::class, 'publicRooms']);