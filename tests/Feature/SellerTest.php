<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_seller_registration_landing_page(): void
    {
        $response = $this->get(route('seller.register'));

        $response->assertStatus(200);
        $response->assertSee('Program Mitra Penjual NusantaraMart');
        $response->assertSee('Buka Toko & Jual Produkmu di NusantaraMart', false);
    }

    public function test_authenticated_user_can_submit_store_application(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('seller.register.submit'), [
            'name' => 'Toko Sejahtera Berkah',
            'ktp_nik' => '3201123456780001',
            'ktp_name' => 'Raditya Pratama',
            'ktp_photo' => UploadedFile::fake()->image('ktp.jpg', 600, 400),
            'city' => 'Kota Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'phone' => '081298765432',
            'description' => 'Toko resmi yang menjual aneka kebutuhan rumah tangga dan gadget.',
            'address_detail' => 'Jl. Kemang Raya No. 45',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('seller.register'));
        $this->assertDatabaseHas('stores', [
            'user_id' => $user->id,
            'name' => 'Toko Sejahtera Berkah',
            'ktp_nik' => '3201123456780001',
            'ktp_name' => 'Raditya Pratama',
            'status' => 'pending',
        ]);

        $store = Store::where('user_id', $user->id)->first();
        $this->assertNotNull($store->ktp_photo_path);
        Storage::disk('public')->assertExists($store->ktp_photo_path);
    }

    public function test_submission_fails_if_ktp_is_missing_or_invalid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('seller.register.submit'), [
            'name' => 'Toko Sejahtera Berkah',
            'ktp_nik' => '12345', // invalid digits
            // ktp_name missing
            // ktp_photo missing
            'city' => 'Kota Jakarta Selatan',
            'phone' => '081298765432',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['ktp_nik', 'ktp_name', 'ktp_photo']);
    }

    public function test_submission_fails_if_terms_not_accepted(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('seller.register.submit'), [
            'name' => 'Toko Sejahtera Berkah',
            'ktp_nik' => '3201123456780001',
            'ktp_name' => 'Raditya Pratama',
            'ktp_photo' => UploadedFile::fake()->image('ktp.jpg'),
            'city' => 'Kota Jakarta Selatan',
            'phone' => '081298765432',
            // terms missing
        ]);

        $response->assertSessionHasErrors(['terms']);
    }

    public function test_user_sees_pending_status_when_store_is_pending(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'name' => 'Toko Kopi Asli Nusantara',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('seller.register'));

        $response->assertStatus(200);
        $response->assertSee('Menunggu Persetujuan Admin');
        $response->assertSee('Pengajuan Toko Sedang Ditinjau');
        $response->assertSee('Toko Kopi Asli Nusantara');
    }

    public function test_admin_can_view_stores_moderation_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = Store::factory()->create(['name' => 'Toko Elektronik Hebat']);

        $response = $this->actingAs($admin)->get(route('admin.stores'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi & Pengajuan Toko Penjual', false);
        $response->assertSee('Toko Elektronik Hebat');
    }

    public function test_admin_can_approve_store_and_user_becomes_seller(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.stores.approve', $store->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'seller',
        ]);
    }

    public function test_admin_can_reject_store_with_rejection_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'customer']);
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.stores.reject', $store->id), [
            'rejection_reason' => 'Nomor WhatsApp tidak aktif saat dihubungi dan alamat pengiriman tidak jelas.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'status' => 'rejected',
            'rejection_reason' => 'Nomor WhatsApp tidak aktif saat dihubungi dan alamat pengiriman tidak jelas.',
        ]);
    }

    public function test_user_sees_rejection_reason_and_can_reapply(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->rejected('Nama toko melanggar hak cipta merek lain.')->create([
            'user_id' => $user->id,
            'name' => 'Toko Apple KW Super',
        ]);

        $response = $this->actingAs($user)->get(route('seller.register'));

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Toko Belum Disetujui oleh Admin');
        $response->assertSee('Nama toko melanggar hak cipta merek lain.');

        // Reapply
        $reapplyResponse = $this->actingAs($user)->post(route('seller.register.submit'), [
            'name' => 'Toko Gadget Murah Jaya',
            'ktp_nik' => '3201998877660001',
            'ktp_name' => 'Budi Reapply',
            'city' => 'Kota Surabaya',
            'phone' => '082199887766',
            'description' => 'Menjual aksesoris gadget original.',
            'terms' => '1',
        ]);

        $reapplyResponse->assertRedirect(route('seller.register'));
        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'Toko Gadget Murah Jaya',
            'status' => 'pending',
            'rejection_reason' => null,
        ]);
    }

    public function test_approved_seller_can_access_seller_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('seller.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Seller Center');
        $response->assertSee($store->name);
    }

    public function test_unapproved_user_is_redirected_away_from_seller_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('seller.dashboard'));

        $response->assertRedirect(route('seller.register'));
    }

    public function test_seller_can_create_new_real_product(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create([
            'user_id' => $user->id,
            'name' => 'Erigo Official Store',
        ]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('seller.products.store'), [
            'category_id' => $category->id,
            'name' => 'Jaket Coach Erigo Vintage Windbreaker',
            'description' => 'Jaket coach windbreaker original berbahan taslan premium anti air dan nyaman dipakai.',
            'price' => 350000,
            'discount_price' => 245000,
            'stock' => 25,
            'weight_grams' => 450,
            'spiciness_level' => 0,
            'is_available' => '1',
        ]);

        $response->assertRedirect(route('seller.products.index'));
        $this->assertDatabaseHas('products', [
            'store_id' => $store->id,
            'brand' => 'Erigo Official Store',
            'name' => 'Jaket Coach Erigo Vintage Windbreaker',
            'price' => 350000,
            'discount_price' => 245000,
            'stock' => 25,
        ]);
    }

    public function test_seller_can_edit_their_product(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Produk Awal Toko',
            'price' => 100000,
            'stock' => 10,
        ]);

        $response = $this->actingAs($user)->put(route('seller.products.update', $product->id), [
            'category_id' => $category->id,
            'name' => 'Produk Setelah Diedit',
            'description' => 'Deskripsi baru yang jauh lebih lengkap dan jelas untuk calon pembeli.',
            'price' => 120000,
            'discount_price' => 99000,
            'stock' => 30,
            'weight_grams' => 200,
            'is_available' => '1',
        ]);

        $response->assertRedirect(route('seller.products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Produk Setelah Diedit',
            'price' => 120000,
            'discount_price' => 99000,
            'stock' => 30,
        ]);
    }

    public function test_seller_cannot_edit_another_seller_product(): void
    {
        $user1 = User::factory()->create(['role' => 'seller']);
        $store1 = Store::factory()->approved()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create(['role' => 'seller']);
        $store2 = Store::factory()->approved()->create(['user_id' => $user2->id]);

        $product = Product::factory()->create(['store_id' => $store2->id]);

        $response = $this->actingAs($user1)->get(route('seller.products.edit', $product->id));
        $response->assertStatus(403);
    }

    public function test_seller_can_delete_their_product(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name' => 'Produk Mau Dihapus',
        ]);

        $response = $this->actingAs($user)->delete(route('seller.products.destroy', $product->id));

        $response->assertRedirect(route('seller.products.index'));
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_seller_can_create_product_with_multiple_gallery_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();

        $file1 = UploadedFile::fake()->image('photo1.jpg');
        $file2 = UploadedFile::fake()->image('photo2.jpg');
        $file3 = UploadedFile::fake()->image('photo3.jpg');

        $response = $this->actingAs($user)->post(route('seller.products.store'), [
            'category_id' => $category->id,
            'name' => 'Kacamata Anti-Radiasi Multi Foto',
            'description' => 'Kacamata anti radiasi dengan foto utama dan foto galeri lengkap.',
            'price' => 100000,
            'discount_price' => 39000,
            'stock' => 50,
            'weight_grams' => 250,
            'is_available' => '1',
            'images' => [$file1, $file2, $file3],
        ]);

        $response->assertRedirect(route('seller.products.index'));

        $product = Product::where('name', 'Kacamata Anti-Radiasi Multi Foto')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_path);
        $this->assertIsArray($product->gallery_images);
        $this->assertCount(3, $product->gallery_images);
        $this->assertCount(3, $product->allImageUrls());
    }

    public function test_product_detail_page_displays_real_store_stats_and_location(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create([
            'user_id' => $user->id,
            'name' => 'Kairo Authentic Gear',
            'city' => 'Bandung Barat',
            'rating' => 4.8,
        ]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Kacamata Vintage Titanium',
            'brand' => 'Kairo Authentic Gear',
            'sold_count' => 14,
        ]);

        $response = $this->get(route('product.detail', ['product' => $product->slug]));

        $response->assertStatus(200);
        $response->assertSee('Bandung Barat');
        $response->assertSee('★ 4.8');
        $response->assertSee('14 Terjual');
        $response->assertDontSee('📍 Jakarta Selatan');
    }

    public function test_seller_can_view_orders_page_with_correct_unit_price(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name' => 'Kacamata Anti-Radiasi (Kacamata + Box) (Hitam)',
            'price' => 39000,
        ]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-TEST',
            'customer_name' => 'Gerry Pratama',
            'customer_phone' => '085139132952',
            'customer_address' => 'Jl. Mawar No. 12, Bandung',
            'customer_notes' => 'Tolong bubble wrap tebal ya',
            'total_amount' => 39000,
            'shipping_cost' => 10000,
            'grand_total' => 49000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Kacamata Anti-Radiasi (Kacamata + Box) (Hitam)',
            'quantity' => 1,
            'unit_price' => 39000,
            'subtotal' => 39000,
        ]);

        $response = $this->actingAs($user)->get(route('seller.orders'));

        $response->assertStatus(200);
        $response->assertSee('Pesanan Masuk Toko');
        $response->assertSee('#SNK-20260904-TEST');
        $response->assertSee('Gerry Pratama');
        $response->assertSee('085139132952');
        $response->assertSee('@ Rp 39.000');
        $response->assertDontSee('@ Rp 0');
        $response->assertSee('Terima & Proses Pesanan', false);
    }

    public function test_seller_can_process_order_to_processing(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-PROC',
            'customer_name' => 'Gerry',
            'customer_phone' => '085139132952',
            'customer_address' => 'Jl. Mawar No. 12',
            'total_amount' => 39000,
            'grand_total' => 39000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 39000,
            'subtotal' => 39000,
        ]);

        $response = $this->actingAs($user)->patch(route('seller.orders.update-status', $order->id), [
            'status' => 'processing',
        ]);

        $response->assertRedirect();
        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_seller_can_ship_order_with_courier_and_tracking_number(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-SHIP',
            'customer_name' => 'Gerry',
            'customer_phone' => '085139132952',
            'customer_address' => 'Jl. Mawar No. 12',
            'total_amount' => 39000,
            'grand_total' => 39000,
            'status' => 'processing',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 39000,
            'subtotal' => 39000,
        ]);

        $response = $this->actingAs($user)->patch(route('seller.orders.update-status', $order->id), [
            'status' => 'shipped',
            'shipping_courier' => 'J&T Express',
            'tracking_number' => 'JT9876543210',
        ]);

        $response->assertRedirect();
        $freshOrder = $order->fresh();
        $this->assertEquals('shipped', $freshOrder->status);
        $this->assertEquals('J&T Express', $freshOrder->shipping_courier);
        $this->assertEquals('JT9876543210', $freshOrder->tracking_number);
    }

    public function test_seller_can_complete_order(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['store_id' => $store->id]);

        $order = Order::create([
            'order_code' => 'SNK-20260904-DONE',
            'customer_name' => 'Gerry',
            'customer_phone' => '085139132952',
            'customer_address' => 'Jl. Mawar No. 12',
            'total_amount' => 39000,
            'grand_total' => 39000,
            'status' => 'shipped',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 39000,
            'subtotal' => 39000,
        ]);

        $response = $this->actingAs($user)->patch(route('seller.orders.update-status', $order->id), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $freshOrder = $order->fresh();
        $this->assertEquals('completed', $freshOrder->status);
        $this->assertEquals('paid', $freshOrder->payment_status);
    }

    public function test_seller_cannot_update_order_of_another_store(): void
    {
        $user1 = User::factory()->create(['role' => 'seller']);
        $store1 = Store::factory()->approved()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create(['role' => 'seller']);
        $store2 = Store::factory()->approved()->create(['user_id' => $user2->id]);
        $product2 = Product::factory()->create(['store_id' => $store2->id]);

        $order = Order::create([
            'order_code' => 'SNK-OTHER-STORE',
            'customer_name' => 'Buyer',
            'customer_phone' => '08123456789',
            'customer_address' => 'Address',
            'total_amount' => 50000,
            'grand_total' => 50000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($user1)->patch(route('seller.orders.update-status', $order->id), [
            'status' => 'processing',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->status);
    }
}
