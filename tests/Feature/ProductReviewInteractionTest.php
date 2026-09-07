<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewComment;
use App\Models\ProductReviewLike;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewInteractionTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected Store $store;

    protected User $buyer;

    protected Product $product;

    protected ProductReview $review;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);

        // Create seller with store
        $this->seller = User::factory()->create(['name' => 'Toko Snack Official']);
        $this->store = Store::factory()->create([
            'user_id' => $this->seller->id,
            'name' => 'Snack Official Store',
            'status' => 'approved',
        ]);

        // Create product
        $category = Category::firstOrCreate(['slug' => 'snack-makanan'], ['name' => 'Snack & Makanan']);
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'store_id' => $this->store->id,
            'name' => 'Keripik Kentang Renyah',
            'slug' => 'keripik-kentang-renyah',
            'price' => 25000,
        ]);

        // Create buyer and review
        $this->buyer = User::factory()->create(['name' => 'Buyer Santoso']);
        $this->review = ProductReview::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'rating' => 5,
            'review' => 'Rasa keripiknya gurih dan renyah banget!',
        ]);
    }

    public function test_guest_cannot_like_a_review(): void
    {
        $response = $this->postJson(route('reviews.like', $this->review));
        $response->assertUnauthorized();

        $this->assertDatabaseCount('product_review_likes', 0);
    }

    public function test_authenticated_user_can_like_and_unlike_a_review(): void
    {
        $user = User::factory()->create();

        // 1. Like
        $responseLike = $this->actingAs($user)->postJson(route('reviews.like', $this->review));
        $responseLike->assertOk()
            ->assertJson([
                'success' => true,
                'liked' => true,
                'likes_count' => 1,
            ]);

        $this->assertDatabaseHas('product_review_likes', [
            'product_review_id' => $this->review->id,
            'user_id' => $user->id,
        ]);

        // 2. Unlike (toggle)
        $responseUnlike = $this->actingAs($user)->postJson(route('reviews.like', $this->review));
        $responseUnlike->assertOk()
            ->assertJson([
                'success' => true,
                'liked' => false,
                'likes_count' => 0,
            ]);

        $this->assertDatabaseMissing('product_review_likes', [
            'product_review_id' => $this->review->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_post_comment_on_review(): void
    {
        $response = $this->postJson(route('reviews.comments.store', $this->review), [
            'comment' => 'Terima kasih reviewnya!',
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('product_review_comments', 0);
    }

    public function test_authenticated_user_can_post_comment_on_review(): void
    {
        $otherUser = User::factory()->create(['name' => 'Komentator']);

        $response = $this->actingAs($otherUser)->postJson(route('reviews.comments.store', $this->review), [
            'comment' => 'Setuju, keripik ini emang paling enak!',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'comments_count' => 1,
                'comment' => [
                    'user_name' => 'Komentator',
                    'comment' => 'Setuju, keripik ini emang paling enak!',
                    'is_seller' => false,
                ],
            ]);

        $this->assertDatabaseHas('product_review_comments', [
            'product_review_id' => $this->review->id,
            'user_id' => $otherUser->id,
            'comment' => 'Setuju, keripik ini emang paling enak!',
        ]);
    }

    public function test_seller_comment_has_is_seller_badge_flag(): void
    {
        $response = $this->actingAs($this->seller)->postJson(route('reviews.comments.store', $this->review), [
            'comment' => 'Terima kasih banyak atas pesanannya kak, ditunggu repeat ordernya!',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'comments_count' => 1,
                'comment' => [
                    'user_name' => 'Toko Snack Official',
                    'is_seller' => true,
                ],
            ]);

        $this->assertDatabaseHas('product_review_comments', [
            'product_review_id' => $this->review->id,
            'user_id' => $this->seller->id,
        ]);
    }

    public function test_comment_validation_fails_on_empty_or_too_short(): void
    {
        $user = User::factory()->create();

        $responseEmpty = $this->actingAs($user)->postJson(route('reviews.comments.store', $this->review), [
            'comment' => '',
        ]);
        $responseEmpty->assertUnprocessable();
        $responseEmpty->assertJsonValidationErrors('comment');

        $responseShort = $this->actingAs($user)->postJson(route('reviews.comments.store', $this->review), [
            'comment' => 'a',
        ]);
        $responseShort->assertUnprocessable();
        $responseShort->assertJsonValidationErrors('comment');
    }

    public function test_product_detail_page_renders_reviews_with_likes_and_comments(): void
    {
        // Add a like
        ProductReviewLike::create([
            'product_review_id' => $this->review->id,
            'user_id' => $this->seller->id,
        ]);

        // Add a seller comment
        ProductReviewComment::create([
            'product_review_id' => $this->review->id,
            'user_id' => $this->seller->id,
            'comment' => 'Terima kasih ulasannya yaa kak!',
        ]);

        $response = $this->actingAs($this->buyer)->get(route('product.detail', $this->product->slug));
        $response->assertOk();
        $response->assertSee('Rasa keripiknya gurih dan renyah banget!');
        $response->assertSee('Terima kasih ulasannya yaa kak!');
        $response->assertSee('Penjual');
        $response->assertSee('Balas');
        $response->assertSee('balasan');
        $response->assertSee('Tambahkan balasan...');
        $response->assertDontSee('💬 Komentar Ulasan');
    }
}
