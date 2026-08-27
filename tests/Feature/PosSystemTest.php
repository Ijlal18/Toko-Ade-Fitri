<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login page', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});

test('authenticated user can visit dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('pos page renders product list and categories', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'Bahan Kue', 'slug' => 'bahan-kue']);
    $product = Product::create([
        'category_id' => $category->id,
        'sku' => 'TEST-001',
        'name' => 'Tepung Terigu 1kg',
        'purchase_price' => 10000,
        'selling_price_retail' => 12000,
        'selling_price_wholesale' => 11000,
        'stock' => 50,
        'min_stock' => 10,
        'unit' => 'kg',
    ]);

    $response = $this->actingAs($user)->get('/pos');

    $response->assertStatus(200);
});

test('pos transaction checkout deducts stock successfully', function () {
    $user = User::factory()->create();
    $customer = Customer::create(['name' => 'Budi Wholesale', 'type' => 'wholesale']);
    $product = Product::create([
        'sku' => 'TEST-002',
        'name' => 'Plastik HD 15x30',
        'purchase_price' => 15000,
        'selling_price_retail' => 20000,
        'selling_price_wholesale' => 18000,
        'stock' => 100,
        'min_stock' => 10,
        'unit' => 'pack',
    ]);

    $payload = [
        'customer_id' => $customer->id,
        'payment_method' => 'cash',
        'discount_amount' => 1000,
        'tax_amount' => 0,
        'paid_amount' => 50000,
        'items' => [
            [
                'product_id' => $product->id,
                'price_type' => 'wholesale',
                'unit_price' => 18000,
                'quantity' => 2,
            ],
        ],
    ];

    $response = $this->actingAs($user)->post('/pos', $payload);

    $response->assertRedirect();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 98,
    ]);
    $this->assertDatabaseHas('transactions', [
        'customer_id' => $customer->id,
        'payment_method' => 'cash',
    ]);
});
