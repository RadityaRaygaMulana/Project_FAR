<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Database\Seeders\VoucherSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected Store $store;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);
        $this->seed(VoucherSeeder::class);

        $this->seller = User::factory()->create();
        $this->store = Store::factory()->create([
            'user_id' => $this->seller->id,
            'status' => 'approved',
        ]);

        $category = Category::firstOrCreate(['slug' => 'makanan-ringan'], ['name' => 'Makanan Ringan']);
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'name' => 'Keripik Singkong Balado',
            'slug' => 'keripik-singkong-balado',
            'price' => 15000,
        ]);
    }

    public function test_guest_can_access_home_login_and_register_pages(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_guest_cannot_access_protected_pages_and_is_redirected_to_login(): void
    {
        $protectedRoutes = [
            route('cart'),
            route('voucher.index'),
            route('product.detail', $this->product->slug),
            route('store.show', $this->store->slug),
            route('search'),
            route('search.results'),
            route('official.brand'),
            route('products.trending'),
            route('topup.bills'),
            route('promo.limited'),
            route('kebutuhan.pokok'),
            route('gratis.ongkir'),
            route('penawaran.spesial'),
            route('produk.baru'),
            route('checkout.show'),
            route('seller.register'),
        ];

        foreach ($protectedRoutes as $url) {
            $response = $this->get($url);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_user_can_access_marketplace_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('cart'))->assertOk();
        $this->actingAs($user)->get(route('product.detail', $this->product->slug))->assertOk();
        $this->actingAs($user)->get(route('voucher.index'))->assertOk();
        $this->actingAs($user)->get(route('store.show', $this->store->slug))->assertOk();
    }

    public function test_layout_contains_clean_auth_modal(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('loginModalOpen');
        $response->assertSee('showLoginRequiredModal');
        $response->assertSee('closeAuthModal');
        $response->assertSee('authModal.title');
        $response->assertSee('authModal.message');
        $response->assertSee('window.showAuthModal');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
    }

    public function test_header_search_bar_only_appears_on_home_page(): void
    {
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('placeholder="Cari produk apa saja di NusantaraMart..."', false);

        $user = User::factory()->create();
        $cartResponse = $this->actingAs($user)->get(route('cart'));
        $cartResponse->assertOk();
        $cartResponse->assertDontSee('placeholder="Cari produk apa saja di NusantaraMart..."', false);

        $detailResponse = $this->actingAs($user)->get(route('product.detail', $this->product->slug));
        $detailResponse->assertOk();
        $detailResponse->assertDontSee('placeholder="Cari produk apa saja di NusantaraMart..."', false);
    }
}
