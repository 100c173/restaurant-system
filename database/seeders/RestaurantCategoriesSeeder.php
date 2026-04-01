<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestaurantCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'مشروبات',
            'وجبات سريعة',
            'حلويات',
            'مأكولات بحرية',
            'مشاوي',
            'أطباق رئيسية',
            'سلطات',
            'مقبلات',
            'فطور',
            'قهوة',
            'شاي',
            'عصائر طبيعية',
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
            ]);
        }
    }
}
