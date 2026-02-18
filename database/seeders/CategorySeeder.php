<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'icon' => 'fas fa-laptop',
                'children' => ['Laptops', 'Smartphones', 'Audio', 'Gaming']
            ],
            [
                'name' => 'Watches',
                'icon' => 'fas fa-clock',
                'children' => ['Luxury Watches', 'Vintage Timepieces', 'Smartwatches']
            ],
            [
                'name' => 'Vintage Cars',
                'icon' => 'fas fa-car',
                'children' => ['Classic Cars', 'Muscle Cars', 'Vintage Luxury']
            ],
            [
                'name' => 'Jewelry',
                'icon' => 'fas fa-gem',
                'children' => ['Rings', 'Necklaces', 'Bracelets', 'Earrings']
            ],
            [
                'name' => 'Art',
                'icon' => 'fas fa-palette',
                'children' => ['Paintings', 'Sculptures', 'Digital Art', 'Photography']
            ],
        ];

        foreach ($categories as $categoryData) {
            $parent = Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'icon' => $categoryData['icon'],
                'is_active' => true,
            ]);

            if (isset($categoryData['children'])) {
                foreach ($categoryData['children'] as $childName) {
                    Category::create([
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'slug' => Str::slug($childName),
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
