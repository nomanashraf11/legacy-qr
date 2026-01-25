<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCorsHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', '*')
                ->header('Access-Control-Allow-Headers', '*')
                ->header('Access-Control-Expose-Headers', '*')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        // Force CORS headers on ALL responses - no exceptions
        $response->headers->set('Access-Control-Allow-Origin', '*', true);
        $response->headers->set('Access-Control-Allow-Methods', '*', true);
        $response->headers->set('Access-Control-Allow-Headers', '*', true);
        $response->headers->set('Access-Control-Expose-Headers', '*', true);
        $response->headers->set('Access-Control-Max-Age', '86400', true);
        $response->headers->set('Access-Control-Allow-Credentials', 'false', true);

        return $response;
    }
}
