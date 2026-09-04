<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Selamat Datang Kembali');
    }

    public function test_register_page_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Buat Akun NusantaraMart');
    }

    public function test_user_can_register_successfully(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'username' => 'budisnack',
            'email' => 'budi@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'username' => 'budisnack',
            'email' => 'budi@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_user_can_verify_email_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'otp_code' => '123456',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->post('/verify-email', [
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNull($user->fresh()->otp_code);
    }

    public function test_user_can_login_with_username(): void
    {
        $user = User::factory()->create([
            'username' => 'rian_keripik',
            'email' => 'rian@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'rian_keripik',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'username' => 'citra_basreng',
            'email' => 'citra@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'citra@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_login_always_redirects_to_home_even_if_intended_url_is_present(): void
    {
        $user = User::factory()->create([
            'username' => 'donny_snack',
            'email' => 'donny@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->withSession(['url.intended' => '/checkout'])
            ->post('/login', [
                'login' => 'donny_snack',
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('correct_pass'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'testuser',
            'password' => 'wrong_pass',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_guest_cannot_checkout_without_login(): void
    {
        $category = Category::create([
            'name' => 'Keripik',
            'slug' => 'keripik',
            'icon' => '🥔',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Basreng Pedas',
            'slug' => 'basreng-pedas',
            'price' => 15000,
            'weight_grams' => 200,
            'spiciness_level' => 4,
            'stock' => 50,
            'is_available' => true,
        ]);

        $response = $this->post('/checkout', [
            'customer_name' => 'Guest User',
            'customer_phone' => '08123456789',
            'customer_address' => 'Jl. Merdeka No 1',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_authenticated_user_checkout_creates_order_linked_to_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Rian Pratama',
            'username' => 'rianpratama',
        ]);

        $category = Category::create([
            'name' => 'Keripik',
            'slug' => 'keripik',
            'icon' => '🥔',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Keripik Kaca Pedas Daun Jeruk',
            'slug' => 'keripik-kaca-pedas',
            'price' => 13500,
            'weight_grams' => 150,
            'spiciness_level' => 5,
            'stock' => 20,
            'is_available' => true,
        ]);

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Rian Pratama',
            'customer_phone' => '08123456789',
            'customer_address' => 'Jl. Dago No. 10, Bandung',
            'customer_notes' => 'Tolong pedasnya level maksimal',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'Rian Pratama',
            'total_amount' => 27000,
            'grand_total' => 42000, // 27000 + 15000 ongkir
        ]);

        $order = $user->orders()->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('my.orders'));

        // Check My Orders page
        $myOrdersResponse = $this->actingAs($user)->get('/my-orders');
        $myOrdersResponse->assertStatus(200);
        $myOrdersResponse->assertSee($order->order_code);
        $myOrdersResponse->assertSee('Keripik Kaca Pedas Daun Jeruk');
    }

    public function test_prevent_back_history_headers_are_applied_to_web_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('must-revalidate', $response->headers->get('Cache-Control'));
        $this->assertEquals('no-cache', $response->headers->get('Pragma'));
        $this->assertEquals('Fri, 01 Jan 1990 00:00:00 GMT', $response->headers->get('Expires'));
    }

    public function test_user_cannot_access_protected_route_after_logout(): void
    {
        $user = User::factory()->create();

        // 1. Logged in accessing protected settings
        $response = $this->actingAs($user)->get(route('settings'));
        $response->assertStatus(200);

        // 2. Logout
        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect(route('home'));
        $this->assertGuest();

        // 3. Attempting to back or visit settings again
        $backResponse = $this->get(route('settings'));
        $backResponse->assertRedirect(route('login'));
    }
}
