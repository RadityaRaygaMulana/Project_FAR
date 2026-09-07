<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_have_multiple_products(): void
    {
        $category = Category::factory()->create([
            'name' => 'Snack Pedas',
            'slug' => 'snack-pedas',
        ]);

        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Basreng Pedas',
        ]);

        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Keripik Kaca',
        ]);

        $this->assertCount(2, $category->products);
        $this->assertEquals($category->id, $product1->category->id);
        $this->assertEquals('Snack Pedas', $product2->category->name);
    }

    public function test_product_price_and_discount_accessors(): void
    {
        $productWithDiscount = Product::factory()->create([
            'price' => 20000,
            'discount_price' => 15000,
        ]);

        $this->assertTrue($productWithDiscount->hasDiscount());
        $this->assertEquals(15000, $productWithDiscount->effective_price);
        $this->assertEquals('Rp 20.000', $productWithDiscount->formatted_price);
        $this->assertEquals('Rp 15.000', $productWithDiscount->formatted_discount_price);

        $productWithoutDiscount = Product::factory()->create([
            'price' => 18000,
            'discount_price' => null,
        ]);

        $this->assertFalse($productWithoutDiscount->hasDiscount());
        $this->assertEquals(18000, $productWithoutDiscount->effective_price);
        $this->assertNull($productWithoutDiscount->formatted_discount_price);
    }

    public function test_product_scopes(): void
    {
        Product::factory()->create([
            'is_available' => true,
            'stock' => 10,
            'is_featured' => true,
        ]);

        Product::factory()->create([
            'is_available' => false,
            'stock' => 5,
            'is_featured' => false,
        ]);

        Product::factory()->create([
            'is_available' => true,
            'stock' => 0,
            'is_featured' => false,
        ]);

        $this->assertCount(1, Product::available()->get());
        $this->assertCount(1, Product::featured()->get());
    }

    public function test_order_and_order_items_relationship(): void
    {
        $order = Order::factory()->create([
            'order_code' => Order::generateOrderCode(),
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'total_amount' => 30000,
            'shipping_cost' => 10000,
            'grand_total' => 40000,
        ]);

        $product = Product::factory()->create(['price' => 15000]);

        $item1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);

        $this->assertCount(1, $order->items);
        $this->assertEquals('Budi Santoso', $item1->order->customer_name);
        $this->assertEquals('Rp 40.000', $order->formatted_grand_total);
        $this->assertStringStartsWith('SNK-', $order->order_code);
    }

    public function test_user_creation_with_username_and_role(): void
    {
        $user = User::factory()->create([
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'role' => 'customer',
        ]);

        $this->assertEquals('johndoe', $user->username);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertFalse($user->isAdmin());

        $admin = User::factory()->admin()->create([
            'username' => 'superadmin',
        ]);

        $this->assertTrue($admin->isAdmin());
    }

    public function test_marketplace_seeder_populates_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['username' => 'admin', 'role' => 'admin']);
        $this->assertDatabaseHas('categories', ['slug' => 'elektronik-gadget']);
        $this->assertDatabaseHas('categories', ['slug' => 'fashion-pakaian']);
        $this->assertGreaterThanOrEqual(6, Category::count());
        $this->assertEquals(0, Product::count());
    }

    public function test_product_origin_city_and_address_uses_real_store_location(): void
    {
        $store = Store::factory()->create([
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'address_detail' => 'Jl. Merdeka No. 12',
        ]);

        $product = Product::factory()->create([
            'store_id' => $store->id,
        ]);

        $this->assertEquals('Kota Bandung', $product->origin_city);
        $this->assertEquals('Jl. Merdeka No. 12, Kota Bandung, Jawa Barat', $product->origin_address);
    }
}
