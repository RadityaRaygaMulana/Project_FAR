<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderReturnAndDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_mark_order_as_delivered(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);

        $order = Order::create([
            'order_code' => 'SNK-DELIV-001',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Mawar No. 1',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'shipped',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($seller)->patch(route('seller.orders.update-status', $order), [
            'status' => 'delivered',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);
    }

    public function test_buyer_can_confirm_delivered_order_as_completed(): void
    {
        $buyer = User::factory()->create();
        $store = Store::factory()->approved()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-COMP-001',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kenanga No. 2',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivered_at' => now()->subDay(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($buyer)->post(route('orders.complete', $order));

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_buyer_can_request_order_return_with_proof_image(): void
    {
        Storage::fake('public');

        $buyer = User::factory()->create();
        $store = Store::factory()->approved()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-RET-001',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Melati No. 3',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivered_at' => now()->subDay(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $fakeImage = UploadedFile::fake()->image('bukti_rusak.jpg');

        $response = $this->actingAs($buyer)->post(route('orders.return', $order), [
            'return_reason' => 'Produk Rusak / Cacat',
            'return_description' => 'Kemasan basah dan sobek saat diterima dari kurir.',
            'return_proof_image' => $fakeImage,
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('requested', $order->return_status);
        $this->assertEquals('Produk Rusak / Cacat', $order->return_reason);
        $this->assertEquals('Kemasan basah dan sobek saat diterima dari kurir.', $order->return_description);
        $this->assertNotNull($order->return_proof_image);
        $this->assertNotNull($order->return_requested_at);
        Storage::disk('public')->assertExists($order->return_proof_image);
    }

    public function test_buyer_cannot_request_return_if_order_is_already_completed(): void
    {
        $buyer = User::factory()->create();
        $store = Store::factory()->approved()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-RET-002',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Anggrek No. 4',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'delivered_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $this->assertFalse($order->canBeReturnedByBuyer());

        $response = $this->actingAs($buyer)->post(route('orders.return', $order), [
            'return_reason' => 'Produk Rusak / Cacat',
            'return_description' => 'Mau komplain padahal sudah selesai.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $order->refresh();
        $this->assertNull($order->return_status);
    }

    public function test_seller_can_approve_return_and_restock_products(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);

        $order = Order::create([
            'order_code' => 'SNK-RET-003',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Melati No. 5',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'return_status' => 'requested',
            'return_reason' => 'Produk Rusak / Cacat',
            'return_description' => 'Rusak di jalan.',
            'return_requested_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 3,
            'unit_price' => 50000,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($seller)->patch(route('seller.orders.return.respond', $order), [
            'action' => 'approve',
            'response_note' => 'Disetujui. Dana refund segera kami proses.',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('returned', $order->status);
        $this->assertEquals('approved', $order->return_status);
        $this->assertNotNull($order->return_responded_at);
        $this->assertEquals('Disetujui. Dana refund segera kami proses.', $order->return_response_note);

        // Product stock should have been replenished (+3)
        $product->refresh();
        $this->assertEquals(13, $product->stock);
    }

    public function test_seller_can_reject_return_with_note(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);

        $order = Order::create([
            'order_code' => 'SNK-RET-004',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kenari No. 6',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'return_status' => 'requested',
            'return_reason' => 'Produk Tidak Sesuai Deskripsi / Salah Kirim',
            'return_description' => 'Beda warna.',
            'return_requested_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($seller)->patch(route('seller.orders.return.respond', $order), [
            'action' => 'reject',
            'response_note' => 'Ditolak karena segel telah dibuka dan bukti tidak valid.',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('delivered', $order->status); // stays delivered
        $this->assertEquals('rejected', $order->return_status);
        $this->assertNotNull($order->return_responded_at);
        $this->assertEquals('Ditolak karena segel telah dibuka dan bukti tidak valid.', $order->return_response_note);

        // Stock unchanged
        $product->refresh();
        $this->assertEquals(10, $product->stock);
    }

    public function test_orders_delivered_more_than_7_days_ago_are_auto_completed(): void
    {
        $buyer = User::factory()->create();
        $store = Store::factory()->approved()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        // Order 1: delivered 8 days ago, no return request -> should be completed
        $order1 = Order::create([
            'order_code' => 'SNK-AUTO-001',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kenanga No. 7',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivered_at' => Carbon::now()->subDays(8),
        ]);

        // Order 2: delivered 3 days ago -> should stay delivered
        $order2 = Order::create([
            'order_code' => 'SNK-AUTO-002',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kenanga No. 8',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivered_at' => Carbon::now()->subDays(3),
        ]);

        // Order 3: delivered 8 days ago but has pending return request -> should NOT be completed
        $order3 = Order::create([
            'order_code' => 'SNK-AUTO-003',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kenanga No. 9',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'delivered_at' => Carbon::now()->subDays(8),
            'return_status' => 'requested',
            'return_requested_at' => Carbon::now()->subDays(2),
        ]);

        $updatedCount = Order::autoCompleteDeliveredOrders();
        $this->assertEquals(1, $updatedCount);

        $order1->refresh();
        $this->assertEquals('completed', $order1->status);
        $this->assertNotNull($order1->completed_at);

        $order2->refresh();
        $this->assertEquals('delivered', $order2->status);

        $order3->refresh();
        $this->assertEquals('delivered', $order3->status);
    }
}
