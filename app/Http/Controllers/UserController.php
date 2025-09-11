<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="User management operations"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id", "name", "email", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", example=1, description="Unique user identifier"),
 *     @OA\Property(property="name", type="string", maxLength=255, example="John Doe", description="User full name"),
 *     @OA\Property(property="email", type="string", maxLength=255, example="john@example.com", description="User email address"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-15T10:30:00.000000Z", description="Email verification timestamp"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z", description="User creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z", description="User last update timestamp"),
 *     @OA\Property(property="roles", type="array", @OA\Items(ref="#/components/schemas/Role"), description="User roles"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(ref="#/components/schemas/Permission"), description="User permissions")
 * )
 *
 * @OA\Schema(
 *     schema="UserCreate",
 *     type="object",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="John Doe", description="User full name"),
 *     @OA\Property(property="email", type="string", maxLength=255, example="john@example.com", description="User email address"),
 *     @OA\Property(property="password", type="string", minLength=8, example="password123", description="User password"),
 *     @OA\Property(property="password_confirmation", type="string", example="password123", description="Password confirmation"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="integer"), example={1, 2}, description="Role IDs to assign")
 * )
 *
 * @OA\Schema(
 *     schema="UserUpdate",
 *     type="object",
 *     required={"name", "email"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="John Smith", description="User full name"),
 *     @OA\Property(property="email", type="string", maxLength=255, example="johnsmith@example.com", description="User email address"),
 *     @OA\Property(property="password", type="string", minLength=8, example="newpassword123", description="New password (optional)"),
 *     @OA\Property(property="password_confirmation", type="string", example="newpassword123", description="Password confirmation"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="integer"), example={1, 3}, description="Role IDs to assign")
 * )
 */
class UserController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get list of users",
     *     description="Retrieve a paginated list of users with filtering, searching, and sorting options",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in name or email",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by role name",
     *         required=false,
     *         @OA\Schema(type="string", example="admin")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "email", "created_at", "updated_at"}, example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="with_roles",
     *         in="query",
     *         description="Include user roles in response",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Users fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to view users")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->cannot('viewAny', User::class)) {
            return $this->error('You are not authorized to view users', [], 403);
        }

        try {
            $query = User::query();

            // Include roles if requested
            if ($request->boolean('with_roles')) {
                $query->with(['roles.permissions']);
            }

            // Search by name or email
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->has('role')) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            // Sort users
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $users = $query->paginate($request->get('per_page', 10));

            return $this->success('Users fetched successfully', [
                'users' => UserResource::collection($users->items()),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ]);
        } catch (\Exception $e) {
            return $this->error('Error fetching users', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a new user",
     *     description="Create a new user with optional role assignments",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
     *             @OA\Property(property="email", type="string", maxLength=255, example="john@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="integer"), example={1, 2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to create users")
     *         )
     *     )
     * )
     */
    public function store(UserRequest $request): JsonResponse
    {
        if ($request->user()->cannot('create', User::class)) {
            return $this->error('You are not authorized to create users', [], 403);
        }

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Hash password
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Remove roles from user data
            $roles = $validatedData['roles'] ?? [];
            unset($validatedData['roles']);

            // Create user
            $user = User::create($validatedData);

            // Assign roles if provided
            if (!empty($roles)) {
                $user->roles()->sync($roles);
            }

            DB::commit();

            // Load roles for response
            $user->load(['roles.permissions']);

            return $this->success('User created successfully', new UserResource($user));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Error creating user', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get a specific user",
     *     description="Retrieve details of a specific user by ID",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="with_roles",
     *         in="query",
     *         description="Include user roles in response",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User fetched successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to view this user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function show(Request $request, User $user): JsonResponse
    {
        if ($request->user()->cannot('view', $user)) {
            return $this->error('You are not authorized to view this user', [], 403);
        }

        // Load roles if requested
        if ($request->boolean('with_roles')) {
            $user->load(['roles.permissions']);
        }

        return $this->success('User fetched successfully', new UserResource($user));
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update a user",
     *     description="Update an existing user with optional role assignments",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="John Smith"),
     *             @OA\Property(property="email", type="string", maxLength=255, example="johnsmith@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="integer"), example={1, 3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update this user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        if ($request->user()->cannot('update', $user)) {
            return $this->error('You are not authorized to update this user', [], 403);
        }
        logger($request->all());
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Hash password if provided
            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }

            // Handle roles
            $roles = $validatedData['roles'] ?? null;
            unset($validatedData['roles']);

            // Update user
            $user->update($validatedData);

            // Update roles if provided
            if ($roles !== null) {
                $user->roles()->sync($roles);
            }

            DB::commit();

            // Load roles for response
            $user->load(['roles.permissions']);

            return $this->success('User updated successfully', new UserResource($user));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Error updating user', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     description="Delete a user (users cannot delete themselves)",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete users")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->cannot('delete', $user)) {
            return $this->error('You are not authorized to delete users', [], 403);
        }

        try {
            $user->delete();
            return $this->success('User deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Error deleting user', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/assign-roles",
     *     summary="Assign roles to a user",
     *     description="Assign additional roles to a user",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"roles"},
     *             @OA\Property(property="roles", type="array", @OA\Items(type="integer"), example={1, 2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Roles assigned successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function assignRoles(Request $request, User $user): JsonResponse
    {
        if ($request->user()->cannot('edit', $user)) {
            return $this->error('You are not authorized to assign roles', [], 403);
        }

        try {
            $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id'
            ]);

            $user->roles()->syncWithoutDetaching($request->roles);
            $user->load(['roles.permissions']);

            return $this->success('Roles assigned successfully', new UserResource($user));
        } catch (\Exception $e) {
            return $this->error('Error assigning roles', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}/remove-roles",
     *     summary="Remove roles from a user",
     *     description="Remove specific roles from a user",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"roles"},
     *             @OA\Property(property="roles", type="array", @OA\Items(type="integer"), example={1, 2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Roles removed successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function removeRoles(Request $request, User $user): JsonResponse
    {
        if ($request->user()->cannot('removeRoles', $user)) {
            return $this->error('You are not authorized to remove roles', [], 403);
        }

        try {
            $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id'
            ]);

            $user->roles()->detach($request->roles);
            $user->load(['roles.permissions']);

            return $this->success('Roles removed successfully', new UserResource($user));
        } catch (\Exception $e) {
            return $this->error('Error removing roles', [$e->getMessage()], 500);
        }
    }
}
