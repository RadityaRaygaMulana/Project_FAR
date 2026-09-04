@extends('seller.layout')

@section('title', 'Pengaturan Toko — ' . $store->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <button type="button" 
                onclick="window.smartNav ? window.smartNav.goBack('{{ route('seller.dashboard') }}') : window.location.href='{{ route('seller.dashboard') }}'" 
                class="w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0 shadow-2xs group"
                title="Kembali ke Dashboard Toko">
            <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] tracking-tight">
                Pengaturan Profil Toko ⚙️
            </h1>
            <p class="text-xs text-[#8A7C70]">Perbarui identitas, kontak, dan alamat asal pengiriman tokomu</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-xs space-y-6"
         x-data="{
             previewUrl: '{{ $store->logo_url }}',
             originalUrl: '{{ $store->logo_url }}',
             removeLogo: false,
             onFileChange(e) {
                 const file = e.target.files[0];
                 if (file) {
                     this.removeLogo = false;
                     this.previewUrl = URL.createObjectURL(file);
                 }
             },
             markRemoved() {
                 this.removeLogo = true;
                 this.previewUrl = null;
                 if (this.$refs.logoInput) {
                     this.$refs.logoInput.value = '';
                 }
             },
             resetToOriginal() {
                 this.removeLogo = false;
                 this.previewUrl = this.originalUrl;
                 if (this.$refs.logoInput) {
                     this.$refs.logoInput.value = '';
                 }
             }
         }">
        <form action="{{ route('seller.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Store Profile Photo / Logo Card -->
            <div class="p-5 sm:p-6 bg-[#FAF8F5] border border-[#EAE1D7] rounded-3xl space-y-4">
                <div>
                    <label class="block text-xs sm:text-sm font-black text-[#2D241E] mb-0.5">
                        Foto Profil & Logo Toko 📸
                    </label>
                    <p class="text-xs text-[#8A7C70]">
                        Logo ini akan menjadi identitas resmi tokomu di halaman profil toko, rincian produk, dan ruang obrolan chat.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-5">
                    <!-- Photo Preview Box -->
                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-3xl overflow-hidden bg-[#F2EAE0] border-2 border-dashed border-[#D4C4B5] flex items-center justify-center shrink-0 shadow-xs group">
                        <template x-if="previewUrl && !removeLogo">
                            <img :src="previewUrl" alt="Preview Logo Toko" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!previewUrl || removeLogo">
                            <div class="w-full h-full bg-gradient-to-br from-[#6B4226] to-[#452713] text-white flex flex-col items-center justify-center text-center p-2">
                                <span class="text-3xl">🏬</span>
                                <span class="text-[9px] font-bold text-amber-200 mt-0.5">Belum Ada Foto</span>
                            </div>
                        </template>

                        <!-- Verified Badge Pill -->
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-500 rounded-full text-white flex items-center justify-center text-xs font-bold border-2 border-white shadow-xs" title="Official Verified Store">✓</span>
                    </div>

                    <!-- Actions & Hints -->
                    <div class="space-y-3 text-center sm:text-left flex-1">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                            <input type="file" 
                                   name="logo" 
                                   x-ref="logoInput" 
                                   @change="onFileChange($event)" 
                                   accept="image/png, image/jpeg, image/jpg, image/webp" 
                                   class="hidden" 
                                   id="store-logo-input">

                            <button type="button" 
                                    @click="$refs.logoInput.click()" 
                                    class="px-4 py-2.5 bg-white hover:bg-[#FAF4ED] active:bg-[#F2EAE0] text-[#6B4226] hover:text-[#54321B] border border-[#6B4226]/40 text-xs font-bold rounded-xl transition shadow-2xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="previewUrl && !removeLogo ? 'Ganti Foto Profil' : 'Unggah Foto Profil'">Unggah Foto Profil</span>
                            </button>

                            <template x-if="(previewUrl && !removeLogo) || (originalUrl && !removeLogo)">
                                <button type="button" 
                                        @click="markRemoved()" 
                                        class="px-3.5 py-2.5 bg-white hover:bg-rose-50 active:bg-rose-100 text-rose-600 hover:text-rose-700 border border-rose-200 text-xs font-bold rounded-xl transition shadow-2xs flex items-center gap-1 cursor-pointer active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Hapus</span>
                                </button>
                            </template>

                            <template x-if="removeLogo || (previewUrl !== originalUrl)">
                                <button type="button" 
                                        @click="resetToOriginal()" 
                                        class="px-3 py-2.5 text-[#8A7C70] hover:text-[#2D241E] text-xs font-semibold rounded-xl transition cursor-pointer">
                                    <span>Batalkan Perubahan</span>
                                </button>
                            </template>
                        </div>

                        <!-- Hidden flag for remove_logo -->
                        <input type="hidden" name="remove_logo" :value="removeLogo ? '1' : '0'">

                        <p class="text-[11px] text-[#8A7C70] leading-relaxed">
                            Mendukung JPG, PNG, WEBP (maks. 3MB). Disarankan berukuran rasio persegi <strong>1:1</strong> (contoh: 500x500 px) agar foto tampil sempurna.
                        </p>
                        @error('logo')
                            <p class="text-[11px] text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Store Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                    Nama Toko Resmi <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       required 
                       minlength="3" 
                       maxlength="100"
                       value="{{ old('name', $store->name) }}" 
                       class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                @error('name')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- City & Phone (2 Cols) -->
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
                           value="{{ old('city', $store->city) }}" 
                           class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                    @error('city')
                        <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                        Nomor WhatsApp / Telepon <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                       name="phone" 
                       id="phone" 
                       required 
                       maxlength="30"
                       value="{{ old('phone', $store->phone) }}" 
                       class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                    @error('phone')
                        <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                    Deskripsi / Bio Toko
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="4" 
                          maxlength="1000"
                          class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20 leading-relaxed">{{ old('description', $store->description) }}</textarea>
                @error('description')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Full Address -->
            <div>
                <label for="address_detail" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                    Alamat Lengkap Usaha / Gudang
                </label>
                <input type="text" 
                       name="address_detail" 
                       id="address_detail" 
                       maxlength="500"
                       value="{{ old('address_detail', $store->address_detail) }}" 
                       class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                @error('address_detail')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Store Shopping Advantages / Highlights -->
            <div class="p-5 sm:p-6 bg-[#FAF8F5] border border-[#EAE1D7] rounded-3xl space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🛡️</span>
                    <div>
                        <label for="advantages" class="block text-xs sm:text-sm font-black text-[#2D241E]">
                            Poin Keunggulan Belanja di Toko Anda
                        </label>
                        <p class="text-[11px] text-[#8A7C70]">
                            Poin-poin ini akan ditampilkan di bawah deskripsi setiap produk tokomu. Tuliskan <strong>1 poin per baris</strong>.
                        </p>
                    </div>
                </div>

                @php
                    $defaultAdvantages = implode("\n", $store->advantages_list);
                @endphp
                <textarea name="advantages" 
                          id="advantages" 
                          rows="4" 
                          maxlength="2000"
                          placeholder="Contoh:
Produk 100% Original langsung dari distributor & produsen terverifikasi.
Pengemasan aman menggunakan kardus tebal & lapisan bubble wrap tanpa biaya tambahan.
Pengiriman cepat setiap hari kerja ke seluruh pelosok wilayah Indonesia.
Layanan pelanggan aktif dan tanggap siap membantu jika ada kendala pesanan."
                          class="w-full px-4 py-3 bg-white border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20 leading-relaxed font-sans">{{ old('advantages', $store->advantages ?: $defaultAdvantages) }}</textarea>
                <div class="flex flex-wrap justify-between items-center gap-2 text-[10px] text-[#8A7C70]">
                    <span>Tips: Buat kalimat singkat dan jelas mengenai jaminan keaslian, keamanan kemasan, atau kecepatan respon tokomu.</span>
                    <button type="button" 
                            onclick="document.getElementById('advantages').value = `Produk 100% Original langsung dari distributor & produsen terverifikasi.\nPengemasan aman menggunakan kardus tebal & lapisan bubble wrap tanpa biaya tambahan.\nPengiriman cepat setiap hari kerja ke seluruh pelosok wilayah Indonesia.\nLayanan pelanggan aktif dan tanggap siap membantu jika ada kendala pesanan.`"
                            class="text-[#6B4226] hover:underline font-bold cursor-pointer">
                        ↺ Reset ke Teks Standar
                    </button>
                </div>
                @error('advantages')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-[#F2EAE0] flex justify-end">
                <button type="submit" 
                        class="px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm rounded-2xl shadow-md transition transform active:scale-95 cursor-pointer">
                    Simpan Pengaturan Toko 💾
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
