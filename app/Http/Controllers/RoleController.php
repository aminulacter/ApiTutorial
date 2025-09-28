<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="Role management operations"
 * )
 *
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     required={"id", "name", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", example=1, description="Unique role identifier"),
 *     @OA\Property(property="name", type="string", maxLength=255, example="admin", description="Role name"),
 *     @OA\Property(property="display_name", type="string", maxLength=255, nullable=true, example="Administrator", description="Role display name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, example="Administrator role with full access", description="Role description"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z", description="Role creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z", description="Role last update timestamp"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(ref="#/components/schemas/Permission"), description="Role permissions"),
 *     @OA\Property(property="users_count", type="integer", example=5, description="Number of users with this role"),
 *     @OA\Property(property="permissions_count", type="integer", example=10, description="Number of permissions assigned to this role")
 * )
 *
 * @OA\Schema(
 *     schema="RoleCreate",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="manager", description="Role name"),
 *     @OA\Property(property="display_name", type="string", maxLength=255, nullable=true, example="Manager", description="Role display name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, example="Manager role with limited access", description="Role description"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3}, description="Permission IDs to assign")
 * )
 *
 * @OA\Schema(
 *     schema="RoleUpdate",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="senior_manager", description="Role name"),
 *     @OA\Property(property="display_name", type="string", maxLength=255, nullable=true, example="Senior Manager", description="Role display name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, example="Senior manager role with extended access", description="Role description"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 4, 5}, description="Permission IDs to assign")
 * )
 */
class RoleController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get list of roles",
     *     description="Retrieve a paginated list of roles with filtering, searching, and sorting options",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in name or display_name",
     *         required=false,
     *         @OA\Schema(type="string", example="admin")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "display_name", "created_at", "updated_at"}, example="created_at")
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
     *         name="with_permissions",
     *         in="query",
     *         description="Include role permissions in response",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Roles fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Role")
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
     *             @OA\Property(property="message", type="string", example="You are not authorized to view roles")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->cannot('viewAny', Role::class)) {
            return $this->error('You are not authorized to view roles', [], 403);
        }

        try {
            $query = Role::query();

            // Include permissions if requested
            if ($request->boolean('with_permissions')) {
                $query->with(['permissions']);
            }

            // Search by name or display_name
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%");
                });
            }

            // Sort roles
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $roles = $query->paginate($request->get('per_page', 10));

            return $this->success('Roles fetched successfully', [
                'roles' => RoleResource::collection($roles->items()),
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'next_page_url' => $roles->nextPageUrl(),
                'prev_page_url' => $roles->previousPageUrl(),
            ]);
        } catch (\Exception $e) {
            return $this->error('Error fetching roles', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create a new role",
     *     description="Create a new role with optional permission assignments",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="manager"),
     *             @OA\Property(property="display_name", type="string", maxLength=255, example="Manager"),
     *             @OA\Property(property="description", type="string", maxLength=1000, example="Manager role with limited access"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
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
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to create roles")
     *         )
     *     )
     * )
     */
    public function store(RoleRequest $request): JsonResponse
    {
        if ($request->user()->cannot('create', Role::class)) {
            return $this->error('You are not authorized to create roles', [], 403);
        }

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Remove permissions from role data
            $permissions = $validatedData['permissions'] ?? [];
            unset($validatedData['permissions']);

            // Create role
            $role = Role::create($validatedData);

            // Assign permissions if provided
            if (!empty($permissions)) {
                $role->permissions()->sync($permissions);
            }

            DB::commit();

            // Load permissions for response
            $role->load(['permissions']);

            return $this->success('Role created successfully', new RoleResource($role));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Error creating role', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     summary="Get a specific role",
     *     description="Retrieve details of a specific role by ID",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="with_permissions",
     *         in="query",
     *         description="Include role permissions in response",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role fetched successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to view this role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function show(Request $request, Role $role): JsonResponse
    {
        if ($request->user()->cannot('view', $role)) {
            return $this->error('You are not authorized to view this role', [], 403);
        }

        // Load permissions if requested
        if ($request->boolean('with_permissions')) {
            $role->load(['permissions']);
        }

        return $this->success('Role fetched successfully', new RoleResource($role));
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="Update a role",
     *     description="Update an existing role with optional permission assignments",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="senior_manager"),
     *             @OA\Property(property="display_name", type="string", maxLength=255, example="Senior Manager"),
     *             @OA\Property(property="description", type="string", maxLength=1000, example="Senior manager role with extended access"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 4, 5})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
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
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update this role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        if ($request->user()->cannot('update', $role)) {
            return $this->error('You are not authorized to update this role', [], 403);
        }

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Handle permissions
            $permissions = $validatedData['permissions'] ?? null;
            unset($validatedData['permissions']);

            // Update role
            $role->update($validatedData);

            // Update permissions if provided
            if ($permissions !== null) {
                $role->permissions()->sync($permissions);
            }

            DB::commit();

            // Load permissions for response
            $role->load(['permissions']);

            return $this->success('Role updated successfully', new RoleResource($role));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Error updating role', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Delete a role",
     *     description="Delete a role and remove all permission assignments",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete roles")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        if ($request->user()->cannot('delete', $role)) {
            return $this->error('You are not authorized to delete roles', [], 403);
        }

        try {
            $role->delete();
            return $this->success('Role deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Error deleting role', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/roles/{id}/attach-permissions",
     *     summary="Attach permissions to a role",
     *     description="Attach additional permissions to a role without removing existing ones",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions attached successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Permissions attached successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to assign permissions")
     *         )
     *     )
     * )
     */
    public function attachPermissions(Request $request, Role $role): JsonResponse
    {
        if ($request->user()->cannot('edit', $role)) {
            return $this->error('You are not authorized to assign permissions', [], 403);
        }

        try {
            $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            $role->permissions()->syncWithoutDetaching($request->permissions);
            $role->load(['permissions']);

            return $this->success('Permissions attached successfully', new RoleResource($role));
        } catch (\Exception $e) {
            return $this->error('Error attaching permissions', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}/detach-permissions",
     *     summary="Detach permissions from a role",
     *     description="Remove specific permissions from a role",
     *     tags={"Roles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions detached successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Permissions detached successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Role")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to remove permissions")
     *         )
     *     )
     * )
     */
    public function detachPermissions(Request $request, Role $role): JsonResponse
    {
        if ($request->user()->cannot('edit', $role)) {
            return $this->error('You are not authorized to remove permissions', [], 403);
        }

        try {
            $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            $role->permissions()->detach($request->permissions);
            $role->load(['permissions']);

            return $this->success('Permissions detached successfully', new RoleResource($role));
        } catch (\Exception $e) {
            return $this->error('Error detaching permissions', [$e->getMessage()], 500);
        }
    }
    public function allPermissions() {
        $permissions = Permission::all();
        return $this->success('All permissions fetched successfully', $permissions);
    }
}
