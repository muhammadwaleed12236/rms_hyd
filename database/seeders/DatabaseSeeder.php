<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Item;
use App\Models\Size;
use App\Models\Sale;
use App\Models\Expense;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seed categories
        $categories = Category::factory()->count(5)->create();

        // Seed subcategories
        foreach ($categories as $category) {
            Subcategory::factory()->count(3)->create(['category_id' => $category->id]);
        }

        // Seed items with sizes
        $items = Item::factory()->count(10)->create();
        foreach ($items as $item) {
            Size::factory()->count(2)->create(['item_id' => $item->id]);
        }

        // Seed sales
        Sale::factory()->count(20)->create();

        // Seed expenses
        Expense::factory()->count(15)->create();
    }
}