<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_user_is_forbidden_from_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard_and_see_system_operations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSeeText('Pusat Operasi & Pemeliharaan Sistem');
        $response->assertSeeText('Pembersihan Cache Sistem');
        $response->assertSeeText('Optimasi & Vacuum Database');
    }

    public function test_admin_can_clear_cache_and_optimize_database(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $cacheResponse = $this->actingAs($admin)->post(route('admin.cache.clear'));
        $cacheResponse->assertSessionHas('admin_success');

        $dbResponse = $this->actingAs($admin)->post(route('admin.database.optimize'));
        $dbResponse->assertSessionHas('admin_success');
    }

    public function test_admin_can_view_logs_and_clear_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Write a test log entry
        $logFile = storage_path('logs/laravel.log');
        File::append($logFile, '['.now()->format('Y-m-d H:i:s')."] local.INFO: Test Admin System Log Entry\n");

        $logsResponse = $this->actingAs($admin)->get(route('admin.logs'));
        $logsResponse->assertStatus(200);
        $logsResponse->assertSeeText('Catatan Log Sistem');
        $logsResponse->assertSeeText('Test Admin System Log Entry');

        // Real-time JSON Feed
        $feedResponse = $this->actingAs($admin)->getJson(route('admin.logs.feed').'?feed=1');
        $feedResponse->assertStatus(200);
        $feedResponse->assertJsonStructure(['entries', 'logSize', 'timestamp']);

        // Clear logs
        $clearResponse = $this->actingAs($admin)->post(route('admin.logs.clear'));
        $clearResponse->assertSessionHas('admin_success');
    }

    public function test_admin_can_manage_users_verify_reset_password_and_delete(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => null,
        ]);

        // Manage Users list
        $usersResponse = $this->actingAs($admin)->get(route('admin.users'));
        $usersResponse->assertStatus(200);
        $usersResponse->assertSee($customer->name);

        // Switch role via edit/update (Jadikan Admin)
        $updateResponse = $this->actingAs($admin)->put(route('admin.users.update', $customer), [
            'name' => 'Customer Updated',
            'username' => 'customer_updated',
            'email' => 'updated@example.com',
            'phone' => '08123456789',
            'role' => 'admin',
        ]);
        $updateResponse->assertSessionHas('admin_success');
        $this->assertEquals('admin', $customer->fresh()->role);
        $this->assertEquals('Customer Updated', $customer->fresh()->name);

        // Switch role via direct patch
        $roleResponse = $this->actingAs($admin)->patch(route('admin.users.role', $customer), [
            'role' => 'customer',
        ]);
        $roleResponse->assertSessionHas('admin_success');
        $this->assertEquals('customer', $customer->fresh()->role);

        // Verify User manually
        $verifyResponse = $this->actingAs($admin)->post(route('admin.users.verify', $customer));
        $verifyResponse->assertSessionHas('admin_success');
        $this->assertNotNull($customer->fresh()->email_verified_at);

        // Reset password
        $resetResponse = $this->actingAs($admin)->post(route('admin.users.reset-password', $customer), [
            'new_password' => 'newSecretPass99',
        ]);
        $resetResponse->assertSessionHas('admin_success');
        $this->assertTrue(Hash::check('newSecretPass99', $customer->fresh()->password));

        // Delete user
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.users.destroy', $customer));
        $deleteResponse->assertSessionHas('admin_success');
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }

    public function test_admin_login_bypasses_otp_code_verification(): void
    {
        $admin = User::factory()->create([
            'username' => 'superadmin',
            'email' => 'superadmin@snackaroo.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => null,
        ]);

        $response = $this->post(route('login'), [
            'login' => 'superadmin',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh()->email_verified_at);
    }

    public function test_audit_logger_records_system_actions_in_logs(): void
    {
        $logFile = storage_path('logs/laravel.log');
        File::put($logFile, '');

        $customer = User::factory()->create([
            'username' => 'audittester',
            'email' => 'audit@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        // Login audit log
        $this->post(route('login'), [
            'login' => 'audittester',
            'password' => 'secret123',
        ]);

        $logContent = File::get($logFile);
        $this->assertStringContainsString('[AUDIT:AUTH] Login Sukses', $logContent);
        $this->assertStringContainsString('audittester', $logContent);

        // Logout audit log
        $this->post(route('logout'));
        $logContent = File::get($logFile);
        $this->assertStringContainsString('[AUDIT:AUTH] Logout', $logContent);
    }

    public function test_admin_can_suspend_user_with_duration_and_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'username' => 'suspendee',
            'role' => 'customer',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.suspend', $customer), [
            'suspension_reason' => 'Melanggar aturan komunitas terkait spam.',
            'duration' => '7_days',
        ]);

        $response->assertRedirect();
        $fresh = $customer->fresh();
        $this->assertTrue((bool) $fresh->is_suspended);
        $this->assertSame('Melanggar aturan komunitas terkait spam.', $fresh->suspension_reason);
        $this->assertNotNull($fresh->suspended_until);
        $this->assertTrue($fresh->suspended_until->isFuture());

        // Login attempt blocked
        $this->post(route('logout'));
        $loginResponse = $this->post(route('login'), [
            'login' => 'suspendee',
            'password' => 'password123',
        ]);
        $loginResponse->assertSessionHas('suspended', true);
        $loginResponse->assertSessionHas('suspension_reason');
    }

    public function test_user_can_delete_own_account_with_correct_password(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'password' => Hash::make('mypassword123'),
        ]);

        $response = $this->actingAs($customer)->delete(route('settings.delete_account'), [
            'password' => 'mypassword123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }
}
