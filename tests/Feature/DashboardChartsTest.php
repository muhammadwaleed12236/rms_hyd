<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\Size;
use App\Models\Subcategory;
use App\Models\User;

it('dashboard displays weekly sales and top items charts data', function () {
    // Create and authenticate a user
    $user = User::firstOrCreate(['email' => 'test@example.com'], ['name' => 'Test User', 'password' => bcrypt('password')]);
    $this->actingAs($user);

    // Create test data
    $category = Category::firstOrCreate(['name' => 'Food']);
    $subcategory = Subcategory::firstOrCreate(['name' => 'Items'], ['category_id' => $category->id]);
    
    $item1 = Item::create(['name' => 'Item A', 'subcategory_id' => $subcategory->id]);
    $size1 = Size::create(['item_id' => $item1->id, 'size' => 'Regular', 'price' => 100]);
    
    $item2 = Item::create(['name' => 'Item B', 'subcategory_id' => $subcategory->id]);
    $size2 = Size::create(['item_id' => $item2->id, 'size' => 'Regular', 'price' => 200]);

    // Create some sales
    $this->postJson('/sales', [
        'type' => 'dine_in',
        'total' => 300,
        'cart' => [
            ['item_id' => $item1->id, 'name' => 'Item A', 'size' => 'Regular', 'size_id' => $size1->id, 'price' => 100, 'quantity' => 3, 'total' => 300],
        ],
        'customer_name' => 'Test Customer',
    ]);

    $this->postJson('/sales', [
        'type' => 'take_away',
        'total' => 400,
        'cart' => [
            ['item_id' => $item2->id, 'name' => 'Item B', 'size' => 'Regular', 'size_id' => $size2->id, 'price' => 200, 'quantity' => 2, 'total' => 400],
        ],
        'customer_name' => 'Another Customer',
    ]);

    // Access dashboard
    $response = $this->get('/');
    $response->assertSuccessful();
    
    // Verify view data contains weekly sales data and top items
    $response->assertViewHas('weeklySalesData');
    $response->assertViewHas('topItems');
    
    $weeklySalesData = $response->viewData('weeklySalesData');
    $topItems = $response->viewData('topItems');
    
    // Verify structure of weekly sales data
    expect($weeklySalesData)->toHaveKeys(['weeks', 'sales', 'revenue']);
    expect(count($weeklySalesData['weeks']))->toBe(12);
    expect(count($weeklySalesData['sales']))->toBe(12);
    expect(count($weeklySalesData['revenue']))->toBe(12);
    
    // Verify top items contains our sold items
    expect(count($topItems))->toBeGreaterThan(0);
    expect($topItems[0])->toHaveKeys(['name', 'quantity', 'revenue']);
});
