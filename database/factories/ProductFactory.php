<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Electronics', 'Computers', 'Accessories', 'Gaming', 'Office', 'Home', 'Mobile', 'Audio', 'Video', 'Networking'];
        $brands = ['Apple', 'Samsung', 'Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'Microsoft', 'Logitech', 'Razer', 'Corsair', 'Kingston'];
        
        return [
            'name' => fake()->words(rand(2, 4), true),
            'description' => fake()->paragraph(rand(2, 4)),
            'price' => fake()->randomFloat(2, 9.99, 2999.99),
            'stock' => fake()->numberBetween(0, 500),
            'sku' => strtoupper(fake()->unique()->lexify('???-###')),
            'image' => fake()->imageUrl(640, 480, 'products'),
            'category' => fake()->randomElement($categories),
            'brand' => fake()->randomElement($brands),
            'is_active' => true//fake()->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is in stock.
     */
    public function inStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => fake()->numberBetween(1, 500),
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the product is expensive (high price).
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->randomFloat(2, 1000, 2999.99),
        ]);
    }

    /**
     * Indicate that the product is affordable (low price).
     */
    public function affordable(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => fake()->randomFloat(2, 9.99, 99.99),
        ]);
    }
}