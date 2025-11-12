<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\Size;
use App\Models\Subcategory;
use App\Models\User;

it('stores sale with item names in cart', function () {
    // Create and authenticate a user
    $user = User::firstOrCreate(['email' => 'test@example.com'], ['name' => 'Test User', 'password' => bcrypt('password')]);
    $this->actingAs($user);

    // Create a category and subcategory
    $category = Category::firstOrCreate(['name' => 'Food']);
    $subcategory = Subcategory::firstOrCreate(
        ['name' => 'Burgers'],
        ['category_id' => $category->id]
    );
    
    // Create an item with a size
    $item = Item::create(['name' => 'Burger Deluxe', 'subcategory_id' => $subcategory->id]);
    $size = Size::create(['item_id' => $item->id, 'size' => 'Large', 'price' => 450]);

    // Create a sale with cart containing item name
    $cartData = [
        [
            'item_id' => $item->id,
            'name' => 'Burger Deluxe',
            'size' => 'Large',
            'size_id' => $size->id,
            'price' => 450,
            'quantity' => 2,
            'total' => 900,
        ],
    ];

    $response = $this->postJson('/sales', [
        'type' => 'dine_in',
        'total' => 900,
        'cart' => $cartData,
        'customer_name' => 'John Doe',
    ]);

    $response->assertSuccessful();
    $data = $response->json();

    // Verify item name is stored in cart
    expect($data['cart'][0]['name'])->toBe('Burger Deluxe');
    expect($data['cart'][0]['item_id'])->toBe($item->id);
    expect($data['cart'][0]['quantity'])->toBe(2);
});
