<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Info(
 *     title="Product API",
 *     version="1.0.0",
 *     description="A comprehensive API for managing products with authentication, image uploads, and advanced filtering capabilities",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Development server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT token in the format: Bearer {token}"
 * )
 * 
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     required={"id", "name", "price", "stock", "is_active", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", example=1, description="Unique product identifier"),
 *     @OA\Property(property="name", type="string", maxLength=255, example="iPhone 15 Pro", description="Product name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, example="Latest iPhone with advanced features", description="Product description"),
 *     @OA\Property(property="price", type="number", format="float", minimum=0, maximum=999999.99, example=999.99, description="Product price"),
 *     @OA\Property(property="stock", type="integer", minimum=0, maximum=999999, example=100, description="Available stock quantity"),
 *     @OA\Property(property="sku", type="string", maxLength=50, nullable=true, example="IPH15PRO-001", description="Stock Keeping Unit"),
 *     @OA\Property(property="image", type="string", nullable=true, example="products/1234567890_abc123.jpg", description="Product image file path"),
 *     @OA\Property(property="category", type="string", maxLength=100, nullable=true, example="Electronics", description="Product category"),
 *     @OA\Property(property="brand", type="string", maxLength=100, nullable=true, example="Apple", description="Product brand"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Product active status"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z", description="Product creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z", description="Product last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="ProductCreate",
 *     type="object",
 *     required={"name", "price", "stock"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="iPhone 15 Pro", description="Product name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, example="Latest iPhone with advanced features", description="Product description"),
 *     @OA\Property(property="price", type="number", format="float", minimum=0, maximum=999999.99, example=999.99, description="Product price"),
 *     @OA\Property(property="stock", type="integer", minimum=0, maximum=999999, example=100, description="Available stock quantity"),
 *     @OA\Property(property="sku", type="string", maxLength=50, nullable=true, example="IPH15PRO-001", description="Stock Keeping Unit (must be unique)"),
 *     @OA\Property(property="image", type="string", format="binary", description="Product image file (jpeg, png, jpg, gif, svg, max 2MB)"),
 *     @OA\Property(property="category", type="string", maxLength=100, nullable=true, example="Electronics", description="Product category"),
 *     @OA\Property(property="brand", type="string", maxLength=100, nullable=true, example="Apple", description="Product brand"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Product active status")
 * )
 * 
 * @OA\Schema(
 *     schema="ProductUpdate",
 *     type="object",
 *     required={"name", "price", "stock"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="iPhone 15 Pro Max", description="Product name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, example="Updated description", description="Product description"),
 *     @OA\Property(property="price", type="number", format="float", minimum=0, maximum=999999.99, example=1099.99, description="Product price"),
 *     @OA\Property(property="stock", type="integer", minimum=0, maximum=999999, example=50, description="Available stock quantity"),
 *     @OA\Property(property="sku", type="string", maxLength=50, nullable=true, example="IPH15PROMAX-001", description="Stock Keeping Unit (must be unique)"),
 *     @OA\Property(property="image", type="string", format="binary", description="New product image file (jpeg, png, jpg, gif, svg, max 2MB)"),
 *     @OA\Property(property="category", type="string", maxLength=100, nullable=true, example="Electronics", description="Product category"),
 *     @OA\Property(property="brand", type="string", maxLength=100, nullable=true, example="Apple", description="Product brand"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Product active status")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="code", type="integer", example=422, description="Validation error code"),
 *     @OA\Property(property="status", type="string", example="error", description="Error status"),
 *     @OA\Property(property="message", type="string", example="Validation failed", description="Error message"),
 *     @OA\Property(property="errors", type="object", description="Field-specific validation errors")
 * )
 * 
 * @OA\Schema(
 *     schema="UnauthorizedError",
 *     type="object",
 *     @OA\Property(property="code", type="integer", example=403, description="Unauthorized error code"),
 *     @OA\Property(property="status", type="string", example="error", description="Error status"),
 *     @OA\Property(property="message", type="string", example="You are not authorized to perform this action", description="Error message")
 * )
 * 
 * @OA\Schema(
 *     schema="NotFoundError",
 *     type="object",
 *     @OA\Property(property="code", type="integer", example=404, description="Not found error code"),
 *     @OA\Property(property="status", type="string", example="error", description="Error status"),
 *     @OA\Property(property="message", type="string", example="Resource not found", description="Error message")
 * )
 * 
 * @OA\Schema(
 *     schema="ServerError",
 *     type="object",
 *     @OA\Property(property="code", type="integer", example=500, description="Server error code"),
 *     @OA\Property(property="status", type="string", example="error", description="Error status"),
 *     @OA\Property(property="message", type="string", example="Internal server error", description="Error message"),
 *     @OA\Property(property="errors", type="array", @OA\Items(type="string"), description="Error details")
 * )
 */
class ProductController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get list of products",
     *     description="Retrieve a paginated list of products with filtering, searching, and sorting options",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by product category",
     *         required=false,
     *         @OA\Schema(type="string", example="Electronics")
     *     ),
     *     @OA\Parameter(
     *         name="brand",
     *         in="query",
     *         description="Filter by product brand",
     *         required=false,
     *         @OA\Schema(type="string", example="Apple")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price filter",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=10.00)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price filter",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=1000.00)
     *     ),
     *     @OA\Parameter(
     *         name="in_stock",
     *         in="query",
     *         description="Filter by stock availability",
     *         required=false,
     *         @OA\Schema(type="string", enum={"true", "false"}, example="true")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in name, description, or SKU",
     *         required=false,
     *         @OA\Schema(type="string", example="iPhone")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "price", "created_at", "updated_at"}, example="created_at")
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
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Products fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Product")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="filters_applied", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to view products")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error fetching products"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->cannot('viewAny', Product::class)) {
            return $this->error('You are not authorized to view products',[],403);
        }
        $query = Product::query();
       
       try{
         // Filter by category
         if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by brand
        if ($request->has('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock availability
        if ($request->has('in_stock')) {
            if ($request->in_stock === 'true') {
                $query->where('stock', '>', 0);
            } else {
                $query->where('stock', '=', 0);
            }
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            $query->where('is_active', true);
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Sort products
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->get('per_page', 10));
        return $this->success(
            'Products fetched successfully',[
                'products' => ProductResource::collection($products->items()),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
                'filters_applied' => $request->only(['category', 'brand', 'min_price', 'max_price', 'in_stock', 'is_active', 'search', 'sort_by', 'sort_order'])
            ]
         
        );
       }
       catch(\Exception $e){
        return $this->error('Error fetching products',[$e->getMessage()],500);
       }
       
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     description="Create a new product with optional image upload",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "price", "stock"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="iPhone 15 Pro"),
     *                 @OA\Property(property="description", type="string", maxLength=1000, example="Latest iPhone with advanced features"),
     *                 @OA\Property(property="price", type="number", format="float", minimum=0, maximum=999999.99, example=999.99),
     *                 @OA\Property(property="stock", type="integer", minimum=0, maximum=999999, example=100),
     *                 @OA\Property(property="sku", type="string", maxLength=50, example="IPH15PRO-001"),
     *                 @OA\Property(property="image", type="string", format="binary", description="Product image file"),
     *                 @OA\Property(property="category", type="string", maxLength=100, example="Electronics"),
     *                 @OA\Property(property="brand", type="string", maxLength=100, example="Apple"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="stock", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to create products")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error creating product"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function store(ProductRequest $request): JsonResponse
    {
        if ($request->user()->cannot('create', Product::class)) {
            return $this->error('You are not authorized to create products',[],403);
        }

        try {
            $validatedData = $request->validated();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $validatedData['image'] = 'storage/'.$imagePath;
            } else {
                $validatedData['image'] = null;
            }

            $product = Product::create($validatedData);

            return $this->success('Product created successfully', new ProductResource($product));
        } catch (\Exception $e) {
            return $this->error('Error creating product', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get a specific product",
     *     description="Retrieve details of a specific product by ID",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product fetched successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to view this product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function show(Product $product): JsonResponse
    {
        if (request()->user()->cannot('view', $product)) {
            return $this->error('You are not authorized to view this product',[],403);
        }
        return $this->success('Product fetched successfully', new ProductResource($product));
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a product",
     *     description="Update an existing product with optional image replacement",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "price", "stock"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="iPhone 15 Pro Max"),
     *                 @OA\Property(property="description", type="string", maxLength=1000, example="Updated description"),
     *                 @OA\Property(property="price", type="number", format="float", minimum=0, maximum=999999.99, example=1099.99),
     *                 @OA\Property(property="stock", type="integer", minimum=0, maximum=999999, example=50),
     *                 @OA\Property(property="sku", type="string", maxLength=50, example="IPH15PROMAX-001"),
     *                 @OA\Property(property="image", type="string", format="binary", description="New product image file"),
     *                 @OA\Property(property="category", type="string", maxLength=100, example="Electronics"),
     *                 @OA\Property(property="brand", type="string", maxLength=100, example="Apple"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="stock", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update this product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error updating product"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        if (request()->user()->cannot('update', $product)) {
            return $this->error('You are not authorized to update this product',[],403);
        }

        try {
            $validatedData = $request->validated();
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                
                // Upload new image
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $validatedData['image'] ='storage/'.$imagePath;
            } else {
                // If no new image is provided, keep the existing image
                unset($validatedData['image']);
            }

            $product->update($validatedData);

            return $this->success('Product updated successfully', new ProductResource($product));
        } catch (\Exception $e) {
            return $this->error('Error updating product', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     description="Delete a product and its associated image file",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete products")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error deleting product"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        if (request()->user()->cannot('delete', $product)) {
            return $this->error('You are not authorized to delete products',[],403);
        }

        try {
            // Delete associated image if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            
            $product->delete();

            return $this->success('Product deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Error deleting product', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/categories",
     *     summary="Get available categories",
     *     description="Retrieve a list of all available product categories",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="string", example="Electronics")
     *             )
     *         )
     *     )
     * )
     */
    public function categories(): JsonResponse
    {
        $categories = Product::distinct()->pluck('category')->filter()->values();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/brands",
     *     summary="Get available brands",
     *     description="Retrieve a list of all available product brands",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Brands retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Brands fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="string", example="Apple")
     *             )
     *         )
     *     )
     * )
     */
    public function brands(): JsonResponse
    {
        $brands = Product::distinct()->pluck('brand')->filter()->values();
        return $this->success('Brands fetched successfully', $brands);
    }

}