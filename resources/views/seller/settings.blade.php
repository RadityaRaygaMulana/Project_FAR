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

    <!-- OPERATIONAL STATUS (MODE LIBUR / TUTUP SEMENTARA) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EAE1D7] shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#F2EAE0]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-xl shrink-0 {{ $store->isClosed() ? 'bg-amber-100 text-amber-800' : ($store->isSuspended() ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800') }}">
                    @if($store->isSuspended())
                        🚫
                    @elseif($store->isClosed())
                        ⏸️
                    @else
                        🟢
                    @endif
                </div>
                <div>
                    <h2 class="text-base font-black text-[#2D241E]">Status Operasional Toko</h2>
                    <p class="text-xs text-[#7A6C60]">Atur ketersediaan toko saat kamu sedang libur atau tidak dapat melayani pesanan.</p>
                </div>
            </div>

            <!-- Status Pill -->
            <div>
                @if($store->isSuspended())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-red-100 text-red-800 border border-red-200">
                        <span>🚫</span>
                        <span>Ditangguhkan Admin</span>
                    </span>
                @elseif($store->isClosed())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                        <span>⏸️</span>
                        <span>Tutup Sementara (Mode Libur)</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Buka (Melayani Pesanan)</span>
                    </span>
                @endif
            </div>
        </div>

        <div class="bg-[#FAF8F5] rounded-2xl p-5 border border-[#EAE1D7] space-y-3">
            <h3 class="text-xs font-bold text-[#2D241E] uppercase tracking-wider">Tentang Mode Libur:</h3>
            <ul class="text-xs text-[#5A4B40] space-y-2 list-disc list-inside">
                <li>Saat toko <strong>Tutup Sementara</strong>, profil toko dan seluruh produk tokomu tetap dapat dicari dan dilihat oleh pelanggan.</li>
                <li>Tombol <strong>"Beli Sekarang"</strong> dan <strong>"+ Keranjang"</strong> akan dinonaktifkan sementara dan digantikan pemberitahuan ramah bahwa tokomu sedang berlibur.</li>
                <li>Kamu tetap memiliki akses penuh ke <em>Seller Center</em> untuk memantau stok, riwayat pesanan, atau memperbarui harga barang.</li>
                <li>Kamu dapat membuka toko kembali secara instan kapan saja hanya dengan 1 klik.</li>
            </ul>
        </div>

        @if($store->isSuspended())
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span>Toko kamu saat ini dalam masa penangguhan admin dan tidak dapat diubah statusnya secara mandiri.</span>
            </div>
        @else
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                <div class="text-xs text-[#7A6C60]">
                    @if($store->isClosed())
                        Status saat ini: <strong class="text-amber-800">Tutup Sementara</strong>. Buka kembali jika tokomu sudah siap melayani pesanan baru.
                    @else
                        Status saat ini: <strong class="text-emerald-800">Buka</strong>. Aktifkan tutup sementara jika kamu sedang cuti, liburan, atau istirahat operasional.
                    @endif
                </div>

                <form action="{{ route('seller.store.toggle_status') }}" method="POST">
                    @csrf
                    @if($store->isClosed())
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-2xl shadow-md transition transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                            <span>▶️</span>
                            <span>Buka Toko Kembali Sekarang</span>
                        </button>
                    @else
                        <button type="submit" 
                                onclick="return confirm('Apakah kamu yakin ingin menutup toko sementara (mode libur)? Pembeli tidak akan dapat melakukan pesanan baru hingga kamu membukanya kembali.')"
                                class="w-full sm:w-auto px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs rounded-2xl shadow-md transition transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                            <span>⏸️</span>
                            <span>Tutup Toko Sementara (Mode Libur)</span>
                        </button>
                    @endif
                </form>
            </div>
        @endif
    </div>

    <!-- DANGER ZONE: GULUNG TIKAR / TUTUP TOKO PERMANEN -->
    <div x-data="{ openDangerModal: false }" class="bg-rose-50/50 rounded-3xl p-6 sm:p-8 border border-rose-200 shadow-xs space-y-5">
        <div class="flex items-center gap-3 pb-4 border-b border-rose-200">
            <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl shrink-0">
                🚨
            </div>
            <div>
                <h2 class="text-base font-black text-rose-950">Zona Berbahaya: Gulung Tikar / Tutup Toko Permanen</h2>
                <p class="text-xs text-rose-800/80">Penutupan toko secara permanen dan berhenti beroperasi di platform NusantaraMart.</p>
            </div>
        </div>

        <div class="text-xs text-rose-900/90 leading-relaxed space-y-2">
            <p>
                Jika kamu memutuskan untuk <strong>berhenti beroperasi selamanya (gulung tikar)</strong>, kamu dapat menutup toko ini secara permanen.
            </p>
            <p class="font-bold text-rose-950">
                ⚠️ Perhatian Konsekuensi Penting:
            </p>
            <ul class="list-disc list-inside space-y-1 text-rose-800">
                <li>Seluruh produk yang ada di etalase tokomu akan dihapus secara permanen.</li>
                <li>Profil toko tidak akan dapat diakses kembali oleh siapapun.</li>
                <li>Toko tidak dapat ditutup apabila masih ada pesanan pembeli yang sedang berjalan (status <em>Menunggu</em> atau <em>Diproses</em>).</li>
                <li><strong>Akun pelanggan milikmu tetap aman dan aktif</strong> sehingga kamu tetap bisa berbelanja produk di NusantaraMart sebagai pembeli.</li>
            </ul>
        </div>

        <div class="pt-2 flex justify-end">
            <button type="button" 
                    @click="openDangerModal = true"
                    class="px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-2xl shadow-md transition transform active:scale-95 flex items-center gap-2 cursor-pointer">
                <span>🛑</span>
                <span>Tutup Toko Permanen (Gulung Tikar)</span>
            </button>
        </div>

        <!-- MODAL KONFIRMASI TUTUP TOKO PERMANEN -->
        <div x-show="openDangerModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="openDangerModal = false"
                 class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-rose-200 space-y-5 text-left"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl shrink-0">
                        🛑
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2D241E]">Konfirmasi Gulung Tikar</h3>
                        <p class="text-xs text-[#7A6C60]">Tutup Toko "{{ $store->name }}" Selamanya</p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs space-y-2">
                    <p class="font-black text-rose-950">Apakah kamu benar-benar yakin ingin berhenti beroperasi?</p>
                    <p>Tindakan ini <strong>tidak dapat dibatalkan</strong>. Toko dan seluruh katalog produk akan langsung dihapus dari sistem NusantaraMart.</p>
                </div>

                <form action="{{ route('seller.store.close_permanently') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-[#2D241E] mb-1.5">
                            Konfirmasi Kata Sandi Akunmu <span class="text-rose-600">*</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               required
                               placeholder="Masukkan password akun untuk otorisasi..."
                               class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        <p class="text-[10px] text-[#8A7C70] mt-1">Demi keamanan tokomu, masukkan password akunmu untuk melanjutkan.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" 
                                @click="openDangerModal = false"
                                class="px-5 py-2.5 rounded-xl border border-[#EAE1D7] text-[#5A4B40] hover:bg-[#FAF8F5] text-xs font-bold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-md transition transform active:scale-95 flex items-center gap-1.5 cursor-pointer">
                            <span>🛑</span>
                            <span>Ya, Tutup Toko Permanen</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>

</div>
@endsection
