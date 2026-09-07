<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderReviewTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_shows_in_my_orders_page_with_unreviewed_status(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10]);

        $order = Order::create([
            'order_code' => 'SNK-REVIEW-001',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Kenanga No. 5',
            'total_amount' => 30000,
            'grand_total' => 30000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 30000,
            'subtotal' => 30000,
        ]);

        $this->assertTrue($order->hasUnreviewedItems());
        $this->assertFalse($order->isFullyReviewed());

        $response = $this->actingAs($buyer)->get(route('my.orders'));
        $response->assertOk();
        $response->assertSee('Belum Dinilai');
        $response->assertSee('SNK-REVIEW-001');
        $response->assertSee('Beri Nilai');
    }

    public function test_buyer_can_submit_review_with_order_id_and_photo(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'stock' => 10, 'rating' => 0]);

        $order = Order::create([
            'order_code' => 'SNK-REVIEW-002',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Melati No. 2',
            'total_amount' => 45000,
            'grand_total' => 45000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 45000,
            'subtotal' => 45000,
        ]);

        $photo = UploadedFile::fake()->image('review_pic.jpg');

        $response = $this->actingAs($buyer)->post(route('product.reviews.store', $product->slug), [
            'rating' => 5,
            'review' => 'Rasa keripiknya gurih dan renyah banget, pengiriman kilat!',
            'order_id' => $order->id,
            'photo' => $photo,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'review' => 'Rasa keripiknya gurih dan renyah banget, pengiriman kilat!',
        ]);

        $review = ProductReview::where('order_id', $order->id)->first();
        $this->assertNotNull($review);
        $this->assertNotNull($review->photos);
        $this->assertCount(1, $review->photos);
        Storage::disk('public')->assertExists($review->photos[0]);

        // Verify product rating recalculated
        $product->refresh();
        $this->assertEquals(5.0, (float) $product->rating);

        // Verify order is now fully reviewed
        $order->refresh();
        $this->assertFalse($order->hasUnreviewedItems());
        $this->assertTrue($order->isFullyReviewed());
    }

    public function test_reviewed_item_shows_rated_badge_on_my_orders_and_order_detail(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-REVIEW-003',
            'user_id' => $buyer->id,
            'customer_name' => $buyer->name,
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Mawar',
            'total_amount' => 20000,
            'grand_total' => 20000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 20000,
            'subtotal' => 20000,
        ]);

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'review' => 'Mantap!',
        ]);

        $responseOrders = $this->actingAs($buyer)->get(route('my.orders'));
        $responseOrders->assertOk();
        $responseOrders->assertSee('Dinilai (5/5)');

        $responseDetail = $this->actingAs($buyer)->get(route('order.detail', ['order_code' => $order->order_code]));
        $responseDetail->assertOk();
        $responseDetail->assertSee('Dinilai (5/5)');
    }

    public function test_review_photos_are_rendered_on_product_detail_page(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 5,
            'review' => 'Barang istimewa!',
            'photos' => ['reviews/sample_review.jpg'],
        ]);

        $response = $this->actingAs($buyer)->get(route('product.detail', $product->slug));
        $response->assertOk();
        $response->assertSee('Barang istimewa!');
        $response->assertSee('storage/reviews/sample_review.jpg');
    }
}
