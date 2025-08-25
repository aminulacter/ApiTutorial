<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can add authorization logic here if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;
        
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock' => 'required|integer|min:0|max:999999',
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'image' => 'nullable|image:jpeg,png,jpg,gif,svg|max:2048',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'description.max' => 'Product description cannot exceed 1000 characters.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Product price must be a valid number.',
            'price.min' => 'Product price cannot be negative.',
            'price.max' => 'Product price cannot exceed 999,999.99.',
            'stock.required' => 'Product stock is required.',
            'stock.integer' => 'Product stock must be a whole number.',
            'stock.min' => 'Product stock cannot be negative.',
            'stock.max' => 'Product stock cannot exceed 999,999.',
            'sku.unique' => 'This SKU is already in use.',
            'sku.max' => 'SKU cannot exceed 50 characters.',
            'image.image' => 'Image must be an image file.',
            'image.max' => 'Image file size cannot exceed 2MB.',
            'category.max' => 'Category cannot exceed 100 characters.',
            'brand.max' => 'Brand cannot exceed 100 characters.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'product name',
            'description' => 'product description',
            'price' => 'product price',
            'stock' => 'product stock',
            'sku' => 'SKU',
            'image' => 'product image',
            'category' => 'product category',
            'brand' => 'product brand',
            'is_active' => 'active status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert empty strings to null for nullable fields
        $this->merge([
            'description' => $this->description ?: null,
            'sku' => $this->sku ?: null,
            'image' => $this->image ?: null,
            'category' => $this->category ?: null,
            'brand' => $this->brand ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

}