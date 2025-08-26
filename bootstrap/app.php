<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your custom JWT middleware

        $middleware->alias([
            'jwt.aminul' => \App\Http\Middleware\JWTAuthentication::class,
        ]);
       
        // $middleware->api(append: [
        //     'jwt.auth' => \App\Http\Middleware\JWTAuthentication::class,
        // ]);

        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            // Always return JSON
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error'   => $e->getMessage(), // optional
                'code' => 404,
                'status' => 'error',
            ]);
        });
        $exceptions->renderable(function (TokenInvalidException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, you have been logged out. Please login again to continue.',
                    'errors' => ['token' => 'Invalid token provided']
                ]);
            }
        });
        $exceptions->renderable(function (TokenExpiredException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, your session has expired. Please login again to continue.',
                    'errors' => ['token' => 'Token has expired']
                ]);
            }
        });


        $exceptions->renderable(function (JWTException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, you have been logged out. Please login again to continue.',
                    'errors' => ['token' => 'Token could not be parsed']
                ]);
            }
        });
         // Handle Model Not Found errors for API routes (database 404s)
         $exceptions->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Resource not found',
                    'errors' => ['resource' => 'The requested model could not be found']
                ]);
            }
        });
        $exceptions->renderable(function (UnauthorizedException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Sorry, you are not authorized to access this resource',
                    'errors' => ['authorization' => $e->getMessage()]
                ]);
            }
        });
        $exceptions->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 405,
                    'status' => 'error',
                    'message' => 'Method not allowed',
                    'errors' => ['method' => 'The HTTP method is not allowed for this endpoint']
                ]);
            }
        });
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 422,
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ]);
            }
        });
        $exceptions->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'Forbidden',
                    'errors' => ['authorization' => 'You do not have permission to access this resource']
                ]);
            }
        });
        $exceptions->renderable(function (Throwable $e, Request $request) {
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
                ]);
            }
        });
    })->create();
