<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreAdvantagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_update_store_advantages(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->approved()->create([
            'user_id' => $user->id,
            'name' => 'Snack Mantap Abadi',
        ]);

        $customAdvantages = "Bahan baku segar pilihan organik.\nGaransi renyah sampai tujuan atau ganti baru.\nFree tester cemilan setiap pembelian.";

        $response = $this->actingAs($user)->put(route('seller.settings.update'), [
            'name' => 'Snack Mantap Abadi',
            'city' => 'Bandung',
            'phone' => '081234567890',
            'advantages' => $customAdvantages,
        ]);

        $response->assertRedirect(route('seller.settings'));
        $response->assertSessionHas('success');

        $store->refresh();
        $this->assertEquals($customAdvantages, $store->advantages);
        $this->assertCount(3, $store->advantages_list);
        $this->assertEquals('Bahan baku segar pilihan organik.', $store->advantages_list[0]);
    }

    public function test_product_detail_displays_custom_store_advantages(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create([
            'user_id' => $seller->id,
            'name' => 'Toko Barokah Jaya',
            'advantages' => "Garansi uang kembali 100% jika produk cacat.\nDipacking kayu khusus barang pecah belah.",
        ]);

        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name' => 'Keripik Tempe Renyah Gurih',
            'description' => 'Deskripsi keripik tempe istimewa warisan leluhur.',
        ]);

        $response = $this->actingAs($seller)->get(route('product.detail', $product->slug));
        $response->assertOk();
        $response->assertSee('Keunggulan Belanja di Toko Barokah Jaya:');
        $response->assertSee('Garansi uang kembali 100% jika produk cacat.');
        $response->assertSee('Dipacking kayu khusus barang pecah belah.');
        $response->assertSee('Deskripsi keripik tempe istimewa warisan leluhur.');
    }

    public function test_product_detail_falls_back_to_default_advantages_when_not_set(): void
    {
        $seller = User::factory()->create();
        $store = Store::factory()->approved()->create([
            'user_id' => $seller->id,
            'name' => 'Toko Standar',
            'advantages' => null,
        ]);

        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name' => 'Kerupuk Ikan Gurih',
        ]);

        $response = $this->actingAs($seller)->get(route('product.detail', $product->slug));
        $response->assertOk();
        $response->assertSee('Keunggulan Belanja di Toko Standar:');
        $response->assertSee('Produk 100% Original langsung dari distributor', false);
        $response->assertSee('Pengemasan aman menggunakan kardus tebal', false);
    }
}
