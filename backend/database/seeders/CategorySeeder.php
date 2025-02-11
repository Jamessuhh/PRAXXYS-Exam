<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Sports',
            'Electronics',
            'Clothing',
            'Books',
            'Home & Garden'
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
} 