<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyEssentialsTest extends TestCase
{
    use RefreshDatabase;

    protected Category $foodCategory;

    protected Category $healthCategory;

    protected Category $electronicCategory;

    protected Product $foodProduct;

    protected Product $healthProduct;

    protected Product $electronicProduct;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);

        $this->foodCategory = Category::firstOrCreate(
            ['slug' => 'makanan-minuman'],
            ['name' => 'Makanan & Minuman', 'icon' => '🍜']
        );

        $this->healthCategory = Category::firstOrCreate(
            ['slug' => 'kesehatan-vitamin'],
            ['name' => 'Kesehatan & Vitamin', 'icon' => '💊']
        );

        $this->electronicCategory = Category::firstOrCreate(
            ['slug' => 'elektronik-gadget'],
            ['name' => 'Elektronik & Gadget', 'icon' => '📱']
        );

        $this->foodProduct = Product::factory()->create([
            'category_id' => $this->foodCategory->id,
            'name' => 'Beras Organik Premium 5kg',
            'slug' => 'beras-organik-premium-5kg',
            'price' => 75000,
            'discount_price' => 69000,
            'stock' => 40,
            'is_available' => true,
        ]);

        $this->healthProduct = Product::factory()->create([
            'category_id' => $this->healthCategory->id,
            'name' => 'Vitamin C 1000mg Suplemen Imun',
            'slug' => 'vitamin-c-1000mg-suplemen-imun',
            'price' => 45000,
            'discount_price' => null,
            'stock' => 60,
            'is_available' => true,
        ]);

        $this->electronicProduct = Product::factory()->create([
            'category_id' => $this->electronicCategory->id,
            'name' => 'Earphone Bluetooth Wireless Gadget',
            'slug' => 'earphone-bluetooth-wireless-gadget',
            'price' => 150000,
            'discount_price' => null,
            'stock' => 20,
            'is_available' => true,
        ]);
    }

    public function test_daily_essentials_page_loads_successfully_and_shows_food_and_health(): void
    {
        $response = $this->get(route('kebutuhan.pokok'));

        $response->assertStatus(200);
        $response->assertSee('Kebutuhan Pokok & Kesehatan Keluarga', false);
        $response->assertSee('Beras Organik Premium 5kg');
        $response->assertSee('Vitamin C 1000mg Suplemen Imun');
        // Electronics must NOT appear on Daily Essentials page
        $response->assertDontSee('Earphone Bluetooth Wireless Gadget');
    }

    public function test_daily_essentials_filters_by_specific_category(): void
    {
        $response = $this->get(route('kebutuhan.pokok', ['category' => 'makanan-minuman']));

        $response->assertStatus(200);
        $response->assertSee('Beras Organik Premium 5kg');
        $response->assertDontSee('Vitamin C 1000mg Suplemen Imun');
    }

    public function test_daily_essentials_filters_by_discount_only(): void
    {
        $response = $this->get(route('kebutuhan.pokok', ['only_discount' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Beras Organik Premium 5kg');
        $response->assertDontSee('Vitamin C 1000mg Suplemen Imun');
    }

    public function test_daily_essentials_ajax_request_returns_json_without_page_refresh(): void
    {
        $response = $this->getJson(route('kebutuhan.pokok', ['category' => 'kesehatan-vitamin']), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'total']);
        $this->assertEquals(1, $response->json('total'));
        $this->assertStringContainsString('Vitamin C 1000mg Suplemen Imun', $response->json('html'));
        $this->assertStringNotContainsString('Beras Organik Premium 5kg', $response->json('html'));
    }

    public function test_home_page_links_to_daily_essentials_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('kebutuhan.pokok'));
    }
}
