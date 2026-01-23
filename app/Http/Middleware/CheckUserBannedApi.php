<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserBannedApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->isBanned == 1) {
            $request->user()->tokens()->delete();
            return response()->json([
                'status' => 403,
                'message' => 'Your account has been banned. Please contact support for assistance.'
            ]);
        }
        return $next($request);
    }
}
