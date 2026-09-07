<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);

        $this->user = User::factory()->create();

        $elektronik = Category::where('slug', 'elektronik-gadget')->first();
        $fashion = Category::where('slug', 'fashion-pakaian')->first();

        Product::factory()->create([
            'category_id' => $elektronik->id,
            'name' => 'TWS Earphone Wireless Bluetooth 5.3 ANC Pro Ultra',
            'slug' => 'tws-earphone-wireless-bluetooth-5-3-anc-pro-ultra',
            'brand' => 'SoundCore Official',
            'badge' => 'Mall',
            'price' => 299000,
            'discount_price' => 199000,
            'stock' => 50,
            'is_available' => true,
        ]);

        Product::factory()->create([
            'category_id' => $fashion->id,
            'name' => 'Sneakers Pria Kasual Vulcanized Kanvas Classic Low',
            'slug' => 'sneakers-pria-kasual-kanvas-classic-low',
            'brand' => 'Ventela Shoes',
            'badge' => 'Official',
            'price' => 289000,
            'discount_price' => 225000,
            'stock' => 50,
            'is_available' => true,
        ]);
    }

    public function test_home_page_loads_with_marketplace_catalog(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('NusantaraMart');
        $response->assertSee('TWS Earphone Wireless');
        $response->assertSee('Sneakers Pria Kasual');
    }

    public function test_home_page_filter_by_category(): void
    {
        $response = $this->get(route('home', ['category' => 'elektronik-gadget']));

        $response->assertStatus(200);
        $response->assertSee('TWS Earphone Wireless');
    }

    public function test_search_discovery_page_loads_with_recommendations(): void
    {
        $response = $this->actingAs($this->user)->get(route('search'));

        $response->assertStatus(200);
        $response->assertSee('Pencarian & Eksplorasi');
        $response->assertSee('Tren Pencarian Terpopuler');
        $response->assertSee('Kategori Belanja Pilihan');
    }

    public function test_search_results_page_loads_with_filters_and_products(): void
    {
        $response = $this->actingAs($this->user)->get(route('search.results', ['q' => 'TWS']));

        $response->assertStatus(200);
        $response->assertSee('Hasil pencarian untuk');
        $response->assertSee('TWS');
        $response->assertSee('Filter');
        $response->assertSee('Lokasi');
        $response->assertSee('Tipe Penjual');
        $response->assertSee('Urutkan:');
    }

    public function test_checkout_creates_order_and_reduces_stock(): void
    {
        $user = User::first() ?? User::factory()->create();
        $product = Product::first();
        $initialStock = $product->stock;

        $payload = [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Merdeka No. 10, Bandung',
            'customer_notes' => 'Tolong pedasnya mantap',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('checkout'), $payload);

        $order = Order::latest()->first();

        $this->assertNotNull($order);
        $this->assertEquals('Budi Santoso', $order->customer_name);
        $this->assertCount(1, $order->items);
        $this->assertEquals($initialStock - 2, $product->fresh()->stock);

        $response->assertRedirect(route('my.orders'));
    }

    public function test_order_detail_page_loads_invoice(): void
    {
        $product = Product::first();

        $order = Order::factory()->create([
            'order_code' => Order::generateOrderCode(),
            'customer_name' => 'Siti Nurhaliza',
            'total_amount' => $product->effective_price,
            'grand_total' => $product->effective_price + 15000,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => $product->effective_price,
            'subtotal' => $product->effective_price,
        ]);

        $response = $this->actingAs($this->user)->get(route('order.detail', ['order_code' => $order->order_code]));

        $response->assertStatus(200);
        $response->assertSee($order->order_code);
        $response->assertSee('Siti Nurhaliza');
        $response->assertSee($product->name);
    }

    public function test_cart_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('cart'));

        $response->assertStatus(200);
        $response->assertSee('Keranjang Belanja Kamu');
    }

    public function test_checkout_with_coupon_and_payment_method(): void
    {
        $user = User::first() ?? User::factory()->create();
        $product = Product::first();

        $payload = [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Merdeka No. 10, Bandung',
            'payment_method' => 'cod',
            'coupon_code' => 'SNACKSERU',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('checkout'), $payload);

        $order = Order::latest()->first();

        $this->assertNotNull($order);
        $expectedSubtotal = $product->effective_price * 2;
        $expectedShipping = $expectedSubtotal >= 100000 ? 0 : 15000;
        $this->assertEquals(max(0, $expectedSubtotal + $expectedShipping - 10000), $order->grand_total);
    }

    public function test_checkout_with_qris_sets_payment_status_to_paid_immediately(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 35000, 'stock' => 10]);

        $payload = [
            'customer_name' => 'Gerry Pratama',
            'customer_phone' => '085139132952',
            'customer_address' => 'Jl. Mawar No. 12, Bandung',
            'payment_method' => 'qris',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('checkout'), $payload);

        $order = Order::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('qris', $order->payment_method);
        $this->assertEquals('paid', $order->payment_status);
        $response->assertSessionHas('success');
        $this->assertStringContainsString('QRIS telah lunas', session('success'));
    }

    public function test_checkout_with_cod_sets_payment_status_to_unpaid(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 35000, 'stock' => 10]);

        $payload = [
            'customer_name' => 'Gerry Pratama',
            'customer_phone' => '085139132952',
            'customer_address' => 'Jl. Mawar No. 12, Bandung',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('checkout'), $payload);

        $order = Order::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals('unpaid', $order->payment_status);
        $response->assertSessionHas('success');
        $this->assertStringContainsString('Bayar di Tempat (COD)', session('success'));
    }

    public function test_checkout_fails_when_product_does_not_allow_selected_payment_method(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 35000,
            'stock' => 10,
            'allowed_payment_methods' => ['qris'],
        ]);

        $payload = [
            'customer_name' => 'Gerry Pratama',
            'customer_phone' => '085139132952',
            'customer_address' => 'Jl. Mawar No. 12, Bandung',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('checkout'), $payload);

        $response->assertSessionHasErrors(['payment_method']);
    }

    public function test_checkout_rejects_transfer_bank_and_unsupported_payment_methods(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 35000,
            'stock' => 10,
            'allowed_payment_methods' => ['qris', 'cod'],
        ]);

        $payload = [
            'customer_name' => 'Gerry Pratama',
            'customer_phone' => '085139132952',
            'customer_address' => 'Jl. Mawar No. 12, Bandung',
            'payment_method' => 'transfer_bank',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('checkout'), $payload);

        $response->assertSessionHasErrors(['payment_method']);
    }

    public function test_checkout_with_product_variant_decrements_variant_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 50000,
            'stock' => 10,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Warna Coklat',
            'price' => 55000,
            'stock' => 5,
        ]);

        $payload = [
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '081234567899',
            'customer_address' => 'Jl. Asia Afrika No. 1, Bandung',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'variant_name' => 'Warna Coklat',
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('checkout'), $payload);
        $response->assertSessionHasNoErrors();

        $order = Order::where('customer_phone', '081234567899')->first();
        $this->assertNotNull($order);
        $this->assertEquals('cod', $order->payment_method);
        $item = $order->items()->first();
        $this->assertStringContainsString('Warna Coklat', $item->product_name);
        $this->assertEquals(55000, $item->unit_price);
        $this->assertEquals(3, $variant->fresh()->stock);
    }

    public function test_checkout_page_renders_for_authenticated_user(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get(route('checkout.show'));

        $response->assertStatus(200);
        $response->assertSee('Checkout Pembayaran');
        $response->assertSee('Alamat Pengiriman');
    }

    public function test_checkout_page_renders_saved_address_when_user_has_configured_address(): void
    {
        $user = User::factory()->create([
            'recipient_name' => 'Dewi Lestari',
            'recipient_phone' => '081399887766',
            'address_label' => 'Rumah',
            'address_detail' => 'Jl. Kebon Sirih No. 15',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'default_address' => 'Jl. Kebon Sirih No. 15, Jakarta Pusat, DKI Jakarta',
            'map_notes' => 'Pagar putih sebelah kantor pos',
        ]);

        $response = $this->actingAs($user)->get(route('checkout.show'));

        $response->assertStatus(200);
        $response->assertSee('Dewi Lestari');
        $response->assertSee('081399887766');
        $response->assertSee('Jl. Kebon Sirih No. 15');
        $response->assertSee('Pagar putih sebelah kantor pos');
        $response->assertSee('Pesan Sekarang');
    }

    public function test_checkout_page_prompts_to_complete_address_when_user_has_no_address(): void
    {
        $user = User::factory()->create([
            'recipient_name' => null,
            'recipient_phone' => null,
            'address_detail' => null,
            'default_address' => null,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.show'));

        $response->assertStatus(200);
        $response->assertSee('Alamat Pengiriman Belum Lengkap');
        $response->assertSee('Lengkapi Alamat di Pengaturan Sekarang');
        $response->assertSee('Lengkapi Alamat Dahulu Sebelum Bayar');
    }

    public function test_checkout_page_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get(route('checkout.show'));

        $response->assertRedirect(route('login'));
    }

    public function test_search_suggestions_endpoint_returns_json_matches(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('search.suggestions', ['q' => 'TWS']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'query',
            'store_query',
            'suggestions',
        ]);
        $response->assertJsonFragment([
            'query' => 'TWS',
        ]);
    }

    public function test_product_detail_page_loads_with_comprehensive_information(): void
    {
        $product = Product::firstOrFail();

        $response = $this->actingAs($this->user)->get(route('product.detail', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Kembali');
        $response->assertSee($product->name);
        $response->assertSee('Spesifikasi Produk');
        $response->assertSee('Deskripsi & Informasi Lengkap Produk', false);
        $response->assertSee('+ Keranjang');
        $response->assertSee('Beli Sekarang');

        $byIdResponse = $this->actingAs($this->user)->get('/product/'.$product->id);
        $byIdResponse->assertStatus(200);
        $byIdResponse->assertSee($product->name);
    }

    public function test_seller_store_page_loads_with_profile_and_catalog(): void
    {
        $product = Product::whereNotNull('brand')->where('brand', '!=', '')->firstOrFail();

        $response = $this->actingAs($this->user)->get(route('store.show', urlencode($product->brand)));

        $response->assertStatus(200);
        $response->assertSee($product->brand);
        $response->assertSee('Rating Toko');
        $response->assertSee('Semua Produk Toko');
        $response->assertSee($product->name);
    }

    public function test_official_brand_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('official.brand'));

        $response->assertStatus(200);
        $response->assertSee('Official Brand & Store');
        $response->assertSee('Jaminan 100% Produk Original');
    }

    public function test_search_results_with_badge_official_redirects_to_official_brand(): void
    {
        $response = $this->actingAs($this->user)->get(route('search.results', ['badge' => 'Official']));

        $response->assertRedirect(route('official.brand'));
    }

    public function test_trending_products_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('products.trending'));

        $response->assertStatus(200);
        $response->assertSee('Produk Trending');
        $response->assertSee('Semua Produk Trending');
    }

    public function test_search_results_with_sort_popular_redirects_to_trending(): void
    {
        $response = $this->actingAs($this->user)->get(route('search.results', ['sort' => 'popular']));

        $response->assertRedirect(route('products.trending'));
    }

    public function test_topup_bills_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('topup.bills'));

        $response->assertStatus(200);
        $response->assertSee('Top Up & Tagihan', false);
        $response->assertSee('Pulsa & Data', false);
        $response->assertSee('Listrik PLN');
        $response->assertSee('Air PDAM');
        $response->assertSee('BPJS');
        $response->assertSee('Internet & TV', false);
        $response->assertSee('Voucher Game');
    }

    public function test_topup_checkout_processes_payment_and_creates_receipt(): void
    {
        $payload = [
            'service_type' => 'pln_token',
            'customer_number' => '14238592019',
            'provider' => 'PT PLN (Persero)',
            'product_name' => 'Token Listrik PLN Rp 50.000',
            'amount' => 50000,
            'admin_fee' => 1500,
            'payment_method' => 'qris',
        ];

        $response = $this->actingAs($this->user)->post(route('topup.checkout'), $payload);

        $response->assertRedirect(route('topup.bills'));
        $response->assertSessionHas('topup_success');
    }

    public function test_topup_checkout_for_authenticated_user_records_audit_log(): void
    {
        $user = User::first() ?? User::factory()->create();

        $payload = [
            'service_type' => 'pulsa',
            'customer_number' => '081234567890',
            'provider' => 'Telkomsel',
            'product_name' => 'Pulsa Telkomsel Rp 25.000',
            'amount' => 25000,
            'admin_fee' => 1500,
            'payment_method' => 'qris',
        ];

        $response = $this->actingAs($user)->post(route('topup.checkout'), $payload);

        $response->assertRedirect(route('topup.bills'));
        $response->assertSessionHas('topup_success');

        $order = Order::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('completed', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertStringContainsString('ORD-PPOB-', $order->order_code);
        $this->assertEquals(26500, $order->grand_total);
        $this->assertEquals(1, $order->items()->count());

        $myOrdersResponse = $this->actingAs($user)->get(route('my.orders'));
        $myOrdersResponse->assertStatus(200);
        $myOrdersResponse->assertSee($order->order_code);
        $myOrdersResponse->assertSee('Pulsa Telkomsel');
    }

    public function test_topup_bills_page_renders_qris_only_and_does_not_contain_cod(): void
    {
        $response = $this->actingAs($this->user)->get(route('topup.bills'));
        $response->assertOk();
        $response->assertSee('QRIS Instant');
        $response->assertDontSee('COD (Bayar Tunai)');
        $response->assertDontSee('Bebas Biaya Admin');
        $response->assertDontSee('Layanan produk digital & tagihan diproses');
    }

    public function test_topup_checkout_fails_when_payment_method_is_not_qris(): void
    {
        $payload = [
            'service_type' => 'pulsa',
            'customer_number' => '081234567890',
            'provider' => 'Telkomsel',
            'product_name' => 'Pulsa Telkomsel Rp 25.000',
            'amount' => 25000,
            'admin_fee' => 1500,
            'payment_method' => 'cod',
        ];

        $response = $this->actingAs($this->user)->post(route('topup.checkout'), $payload);
        $response->assertSessionHasErrors('payment_method');
    }

    public function test_cart_page_renders_shopee_style_store_grouping_and_variant_changer(): void
    {
        $response = $this->actingAs($this->user)->get(route('cart'));
        $response->assertOk();
        $response->assertSee('Keranjang Belanja Kamu');
        $response->assertSee('storeGroups');
        $response->assertSee('openVariantModal');
        $response->assertSee('toggleStoreSelect');
    }

    public function test_api_products_variants_returns_json_with_variants_and_store_info(): void
    {
        $product = Product::factory()->create([
            'name' => 'Produk Tes Varian',
            'price' => 50000,
            'discount_price' => 45000,
        ]);

        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Warna Merah',
            'price' => 45000,
            'stock' => 15,
        ]);

        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Warna Biru',
            'price' => 48000,
            'stock' => 20,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('api.products.variants', $product));
        $response->assertOk();
        $response->assertJsonStructure([
            'product_id',
            'product_name',
            'base_price',
            'base_image',
            'store' => ['id', 'name', 'slug', 'badge', 'city'],
            'variants' => [
                '*' => ['id', 'name', 'price', 'stock', 'image_url'],
            ],
        ]);
        $response->assertJsonFragment([
            'name' => 'Warna Merah',
            'price' => 45000,
        ]);
        $response->assertJsonFragment([
            'name' => 'Warna Biru',
            'price' => 48000,
        ]);
    }
}
