<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Database\Seeders\MarketplaceSeeder;
use Database\Seeders\VoucherSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected Product $product;

    protected Voucher $shippingVoucher;

    protected Voucher $discountVoucher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MarketplaceSeeder::class);
        $this->seed(VoucherSeeder::class);

        $this->user = User::factory()->create([
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'province' => 'Jawa Barat',
            'city' => 'Kota Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'address_detail' => 'Jl. Dago No. 123',
            'default_address' => 'Jl. Dago No. 123, Coblong, Kota Bandung',
        ]);

        $this->category = Category::firstOrCreate(
            ['slug' => 'snack-makanan'],
            ['name' => 'Snack & Makanan', 'icon' => '🍿']
        );

        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Keripik Tempe Renyah',
            'price' => 50000,
            'discount_price' => null,
            'stock' => 100,
            'is_available' => true,
        ]);

        $this->shippingVoucher = Voucher::where('code', 'ONGKIRFREE')->first();
        $this->discountVoucher = Voucher::where('code', 'DISKONMEMBER')->first();
    }

    public function test_voucher_page_loads_and_displays_shipping_and_discount_vouchers(): void
    {
        $response = $this->actingAs($this->user)->get(route('voucher.index'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Voucher NusantaraMart');
        $response->assertSee('ONGKIRFREE');
        $response->assertSee('DISKONMEMBER');
        $response->assertSee('Gratis Ongkir');
        $response->assertSee('Diskon Belanja');
    }

    public function test_authenticated_user_can_claim_voucher(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('voucher.claim', $this->shippingVoucher->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_vouchers', [
            'user_id' => $this->user->id,
            'voucher_id' => $this->shippingVoucher->id,
        ]);
    }

    public function test_user_cannot_claim_same_voucher_twice(): void
    {
        UserVoucher::create([
            'user_id' => $this->user->id,
            'voucher_id' => $this->shippingVoucher->id,
            'claimed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('voucher.claim', $this->shippingVoucher->id));

        $response->assertStatus(200);
        $response->assertJson(['already_claimed' => true]);
    }

    public function test_checkout_accepts_one_shipping_and_one_discount_voucher(): void
    {
        // User buys 2 items = subtotal 100,000 (qualifies for both vouchers)
        // Shipping voucher ONGKIRFREE gives 15,000 discount on shipping
        // Discount voucher DISKONMEMBER gives 15,000 discount on items
        $response = $this->actingAs($this->user)
            ->post(route('checkout'), [
                'customer_name' => $this->user->recipient_name,
                'customer_phone' => $this->user->recipient_phone,
                'customer_address' => $this->user->default_address,
                'payment_method' => 'qris',
                'shipping_voucher_code' => 'ONGKIRFREE',
                'discount_voucher_code' => 'DISKONMEMBER',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response->assertRedirect(route('my.orders'));

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('ONGKIRFREE', $order->shipping_voucher_code);
        $this->assertEquals('DISKONMEMBER', $order->discount_voucher_code);
        $this->assertEquals(15000, $order->discount_amount);
        $this->assertEquals(100000, $order->total_amount);

        // Verify UserVoucher records were marked as used
        $this->assertDatabaseHas('user_vouchers', [
            'user_id' => $this->user->id,
            'voucher_id' => $this->shippingVoucher->id,
            'order_id' => $order->id,
        ]);
        $this->assertDatabaseHas('user_vouchers', [
            'user_id' => $this->user->id,
            'voucher_id' => $this->discountVoucher->id,
            'order_id' => $order->id,
        ]);
    }

    public function test_checkout_rejects_voucher_when_category_is_mismatched(): void
    {
        // Try passing a discount voucher code in the shipping_voucher_code field
        $response = $this->actingAs($this->user)
            ->post(route('checkout'), [
                'customer_name' => $this->user->recipient_name,
                'customer_phone' => $this->user->recipient_phone,
                'customer_address' => $this->user->default_address,
                'payment_method' => 'qris',
                'shipping_voucher_code' => 'DISKONMEMBER', // Invalid! DISKONMEMBER is discount, not shipping
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('shipping_voucher_code');
    }

    public function test_checkout_validates_minimum_spend(): void
    {
        // SUPERHEMAT requires min spend Rp 100,000.
        // Buy only 1 item = subtotal Rp 50,000 (does not meet min_spend).
        $response = $this->actingAs($this->user)
            ->post(route('checkout'), [
                'customer_name' => $this->user->recipient_name,
                'customer_phone' => $this->user->recipient_phone,
                'customer_address' => $this->user->default_address,
                'payment_method' => 'qris',
                'discount_voucher_code' => 'SUPERHEMAT',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1, // 50,000 < 100,000
                    ],
                ],
            ]);

        $response->assertRedirect(route('my.orders'));

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        // Discount should be 0 because min_spend was not met
        $this->assertEquals(0, $order->discount_amount);
    }

    public function test_checkout_supports_separate_redeem_code_and_voucher_concurrently(): void
    {
        // 2 items * 50,000 = 100,000
        // Shipping voucher = 15,000 discount
        // Discount voucher = 15,000 discount
        // Redeem code SNACKSERU = 10,000 discount
        $response = $this->actingAs($this->user)
            ->post(route('checkout'), [
                'customer_name' => $this->user->recipient_name,
                'customer_phone' => $this->user->recipient_phone,
                'customer_address' => $this->user->default_address,
                'payment_method' => 'qris',
                'shipping_voucher_code' => 'ONGKIRFREE',
                'discount_voucher_code' => 'DISKONMEMBER',
                'redeem_code' => 'SNACKSERU',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response->assertRedirect(route('my.orders'));

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('ONGKIRFREE', $order->shipping_voucher_code);
        $this->assertEquals('DISKONMEMBER', $order->discount_voucher_code);
        $this->assertEquals('SNACKSERU', $order->redeem_code);
        $this->assertEquals(10000, $order->redeem_discount_amount);
        $this->assertEquals(25000, $order->discount_amount); // 15,000 voucher + 10,000 redeem
        $this->assertEquals(75000, $order->grand_total); // 100,000 - 25,000
    }

    public function test_checkout_supports_redeem_code_only(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('checkout'), [
                'customer_name' => $this->user->recipient_name,
                'customer_phone' => $this->user->recipient_phone,
                'customer_address' => $this->user->default_address,
                'payment_method' => 'qris',
                'redeem_code' => 'SNACKSERU',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response->assertRedirect(route('my.orders'));

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertNull($order->shipping_voucher_code);
        $this->assertNull($order->discount_voucher_code);
        $this->assertEquals('SNACKSERU', $order->redeem_code);
        $this->assertEquals(10000, $order->discount_amount);
    }

    public function test_checkout_view_displays_both_voucher_slots_and_redeem_code_card(): void
    {
        $response = $this->actingAs($this->user)->get(route('checkout'));

        $response->assertStatus(200);
        $response->assertSee('Voucher NusantaraMart');
        $response->assertSee('Kode Redeem Promo');
        $response->assertSee('Potongan Kode Redeem');
    }
}
