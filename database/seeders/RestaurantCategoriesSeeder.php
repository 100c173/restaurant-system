<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Beverages', 'img' => 'categories/beverages.jpg'],
            ['name' => 'Fast Food', 'img' => 'categories/fast_food.jpg'],
            ['name' => 'Desserts', 'img' => 'categories/desserts.png'],
            ['name' => 'Seafood', 'img' => 'categories/seafood.avif'],
            ['name' => 'Grills', 'img' => 'categories/grills.jpg'],
            ['name' => 'Main Dishes', 'img' => 'categories/main_dishes.png'],
            ['name' => 'Salads', 'img' => 'categories/salads.png'],
            ['name' => 'Appetizers', 'img' => 'categories/appetizers.avif'],
            ['name' => 'Breakfast', 'img' => 'categories/breakfast.webp'],
            ['name' => 'Coffee', 'img' => 'categories/coffee.jpg'],
            ['name' => 'Tea', 'img' => 'categories/tea.avif'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'img' => $category['img'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}