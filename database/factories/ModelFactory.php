<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Item;
use App\Models\Size;
use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModelFactory extends Factory
{
    protected $model = null;

    public function defineCategory()
    {
        return [
            'name' => $this->faker->word,
        ];
    }

    public function defineSubcategory()
    {
        return [
            'name' => $this->faker->word,
            'category_id' => Category::factory(),
        ];
    }

    public function defineItem()
    {
        return [
            'name' => $this->faker->word,
            'subcategory_id' => Subcategory::factory(),
        ];
    }

    public function defineSize()
    {
        return [
            'size' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'item_id' => Item::factory(),
        ];
    }

    public function defineSale()
    {
        return [
            'total' => $this->faker->randomFloat(2, 50, 500),
            'type' => $this->faker->randomElement(['dine_in', 'takeaway', 'delivery']),
            'customer_name' => $this->faker->name,
        ];
    }

    public function defineExpense()
    {
        return [
            'title' => $this->faker->word,
            'amount' => $this->faker->randomFloat(2, 5, 100),
        ];
    }
}