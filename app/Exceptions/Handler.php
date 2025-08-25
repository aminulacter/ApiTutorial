<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        
        // Handle authentication exceptions
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, you have been logged out. Please login again to continue.',
                    'errors' => ['token' => 'Authentication required']
                ], 401);
            }
        });

        // Handle JWT specific exceptions
        $this->renderable(function (TokenExpiredException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, your session has expired. Please login again to continue.',
                    'errors' => ['token' => 'Token has expired']
                ], 401);
            }
        });

        $this->renderable(function (TokenInvalidException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, you have been logged out. Please login again to continue.',
                    'errors' => ['token' => 'Invalid token provided']
                ], 401);
            }
        });

        $this->renderable(function (JWTException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, you have been logged out. Please login again to continue.',
                    'errors' => ['token' => 'Token could not be parsed']
                ], 401);
            }
        });
    }
}