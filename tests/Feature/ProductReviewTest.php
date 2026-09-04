<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_review_for_product(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Kacamata Vintage',
            'rating' => 5.0,
        ]);

        $buyer = User::factory()->create(['role' => 'customer', 'name' => 'Budi Santoso']);

        $response = $this->actingAs($buyer)->post(route('product.reviews.store', ['product' => $product->slug]), [
            'rating' => 5,
            'review' => 'Kacamata sangat bagus, frame kokoh dan lensa jernih anti radiasi!',
            'variant_name' => 'Hitam Glossy',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 5,
            'review' => 'Kacamata sangat bagus, frame kokoh dan lensa jernih anti radiasi!',
            'variant_name' => 'Hitam Glossy',
        ]);

        // Verify it appears on product detail page
        $detailResponse = $this->get(route('product.detail', ['product' => $product->slug]));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Budi Santoso');
        $detailResponse->assertSee('Kacamata sangat bagus, frame kokoh');
        $detailResponse->assertSee('Hitam Glossy');
    }

    public function test_review_validation_requires_valid_rating(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $buyer = User::factory()->create();

        $response = $this->actingAs($buyer)->post(route('product.reviews.store', ['product' => $product->slug]), [
            'rating' => 6, // Invalid rating > 5
            'review' => 'Test',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_guest_cannot_submit_review(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->post(route('product.reviews.store', ['product' => $product->slug]), [
            'rating' => 5,
            'review' => 'Guest review',
        ]);

        $response->assertRedirect(route('login'));
    }
}
