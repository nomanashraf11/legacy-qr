<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // For API routes, always return JSON with CORS headers
        if ($request->is('api/*') || $request->expectsJson()) {
            $corsHeaders = [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => '*',
                'Access-Control-Allow-Headers' => '*',
                'Access-Control-Expose-Headers' => '*',
                'Access-Control-Max-Age' => '86400',
            ];
            
            // Handle ModelNotFoundException
            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Resource not found'
                ], 404)->withHeaders($corsHeaders);
            }
            
            // Handle NotFoundHttpException
            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Route not found'
                ], 404)->withHeaders($corsHeaders);
            }
            
            // Handle ValidationException
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Validation failed',
                    'errors' => $exception->errors()
                ], 422)->withHeaders($corsHeaders);
            }
            
            // Handle all other exceptions for API routes
            $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            return response()->json([
                'status' => $statusCode,
                'message' => config('app.debug') ? $exception->getMessage() : 'Internal server error'
            ], $statusCode)->withHeaders($corsHeaders);
        }
        
        return parent::render($request, $exception);
    }
    
    /**
     * Handle unauthenticated requests.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Always return JSON for API routes
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized. Please provide valid credentials.'
            ], 401)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => '*',
                'Access-Control-Allow-Headers' => '*',
                'Access-Control-Expose-Headers' => '*',
            ]);
        }

        return redirect()->guest(route('login'));
    }
}
