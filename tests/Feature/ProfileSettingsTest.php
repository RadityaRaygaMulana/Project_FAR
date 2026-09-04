<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_settings_page_requires_authentication(): void
    {
        $response = $this->get(route('settings'));

        $response->assertRedirect(route('login'));
    }

    public function test_settings_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun & Profil');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_user_can_update_profile_and_default_address(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->put(route('settings.profile'), [
            'name' => 'Rian Updated',
            'username' => 'rian_updated',
            'email' => 'rian_updated@snackaroo.com',
            'phone' => '081298765432',
            'default_address' => 'Jl. Dago No. 100, Bandung',
            'favorite_spiciness' => 5,
        ]);

        $response->assertRedirect(route('settings'));
        $response->assertSessionHas('profile_success');

        $user->refresh();
        $this->assertEquals('Rian Updated', $user->name);
        $this->assertEquals('rian_updated', $user->username);
        $this->assertEquals('rian_updated@snackaroo.com', $user->email);
        $this->assertEquals('081298765432', $user->phone);
        $this->assertEquals('Jl. Dago No. 100, Bandung', $user->default_address);
        $this->assertEquals(5, $user->favorite_spiciness);
    }

    public function test_user_cannot_update_profile_with_duplicate_email(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1)->put(route('settings.profile'), [
            'name' => 'Duplicate Test',
            'username' => 'duplicate_user',
            'email' => $user2->email, // Email already taken by user2
            'favorite_spiciness' => 3,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('settings.password'), [
            'current_password' => 'oldpassword123',
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertRedirect(route('settings'));
        $response->assertSessionHas('password_success');

        $this->assertTrue(Hash::check('newsecretpassword123', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('settings.password'), [
            'current_password' => 'wrongpassword123',
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('correctpassword123', $user->fresh()->password));
    }

    public function test_user_can_upload_avatar_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'customer']);
        $file = UploadedFile::fake()->image('my_avatar.png', 200, 200);

        $response = $this->actingAs($user)->put(route('settings.profile'), [
            'name' => 'Avatar User',
            'username' => 'avatar_user',
            'email' => 'avatar@example.com',
            'phone' => '08123456789',
            'avatar' => $file,
            'favorite_spiciness' => 3,
        ]);

        $response->assertRedirect(route('settings'));
        $response->assertSessionHas('profile_success');

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertNotNull($user->avatar_url);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_user_can_update_structured_shipping_address(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->put(route('settings.address'), [
            'address_label' => 'Kantor',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Kebayoran Baru',
            'postal_code' => '12160',
            'address_detail' => 'Gedung Menara Mandiri Lt. 15, Jl. Jend. Sudirman Kav. 54-55',
            'map_notes' => 'Titip di resepsionis lobby timur',
        ]);

        $response->assertRedirect(route('settings'));
        $response->assertSessionHas('address_success');

        $user->refresh();
        $this->assertEquals('Kantor', $user->address_label);
        $this->assertEquals('Budi Santoso', $user->recipient_name);
        $this->assertEquals('081234567890', $user->recipient_phone);
        $this->assertEquals('DKI Jakarta', $user->province);
        $this->assertEquals('Jakarta Selatan', $user->city);
        $this->assertEquals('Kebayoran Baru', $user->district);
        $this->assertEquals('12160', $user->postal_code);
        $this->assertStringContainsString('Gedung Menara Mandiri', $user->formatted_address);
        $this->assertEquals('Titip di resepsionis lobby timur', $user->map_notes);
    }

    public function test_user_can_update_address_with_return_to_checkout(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->put(route('settings.address'), [
            'return_to' => 'checkout',
            'address_label' => 'Rumah',
            'recipient_name' => 'Citra Lestari',
            'recipient_phone' => '081299887766',
            'province' => 'Jawa Barat',
            'city' => 'Kota Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'address_detail' => 'Jl. Dipati Ukur No. 10',
        ]);

        $response->assertRedirect(route('checkout.show'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Citra Lestari', $user->recipient_name);
        $this->assertTrue($user->hasCompleteAddress());
    }
}
