<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminRole;
    protected $userRole;
    protected $permissions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        $this->permissions = [
            Permission::create(['name' => 'roles.view', 'display_name' => 'View Roles']),
            Permission::create(['name' => 'roles.create', 'display_name' => 'Create Roles']),
            Permission::create(['name' => 'roles.update', 'display_name' => 'Update Roles']),
            Permission::create(['name' => 'roles.delete', 'display_name' => 'Delete Roles']),
            Permission::create(['name' => 'roles.assign_permissions', 'display_name' => 'Assign Permissions']),
            Permission::create(['name' => 'roles.remove_permissions', 'display_name' => 'Remove Permissions']),
            Permission::create(['name' => 'users.view', 'display_name' => 'View Users']),
            Permission::create(['name' => 'products.view', 'display_name' => 'View Products']),
        ];

        // Create roles
        $this->adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Administrator role with full access'
        ]);

        $this->userRole = Role::create([
            'name' => 'user',
            'display_name' => 'Regular User',
            'description' => 'Regular user role with limited access'
        ]);

        // Assign all permissions to admin role
        $this->adminRole->permissions()->attach($this->permissions);

        // Assign limited permissions to user role
        $this->userRole->permissions()->attach([
            $this->permissions[0]->id, // roles.view
            $this->permissions[6]->id, // users.view
        ]);

        // Create admin user
        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123')
        ]);

        $this->user->roles()->attach($this->adminRole);
    }

    /**
     * Generate JWT token for a user
     */
    protected function generateToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * Make authenticated request with JWT token
     */
    protected function authenticatedRequest(string $method, string $uri, User $user, array $data = []): \Illuminate\Testing\TestResponse
    {
        $token = $this->generateToken($user);
        
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->json($method, $uri, $data);
    }

    public function test_can_get_roles_list()
    {
        $response = $this->authenticatedRequest('GET', '/api/roles', $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'roles',
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_can_create_role()
    {
        $roleData = [
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Manager role with moderate access',
            'permissions' => [$this->permissions[0]->id, $this->permissions[1]->id]
        ];

        $response = $this->authenticatedRequest('POST', '/api/roles', $this->user, $roleData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'display_name',
                    'description',
                    'permissions'
                ]
            ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Manager role with moderate access'
        ]);
    }

    public function test_can_get_specific_role()
    {
        $response = $this->authenticatedRequest('GET', "/api/roles/{$this->adminRole->id}", $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'display_name',
                    'description'
                ]
            ]);
    }

    public function test_can_update_role()
    {
        $updateData = [
            'name' => 'senior_admin',
            'display_name' => 'Senior Administrator',
            'description' => 'Senior administrator role with extended access'
        ];

        $response = $this->authenticatedRequest('PUT', "/api/roles/{$this->adminRole->id}", $this->user, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('roles', [
            'id' => $this->adminRole->id,
            'name' => 'senior_admin',
            'display_name' => 'Senior Administrator',
            'description' => 'Senior administrator role with extended access'
        ]);
    }

    public function test_can_update_role_with_permissions()
    {
        $updateData = [
            'name' => 'updated_admin',
            'display_name' => 'Updated Administrator',
            'permissions' => [$this->permissions[0]->id, $this->permissions[1]->id, $this->permissions[2]->id]
        ];

        $response = $this->authenticatedRequest('PUT', "/api/roles/{$this->adminRole->id}", $this->user, $updateData);

        $response->assertStatus(200);

        // Verify role data was updated
        $this->assertDatabaseHas('roles', [
            'id' => $this->adminRole->id,
            'name' => 'updated_admin',
            'display_name' => 'Updated Administrator'
        ]);

        // Verify permissions were updated
        $updatedRole = $this->adminRole->fresh();
        $this->assertEquals(3, $updatedRole->permissions->count());
        $this->assertTrue($updatedRole->hasPermission('roles.view'));
        $this->assertTrue($updatedRole->hasPermission('roles.create'));
        $this->assertTrue($updatedRole->hasPermission('roles.update'));
    }

    public function test_can_delete_role()
    {
        $newRole = Role::create([
            'name' => 'temp_role',
            'display_name' => 'Temporary Role',
            'description' => 'A temporary role for testing'
        ]);

        $response = $this->authenticatedRequest('DELETE', "/api/roles/{$newRole->id}", $this->user);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('roles', [
            'id' => $newRole->id
        ]);
    }

    public function test_can_attach_permissions_to_role()
    {
        $newRole = Role::create([
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => 'A test role'
        ]);

        // Initially assign one permission
        $newRole->permissions()->attach($this->permissions[0]->id);

        $response = $this->authenticatedRequest('POST', "/api/roles/{$newRole->id}/attach-permissions", $this->user, [
            'permissions' => [$this->permissions[1]->id, $this->permissions[2]->id]
        ]);

        $response->assertStatus(200);

        // Verify permissions were attached (should have 3 total now)
        $updatedRole = $newRole->fresh();
        $this->assertEquals(3, $updatedRole->permissions->count());
        $this->assertTrue($updatedRole->hasPermission('roles.view'));
        $this->assertTrue($updatedRole->hasPermission('roles.create'));
        $this->assertTrue($updatedRole->hasPermission('roles.update'));
    }

    public function test_can_detach_permissions_from_role()
    {
        $newRole = Role::create([
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => 'A test role'
        ]);

        // Initially assign multiple permissions
        $newRole->permissions()->attach([
            $this->permissions[0]->id,
            $this->permissions[1]->id,
            $this->permissions[2]->id
        ]);

        $response = $this->authenticatedRequest('DELETE', "/api/roles/{$newRole->id}/detach-permissions", $this->user, [
            'permissions' => [$this->permissions[1]->id, $this->permissions[2]->id]
        ]);

        $response->assertStatus(200);

        // Verify permissions were detached (should have 1 remaining)
        $updatedRole = $newRole->fresh();
        $this->assertEquals(1, $updatedRole->permissions->count());
        $this->assertTrue($updatedRole->hasPermission('roles.view'));
        $this->assertFalse($updatedRole->hasPermission('roles.create'));
        $this->assertFalse($updatedRole->hasPermission('roles.update'));
    }

    public function test_can_remove_all_permissions_from_role()
    {
        $newRole = Role::create([
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => 'A test role'
        ]);

        // Initially assign multiple permissions
        $newRole->permissions()->attach([
            $this->permissions[0]->id,
            $this->permissions[1]->id,
            $this->permissions[2]->id
        ]);

        $response = $this->authenticatedRequest('DELETE', "/api/roles/{$newRole->id}/detach-permissions", $this->user, [
            'permissions' => [$this->permissions[0]->id, $this->permissions[1]->id, $this->permissions[2]->id]
        ]);

        $response->assertStatus(200);

        // Verify all permissions were removed
        $updatedRole = $newRole->fresh();
        $this->assertEquals(0, $updatedRole->permissions->count());
    }

    public function test_unauthorized_user_cannot_access_roles()
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->authenticatedRequest('GET', '/api/roles', $regularUser);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 403,
                'status' => 'error',
                'message' => 'You are not authorized to view roles'
            ]);
    }

    public function test_user_with_limited_permissions_can_view_but_not_create_roles()
    {
        $limitedUser = User::create([
            'name' => 'Limited User',
            'email' => 'limited@example.com',
            'password' => bcrypt('password123')
        ]);

        $limitedUser->roles()->attach($this->userRole);

        // Should be able to view roles
        $response = $this->authenticatedRequest('GET', '/api/roles', $limitedUser);
        $response->assertStatus(200);

        // Should not be able to create roles
        $roleData = [
            'name' => 'test_role',
            'display_name' => 'Test Role'
        ];

        $response = $this->authenticatedRequest('POST', '/api/roles', $limitedUser, $roleData);
        $response->assertStatus(200)
            ->assertJson([
                'code' => 403,
                'status' => 'error',
                'message' => 'You are not authorized to create roles'
            ]);
    }
}
