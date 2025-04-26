<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategoryAndProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read and decode the JSON files
        $categoriesJson = File::get(database_path('data/categories.json'));
        $productsJson = File::get(database_path('data/products.json'));
        
        $categoriesData = json_decode($categoriesJson, true)['categories'];
        $productsData = json_decode($productsJson, true)['products'];

        // Create categories
        foreach ($categoriesData as $categoryData) {
            Category::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'status' => $categoryData['status'],
                'slug' => Str::slug($categoryData['name'])
            ]);
        }

        // Create products
        foreach ($productsData as $productData) {
            Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'stock' => $productData['stock'],
                'category_id' => $productData['category_id'],
                'status' => $productData['status'],
                'image_url' => $productData['image_url'],
                'slug' => Str::slug($productData['name'])
            ]);
        }
    }
} 