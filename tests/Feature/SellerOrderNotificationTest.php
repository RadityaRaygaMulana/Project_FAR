<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_check_notifications_endpoint_and_receive_correct_counts(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 25000]);

        // When no orders exist
        $response = $this->actingAs($seller)->getJson(route('seller.orders.notifications_check'));
        $response->assertOk();
        $response->assertJson([
            'pending_orders_count' => 0,
            'latest_order' => null,
        ]);

        // Create an incoming order (processing)
        $order1 = Order::create([
            'order_code' => 'SNK-NOTIF-001',
            'user_id' => $buyer->id,
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Merdeka No. 1',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($seller)->getJson(route('seller.orders.notifications_check'));
        $response->assertOk();
        $response->assertJson([
            'pending_orders_count' => 1,
            'latest_order' => [
                'id' => $order1->id,
                'order_code' => 'SNK-NOTIF-001',
                'customer_name' => 'Budi Santoso',
            ],
        ]);
    }

    public function test_orders_for_other_stores_are_not_counted(): void
    {
        $seller1 = User::factory()->create();
        $store1 = Store::factory()->approved()->create(['user_id' => $seller1->id]);
        $product1 = Product::factory()->create(['store_id' => $store1->id]);

        $seller2 = User::factory()->create();
        $store2 = Store::factory()->approved()->create(['user_id' => $seller2->id]);
        $product2 = Product::factory()->create(['store_id' => $store2->id]);

        $buyer = User::factory()->create();

        // Order for store 2
        $orderForStore2 = Order::create([
            'order_code' => 'SNK-STORE2-001',
            'user_id' => $buyer->id,
            'customer_name' => 'Siti Rahma',
            'customer_phone' => '081987654321',
            'customer_address' => 'Jl. Diponegoro No. 2',
            'total_amount' => 40000,
            'grand_total' => 40000,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $orderForStore2->id,
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'quantity' => 1,
            'unit_price' => 40000,
            'subtotal' => 40000,
        ]);

        // Seller 1 checks notifications
        $response = $this->actingAs($seller1)->getJson(route('seller.orders.notifications_check'));
        $response->assertOk();
        $response->assertJson([
            'pending_orders_count' => 0,
            'latest_order' => null,
        ]);

        // Seller 2 checks notifications
        $response2 = $this->actingAs($seller2)->getJson(route('seller.orders.notifications_check'));
        $response2->assertOk();
        $response2->assertJson([
            'pending_orders_count' => 1,
            'latest_order' => [
                'order_code' => 'SNK-STORE2-001',
            ],
        ]);
    }

    public function test_shipped_and_completed_orders_do_not_increment_pending_count(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        // Shipped order
        $order1 = Order::create([
            'order_code' => 'SNK-SHIPPED-001',
            'user_id' => $buyer->id,
            'customer_name' => 'Doni',
            'customer_phone' => '0811223344',
            'customer_address' => 'Jl. Sudirman No. 5',
            'total_amount' => 30000,
            'grand_total' => 30000,
            'status' => 'shipped',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 30000,
            'subtotal' => 30000,
        ]);

        // Completed order
        $order2 = Order::create([
            'order_code' => 'SNK-COMPLETED-001',
            'user_id' => $buyer->id,
            'customer_name' => 'Rina',
            'customer_phone' => '0811223355',
            'customer_address' => 'Jl. Gatot Subroto No. 8',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($seller)->getJson(route('seller.orders.notifications_check'));
        $response->assertOk();
        $response->assertJson([
            'pending_orders_count' => 0,
            'latest_order' => null,
        ]);
    }
}
