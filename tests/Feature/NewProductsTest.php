<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewProductsTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected Category $fashionCategory;

    protected Category $electronicsCategory;

    protected Product $recentProduct;

    protected Product $oldProduct;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);

        $this->user = User::factory()->create();

        $this->store = Store::create([
            'user_id' => $this->user->id,
            'name' => 'Toko Baru Makmur',
            'slug' => 'toko-baru-makmur',
            'phone' => '081234567891',
            'badge' => 'Official',
            'rating' => 4.9,
            'status' => 'approved',
            'city' => 'Kota Surabaya',
            'province' => 'Jawa Timur',
            'address_detail' => 'Jl. Pemuda No. 12',
        ]);

        $this->fashionCategory = Category::firstOrCreate(
            ['slug' => 'fashion-trend'],
            ['name' => 'Fashion Trend', 'icon' => '👗']
        );

        $this->electronicsCategory = Category::firstOrCreate(
            ['slug' => 'gadget-baru'],
            ['name' => 'Gadget Baru', 'icon' => '🎧']
        );

        // Recent product: created 5 days ago (< 1 month)
        $this->recentProduct = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->fashionCategory->id,
            'name' => 'Kemeja Katun Modern Rilisan Anyar',
            'slug' => 'kemeja-katun-modern-rilisan-anyar',
            'price' => 175000,
            'discount_price' => 140000,
            'stock' => 25,
            'is_available' => true,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        // Old product: created 45 days ago (> 1 month)
        $this->oldProduct = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->fashionCategory->id,
            'name' => 'Sepatu Lawas Musim Lalu',
            'slug' => 'sepatu-lawas-musim-lalu',
            'price' => 250000,
            'discount_price' => null,
            'stock' => 10,
            'is_available' => true,
            'created_at' => now()->subDays(45),
            'updated_at' => now()->subDays(45),
        ]);
    }

    public function test_new_products_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('produk.baru'));

        $response->assertStatus(200);
        $response->assertSee('Produk Baru');
        $response->assertSee('Kemeja Katun Modern Rilisan Anyar');
    }

    public function test_new_products_strictly_includes_under_one_month_and_excludes_over_one_month(): void
    {
        $response = $this->actingAs($this->user)->get(route('produk.baru'));

        $response->assertStatus(200);
        // Product created < 1 month ago must be visible
        $response->assertSee('Kemeja Katun Modern Rilisan Anyar');
        // Product created > 1 month ago must NOT be visible
        $response->assertDontSee('Sepatu Lawas Musim Lalu');
    }

    public function test_new_products_category_filtering(): void
    {
        $electronicProduct = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $this->electronicsCategory->id,
            'name' => 'Earphone Nirkabel Stereo HD',
            'slug' => 'earphone-nirkabel-stereo-hd',
            'price' => 300000,
            'is_available' => true,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->user)->get(route('produk.baru', ['category' => 'gadget-baru']));

        $response->assertStatus(200);
        $response->assertSee('Earphone Nirkabel Stereo HD');
        $response->assertDontSee('Kemeja Katun Modern Rilisan Anyar');
    }

    public function test_new_products_ajax_returns_json_grid(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('produk.baru'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'total']);
        $this->assertStringContainsString('Kemeja Katun Modern Rilisan Anyar', $response->json('html'));
        $this->assertStringNotContainsString('Sepatu Lawas Musim Lalu', $response->json('html'));
    }

    public function test_new_products_cards_link_to_product_detail_and_omit_cart_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('produk.baru'));

        $response->assertStatus(200);
        $response->assertSee(route('product.detail', $this->recentProduct->slug));
        $response->assertDontSee('+ Keranjang');
    }

    public function test_home_page_links_to_new_products(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('produk.baru'));
    }
}
