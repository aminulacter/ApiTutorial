<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
                $validatedData['image'] = $imagePath;
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
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        if (request()->user()->cannot('view', $product)) {
            return $this->error('You are not authorized to view this product',[],403);
        }
        return $this->success('Product fetched successfully', new ProductResource($product));
    }

    /**
     * Update the specified resource in storage.
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
                $validatedData['image'] = $imagePath;
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
     * Remove the specified resource from storage.
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
     * Get available categories
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
     * Get available brands
     */
    public function brands(): JsonResponse
    {
        $brands = Product::distinct()->pluck('brand')->filter()->values();
        return $this->success('Brands fetched successfully', $brands);
    }

}