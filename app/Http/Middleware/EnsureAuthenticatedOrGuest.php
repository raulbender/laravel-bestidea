<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticatedOrGuest
{
    public function handle(Request $request, Closure$next): Response
    {
        // 1. If user is already authenticated (regular or guest), proceed
        if (Auth::check()) {
            return $next($request);
        }

        // 2. Check if a guest UUID exists in cookies
        $guestId = $request->cookie('guest_user_id');$guestUser = $guestId ? User::where('id', $guestId)->where('is_anonymous', true)->first() : null;

        // 3. Create a new guest if no valid guest user was found
        if (!$guestUser) {$guestUser = User::create([
                'name'         => 'Guest #' . Str::random(5),
                'is_anonymous' => true,
            ]);
        }

        // 4. Log the guest in for the current request context
        Auth::login($guestUser);

        $response = $next($request);

        // 5. Attach encrypted cookie (expires in 30 days)
        if ($request->cookie('guest_user_id') !==$guestUser->id) {
            $response->cookie('guest_user_id',$guestUser->id, 43200); // 30 days in minutes
        }

        return $response;
    }
}