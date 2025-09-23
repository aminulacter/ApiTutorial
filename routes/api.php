<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Use default auth:api middleware - the exception handler will catch failures
Route::middleware('jwt.auth')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::apiResource('products', ProductController::class);

    // User management routes
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles']);
    Route::delete('users/{user}/remove-roles', [UserController::class, 'removeRoles']);

    // Role management routes
    Route::apiResource('roles', RoleController::class);
    Route::get('allPermissions', [RoleController::class, 'allPermissions']);
    Route::post('roles/{role}/attach-permissions', [RoleController::class, 'attachPermissions']);
    Route::delete('roles/{role}/detach-permissions', [RoleController::class, 'detachPermissions']);
});

// Catch-all route for unmatched API endpoints - this will handle 404 errors
