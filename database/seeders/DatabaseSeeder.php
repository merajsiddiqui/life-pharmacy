<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create test categories
        $categories = Category::factory()->count(5)->create();

        // Create test products for each category
        foreach ($categories as $category) {
            Product::factory()->count(3)->create([
                'category_id' => $category->id
            ]);
        }
    }
}
