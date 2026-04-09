<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'المشروبات', 'img' => 'categories/beverages.jpg'],
            ['name' => 'الوجبات السريعة', 'img' => 'categories/fast_food.jpg'],
            ['name' => 'الحلويات', 'img' => 'categories/desserts.png'],
            ['name' => 'المأكولات البحرية', 'img' => 'categories/seafood.avif'],
            ['name' => 'مشاوي', 'img' => 'categories/grills.jpg'],
            ['name' => 'الأطباق الرئيسية', 'img' => 'categories/main_dishes.png'],
            ['name' => 'السلطات', 'img' => 'categories/salads.png'],
            ['name' => 'المقبلات', 'img' => 'categories/appetizers.avif'],
            ['name' => 'إفطار', 'img' => 'categories/breakfast.webp'],
            ['name' => 'قهوة', 'img' => 'categories/coffee.jpg'],
            ['name' => 'شاي', 'img' => 'categories/tea.avif'],
            ['name' => 'شاورما', 'img' => null],
            ['name' => 'برغر', 'img' => null],
            ['name' => 'بيتزا', 'img' => null],
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