<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adminRole;
    protected $userRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        $permissions = [
            ['name' => 'users.view', 'display_name' => 'View Users'],
            ['name' => 'users.create', 'display_name' => 'Create Users'],
            ['name' => 'users.update', 'display_name' => 'Update Users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users'],
            ['name' => 'users.assign_roles', 'display_name' => 'Assign Roles'],
            ['name' => 'users.remove_roles', 'display_name' => 'Remove Roles'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles
        $this->adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator'
        ]);

        $this->userRole = Role::create([
            'name' => 'user',
            'display_name' => 'Regular User'
        ]);

        // Assign permissions to admin role
        $this->adminRole->permissions()->attach(Permission::all());

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

    public function test_can_get_users_list()
    {
        $response = $this->authenticatedRequest('GET', '/api/users', $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'users',
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->userRole->id]
        ];

        $response = $this->authenticatedRequest('POST', '/api/users', $this->user, $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'roles'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    public function test_can_get_specific_user()
    {
        $newUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->authenticatedRequest('GET', "/api/users/{$newUser->id}", $this->user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email'
                ]
            ]);
    }

    public function test_can_update_user()
    {
        $newUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $updateData = [
            'name' => 'Updated User',
            'email' => 'updated@example.com'
        ];

        $response = $this->authenticatedRequest('PUT', "/api/users/{$newUser->id}", $this->user, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Updated User',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_can_update_user_with_roles()
    {
        $newUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Initially assign user role
        $newUser->roles()->attach($this->userRole);

        $updateData = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'roles' => [$this->adminRole->id, $this->userRole->id] // Update with multiple roles
        ];

        $response = $this->authenticatedRequest('PUT', "/api/users/{$newUser->id}", $this->user, $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'roles'
                ]
            ]);

        // Verify user data was updated
        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Updated User',
            'email' => 'updated@example.com'
        ]);

        // Verify roles were updated
        $updatedUser = $newUser->fresh();
        $this->assertTrue($updatedUser->hasRole('admin'));
        $this->assertTrue($updatedUser->hasRole('user'));
        $this->assertEquals(2, $updatedUser->roles->count());
    }

    public function test_can_update_user_roles_only()
    {
        $newUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $updateData = [
            'name' => 'Test User', // Keep same name
            'email' => 'test@example.com', // Keep same email
            'roles' => [$this->adminRole->id] // Only update roles
        ];

        $response = $this->authenticatedRequest('PUT', "/api/users/{$newUser->id}", $this->user, $updateData);

        $response->assertStatus(200);

        // Verify user data remained unchanged
        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Verify only admin role is assigned
        $updatedUser = $newUser->fresh();
        $this->assertTrue($updatedUser->hasRole('admin'));
        $this->assertFalse($updatedUser->hasRole('user'));
        $this->assertEquals(1, $updatedUser->roles->count());
    }

    public function test_can_remove_all_roles_from_user()
    {
        $newUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // Initially assign multiple roles
        $newUser->roles()->attach([$this->adminRole->id, $this->userRole->id]);

        $updateData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'roles' => [] // Empty roles array to remove all roles
        ];

        $response = $this->authenticatedRequest('PUT', "/api/users/{$newUser->id}", $this->user, $updateData);

        $response->assertStatus(200);

        // Verify all roles were removed
        $updatedUser = $newUser->fresh();
        $this->assertFalse($updatedUser->hasRole('admin'));
        $this->assertFalse($updatedUser->hasRole('user'));
        $this->assertEquals(0, $updatedUser->roles->count());
    }

    public function test_can_delete_user()
    {
        $newUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->authenticatedRequest('DELETE', "/api/users/{$newUser->id}", $this->user);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $newUser->id
        ]);
    }

    public function test_can_assign_roles_to_user()
    {
        $newUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->authenticatedRequest('POST', "/api/users/{$newUser->id}/assign-roles", $this->user, [
            'roles' => [$this->userRole->id]
        ]);

        $response->assertStatus(200);

        $this->assertTrue($newUser->fresh()->hasRole('user'));
    }

    public function test_can_remove_roles_from_user()
    {
        $newUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $newUser->roles()->attach($this->userRole);

        $response = $this->authenticatedRequest('DELETE', "/api/users/{$newUser->id}/remove-roles", $this->user, [
            'roles' => [$this->userRole->id]
        ]);

        $response->assertStatus(200);

        $this->assertFalse($newUser->fresh()->hasRole('user'));
    }

    public function test_unauthorized_user_cannot_access_users()
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->authenticatedRequest('GET', '/api/users', $regularUser);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 403,
                'status' => 'error',
                'message' => 'You are not authorized to view users'
            ]);
    }
}
