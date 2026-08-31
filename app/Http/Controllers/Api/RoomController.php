<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'is_public'   => 'nullable|boolean',
            'expires_at'  => 'nullable|date|after:now',
        ]);

        $room = Room::create($validated);

        return response()->json([
            'data' => $room,
        ], 201);
    }

    /**
     * Fetch a single room by its UUID.
     */
    public function show(string $uuid): JsonResponse
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'data' => $room,
        ], 200);
    }

    /**
     * Fetch all public rooms.
     */
    public function publicRooms(): JsonResponse
    {
        $publicRooms = Room::where('is_public', true)
        ->latest()
        ->paginate(10);

        return response()->json($publicRooms, 200);
    }

}