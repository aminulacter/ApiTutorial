<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 100 products using the factory
        Product::factory(100)->create();

        // Create some specific products with predefined data
        $specificProducts = [
            [
                'name' => 'MacBook Pro M2',
                'description' => 'Latest MacBook Pro with M2 chip, perfect for professionals and developers',
                'price' => 1999.99,
                'stock' => 25,
                'sku' => 'MAC-M2-001',
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8',
                'category' => 'Computers',
                'brand' => 'Apple',
                'is_active' => true,
            ],
            [
                'name' => 'Gaming Mouse RGB',
                'description' => 'High-precision gaming mouse with customizable RGB lighting',
                'price' => 79.99,
                'stock' => 150,
                'sku' => 'GAM-MOU-001',
                'image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46',
                'category' => 'Gaming',
                'brand' => 'Razer',
                'is_active' => true,
            ],
            [
                'name' => 'Wireless Headphones',
                'description' => 'Premium noise-cancelling wireless headphones with 30-hour battery life',
                'price' => 299.99,
                'stock' => 75,
                'sku' => 'AUD-HEAD-001',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e',
                'category' => 'Audio',
                'brand' => 'Sony',
                'is_active' => true,
            ],
            [
                'name' => '4K Smart TV 55"',
                'description' => '55-inch 4K Ultra HD Smart TV with HDR and built-in streaming apps',
                'price' => 699.99,
                'stock' => 30,
                'sku' => 'VID-TV-001',
                'image' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1',
                'category' => 'Video',
                'brand' => 'Samsung',
                'is_active' => true,
            ],
            [
                'name' => 'Mechanical Keyboard Pro',
                'description' => 'Professional mechanical keyboard with Cherry MX Blue switches and RGB backlighting',
                'price' => 149.99,
                'stock' => 100,
                'sku' => 'KEY-MECH-001',
                'image' => 'https://images.unsplash.com/photo-1541140532154-b024d705b90a',
                'category' => 'Accessories',
                'brand' => 'Corsair',
                'is_active' => true,
            ],
        ];

        foreach ($specificProducts as $product) {
            Product::create($product);
        }

        // Create some products with specific states
        Product::factory(10)->expensive()->create();
        Product::factory(15)->affordable()->create();
        Product::factory(5)->outOfStock()->create();
        Product::factory(20)->inactive()->create();
    }
}