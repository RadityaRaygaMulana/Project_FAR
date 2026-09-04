<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_upload_store_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $store = Store::factory()->approved()->create([
            'user_id' => $user->id,
            'name' => 'Toko Cemilan Nusantara',
            'logo' => null,
        ]);

        $logoFile = UploadedFile::fake()->image('store_logo.png', 400, 400);

        $response = $this->actingAs($user)->put(route('seller.settings.update'), [
            'name' => 'Toko Cemilan Nusantara',
            'city' => 'Bandung',
            'phone' => '081234567890',
            'description' => 'Toko resmi aneka cemilan gurih khas Bandung.',
            'address_detail' => 'Jl. Braga No. 10',
            'logo' => $logoFile,
        ]);

        $response->assertRedirect(route('seller.settings'));
        $response->assertSessionHas('success');

        $store->refresh();
        $this->assertNotNull($store->logo);
        Storage::disk('public')->assertExists($store->logo);
        $this->assertStringContainsString('storage/', $store->logo_url);
    }

    public function test_seller_can_replace_existing_store_logo_and_delete_old_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $oldFile = UploadedFile::fake()->image('old_logo.png', 300, 300);
        $oldPath = $oldFile->store('stores/logos', 'public');

        $store = Store::factory()->approved()->create([
            'user_id' => $user->id,
            'name' => 'Snack Barokah',
            'logo' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('new_logo.webp', 500, 500);

        $response = $this->actingAs($user)->put(route('seller.settings.update'), [
            'name' => 'Snack Barokah Updated',
            'city' => 'Surabaya',
            'phone' => '081299998888',
            'logo' => $newFile,
        ]);

        $response->assertRedirect(route('seller.settings'));

        $store->refresh();
        $this->assertNotEquals($oldPath, $store->logo);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($store->logo);
    }

    public function test_seller_can_remove_store_logo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('logo.jpg', 300, 300);
        $path = $file->store('stores/logos', 'public');

        $store = Store::factory()->approved()->create([
            'user_id' => $user->id,
            'name' => 'Toko Enak',
            'logo' => $path,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($user)->put(route('seller.settings.update'), [
            'name' => 'Toko Enak',
            'city' => 'Jakarta',
            'phone' => '081233334444',
            'remove_logo' => '1',
        ]);

        $response->assertRedirect(route('seller.settings'));

        $store->refresh();
        $this->assertNull($store->logo);
        $this->assertNull($store->logo_url);
        Storage::disk('public')->assertMissing($path);
        $this->assertEquals('TE', $store->initials);
    }

    public function test_logo_validation_rejects_non_image_or_oversized_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $store = Store::factory()->approved()->create(['user_id' => $user->id]);

        $fakePdf = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->put(route('seller.settings.update'), [
            'name' => 'Toko Keren',
            'city' => 'Semarang',
            'phone' => '081211112222',
            'logo' => $fakePdf,
        ]);

        $response->assertSessionHasErrors('logo');

        $oversizedImage = UploadedFile::fake()->image('huge.png')->size(4000); // 4MB > 3MB limit

        $response = $this->actingAs($user)->put(route('seller.settings.update'), [
            'name' => 'Toko Keren',
            'city' => 'Semarang',
            'phone' => '081211112222',
            'logo' => $oversizedImage,
        ]);

        $response->assertSessionHasErrors('logo');
    }

    public function test_public_store_and_product_pages_display_store_logo(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create();
        $file = UploadedFile::fake()->image('public_logo.png', 400, 400);
        $path = $file->store('stores/logos', 'public');

        $store = Store::factory()->approved()->create([
            'user_id' => $seller->id,
            'name' => 'Keripik Gurih Official',
            'logo' => $path,
        ]);

        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name' => 'Keripik Singkong Renyah',
        ]);

        // Visit public store page
        $storeResponse = $this->get(route('store.show', urlencode($store->name)));
        $storeResponse->assertOk();
        $storeResponse->assertSee($store->logo_url, false);

        // Visit product detail page
        $productResponse = $this->get(route('product.detail', $product->slug));
        $productResponse->assertOk();
        $productResponse->assertSee($store->logo_url, false);
    }
}
