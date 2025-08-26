<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $adminUser;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->user = User::factory()->create();
        $this->adminUser = User::factory()->create();
        
        // Create roles and permissions
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        
        $viewPermission = Permission::create(['name' => 'products.view']);
        $createPermission = Permission::create(['name' => 'products.create']);
        $editPermission = Permission::create(['name' => 'products.edit']);
        
        // Assign permissions to roles
        $adminRole->permissions()->attach([$viewPermission->id, $createPermission->id, $editPermission->id]);
        $userRole->permissions()->attach([$viewPermission->id]);
        
        // Assign roles to users
        $this->adminUser->roles()->attach($adminRole->id);
        $this->user->roles()->attach($userRole->id);
        
        // Create a test product
        $this->product = Product::factory()->create();
        
        // Set up fake storage
        Storage::fake('public');
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

    /**
     * Test product show method - success case
     */
    public function test_show_product_success()
    {
        $response = $this->authenticatedRequest('GET', "/api/products/{$this->product->id}", $this->adminUser);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock',
                    'sku',
                    'image',
                    'category',
                    'brand',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->price,
                    'stock' => $this->product->stock,
                ]
            ]);
    }

    /**
     * Test product show method - unauthorized access
     */
    public function test_show_product_unauthorized()
    {
        $unauthorizedUser = User::factory()->create();
        
        $response = $this->authenticatedRequest('GET', "/api/products/{$this->product->id}", $unauthorizedUser);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 403,
                'status' => 'error',
                'message' => 'You are not authorized to view this product'
            ]);
    }

    /**
     * Test product show method - product not found
     */
    public function test_show_product_not_found()
    {
        $response = $this->authenticatedRequest('GET', '/api/products/99999', $this->adminUser);

        $response->assertStatus(404);
    }

    /**
     * Test product store method - success case
     */
    public function test_store_product_success()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 100,
            'sku' => 'TEST-SKU-001',
            'category' => 'Electronics',
            'brand' => 'Test Brand',
            'is_active' => true
        ];

        $response = $this->authenticatedRequest('POST', '/api/products', $this->adminUser, $productData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock',
                    'sku',
                    'image',
                    'category',
                    'brand',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'name' => 'Test Product',
                    'description' => 'Test Description',
                    'price' => 99.99,
                    'stock' => 100,
                    'sku' => 'TEST-SKU-001',
                    'category' => 'Electronics',
                    'brand' => 'Test Brand',
                    'is_active' => true
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU-001'
        ]);
    }

    /**
     * Test product store method - with image upload
     */
    public function test_store_product_with_image()
    {
        $image = UploadedFile::fake()->image('product.jpg', 800, 600);
        
        $productData = [
            'name' => 'Test Product with Image',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 100,
            'sku' => 'TEST-SKU-002',
            'image' => $image
        ];

        $response = $this->authenticatedRequest('POST', '/api/products', $this->adminUser, $productData);

        $response->assertStatus(200);
        
        $product = Product::where('sku', 'TEST-SKU-002')->first();
        $this->assertNotNull($product->image);
        $this->assertStringContainsString('products/', $product->image);
        
        // Verify image was stored
        $this->assertTrue(Storage::disk('public')->exists($product->image));
    }

    /**
     * Test product store method - validation errors
     */
    public function test_store_product_validation_errors()
    {
        $invalidData = [
            'name' => '', // Required field empty
            'price' => 'invalid', // Invalid price
            'stock' => -1, // Negative stock
        ];

        $response = $this->authenticatedRequest('POST', '/api/products', $this->adminUser, $invalidData);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['name', 'price', 'stock']);
    }

    /**
     * Test product store method - unauthorized access
     */
    public function test_store_product_unauthorized()
    {
        $productData = [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 100
        ];

        $response = $this->authenticatedRequest('POST', '/api/products', $this->user, $productData);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 403,
                'status' => 'error',
                'message' => 'You are not authorized to create products'
            ]);
    }

    /**
     * Test product update method - success case
     */
    public function test_update_product_success()
    {
        $updateData = [
            'name' => 'Updated Product Name',
            'price' => 149.99,
            'stock' => 50
        ];

        $response = $this->authenticatedRequest('PUT', "/api/products/{$this->product->id}", $this->adminUser, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'id' => $this->product->id,
                    'name' => 'Updated Product Name',
                    'price' => 149.99,
                    'stock' => 50
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'name' => 'Updated Product Name',
            'price' => 149.99,
            'stock' => 50
        ]);
    }

    /**
     * Test product update method - with new image
     */
    public function test_update_product_with_new_image()
    {
        // First, create a product with an image
        $oldImage = UploadedFile::fake()->image('old-product.jpg');
        $this->product->update(['image' => 'products/old-image.jpg']);
        
        $newImage = UploadedFile::fake()->image('new-product.jpg');
        
        $updateData = [
            'name' => 'Updated Product',
            'price' => 99.99,
            'stock' => 50,
            'image' => $newImage
        ];

        $response = $this->authenticatedRequest('PUT', "/api/products/{$this->product->id}", $this->adminUser, $updateData);

        $response->assertStatus(200);
        
        $this->product->refresh();
        $this->assertNotNull($this->product->image);
        $this->assertStringContainsString('products/', $this->product->image);
        $this->assertNotEquals('products/old-image.jpg', $this->product->image);
    }

    /**
     * Test product update method - validation errors
     */
    public function test_update_product_validation_errors()
    {
        $invalidData = [
            'name' => '', // Required field empty
            'price' => 'invalid', // Invalid price
            'stock' => -1, // Negative stock
        ];

        $response = $this->authenticatedRequest('PUT', "/api/products/{$this->product->id}", $this->adminUser, $invalidData);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['name', 'price', 'stock']);
    }

    /**
     * Test product update method - unauthorized access
     */
    public function test_update_product_unauthorized()
    {
        $updateData = [
            'name' => 'Updated Product Name',
            'price' => 99.99,
            'stock' => 50
        ];

        $response = $this->authenticatedRequest('PUT', "/api/products/{$this->product->id}", $this->user, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 403,
                'status' => 'error',
                'message' => 'You are not authorized to update this product'
            ]);
    }

    /**
     * Test product delete method - success case
     */
    public function test_delete_product_success()
    {
        $response = $this->authenticatedRequest('DELETE', "/api/products/{$this->product->id}", $this->adminUser);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'status' => 'success',
                'message' => 'Product deleted successfully'
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $this->product->id
        ]);
    }

    /**
     * Test product delete method - with image cleanup
     */
    public function test_delete_product_with_image_cleanup()
    {
        // Create a product with an image
        $image = UploadedFile::fake()->image('product.jpg');
        $imagePath = $image->store('products', 'public');
        $this->product->update(['image' => $imagePath]);
        
        // Verify image exists
        $this->assertTrue(Storage::disk('public')->exists($imagePath));

        $response = $this->authenticatedRequest('DELETE', "/api/products/{$this->product->id}", $this->adminUser);

        $response->assertStatus(200);
        
        // Verify product is deleted
        $this->assertDatabaseMissing('products', [
            'id' => $this->product->id
        ]);
        
        // Verify image is deleted
        $this->assertFalse(Storage::disk('public')->exists($imagePath));
    }

    /**
     * Test product delete method - unauthorized access
     */
    public function test_delete_product_unauthorized()
    {
        $response = $this->authenticatedRequest('DELETE', "/api/products/{$this->product->id}", $this->user);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 403,
                'status' => 'error',
                'message' => 'You are not authorized to delete products'
            ]);
    }

    /**
     * Test product delete method - product not found
     */
    public function test_delete_product_not_found()
    {
        $response = $this->authenticatedRequest('DELETE', '/api/products/99999', $this->adminUser);

        $response->assertStatus(404);
    }
}
