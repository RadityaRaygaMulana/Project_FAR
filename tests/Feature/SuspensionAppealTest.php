<?php

namespace Tests\Feature;

use App\Models\SuspensionAppeal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspensionAppealTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_can_submit_appeal(): void
    {
        $user = User::factory()->create([
            'is_suspended' => true,
            'suspension_reason' => 'Aktivitas mencurigakan.',
            'suspended_at' => now(),
        ]);

        $response = $this->post(route('appeals.store'), [
            'identifier' => $user->email,
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
            'applicant_phone' => '08123456789',
            'reason' => 'Mohon buka blokir akun saya, akun saya sempat diretas pihak lain.',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('suspension_appeals', [
            'user_id' => $user->id,
            'applicant_email' => $user->email,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_submit_duplicate_pending_appeal(): void
    {
        $user = User::factory()->create([
            'is_suspended' => true,
            'suspension_reason' => 'Pelanggaran spam.',
            'suspended_at' => now(),
        ]);

        SuspensionAppeal::create([
            'user_id' => $user->id,
            'type' => 'user',
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
            'reason' => 'Pengajuan banding pertama yang sedang diproses.',
            'status' => 'pending',
        ]);

        $response = $this->post(route('appeals.store'), [
            'identifier' => $user->username,
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
            'reason' => 'Pengajuan banding kedua saya mohon dibalas.',
        ]);

        $response->assertSessionHas('info');
        $this->assertDatabaseCount('suspension_appeals', 1);
    }

    public function test_admin_can_view_appeals_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.appeals.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengajuan Banding Penangguhan');
    }

    public function test_admin_can_approve_appeal_and_lift_suspension(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'is_suspended' => true,
            'suspension_reason' => 'Toko belum verifikasi.',
            'suspended_at' => now(),
        ]);

        $appeal = SuspensionAppeal::create([
            'user_id' => $user->id,
            'type' => 'user',
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
            'reason' => 'Saya sudah melengkapi data verifikasi yang diminta.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.appeals.approve', $appeal), [
            'admin_notes' => 'Dokumen lengkap, pemblokiran dicabut.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('suspension_appeals', [
            'id' => $appeal->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        $this->assertFalse($user->fresh()->is_suspended);
        $this->assertNull($user->fresh()->suspended_until);
    }

    public function test_admin_can_reject_appeal_with_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'is_suspended' => true,
            'suspension_reason' => 'Pelanggaran berulang.',
            'suspended_at' => now(),
        ]);

        $appeal = SuspensionAppeal::create([
            'user_id' => $user->id,
            'type' => 'user',
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
            'reason' => 'Mohon berikan saya kesempatan kedua.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.appeals.reject', $appeal), [
            'admin_notes' => 'Permohonan ditolak karena pelanggaran berat.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('suspension_appeals', [
            'id' => $appeal->id,
            'status' => 'rejected',
            'admin_notes' => 'Permohonan ditolak karena pelanggaran berat.',
            'reviewed_by' => $admin->id,
        ]);

        $this->assertTrue($user->fresh()->is_suspended);
    }
}
