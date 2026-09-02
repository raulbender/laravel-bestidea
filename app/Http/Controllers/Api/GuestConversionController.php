<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class GuestConversionController extends Controller
{
    public function convert(Request $request): JsonResponse
    {
        $user = $request->user();

        // Prevent registered users from invoking guest conversion
        if (!$user || !$user->is_anonymous) {
            return response()->json([
                'message' => 'Only guest accounts can be converted.',
            ], 403);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'is_anonymous' => false,
        ]);

        // Clear the guest cookie upon successful conversion
        return response()->json([
            'message' => 'Account converted successfully',
            'data'    => $user,
        ], 200)->withoutCookie('guest_user_id');
    }
}