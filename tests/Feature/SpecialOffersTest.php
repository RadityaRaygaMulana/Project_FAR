<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecialOffersTest extends TestCase
{
    use RefreshDatabase;

    protected Store $kairoStore;

    protected User $user;

    protected Category $gadgetCategory;

    protected Product $discountedProduct;

    protected Product $regularProduct;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);

        $this->user = User::factory()->create();

        $this->kairoStore = Store::create([
            'user_id' => $this->user->id,
            'name' => 'Kairo Shop',
            'slug' => 'kairo-shop',
            'logo' => 'stores/logos/Ki6VbEAVABqu4vVYp3rBXsXQdYGNLue4Y4BQsVDY.webp',
            'phone' => '081234567890',
            'badge' => 'Official',
            'rating' => 4.9,
            'status' => 'approved',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'address_detail' => 'Jl. Braga No. 10',
        ]);

        $this->gadgetCategory = Category::firstOrCreate(
            ['slug' => 'gadget-spesial'],
            ['name' => 'Gadget Spesial', 'icon' => '📱']
        );

        $this->discountedProduct = Product::factory()->create([
            'store_id' => $this->kairoStore->id,
            'category_id' => $this->gadgetCategory->id,
            'name' => 'Kacamata Anti-Radiasi Kairo',
            'slug' => 'kacamata-anti-radiasi-kairo',
            'brand' => 'Kairo Shop',
            'price' => 200000,
            'discount_price' => 100000, // 50% discount
            'stock' => 30,
            'is_available' => true,
            'is_featured' => true,
        ]);

        $this->regularProduct = Product::factory()->create([
            'category_id' => $this->gadgetCategory->id,
            'name' => 'Aksesoris Regular',
            'slug' => 'aksesoris-regular',
            'price' => 150000,
            'discount_price' => null,
            'stock' => 10,
            'is_available' => true,
            'is_featured' => false,
        ]);
    }

    public function test_search_results_displays_store_profile_photo(): void
    {
        $response = $this->actingAs($this->user)->get(route('search.results', ['q' => 'kairo']));

        $response->assertStatus(200);
        $response->assertSee('Official Store Kairo Shop');
        $response->assertSee('Ki6VbEAVABqu4vVYp3rBXsXQdYGNLue4Y4BQsVDY.webp');
        $response->assertSee(route('store.show', 'kairo-shop'));
    }

    public function test_special_offers_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('penawaran.spesial'));

        $response->assertStatus(200);
        $response->assertSee('Penawaran Spesial');
        $response->assertSee('Kacamata Anti-Radiasi Kairo');
    }

    public function test_special_offers_filters_by_category(): void
    {
        $otherCat = Category::create([
            'name' => 'Pakaian Santai',
            'slug' => 'pakaian-santai',
            'icon' => '👕',
        ]);

        $otherProduct = Product::factory()->create([
            'category_id' => $otherCat->id,
            'name' => 'Jaket Hoodie Spesial',
            'slug' => 'jaket-hoodie-spesial',
            'price' => 100000,
            'discount_price' => 50000,
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('penawaran.spesial', ['category' => 'gadget-spesial']));
        $response->assertStatus(200);
        $response->assertSee('Kacamata Anti-Radiasi Kairo');
        $response->assertDontSee('Jaket Hoodie Spesial');
    }

    public function test_special_offers_ajax_returns_json_grid(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('penawaran.spesial'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'total']);
        $this->assertStringContainsString('Kacamata Anti-Radiasi Kairo', $response->json('html'));
    }

    public function test_home_page_links_to_special_offers(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('penawaran.spesial'));
    }
}
