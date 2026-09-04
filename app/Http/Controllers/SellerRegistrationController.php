<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerRegistrationController extends Controller
{
    /**
     * Display the seller registration page or current application status.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $store = $user ? $user->store : null;

        if ($store && $store->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.register', compact('store'));
    }

    /**
     * Handle store opening registration or re-application.
     */
    public function register(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('info', 'Silakan masuk terlebih dahulu untuk mendaftar sebagai penjual.');
        }

        $existingStore = $user->store;
        if ($existingStore && $existingStore->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'ktp_nik' => ['required', 'numeric', 'digits:16'],
            'ktp_name' => ['required', 'string', 'min:3', 'max:100'],
            'ktp_photo' => [
                $existingStore && $existingStore->ktp_photo_path ? 'nullable' : 'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:3072',
            ],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address_detail' => ['nullable', 'string', 'max:500'],
            'terms' => ['accepted'],
        ], [
            'ktp_nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'ktp_nik.digits' => 'Nomor Induk Kependudukan (NIK) harus tepat 16 digit angka.',
            'ktp_nik.numeric' => 'NIK harus berupa angka.',
            'ktp_name.required' => 'Nama lengkap sesuai KTP wajib diisi.',
            'ktp_photo.required' => 'Foto KTP asli pemilik toko wajib diunggah untuk verifikasi.',
            'ktp_photo.image' => 'Berkas KTP harus berupa gambar (JPG, PNG, atau WEBP).',
            'ktp_photo.max' => 'Ukuran foto KTP maksimal 3 MB.',
            'terms.accepted' => 'Anda wajib menyetujui Syarat & Ketentuan Mitra Penjual untuk melanjutkan.',
        ]);

        $baseSlug = Str::slug($validated['name']);
        if (empty($baseSlug)) {
            $baseSlug = 'toko-'.Str::random(6);
        }

        $slug = $baseSlug;
        $counter = 1;
        while (Store::where('slug', $slug)->when($existingStore, fn ($q) => $q->where('id', '!=', $existingStore->id))->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $ktpPhotoPath = $existingStore ? $existingStore->ktp_photo_path : null;
        if ($request->hasFile('ktp_photo')) {
            $ktpPhotoPath = $request->file('ktp_photo')->store('ktp', 'public');
        }

        if ($existingStore) {
            $existingStore->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'ktp_nik' => $validated['ktp_nik'],
                'ktp_name' => $validated['ktp_name'],
                'ktp_photo_path' => $ktpPhotoPath,
                'city' => $validated['city'],
                'province' => $validated['province'] ?? $existingStore->province,
                'phone' => $validated['phone'],
                'description' => $validated['description'] ?? null,
                'address_detail' => $validated['address_detail'] ?? null,
                'status' => 'pending',
                'rejection_reason' => null,
                'approved_at' => null,
            ]);

            return redirect()->route('seller.register')
                ->with('success', 'Perubahan pengajuan toko & identitas KTP berhasil dikirim ulang dan sedang menunggu peninjauan Admin.');
        }

        Store::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'ktp_nik' => $validated['ktp_nik'],
            'ktp_name' => $validated['ktp_name'],
            'ktp_photo_path' => $ktpPhotoPath,
            'city' => $validated['city'],
            'province' => $validated['province'] ?? null,
            'phone' => $validated['phone'],
            'description' => $validated['description'] ?? null,
            'address_detail' => $validated['address_detail'] ?? null,
            'badge' => 'Official',
            'rating' => 5.0,
            'status' => 'pending',
        ]);

        return redirect()->route('seller.register')
            ->with('success', 'Pengajuan buka toko berhasil diajukan! Administrator akan segera meninjau toko Anda.');
    }
}
