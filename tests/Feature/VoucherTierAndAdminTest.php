<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTierAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_member_tier_and_tier_requirements_work_correctly(): void
    {
        $silverUser = User::factory()->create(['role' => 'customer']);
        $this->assertEquals('silver', $silverUser->member_tier);
        $this->assertTrue($silverUser->meetsTierRequirement('silver'));
        $this->assertFalse($silverUser->meetsTierRequirement('gold'));
        $this->assertFalse($silverUser->meetsTierRequirement('platinum'));

        // Gold user: >= 3 completed orders
        $goldUser = User::factory()->create(['role' => 'customer']);
        for ($i = 0; $i < 3; $i++) {
            Order::factory()->create([
                'user_id' => $goldUser->id,
                'status' => 'completed',
                'grand_total' => 100000,
            ]);
        }
        $this->assertEquals('gold', $goldUser->member_tier);
        $this->assertTrue($goldUser->meetsTierRequirement('silver'));
        $this->assertTrue($goldUser->meetsTierRequirement('gold'));
        $this->assertFalse($goldUser->meetsTierRequirement('platinum'));

        // Platinum user: role admin or >= 10 orders
        $adminUser = User::factory()->create(['role' => 'admin']);
        $this->assertEquals('platinum', $adminUser->member_tier);
        $this->assertTrue($adminUser->meetsTierRequirement('silver'));
        $this->assertTrue($adminUser->meetsTierRequirement('gold'));
        $this->assertTrue($adminUser->meetsTierRequirement('platinum'));
    }

    public function test_silver_member_cannot_claim_gold_or_platinum_voucher(): void
    {
        $silverUser = User::factory()->create(['role' => 'customer']);

        $goldVoucher = Voucher::create([
            'code' => 'GOLDEXCLUSIVE',
            'name' => 'Diskon Eksklusif Member Gold',
            'category' => 'discount',
            'type' => 'fixed',
            'reward_amount' => 20000,
            'min_spend' => 50000,
            'quota' => 100,
            'member_tier' => 'gold',
            'is_active' => true,
        ]);

        $response = $this->actingAs($silverUser)->postJson(route('voucher.claim', $goldVoucher));
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        $this->assertDatabaseMissing('user_vouchers', [
            'user_id' => $silverUser->id,
            'voucher_id' => $goldVoucher->id,
        ]);
    }

    public function test_platinum_member_can_claim_gold_and_platinum_voucher(): void
    {
        $platinumUser = User::factory()->create(['role' => 'customer']);
        for ($i = 0; $i < 10; $i++) {
            Order::factory()->create([
                'user_id' => $platinumUser->id,
                'status' => 'completed',
                'grand_total' => 100000,
            ]);
        }

        $platinumVoucher = Voucher::create([
            'code' => 'PLATINUMVIP',
            'name' => 'Diskon VIP Platinum',
            'category' => 'discount',
            'type' => 'fixed',
            'reward_amount' => 50000,
            'min_spend' => 100000,
            'quota' => 50,
            'member_tier' => 'platinum',
            'is_active' => true,
        ]);

        $response = $this->actingAs($platinumUser)->postJson(route('voucher.claim', $platinumVoucher));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('user_vouchers', [
            'user_id' => $platinumUser->id,
            'voucher_id' => $platinumVoucher->id,
        ]);
    }

    public function test_weekly_recurring_voucher_schedule_restriction(): void
    {
        $user = User::factory()->create();

        // Create a weekly voucher whose active day rule is NOT today
        // If today is Monday, set rule to 'friday', else set rule to 'monday'
        $notToday = now()->isMonday() ? 'friday' : 'monday';

        $weeklyVoucher = Voucher::create([
            'code' => 'NOTTODAYVOUCHER',
            'name' => 'Voucher Khusus Hari Tertentu',
            'category' => 'discount',
            'type' => 'fixed',
            'reward_amount' => 10000,
            'min_spend' => 0,
            'quota' => 500,
            'member_tier' => 'all',
            'is_weekly_recurring' => true,
            'weekly_day_rule' => $notToday,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('voucher.claim', $weeklyVoucher));
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Voucher mingguan ini hanya aktif pada periode hari yang telah ditentukan.',
        ]);
    }

    public function test_guest_and_regular_user_cannot_access_admin_vouchers(): void
    {
        $guestResponse = $this->get(route('admin.vouchers.index'));
        $guestResponse->assertRedirect(route('login'));

        $regularUser = User::factory()->create(['role' => 'customer']);
        $userResponse = $this->actingAs($regularUser)->get(route('admin.vouchers.index'));
        $userResponse->assertStatus(403);
    }

    public function test_admin_can_manage_vouchers_with_tier_and_weekly_recurring(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Index page
        $indexResponse = $this->actingAs($admin)->get(route('admin.vouchers.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Manajemen Voucher');

        // 2. Create voucher
        $createPayload = [
            'code' => 'WEEKLYGOLD50',
            'name' => 'Voucher Mingguan Khusus Gold',
            'category' => 'discount',
            'type' => 'percentage',
            'reward_amount' => 25,
            'max_discount' => 30000,
            'min_spend' => 50000,
            'quota' => 500,
            'member_tier' => 'gold',
            'is_weekly_recurring' => 1,
            'weekly_day_rule' => 'weekdays',
            'is_active' => 1,
            'description' => 'Voucher rutin setiap hari kerja khusus member Gold & Platinum.',
        ];

        $storeResponse = $this->actingAs($admin)->post(route('admin.vouchers.store'), $createPayload);
        $storeResponse->assertRedirect(route('admin.vouchers.index'));

        $voucher = Voucher::where('code', 'WEEKLYGOLD50')->firstOrFail();
        $this->assertEquals('gold', $voucher->member_tier);
        $this->assertTrue($voucher->is_weekly_recurring);
        $this->assertEquals('weekdays', $voucher->weekly_day_rule);
        $this->assertEquals(25, $voucher->reward_amount);

        // 3. Update voucher
        $updatePayload = array_merge($createPayload, [
            'name' => 'Voucher Mingguan Gold Updated',
            'member_tier' => 'platinum',
            'reward_amount' => 30,
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.vouchers.update', $voucher), $updatePayload);
        $updateResponse->assertRedirect(route('admin.vouchers.index'));

        $voucher->refresh();
        $this->assertEquals('Voucher Mingguan Gold Updated', $voucher->name);
        $this->assertEquals('platinum', $voucher->member_tier);
        $this->assertEquals(30, $voucher->reward_amount);

        // 4. Toggle active
        $toggleResponse = $this->actingAs($admin)->post(route('admin.vouchers.toggle', $voucher));
        $voucher->refresh();
        $this->assertFalse($voucher->is_active);

        // 5. Reset quota
        $voucher->update(['claimed_count' => 150]);
        $resetResponse = $this->actingAs($admin)->post(route('admin.vouchers.reset_quota', $voucher));
        $voucher->refresh();
        $this->assertEquals(0, $voucher->claimed_count);

        // 6. Delete voucher
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.vouchers.destroy', $voucher));
        $deleteResponse->assertRedirect(route('admin.vouchers.index'));
        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }

    public function test_checkout_rejects_voucher_when_buyer_tier_is_insufficient(): void
    {
        $silverUser = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->approved()->create(['user_id' => $seller->id]);

        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name' => 'Keripik Pedas Snackaroo',
            'price' => 75000,
            'discount_price' => null,
            'stock' => 20,
            'is_available' => true,
            'allowed_payment_methods' => ['cod', 'qris'],
        ]);

        $platinumVoucher = Voucher::create([
            'code' => 'TIERPLATINUMONLY',
            'name' => 'Diskon Khusus Platinum',
            'category' => 'discount',
            'type' => 'fixed',
            'reward_amount' => 25000,
            'min_spend' => 50000,
            'quota' => 100,
            'member_tier' => 'platinum',
            'is_active' => true,
        ]);

        $payload = [
            'customer_name' => 'Silver Buyer',
            'customer_phone' => '08123456789',
            'customer_address' => 'Jl. Melati No. 1',
            'payment_method' => 'cod',
            'discount_voucher_code' => 'TIERPLATINUMONLY',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($silverUser)->post(route('checkout'), $payload);
        $response->assertSessionHasErrors('discount_voucher_code');
    }

    public function test_weekly_recurring_voucher_allows_once_per_user_per_week_and_respects_quota(): void
    {
        $user1 = User::factory()->create(['role' => 'customer']);
        $user2 = User::factory()->create(['role' => 'customer']);
        $user3 = User::factory()->create(['role' => 'customer']);

        $weeklyVoucher = Voucher::create([
            'code' => 'WEEKLYMAX2',
            'name' => 'Voucher Mingguan Kuota 2 User',
            'category' => 'discount',
            'type' => 'fixed',
            'reward_amount' => 10000,
            'min_spend' => 0,
            'quota' => 2, // Maximum 2 users in the current week
            'member_tier' => 'all',
            'is_weekly_recurring' => true,
            'weekly_day_rule' => 'all',
            'is_active' => true,
        ]);

        // 1. User 1 claims successfully
        $res1 = $this->actingAs($user1)->postJson(route('voucher.claim', $weeklyVoucher));
        $res1->assertStatus(200);
        $res1->assertJson(['success' => true]);

        // 2. User 1 tries to claim again in the same week -> rejected (already claimed this week)
        $res1Duplicate = $this->actingAs($user1)->postJson(route('voucher.claim', $weeklyVoucher));
        $res1Duplicate->assertStatus(200);
        $res1Duplicate->assertJson([
            'success' => false,
            'already_claimed' => true,
        ]);

        // 3. User 2 claims successfully (total 2/2 quota reached)
        $res2 = $this->actingAs($user2)->postJson(route('voucher.claim', $weeklyVoucher));
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true]);

        // 4. User 3 tries to claim -> rejected because weekly quota of 2 users has run out
        $res3 = $this->actingAs($user3)->postJson(route('voucher.claim', $weeklyVoucher));
        $res3->assertStatus(422);
        $res3->assertJsonFragment([
            'success' => false,
        ]);
        $this->assertStringContainsString('kuota maksimal klaim untuk voucher mingguan periode ini sudah habis', $res3->json('message'));

        // 5. Simulate next week: User 1 can claim again in a new week
        $user1Claim = UserVoucher::where('user_id', $user1->id)->where('voucher_id', $weeklyVoucher->id)->first();
        $user1Claim->update(['claimed_at' => now()->subWeeks(2)]);

        $this->assertTrue($weeklyVoucher->isEligibleForUser($user1));
    }
}
