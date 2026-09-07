<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LimitedPromoTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $elektronik;

    protected Category $fashion;

    protected Product $productDiscount50;

    protected Product $productDiscount20;

    protected Product $productNoDiscount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);

        $this->user = User::factory()->create();

        $this->elektronik = Category::where('slug', 'elektronik-gadget')->first();
        $this->fashion = Category::where('slug', 'fashion-pakaian')->first();

        // 50% discount (100k -> 50k)
        $this->productDiscount50 = Product::factory()->create([
            'category_id' => $this->elektronik->id,
            'name' => 'TWS Super Bass Flash Deal',
            'slug' => 'tws-super-bass-flash-deal',
            'price' => 100000,
            'discount_price' => 50000,
            'stock' => 15,
            'sold_count' => 85,
            'is_available' => true,
        ]);

        // 20% discount (200k -> 160k)
        $this->productDiscount20 = Product::factory()->create([
            'category_id' => $this->fashion->id,
            'name' => 'Sepatu Sneakers Kasual Diskon 20',
            'slug' => 'sepatu-sneakers-kasual-diskon-20',
            'price' => 200000,
            'discount_price' => 160000,
            'stock' => 50,
            'sold_count' => 20,
            'is_available' => true,
        ]);

        // Regular product with no discount
        $this->productNoDiscount = Product::factory()->create([
            'category_id' => $this->elektronik->id,
            'name' => 'Kabel Data Type C Regular',
            'slug' => 'kabel-data-type-c-regular',
            'price' => 50000,
            'discount_price' => null,
            'stock' => 100,
            'sold_count' => 10,
            'is_available' => true,
        ]);
    }

    public function test_limited_promo_page_loads_successfully_and_displays_only_discounted_products(): void
    {
        $response = $this->actingAs($this->user)->get(route('promo.limited'));

        $response->assertStatus(200);
        $response->assertSee('Promo Terbatas Spesial Hari Ini');
        $response->assertSee('TWS Super Bass Flash Deal');
        $response->assertSee('Sepatu Sneakers Kasual Diskon 20');
        // Non-discounted product should not appear on limited promo page
        $response->assertDontSee('Kabel Data Type C Regular');

        // Confirm card links directly to product detail page
        $response->assertSee(route('product.detail', $this->productDiscount50->slug));

        // Confirm cart button is removed from card
        $response->assertDontSee('+ Keranjang');

        // Confirm there are NO claimable vouchers as requested by user
        $response->assertDontSee('Salin Kode');
        $response->assertDontSee('Klaim Voucher');
    }

    public function test_limited_promo_filters_by_category(): void
    {
        $response = $this->actingAs($this->user)->get(route('promo.limited', ['category' => 'elektronik-gadget']));

        $response->assertStatus(200);
        $response->assertSee('TWS Super Bass Flash Deal');
        $response->assertDontSee('Sepatu Sneakers Kasual Diskon 20');
    }

    public function test_limited_promo_filters_by_min_discount(): void
    {
        // 50% filter: only the 50% discounted item should show
        $response = $this->actingAs($this->user)->get(route('promo.limited', ['min_discount' => 50]));

        $response->assertStatus(200);
        $response->assertSee('TWS Super Bass Flash Deal');
        $response->assertDontSee('Sepatu Sneakers Kasual Diskon 20');
    }

    public function test_limited_promo_filters_by_max_price(): void
    {
        // Max price 100k: TWS is 50k (discounted), Sepatu is 160k
        $response = $this->actingAs($this->user)->get(route('promo.limited', ['max_price' => 100000]));

        $response->assertStatus(200);
        $response->assertSee('TWS Super Bass Flash Deal');
        $response->assertDontSee('Sepatu Sneakers Kasual Diskon 20');
    }

    public function test_limited_promo_filters_by_limited_stock(): void
    {
        // Limited stock (<= 25): TWS has 15, Sepatu has 50
        $response = $this->actingAs($this->user)->get(route('promo.limited', ['stock_filter' => 'limited']));

        $response->assertStatus(200);
        $response->assertSee('TWS Super Bass Flash Deal');
        $response->assertDontSee('Sepatu Sneakers Kasual Diskon 20');
    }

    public function test_home_page_links_to_limited_promo_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('promo.limited'));
    }

    public function test_limited_promo_ajax_request_returns_json_without_page_refresh(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('promo.limited', ['category' => 'elektronik-gadget']), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'total']);
        $this->assertEquals(1, $response->json('total'));
        $this->assertStringContainsString('TWS Super Bass Flash Deal', $response->json('html'));
        $this->assertStringNotContainsString('Sepatu Sneakers Kasual Diskon 20', $response->json('html'));
    }
}
