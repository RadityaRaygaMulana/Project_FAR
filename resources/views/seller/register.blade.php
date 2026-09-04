@extends('layouts.app')

@section('title', 'Pendaftaran Toko Penjual — NusantaraMart Seller')

@section('content')
<div class="py-8 sm:py-12 bg-[#FAF8F5] min-h-[85vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Navigation with Smart Back Button -->
        <div class="flex items-center gap-3">
            <button type="button" 
                    onclick="window.smartNav.goBack('{{ route('home') }}')" 
                    class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                    title="Kembali">
                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-[#8A7C70] mb-0.5">
                    <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition">Beranda</a>
                    <span>/</span>
                    <span class="text-[#6B4226] font-semibold">Buka Toko NusantaraMart</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] tracking-tight">
                    Program Mitra Penjual NusantaraMart 🏪
                </h1>
            </div>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-20"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 py-0"
                 class="overflow-hidden p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-800 font-bold p-1 cursor-pointer transition" title="Tutup Notifikasi">✕</button>
            </div>
        @endif

        @if(session('warning'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-20"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 py-0"
                 class="overflow-hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">⏳</span>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-amber-500 hover:text-amber-800 font-bold p-1 cursor-pointer transition" title="Tutup Notifikasi">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-20"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 py-0"
                 class="overflow-hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-800 font-bold p-1 cursor-pointer transition" title="Tutup Notifikasi">✕</button>
            </div>
        @endif

        <!-- CASE 1: NOT LOGGED IN -->
        @guest
            <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-10 shadow-xs text-center space-y-6">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-[#6B4226] to-[#452713] text-white flex items-center justify-center text-4xl mx-auto shadow-md">
                    🏪
                </div>
                <div class="max-w-md mx-auto space-y-2">
                    <h2 class="text-xl sm:text-2xl font-black text-[#2D241E]">
                        Buka Toko & Jual Produkmu di NusantaraMart
                    </h2>
                    <p class="text-xs sm:text-sm text-[#7A6C60] leading-relaxed">
                        Jangkau ribuan pelanggan di seluruh Indonesia dengan platform marketplace terpercaya. Silakan masuk atau daftar akun terlebih dahulu untuk melanjutkan.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-2xl mx-auto text-left pt-2">
                    <div class="p-4 rounded-2xl bg-[#FAF8F5] border border-[#EAE1D7] space-y-1">
                        <span class="text-2xl">⚡</span>
                        <h4 class="font-extrabold text-xs text-[#2D241E]">Pendaftaran Cepat</h4>
                        <p class="text-[11px] text-[#8A7C70]">Cukup isi nama toko, kontak, dan alamat pengiriman.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-[#FAF8F5] border border-[#EAE1D7] space-y-1">
                        <span class="text-2xl">🛡️</span>
                        <h4 class="font-extrabold text-xs text-[#2D241E]">Verifikasi Resmi</h4>
                        <p class="text-[11px] text-[#8A7C70]">Toko diverifikasi oleh Admin untuk menjamin keamanan pembeli.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-[#FAF8F5] border border-[#EAE1D7] space-y-1">
                        <span class="text-2xl">📊</span>
                        <h4 class="font-extrabold text-xs text-[#2D241E]">Seller Center Lengkap</h4>
                        <p class="text-[11px] text-[#8A7C70]">Kelola katalog, stok, harga, dan pesanan secara mandiri.</p>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-3 pt-4">
                    <a href="{{ route('login') }}" 
                       class="px-6 sm:px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm rounded-2xl shadow-xs transition transform active:scale-95">
                        Masuk untuk Membuka Toko 🔒
                    </a>
                    <a href="{{ route('register') }}" 
                       class="px-6 py-3.5 bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226] font-bold text-xs sm:text-sm rounded-2xl shadow-2xs transition">
                        Daftar Akun Baru
                    </a>
                </div>
            </div>

        <!-- CASE 2: STORE APPLICATION IS PENDING REVIEW -->
        @elseif($store && $store->isPending())
            <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-10 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                    <div class="w-18 h-18 rounded-3xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-4xl shrink-0 shadow-2xs animate-pulse">
                        ⏳
                    </div>
                    <div class="text-center sm:text-left space-y-1.5 flex-1">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            <span>Status:</span>
                            <span>Menunggu Persetujuan Admin</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-[#2D241E]">
                            Pengajuan Toko Sedang Ditinjau
                        </h2>
                        <p class="text-xs sm:text-sm text-[#7A6C60] leading-relaxed">
                            Terima kasih telah mendaftar! Tim Administrator NusantaraMart sedang memverifikasi rincian tokomu. Proses ini biasanya memakan waktu maksimal 1x24 jam kerja.
                        </p>
                    </div>
                </div>

                <!-- Submitted Store Overview Card -->
                <div class="bg-[#FAF8F5] rounded-2xl border border-[#EAE1D7] p-5 space-y-3">
                    <h3 class="text-xs font-extrabold uppercase text-[#8A7C70] tracking-wider">
                        Rincian Pengajuan Toko:
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-[#8A7C70] block">Nama Toko:</span>
                            <strong class="text-[#2D241E] text-sm">{{ $store->name }}</strong>
                        </div>
                        <div>
                            <span class="text-[#8A7C70] block">Asal Pengiriman:</span>
                            <strong class="text-[#2D241E]">{{ $store->city }}</strong>
                        </div>
                        <div>
                            <span class="text-[#8A7C70] block">Nomor Telepon / WhatsApp:</span>
                            <strong class="text-[#2D241E]">{{ $store->phone }}</strong>
                        </div>
                        <div>
                            <span class="text-[#8A7C70] block">Pemilik Toko (KTP):</span>
                            <strong class="text-[#2D241E]">{{ $store->ktp_name ?: '-' }} ({{ $store->masked_nik }})</strong>
                        </div>
                        <div>
                            <span class="text-[#8A7C70] block">Tanggal Pengajuan:</span>
                            <strong class="text-[#2D241E]">{{ $store->created_at->format('d M Y, H:i') }} WIB</strong>
                        </div>
                    </div>
                    @if($store->description)
                        <div class="pt-2 border-t border-[#EAE1D7] text-xs">
                            <span class="text-[#8A7C70] block mb-0.5">Deskripsi Toko:</span>
                            <p class="text-[#5A4B40] leading-relaxed">{{ $store->description }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-2 flex-wrap gap-3">
                    <p class="text-xs text-[#8A7C70]">
                        💡 Anda akan otomatis mendapatkan akses ke <strong>Seller Center</strong> setelah disetujui.
                    </p>
                    <a href="{{ route('home') }}" 
                       class="px-5 py-2.5 bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] rounded-xl text-xs font-bold transition">
                        Kembali ke Beranda Belanja
                    </a>
                </div>
            </div>

        <!-- CASE 3: STORE WAS REJECTED (SHOW REASON & EDIT/REAPPLY FORM) OR NEW REGISTRATION -->
        @else
            @if($store && $store->isRejected())
                <!-- Rejection Alert with Admin Message -->
                <div class="bg-rose-50 border-2 border-rose-300 rounded-3xl p-6 sm:p-8 space-y-4 shadow-xs">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500 text-white flex items-center justify-center text-2xl shrink-0 shadow-md">
                            ⚠️
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base sm:text-lg font-black text-rose-950">
                                Pengajuan Toko Belum Disetujui oleh Admin
                            </h3>
                            <p class="text-xs text-rose-700">
                                Jangan khawatir! Anda dapat memperbaiki data toko di bawah ini dan mengajukannya kembali.
                            </p>
                        </div>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-rose-200 text-xs text-rose-900 space-y-1">
                        <span class="font-black uppercase tracking-wider text-[10px] text-rose-600 block">Pesan / Alasan dari Administrator:</span>
                        <p class="font-bold leading-relaxed text-rose-800">
                            "{{ $store->rejection_reason ?: 'Harap periksa kembali kelengkapan informasi toko Anda.' }}"
                        </p>
                    </div>
                </div>
            @endif

            <div x-data="{
                storageKey: 'snackaroo_seller_draft_{{ auth()->id() ?? 'guest' }}',
                showLightbox: false,
                formData: {
                    name: @js(old('name', $store->name ?? '')),
                    ktp_nik: @js(old('ktp_nik', $store->ktp_nik ?? '')),
                    ktp_name: @js(old('ktp_name', $store->ktp_name ?? '')),
                    city: @js(old('city', $store->city ?? '')),
                    phone: @js(old('phone', $store->phone ?? '')),
                    description: @js(old('description', $store->description ?? '')),
                    address_detail: @js(old('address_detail', $store->address_detail ?? '')),
                    terms: {{ old('terms') ? 'true' : 'false' }},
                },
                ktpPreview: {{ $store && $store->ktp_photo_url ? "'".$store->ktp_photo_url."'" : 'null' }},
                ktpFileName: {{ $store && $store->ktp_photo_path ? "'Foto KTP Tersimpan'" : "''" }},
                ktpFileSize: '',
                isExisting: {{ $store && $store->ktp_photo_path ? 'true' : 'false' }},

                async init() {
                    this.loadDraft();
                    await this.loadKtpPhoto();
                },

                loadDraft() {
                    try {
                        const raw = localStorage.getItem(this.storageKey);
                        if (!raw) return;
                        const parsed = JSON.parse(raw);
                        const now = Date.now();
                        const maxAge = 24 * 60 * 60 * 1000; // Batas aktif 24 jam

                        if (now - parsed.savedAt > maxAge) {
                            localStorage.removeItem(this.storageKey);
                            return;
                        }

                        for (const key of ['name', 'ktp_nik', 'ktp_name', 'city', 'phone', 'description', 'address_detail', 'terms']) {
                            if (parsed.data && parsed.data[key] !== undefined && parsed.data[key] !== '' && parsed.data[key] !== false) {
                                if (!this.formData[key]) {
                                    this.formData[key] = parsed.data[key];
                                }
                            }
                        }
                    } catch (e) {
                        console.warn('Gagal memuat draf toko:', e);
                    }
                },

                async loadKtpPhoto() {
                    try {
                        if (typeof getKtpFromDb !== 'function') return;
                        const record = await getKtpFromDb(this.storageKey);
                        if (!record || !record.file) return;

                        const now = Date.now();
                        const maxAge = 24 * 60 * 60 * 1000;
                        if (now - record.savedAt > maxAge) {
                            await deleteKtpFromDb(this.storageKey);
                            return;
                        }

                        const file = record.file;
                        this.ktpFileName = file.name;
                        this.ktpFileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                        this.ktpPreview = URL.createObjectURL(file);
                        this.isExisting = false;

                        if (this.$refs.ktpInput) {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            this.$refs.ktpInput.files = dt.files;
                        }
                    } catch (e) {
                        console.warn('Gagal memuat foto KTP dari draf:', e);
                    }
                },

                saveDraft() {
                    try {
                        const payload = {
                            savedAt: Date.now(),
                            data: this.formData
                        };
                        localStorage.setItem(this.storageKey, JSON.stringify(payload));
                    } catch (e) {}
                },

                async clearDraft() {
                    try {
                        localStorage.removeItem(this.storageKey);
                        if (typeof deleteKtpFromDb === 'function') {
                            await deleteKtpFromDb(this.storageKey);
                        }
                    } catch (e) {}
                },

                async handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.ktpFileName = file.name;
                        this.ktpFileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                        this.ktpPreview = URL.createObjectURL(file);
                        this.isExisting = false;
                        if (typeof saveKtpToDb === 'function') {
                            await saveKtpToDb(this.storageKey, file);
                        }
                        this.saveDraft();
                    }
                },

                async cancelKtp() {
                    this.ktpPreview = null;
                    this.ktpFileName = '';
                    this.ktpFileSize = '';
                    this.isExisting = false;
                    if (this.$refs.ktpInput) {
                        this.$refs.ktpInput.value = '';
                    }
                    if (typeof deleteKtpFromDb === 'function') {
                        await deleteKtpFromDb(this.storageKey);
                    }
                    this.saveDraft();
                }
            }" class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-10 shadow-xs space-y-6">
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-[#2D241E]">
                        {{ $store && $store->isRejected() ? 'Perbaiki Informasi & Ajukan Ulang Toko' : 'Formulir Pendaftaran Buka Toko Resmi' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-[#7A6C60] mt-1">
                        Lengkapi informasi toko dan data verifikasi KTP secara valid agar proses verifikasi oleh Admin dapat segera disetujui.
                    </p>
                </div>

                <form action="{{ route('seller.register.submit') }}" method="POST" enctype="multipart/form-data" @submit="clearDraft()" class="space-y-5">
                    @csrf

                    <!-- Store Name -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                            Nama Toko Penjual <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               required 
                               minlength="3" 
                               maxlength="100"
                               x-model="formData.name"
                               @input="saveDraft()"
                               placeholder="Contoh: Dapur Nusantara, Erigo Official, Gadget Corner"
                               class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition">
                        @error('name')
                            <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Verification / KTP Section -->
                    <div class="p-5 rounded-2xl bg-amber-50/50 border border-amber-200/80 space-y-4">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🪪</span>
                            <div>
                                <h3 class="text-xs sm:text-sm font-extrabold text-[#2D241E]">
                                    Verifikasi Identitas Pemilik Toko (Wajib KTP)
                                </h3>
                                <p class="text-[11px] text-[#7A6C60]">
                                    Data KTP digunakan oleh Administrator untuk verifikasi kepemilikan toko resmi dan mencegah akun palsu.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- NIK KTP -->
                            <div>
                                <label for="ktp_nik" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                                    Nomor Induk Kependudukan (NIK) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="ktp_nik" 
                                       id="ktp_nik" 
                                       required 
                                       pattern="[0-9]{16}"
                                       minlength="16"
                                       maxlength="16"
                                       x-model="formData.ktp_nik"
                                       @input="saveDraft()"
                                       placeholder="16 digit angka NIK (Contoh: 3201...)"
                                       class="w-full px-4 py-3 bg-white border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] tracking-wider focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition font-mono">
                                @error('ktp_nik')
                                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nama Sesuai KTP -->
                            <div>
                                <label for="ktp_name" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                                    Nama Lengkap Sesuai KTP <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="ktp_name" 
                                       id="ktp_name" 
                                       required 
                                       minlength="3" 
                                       maxlength="100"
                                       x-model="formData.ktp_name"
                                       @input="saveDraft()"
                                       placeholder="Contoh: Raditya Pratama"
                                       class="w-full px-4 py-3 bg-white border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition">
                                @error('ktp_name')
                                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Upload Foto KTP with Display & Round Cancel Button -->
                        <div>
                            <label class="block text-xs font-bold text-[#2D241E] mb-1.5">
                                Unggah Foto KTP Asli <span class="text-red-500">*</span>
                                <span class="text-[11px] font-normal text-[#8A7C70]">(Pastikan seluruh sudut KTP, NIK, dan wajah terlihat jelas)</span>
                            </label>

                            <!-- Hidden Real File Input -->
                            <input type="file" 
                                   name="ktp_photo" 
                                   id="ktp_photo" 
                                   x-ref="ktpInput"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   :required="!ktpPreview && !isExisting"
                                   @change="handleFileSelect($event)"
                                   class="hidden">

                            <!-- STATE 1: NO FILE SELECTED (UPLOAD DROPZONE) -->
                            <div x-show="!ktpPreview">
                                <label for="ktp_photo" 
                                       class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-[#D4C5B9] hover:border-[#6B4226] bg-[#FAF8F5] hover:bg-[#FAF4ED]/60 rounded-2xl cursor-pointer transition-all duration-200 group text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#EAE1D7] flex items-center justify-center text-2xl shadow-2xs group-hover:scale-110 transition mb-2">
                                        🪪
                                    </div>
                                    <span class="text-xs sm:text-sm font-bold text-[#6B4226] group-hover:underline">
                                        Pilih Berkas Foto KTP
                                    </span>
                                    <span class="text-[11px] text-[#8A7C70] mt-0.5">
                                        Format JPG, PNG, atau WEBP (Maksimal 3MB)
                                    </span>
                                </label>
                            </div>

                            <!-- STATE 2: FILE UPLOADED / PREVIEW ACTIVE (WITH ROUND CANCEL BUTTON) -->
                            <div x-show="ktpPreview" class="space-y-3">
                                <div class="bg-gradient-to-r from-[#FAF8F5] to-[#FAF4ED] border border-[#EAE1D7] rounded-2xl p-3.5 sm:p-4 flex items-center justify-between gap-3 sm:gap-4 shadow-2xs">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <!-- Mini Image Thumbnail with Zoom Lightbox -->
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden border border-[#EAE1D7] bg-black/5 shrink-0 shadow-2xs relative group cursor-pointer"
                                             @click="showLightbox = true"
                                             title="Klik untuk melihat foto lebih besar">
                                            <img :src="ktpPreview" alt="Pratinjau KTP" class="w-full h-full object-cover group-hover:scale-105 transition">
                                            <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] font-bold transition">
                                                🔍
                                            </div>
                                        </div>

                                        <!-- File Information -->
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 mb-0.5">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                                    <span>✓</span>
                                                    <span x-text="isExisting ? 'KTP Tersimpan' : 'KTP Siap Diunggah'"></span>
                                                </span>
                                                <span x-show="ktpFileSize" x-text="ktpFileSize" class="text-[10px] text-[#8A7C70] font-mono"></span>
                                            </div>
                                            <strong class="text-xs sm:text-sm text-[#2D241E] truncate block font-medium" 
                                                    x-text="ktpFileName || 'Berkas Foto KTP'"></strong>
                                            <span class="text-[10px] text-[#8A7C70] block mt-0.5">
                                                Dokumen siap diverifikasi saat formulir pendaftaran dikirim
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions: Ganti Foto & Round Cancel X Button -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <label for="ktp_photo" 
                                               class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 bg-white hover:bg-stone-50 border border-[#D4C5B9] text-[#5A4B40] hover:text-[#2D241E] text-xs font-bold rounded-xl transition cursor-pointer shadow-2xs">
                                            <span>Ganti</span>
                                        </label>

                                        <!-- Round Cancel Button (X) -->
                                        <button type="button" 
                                                @click="cancelKtp()" 
                                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-rose-50 hover:bg-rose-100 active:bg-rose-200 text-rose-600 hover:text-rose-700 border border-rose-200/80 flex items-center justify-center transition-all duration-200 shadow-2xs hover:scale-105 active:scale-95 cursor-pointer"
                                                title="Batalkan & Hapus Foto KTP">
                                            <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @error('ktp_photo')
                                <p class="text-[11px] text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="text-[10px] text-[#8A7C70] flex items-center gap-1.5">
                            <span>🔒</span>
                            <span>Kerahasiaan data terjamin. Foto KTP hanya dapat dilihat oleh Administrator untuk keperluan validasi.</span>
                        </p>
                    </div>

                    <!-- City & Phone (2 Columns) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                                Kota Asal Pengiriman <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="city" 
                                   id="city" 
                                   required 
                                   maxlength="100"
                                   x-model="formData.city"
                                   @input="saveDraft()"
                                   placeholder="Contoh: Kota Jakarta Selatan, Surabaya, Bandung"
                                   class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition">
                            @error('city')
                                <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                                Nomor Telepon / WhatsApp <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   id="phone" 
                                   required 
                                   maxlength="30"
                                   x-model="formData.phone"
                                   @input="saveDraft()"
                                   placeholder="Contoh: 081234567890"
                                   class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition">
                            @error('phone')
                                <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Store Bio / Description -->
                    <div>
                        <label for="description" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                            Deskripsi Singkat / Slogan Toko
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3" 
                                  maxlength="1000"
                                  x-model="formData.description"
                                  @input="saveDraft()"
                                  placeholder="Ceritakan tentang produk apa saja yang kamu jual, komitmen kualitas, dan keunggulan tokomu..."
                                  class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition leading-relaxed"></textarea>
                        @error('description')
                            <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Full Store Address -->
                    <div>
                        <label for="address_detail" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                            Alamat Gudang / Tempat Usaha
                        </label>
                        <input type="text" 
                               name="address_detail" 
                               id="address_detail" 
                               maxlength="500"
                               x-model="formData.address_detail"
                               @input="saveDraft()"
                               placeholder="Nama jalan, gedung, nomor ruko, atau patokan lokasi usaha"
                               class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition">
                        @error('address_detail')
                            <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Interactive Agreement Checkbox -->
                    <div class="pt-3 border-t border-[#F2EAE0]">
                        <label class="flex items-start gap-3 cursor-pointer select-none group">
                            <input type="checkbox" 
                                   name="terms" 
                                   id="terms" 
                                   value="1" 
                                   required 
                                   x-model="formData.terms"
                                   @change="saveDraft()"
                                   class="w-4.5 h-4.5 mt-0.5 text-[#6B4226] border-[#D4C5B9] rounded focus:ring-[#6B4226] focus:ring-2 cursor-pointer transition">
                            <span class="text-xs text-[#5A4B40] group-hover:text-[#2D241E] leading-relaxed">
                                Saya menyatakan bahwa seluruh informasi toko yang saya berikan adalah benar dan bersedia mematuhi seluruh 
                                <strong class="text-[#6B4226]">Syarat & Ketentuan serta Standar Operasional Mitra Penjual NusantaraMart</strong>.
                            </span>
                        </label>
                        @error('terms')
                            <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 flex flex-col-reverse sm:flex-row items-center justify-between gap-3 border-t border-[#EAE1D7]">
                        <button type="button" 
                                onclick="window.smartNav ? window.smartNav.goBack('{{ route('home') }}') : (window.history.length > 1 ? window.history.back() : window.location.href='{{ route('home') }}')"
                                class="w-full sm:w-auto px-6 py-3.5 bg-white hover:bg-[#FAF4ED] text-[#5A4B40] hover:text-[#6B4226] border border-[#EAE1D7] hover:border-[#6B4226]/30 font-bold text-xs sm:text-sm rounded-2xl transition cursor-pointer flex items-center justify-center gap-2 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span>Kembali</span>
                        </button>

                        <button type="submit" 
                                class="w-full sm:w-auto px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm rounded-2xl shadow-md transition transform active:scale-95 cursor-pointer flex items-center justify-center gap-2">
                            <span>{{ $store && $store->isRejected() ? 'Kirim Ulang Pengajuan Toko 🚀' : 'Kirim Pengajuan Buka Toko 🏪' }}</span>
                        </button>
                    </div>
                </form>

                <!-- In-Page KTP Photo Lightbox Modal (Tampilan Foto Membesar di Halaman) -->
                <div x-show="showLightbox" 
                     x-cloak 
                     class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6 transition-all duration-300"
                     @keydown.escape.window="showLightbox = false">
                    <div class="relative max-w-3xl w-full bg-[#1C1815] rounded-3xl p-4 sm:p-5 border border-white/15 shadow-2xl space-y-4"
                         @click.away="showLightbox = false">
                        
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between px-2 pt-1 text-white">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="text-xl">🪪</span>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-bold text-[#F5EBE1] truncate" x-text="ktpFileName || 'Foto KTP Asli'"></h4>
                                    <p class="text-[10px] text-[#A89F91]">Pratinjau Foto KTP Ukuran Penuh</p>
                                </div>
                            </div>
                            <button type="button" 
                                    @click="showLightbox = false" 
                                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/15 hover:bg-white/25 active:bg-white/35 text-white flex items-center justify-center text-sm font-black transition cursor-pointer"
                                    title="Tutup Pratinjau">
                                ✕
                            </button>
                        </div>

                        <!-- Image Preview Box -->
                        <div class="flex items-center justify-center overflow-hidden rounded-2xl max-h-[75vh] bg-black/60 p-2 border border-white/10">
                            <img :src="ktpPreview" 
                                 alt="Foto KTP Ukuran Besar" 
                                 class="max-w-full max-h-[70vh] object-contain rounded-xl shadow-lg select-none">
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-between px-2 pt-1 text-xs text-[#A89F91]">
                            <span class="text-[11px] flex items-center gap-1.5">
                                <span>🔒</span>
                                <span>Kerahasiaan dokumen terlindungi</span>
                            </span>
                            <button type="button" 
                                    @click="showLightbox = false" 
                                    class="px-4 py-1.5 bg-white/20 hover:bg-white/30 text-white font-bold rounded-xl text-xs transition cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endguest

    </div>
</div>

@push('scripts')
<script>
    // IndexedDB helper to keep KTP photo in draft across page refreshes for 24 hours
    const KTP_DB_NAME = 'snackaroo_seller_media_db';
    const KTP_STORE_NAME = 'ktp_files';

    function openKtpDb() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(KTP_DB_NAME, 1);
            req.onupgradeneeded = () => {
                req.result.createObjectStore(KTP_STORE_NAME);
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    async function saveKtpToDb(key, file) {
        try {
            const db = await openKtpDb();
            const tx = db.transaction(KTP_STORE_NAME, 'readwrite');
            tx.objectStore(KTP_STORE_NAME).put({
                file: file,
                savedAt: Date.now()
            }, key);
        } catch (e) {
            console.warn('Gagal menyimpan berkas KTP ke IndexedDB:', e);
        }
    }

    async function getKtpFromDb(key) {
        try {
            const db = await openKtpDb();
            return new Promise((resolve) => {
                const tx = db.transaction(KTP_STORE_NAME, 'readonly');
                const req = tx.objectStore(KTP_STORE_NAME).get(key);
                req.onsuccess = () => resolve(req.result);
                req.onerror = () => resolve(null);
            });
        } catch (e) {
            return null;
        }
    }

    async function deleteKtpFromDb(key) {
        try {
            const db = await openKtpDb();
            const tx = db.transaction(KTP_STORE_NAME, 'readwrite');
            tx.objectStore(KTP_STORE_NAME).delete(key);
        } catch (e) {}
    }
</script>
@endpush
@endsection
