<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the user profile & settings page.
     */
    public function showSettings(Request $request): View
    {
        $user = $request->user();
        $totalOrders = $user->orders()->count();
        $totalSpent = (float) $user->orders()->whereIn('status', ['paid', 'processing', 'completed'])->sum('grand_total');
        $memberPoints = floor($totalSpent / 1000);

        // Calculate Silver / Gold / Platinum Tier Progression
        if ($user->role === 'admin') {
            $tierKey = 'admin';
            $tier = 'Administrator';
            $tierBadge = '⚡';
            $tierDesc = 'Akses penuh manajemen operasional dan kontrol NusantaraMart.';
            $nextTier = null;
            $nextThreshold = 0;
            $progressPercent = 100;
        } elseif ($totalSpent >= 1000000 || $totalOrders >= 10) {
            $tierKey = 'platinum';
            $tier = 'Platinum Member';
            $tierBadge = '👑';
            $tierDesc = 'Tingkat keanggotaan tertinggi dengan seluruh hak istimewa VIP bebas ongkir & diskon maksimal.';
            $nextTier = null;
            $nextThreshold = 1000000;
            $progressPercent = 100;
        } elseif ($totalSpent >= 250000 || $totalOrders >= 3) {
            $tierKey = 'gold';
            $tier = 'Gold Member';
            $tierBadge = '🥇';
            $tierDesc = 'Pelanggan aktif dengan keuntungan voucher bulanan 30% dan akses awal flash sale.';
            $nextTier = 'Platinum Member';
            $nextThreshold = 1000000;
            $progressPercent = min(100, round((($totalSpent - 250000) / (1000000 - 250000)) * 100));
        } else {
            $tierKey = 'silver';
            $tier = 'Silver Member';
            $tierBadge = '🥈';
            $tierDesc = 'Tingkat awal member resmi NusantaraMart dengan voucher sambutan dan gratis ongkir reguler.';
            $nextTier = 'Gold Member';
            $nextThreshold = 250000;
            $progressPercent = min(100, round(($totalSpent / 250000) * 100));
        }

        return view('settings', [
            'user' => $user,
            'totalOrders' => $totalOrders,
            'totalSpent' => $totalSpent,
            'memberPoints' => $memberPoints,
            'tierKey' => $tierKey,
            'tier' => $tier,
            'tierBadge' => $tierBadge,
            'tierDesc' => $tierDesc,
            'nextTier' => $nextTier,
            'nextThreshold' => $nextThreshold,
            'progressPercent' => $progressPercent,
        ]);
    }

    /**
     * Update user profile information & delivery defaults.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'default_address' => ['nullable', 'string', 'max:1000'],
            'favorite_spiciness' => ['required', 'integer', 'in:0,3,4,5'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        AuditLogger::profile('Perbarui Profil', "Profil pengguna diubah (Nama: {$user->name}, Email: {$user->email})", $user);

        return redirect()->route('settings')
            ->with('profile_success', 'Foto profil dan data akun kamu berhasil diperbarui! ✨');
    }

    /**
     * Update user account password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi lama wajib diisi.',
            'current_password.current_password' => 'Kata sandi lama yang kamu masukkan tidak sesuai dengan kata sandi akunmu.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal harus 6 karakter.',
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        AuditLogger::profile('Ubah Kata Sandi', 'Kata sandi akun berhasil diganti', $user);

        return redirect()->route('settings')
            ->with('password_success', 'Kata sandi akun kamu berhasil diperbarui! 🔒✨');
    }

    /**
     * Update user shipping & delivery address.
     */
    public function updateAddress(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'address_label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address_detail' => ['required', 'string', 'max:1000'],
            'map_notes' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        // Synchronize default_address string for seamless backwards compatibility
        $parts = array_filter([
            $validated['address_detail'],
            'Kec. '.$validated['district'],
            $validated['city'],
            $validated['province'],
            $validated['postal_code'] ?? null,
        ]);
        $validated['default_address'] = implode(', ', $parts);

        $user->update($validated);

        AuditLogger::profile('Perbarui Alamat', "Alamat pengiriman diubah ke {$validated['city']}, {$validated['province']}", $user);

        if ($request->input('return_to') === 'checkout') {
            return redirect()->route('checkout.show')
                ->with('success', 'Alamat pengiriman belanja kamu berhasil disimpan! 📍✨');
        }

        return redirect()->route('settings')
            ->with('address_success', 'Alamat pengiriman belanja kamu berhasil disimpan! 📍✨');
    }
}
