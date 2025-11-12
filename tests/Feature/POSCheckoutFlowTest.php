<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\Size;
use App\Models\Subcategory;
use App\Models\User;

it('complete pos checkout flow stores item names and displays in receipt', function () {
    // Create and authenticate a user
    $user = User::firstOrCreate(['email' => 'test@example.com'], ['name' => 'Test User', 'password' => bcrypt('password')]);
    $this->actingAs($user);

    // Create items with sizes
    $category = Category::firstOrCreate(['name' => 'Food']);
    
    $subcategory1 = Subcategory::firstOrCreate(
        ['name' => 'Burgers'],
        ['category_id' => $category->id]
    );
    $item1 = Item::create(['name' => 'Burger Classic', 'subcategory_id' => $subcategory1->id]);
    $size1 = Size::create(['item_id' => $item1->id, 'size' => 'Single', 'price' => 350]);

    $subcategory2 = Subcategory::firstOrCreate(
        ['name' => 'Drinks'],
        ['category_id' => $category->id]
    );
    $item2 = Item::create(['name' => 'Cola', 'subcategory_id' => $subcategory2->id]);
    $size2 = Size::create(['item_id' => $item2->id, 'size' => 'Large', 'price' => 150]);

    // Simulate POS checkout with multiple items
    $cart = [
        [
            'item_id' => $item1->id,
            'name' => 'Burger Classic',
            'size' => 'Single',
            'size_id' => $size1->id,
            'price' => 350,
            'quantity' => 2,
            'total' => 700,
        ],
        [
            'item_id' => $item2->id,
            'name' => 'Cola',
            'size' => 'Large',
            'size_id' => $size2->id,
            'price' => 150,
            'quantity' => 1,
            'total' => 150,
        ],
    ];

    $response = $this->postJson('/sales', [
        'type' => 'dine_in',
        'total' => 850,
        'cart' => $cart,
        'customer_name' => 'Ahmed Khan',
        'payments' => [
            [
                'method' => 'cash',
                'amount' => 850,
                'transaction_id' => null,
            ],
        ],
    ]);

    $response->assertSuccessful();
    $data = $response->json();

    // Verify the response contains the complete cart with item names
    expect(count($data['cart']))->toBe(2);
    expect($data['cart'][0]['name'])->toBe('Burger Classic');
    expect($data['cart'][0]['quantity'])->toBe(2);
    expect($data['cart'][1]['name'])->toBe('Cola');
    expect($data['cart'][1]['quantity'])->toBe(1);
    
    // Verify payment was recorded
    expect(count($data['payments']))->toBe(1);
    expect($data['payments'][0]['method'])->toBe('cash');
    expect($data['payments'][0]['amount'])->toBe(850);
    
    // Verify sale totals
    expect($data['total'])->toBe(850);
    expect($data['type'])->toBe('dine_in');
    expect($data['customer_name'])->toBe('Ahmed Khan');
});
