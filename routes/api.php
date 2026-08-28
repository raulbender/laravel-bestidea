<?php

use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::post('/rooms', [RoomController::class, 'store']);