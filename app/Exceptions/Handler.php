<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Override the render method to ensure JSON responses for API routes
     */
    public function render($request, Throwable $e)
    {
        // Force JSON responses for all API routes
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return parent::render($request, $e);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Handle 404 Not Found errors for API routes
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Resource not found',
                    'errors' => ['resource' => 'The requested resource could not be found']
                ], 404);
            }
        });

        // Handle Model Not Found errors for API routes (database 404s)
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Resource not found',
                    'errors' => ['resource' => 'The requested model could not be found']
                ], 404);
            }
        });

        // Handle Method Not Allowed errors for API routes
        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 405,
                    'status' => 'error',
                    'message' => 'Method not allowed',
                    'errors' => ['method' => 'The HTTP method is not allowed for this endpoint']
                ], 405);
            }
        });

        // Handle validation exceptions for API routes
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 422,
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
        });
        
        // Handle authentication exceptions
        $this->renderable(function (AuthenticationException $e, Request $request) {
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
        $this->renderable(function (TokenExpiredException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, your session has expired. Please login again to continue.',
                    'errors' => ['token' => 'Token has expired']
                ], 401);
            }
        });

        $this->renderable(function (TokenInvalidException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, you have been logged out. Please login again to continue.',
                    'errors' => ['token' => 'Invalid token provided']
                ], 401);
            }
        });

        $this->renderable(function (JWTException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, you have been logged out. Please login again to continue.',
                    'errors' => ['token' => 'Token could not be parsed']
                ], 401);
            }
        });

        // Fallback handler for any other exceptions in API routes
       
        
        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Forbidden',
                    'errors' => ['authorization' => 'You do not have permission to access this resource']
                ], 403);
            }
        });
        $this->renderable(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Forbidden',
                    'errors' => ['authorization' => 'You do not have permission to access this resource']
                ], 403);
            }
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $statusCode = 500;
                
                // Try to get status code from exception code if it's a valid HTTP status code
                if ($e->getCode() >= 100 && $e->getCode() < 600) {
                    $statusCode = $e->getCode();
                }
                
                return new JsonResponse([
                    'code' => $statusCode,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' =>  ['exception' => $e->getMessage()]
                ], 400);
            }
        });
    }
}