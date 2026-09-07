<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreeShippingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $electronicCategory;

    protected Category $fashionCategory;

    protected Product $electronicProduct;

    protected Product $fashionProduct;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);

        $this->user = User::factory()->create();

        $this->electronicCategory = Category::firstOrCreate(
            ['slug' => 'elektronik-gadget'],
            ['name' => 'Elektronik & Gadget', 'icon' => '📱']
        );

        $this->fashionCategory = Category::firstOrCreate(
            ['slug' => 'fashion-pakaian'],
            ['name' => 'Fashion & Pakaian', 'icon' => '👕']
        );

        $this->electronicProduct = Product::factory()->create([
            'category_id' => $this->electronicCategory->id,
            'name' => 'Smartwatch Waterproof Series 9',
            'slug' => 'smartwatch-waterproof-series-9',
            'price' => 350000,
            'discount_price' => 299000,
            'stock' => 25,
            'is_available' => true,
        ]);

        $this->fashionProduct = Product::factory()->create([
            'category_id' => $this->fashionCategory->id,
            'name' => 'Kemeja Flannel Pria Casual',
            'slug' => 'kemeja-flannel-pria-casual',
            'price' => 120000,
            'discount_price' => null,
            'stock' => 50,
            'is_available' => true,
        ]);
    }

    public function test_free_shipping_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('gratis.ongkir'));

        $response->assertStatus(200);
        $response->assertSee('Koleksi Produk Bebas Ongkir se-Indonesia', false);
        $response->assertSee('Smartwatch Waterproof Series 9');
        $response->assertSee('Kemeja Flannel Pria Casual');
        $response->assertSee('Bebas Ongkir');
        // Card links directly to product detail page
        $response->assertSee(route('product.detail', $this->electronicProduct->slug));
        // Cart button removed from card as requested
        $response->assertDontSee('+ Keranjang');
    }

    public function test_free_shipping_filters_by_category(): void
    {
        $response = $this->actingAs($this->user)->get(route('gratis.ongkir', ['category' => 'elektronik-gadget']));

        $response->assertStatus(200);
        $response->assertSee('Smartwatch Waterproof Series 9');
        $response->assertDontSee('Kemeja Flannel Pria Casual');
    }

    public function test_free_shipping_filters_by_discount(): void
    {
        $response = $this->actingAs($this->user)->get(route('gratis.ongkir', ['only_discount' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Smartwatch Waterproof Series 9');
        $response->assertDontSee('Kemeja Flannel Pria Casual');
    }

    public function test_free_shipping_ajax_returns_json_without_page_refresh(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('gratis.ongkir', ['category' => 'elektronik-gadget']), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'total']);
        $this->assertEquals(1, $response->json('total'));
        $this->assertStringContainsString('Smartwatch Waterproof Series 9', $response->json('html'));
        $this->assertStringNotContainsString('Kemeja Flannel Pria Casual', $response->json('html'));
    }

    public function test_home_page_links_to_free_shipping_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('gratis.ongkir'));
    }
}
