<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_request_order_cancellation_with_reason(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->approved()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-TEST1',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kebon Jeruk No. 5',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'pending',
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

        $response = $this->actingAs($user)->post(route('orders.cancel', $order->id), [
            'reason' => 'Ingin mengubah alamat pengiriman',
        ]);

        $response->assertRedirect();
        $fresh = $order->fresh();
        $this->assertEquals('requested', $fresh->cancellation_status);
        $this->assertEquals('Ingin mengubah alamat pengiriman', $fresh->cancellation_reason);
        $this->assertNotNull($fresh->cancellation_requested_at);
    }

    public function test_buyer_cannot_cancel_shipped_or_completed_order(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->approved()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-TEST2',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kebon Jeruk No. 5',
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

        $response = $this->actingAs($user)->post(route('orders.cancel', $order->id), [
            'reason' => 'Berubah pikiran',
        ]);

        $response->assertSessionHas('error');
        $fresh = $order->fresh();
        $this->assertNull($fresh->cancellation_status);
        $this->assertEquals('shipped', $fresh->status);
    }

    public function test_seller_cannot_ship_order_while_cancellation_is_pending(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-TEST3',
            'customer_name' => 'Buyer',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kebon Jeruk No. 5',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'processing',
            'payment_status' => 'paid',
            'cancellation_status' => 'requested',
            'cancellation_reason' => 'Ingin ganti varian',
            'cancellation_requested_at' => Carbon::now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($sellerUser)->patch(route('seller.orders.update-status', $order->id), [
            'status' => 'shipped',
            'shipping_courier' => 'JNE',
            'tracking_number' => 'JNE123456789',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_seller_can_approve_cancellation_and_restore_stock(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Warna Biru',
            'price' => 50000,
            'stock' => 5,
        ]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-TEST4',
            'customer_name' => 'Buyer',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kebon Jeruk No. 5',
            'total_amount' => 100000,
            'grand_total' => 100000,
            'status' => 'processing',
            'payment_status' => 'paid',
            'cancellation_status' => 'requested',
            'cancellation_reason' => 'Salah pesan varian',
            'cancellation_requested_at' => Carbon::now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name.' (Warna Biru)',
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($sellerUser)->patch(route('seller.orders.cancellation', $order->id), [
            'action' => 'approve',
            'response_note' => 'Disetujui oleh penjual',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $freshOrder = $order->fresh();
        $this->assertEquals('cancelled', $freshOrder->status);
        $this->assertEquals('approved', $freshOrder->cancellation_status);
        $this->assertEquals('Disetujui oleh penjual', $freshOrder->cancellation_response_note);
        $this->assertNotNull($freshOrder->cancellation_responded_at);

        // Check stock restoration
        $this->assertEquals(12, $product->fresh()->stock);
        $this->assertEquals(7, $variant->fresh()->stock);
    }

    public function test_seller_can_reject_cancellation_and_proceed_with_shipping(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-TEST5',
            'customer_name' => 'Buyer',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kebon Jeruk No. 5',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'processing',
            'payment_status' => 'paid',
            'cancellation_status' => 'requested',
            'cancellation_reason' => 'Ingin ganti alamat',
            'cancellation_requested_at' => Carbon::now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        // Reject cancellation
        $rejectResponse = $this->actingAs($sellerUser)->patch(route('seller.orders.cancellation', $order->id), [
            'action' => 'reject',
            'response_note' => 'Paket sudah diserahkan ke kurir jemputan.',
        ]);

        $rejectResponse->assertRedirect();
        $freshOrder = $order->fresh();
        $this->assertEquals('rejected', $freshOrder->cancellation_status);
        $this->assertEquals('Paket sudah diserahkan ke kurir jemputan.', $freshOrder->cancellation_response_note);
        $this->assertEquals('processing', $freshOrder->status);

        // Now seller can ship the order
        $shipResponse = $this->actingAs($sellerUser)->patch(route('seller.orders.update-status', $order->id), [
            'status' => 'shipped',
            'shipping_courier' => 'SiCepat',
            'tracking_number' => '00123456789',
        ]);

        $shipResponse->assertRedirect();
        $this->assertEquals('shipped', $order->fresh()->status);
    }

    public function test_cancellation_requests_older_than_three_days_are_automatically_cancelled(): void
    {
        $store = Store::factory()->approved()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-EXPIRED',
            'customer_name' => 'Buyer Auto Cancel',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Merdeka No. 1',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'processing',
            'payment_status' => 'paid',
            'cancellation_status' => 'requested',
            'cancellation_reason' => 'Menunggu terlalu lama',
            'cancellation_requested_at' => Carbon::now()->subDays(4), // 4 days ago (> 3 days)
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        // Run artisan command
        $this->artisan('orders:process-cancellations')
            ->expectsOutputToContain('kadaluarsa')
            ->assertSuccessful();

        $fresh = $order->fresh();
        $this->assertEquals('cancelled', $fresh->status);
        $this->assertEquals('approved', $fresh->cancellation_status);
        $this->assertStringContainsString('3 hari', $fresh->cancellation_response_note);
        $this->assertEquals(12, $product->fresh()->stock);
    }

    public function test_invoice_page_displays_cancellation_button_at_the_bottom(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->approved()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-INV-CANCEL',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Merdeka No. 1',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'processing',
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

        $response = $this->actingAs($user)->get(route('order.detail', $order->order_code));

        $response->assertStatus(200);
        $response->assertSee('Ingin Membatalkan Pesanan Ini?');
        $response->assertSee('Batalkan Pesanan');
        $response->assertSee('Ajukan Pembatalan Pesanan');
    }

    public function test_shipped_order_displays_shipped_notice_and_cannot_be_cancelled_by_seller_or_buyer(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $sellerUser->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-SHIPPED-TEST',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Merdeka No. 1',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'shipped',
            'shipping_courier' => 'JNE',
            'tracking_number' => 'JNE987654321',
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

        // 1. Buyer views invoice: sees "Pesanan Sedang Dalam Pengiriman" & "Tidak Dapat Dibatalkan"
        $invoiceResponse = $this->actingAs($buyer)->get(route('order.detail', $order->order_code));
        $invoiceResponse->assertStatus(200);
        $invoiceResponse->assertSee('Pesanan Sedang Dalam Pengiriman');
        $invoiceResponse->assertSee('Tidak Dapat Dibatalkan');
        $invoiceResponse->assertDontSee('✕ Batalkan Pesanan');

        // 2. Buyer attempts cancellation: blocked
        $cancelResponse = $this->actingAs($buyer)->post(route('orders.cancel', $order->id), [
            'reason' => 'Ingin membatalkan paket di jalan',
        ]);
        $cancelResponse->assertSessionHas('error');
        $this->assertEquals('shipped', $order->fresh()->status);

        // 3. Seller attempts to cancel shipped order: blocked
        $sellerCancelResponse = $this->actingAs($sellerUser)->patch(route('seller.orders.update-status', $order->id), [
            'status' => 'cancelled',
        ]);
        $sellerCancelResponse->assertSessionHas('error');
        $this->assertEquals('shipped', $order->fresh()->status);
    }
}
