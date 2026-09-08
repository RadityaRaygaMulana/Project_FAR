@extends('seller.layout')

@section('title', 'Tambah Produk Baru — Seller Center')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Bar with Back Link -->
    <div class="flex items-center gap-3">
        <a href="{{ route('seller.products.index') }}" 
           class="w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] flex items-center justify-center transition shadow-2xs">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] tracking-tight">
                Tambah Produk Baru 🛍️
            </h1>
            <p class="text-xs text-[#8A7C70]">Isi informasi produk dengan lengkap untuk mulai menjualnya di marketplace</p>
        </div>
    </div>

    <!-- Product Form Card -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-xs space-y-6">
        <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Product Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                    Nama Produk Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       required 
                       minlength="3" 
                       maxlength="150"
                       value="{{ old('name') }}" 
                       placeholder="Contoh: Keripik Tempe Pedas Manis 250gr Premium"
                       class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                @error('name')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Weight (2 Cols) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                        Kategori Produk <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" 
                            id="category_id" 
                            required 
                            class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->icon }} {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="weight_grams" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                        Berat Bersih Produk (Gram) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="weight_grams" 
                           id="weight_grams" 
                           required 
                           min="1" 
                           value="{{ old('weight_grams') }}" 
                           placeholder="Contoh: 250"
                           class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                    @error('weight_grams')
                        <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Pricing & Stock (3 Cols) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                        Harga Normal (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="price" 
                           id="price" 
                           required 
                           min="1000" 
                           step="100"
                           value="{{ old('price') }}" 
                           placeholder="Contoh: 150000"
                           class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                    @error('price')
                        <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="discount_price" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                        Harga Diskon / Coret (Rp)
                    </label>
                    <input type="number" 
                           name="discount_price" 
                           id="discount_price" 
                           min="1000" 
                           step="100"
                           value="{{ old('discount_price') }}" 
                           placeholder="Kosongkan jika tanpa diskon"
                           class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                    @error('discount_price')
                        <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stock" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                        Jumlah Stok Barang <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="stock" 
                           id="stock" 
                           required 
                           min="0" 
                           value="{{ old('stock') }}" 
                           placeholder="Contoh: 50"
                           class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                    @error('stock')
                        <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                    Deskripsi Lengkap Produk <span class="text-red-500">*</span>
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="5" 
                          required 
                          minlength="10"
                          placeholder="Jelaskan spesifikasi produk, keunggulan, bahan, cara penggunaan, dan garansi..."
                          class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20 leading-relaxed">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Foto Produk (Bisa Lebih Dari 1 Foto) -->
            <div x-data="multiPhotoUpload()">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-[#2D241E]">
                        Foto Produk & Galeri
                        <span class="font-normal text-[#8A7C70] ml-1">(Bisa upload lebih dari 1 foto · Maks. 2MB per foto)</span>
                    </label>
                    <span class="text-[11px] text-[#6B4226] font-bold" x-show="photos.length > 0" x-text="photos.length + ' foto dipilih'"></span>
                </div>

                <!-- Drop Zone / Picker -->
                <div class="space-y-3">
                    <div class="relative"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="handleDrop($event)">
                        <label for="product_images"
                               :class="dragging ? 'border-[#6B4226] bg-[#FAF4ED]' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40'"
                               class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-2xl cursor-pointer transition-all py-6 px-4 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-[#EAE1D7] flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#6B4226]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-[#6B4226]">Klik untuk memilih atau seret foto ke sini</span>
                            <span class="text-[11px] text-[#8A7C70]">Bisa pilih beberapa foto sekaligus (JPG, PNG, WEBP · Maks 2MB)</span>
                            <input type="file" id="product_images" name="images[]" multiple accept="image/*" class="hidden" @change="handleFiles($event)">
                        </label>
                    </div>

                    <!-- Previews Grid -->
                    <template x-if="photos.length > 0">
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 pt-1">
                            <template x-for="(photo, index) in photos" :key="index">
                                <div class="relative group rounded-2xl overflow-hidden border border-[#EAE1D7] bg-white aspect-square shadow-2xs">
                                    <img :src="photo.preview" alt="Preview" class="w-full h-full object-cover">
                                    <span x-show="index === 0" class="absolute top-1.5 left-1.5 bg-[#6B4226] text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-xs">
                                        ⭐ Utama
                                    </span>
                                    <button type="button" 
                                            @click.prevent="removePhoto(index)"
                                            class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs font-bold shadow transition cursor-pointer"
                                            title="Hapus foto ini">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                @error('images')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Varian Produk -->
            <div x-data="variantsManager()" class="border-t border-[#F2EAE0] pt-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-black text-[#2D241E]">Varian Produk</h3>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5">Contoh: Rasa Pedas, Ukuran S/M/L, Warna Merah — bisa + foto sendiri per varian</p>
                    </div>
                    <button type="button"
                            @click="addVariant()"
                            class="flex items-center gap-1.5 px-4 py-2 bg-[#6B4226]/10 hover:bg-[#6B4226]/20 text-[#6B4226] font-bold text-xs rounded-xl transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Varian
                    </button>
                </div>

                <!-- Variant Rows -->
                <div class="space-y-3">
                    <template x-for="(variant, index) in variants" :key="variant.key">
                        <div class="flex gap-3 items-start bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl p-4">

                            <!-- Variant Photo -->
                            <div class="shrink-0">
                                <label :for="'vimg_' + variant.key"
                                       class="w-20 h-20 rounded-xl border-2 border-dashed border-[#EAE1D7] bg-white hover:border-[#6B4226]/40 hover:bg-[#FAF4ED] flex items-center justify-center cursor-pointer transition overflow-hidden relative group">
                                    <img x-show="variant.preview" :src="variant.preview" class="w-full h-full object-cover rounded-xl" alt="Varian">
                                    <div x-show="!variant.preview" class="flex flex-col items-center gap-1 text-[#8A7C70]">
                                        <svg class="w-6 h-6 text-[#6B4226]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-[10px] text-center leading-tight">Foto<br>Varian</span>
                                    </div>
                                    <input type="file"
                                           :id="'vimg_' + variant.key"
                                           :name="'variants[' + index + '][image]'"
                                           accept="image/*"
                                           class="hidden"
                                           @change="handleVariantImage($event, variant)">
                                </label>
                            </div>

                            <!-- Variant Fields -->
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="block text-[11px] font-bold text-[#2D241E] mb-1">Nama Varian <span class="text-red-500">*</span></label>
                                    <input type="text"
                                           :name="'variants[' + index + '][name]'"
                                           x-model="variant.name"
                                           required
                                           placeholder="cth: Rasa Pedas"
                                           maxlength="100"
                                           class="w-full px-3 py-2.5 bg-white border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-[#2D241E] mb-1">Harga Varian (Rp)</label>
                                    <input type="number"
                                           :name="'variants[' + index + '][price]'"
                                           x-model="variant.price"
                                           min="0"
                                           step="100"
                                           placeholder="Opsional"
                                           class="w-full px-3 py-2.5 bg-white border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-[#2D241E] mb-1">Stok Varian</label>
                                    <input type="number"
                                           :name="'variants[' + index + '][stock]'"
                                           x-model="variant.stock"
                                           min="0"
                                           placeholder="Contoh: 10"
                                           class="w-full px-3 py-2.5 bg-white border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <button type="button"
                                    @click="removeVariant(variant.key)"
                                    class="shrink-0 w-8 h-8 mt-1 rounded-full bg-red-50 hover:bg-red-100 text-red-500 hover:text-red-700 flex items-center justify-center transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div x-show="variants.length === 0"
                         class="text-center py-6 text-[#8A7C70] text-xs">
                        Belum ada varian. Klik <strong class="text-[#6B4226]">+ Tambah Varian</strong> jika produk ini memiliki pilihan.
                    </div>
                </div>
            </div>

            <!-- Pilihan Metode Pembayaran (Multi Select) -->
            <div class="pt-4 border-t border-[#F2EAE0] space-y-3">
                <input type="hidden" name="payment_methods_submitted" value="1">
                <div>
                    <label class="block text-xs font-bold text-[#2D241E]">
                        Metode Pembayaran yang Diterima <span class="text-red-500">*</span>
                    </label>
                    <p class="text-[11px] text-[#8A7C70] mt-0.5">
                        Pilih metode pembayaran yang kamu sediakan untuk produk ini (bisa pilih lebih dari satu, minimal 1 metode harus aktif).
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- QRIS -->
                    <label class="relative flex items-start gap-3 p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] cursor-pointer transition select-none">
                        <input type="checkbox" 
                               name="allowed_payment_methods[]" 
                               value="qris" 
                               {{ in_array('qris', old('allowed_payment_methods', ['qris', 'cod'])) ? 'checked' : '' }}
                               class="w-4 h-4 text-[#6B4226] border-stone-300 rounded focus:ring-[#6B4226] mt-0.5 cursor-pointer">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-bold text-[#2D241E] block">📱 QRIS Instan</span>
                            <span class="text-[10px] text-[#8A7C70] block mt-0.5">Semua E-Wallet & Bank</span>
                        </div>
                    </label>

                    <!-- COD -->
                    <label class="relative flex items-start gap-3 p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] cursor-pointer transition select-none">
                        <input type="checkbox" 
                               name="allowed_payment_methods[]" 
                               value="cod" 
                               {{ in_array('cod', old('allowed_payment_methods', ['qris', 'cod'])) ? 'checked' : '' }}
                               class="w-4 h-4 text-[#6B4226] border-stone-300 rounded focus:ring-[#6B4226] mt-0.5 cursor-pointer">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-bold text-[#2D241E] block">💵 COD (Bayar di Tempat)</span>
                            <span class="text-[10px] text-[#8A7C70] block mt-0.5">Bayar tunai ke kurir</span>
                        </div>
                    </label>
                </div>
                @error('allowed_payment_methods')
                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Shipping & Voucher Promo Settings -->
            <div class="pt-4 border-t border-[#F2EAE0] space-y-4" x-data="{ isFreeShipping: {{ old('is_free_shipping') ? 'true' : 'false' }} }">
                <div>
                    <h3 class="text-sm font-bold text-[#2D241E] flex items-center gap-1.5">
                        <span>🚚</span>
                        <span>Pengaturan Bebas Ongkir & Voucher</span>
                    </h3>
                    <p class="text-xs text-[#8A7C70] mt-0.5">
                        Tentukan apakah produk ini menyediakan promo gratis ongkir (dan batas minimal belanja) serta izin penggunaan voucher diskon.
                    </p>
                </div>

                <div class="space-y-3">
                    <!-- Free Shipping Option -->
                    <div class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="hidden" name="is_free_shipping" value="0">
                            <input type="checkbox" 
                                   name="is_free_shipping" 
                                   value="1" 
                                   x-model="isFreeShipping"
                                   {{ old('is_free_shipping') ? 'checked' : '' }}
                                   class="w-4.5 h-4.5 text-[#6B4226] border-stone-300 rounded focus:ring-[#6B4226] mt-0.5 cursor-pointer">
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-bold text-[#2D241E] block">Sediakan Promo Bebas Ongkir (Gratis Ongkir) 🚚</span>
                                <span class="text-[11px] text-[#8A7C70] block mt-0.5">Aktifkan jika produk ini memberikan gratis ongkir bagi pembeli.</span>
                            </div>
                        </label>

                        <!-- Min Spend for Free Shipping (conditionally shown) -->
                        <div x-show="isFreeShipping" x-cloak class="pt-2 pl-7.5 border-t border-[#EAE1D7]/60">
                            <label for="free_shipping_min_spend" class="block text-xs font-bold text-[#2D241E] mb-1">
                                Minimal Belanja untuk Bebas Ongkir (Rp)
                            </label>
                            <div class="relative max-w-xs">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-[#8A7C70]">Rp</span>
                                <input type="number" 
                                       name="free_shipping_min_spend" 
                                       id="free_shipping_min_spend" 
                                       min="0" 
                                       step="1000"
                                       value="{{ old('free_shipping_min_spend', 0) }}" 
                                       placeholder="0"
                                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                            </div>
                            <p class="text-[11px] text-[#8A7C70] mt-1">
                                Isi <strong>0</strong> jika gratis ongkir berlaku tanpa syarat minimal belanja (langsung gratis ongkir).
                            </p>
                            @error('free_shipping_min_spend')
                                <p class="text-[11px] text-red-600 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Allow Vouchers Option -->
                    <div class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5]">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="hidden" name="allow_vouchers" value="0">
                            <input type="checkbox" 
                                   name="allow_vouchers" 
                                   value="1" 
                                   {{ old('allow_vouchers', '1') == '1' ? 'checked' : '' }}
                                   class="w-4.5 h-4.5 text-[#6B4226] border-stone-300 rounded focus:ring-[#6B4226] mt-0.5 cursor-pointer">
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-bold text-[#2D241E] block">Izinkan Penggunaan Voucher Marketplace 🎟️</span>
                                <span class="text-[11px] text-[#8A7C70] block mt-0.5">Jika dicentang, pembeli dapat menggunakan voucher diskon atau cashback pada produk ini. Hilangkan centang jika produk tidak boleh dikenakan potongan voucher.</span>
                            </div>
                        </label>
                        @error('allow_vouchers')
                            <p class="text-[11px] text-red-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Publish Status Checkbox -->
            <div class="pt-2 border-t border-[#F2EAE0]">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" 
                           name="is_available" 
                           value="1" 
                           checked 
                           class="w-5 h-5 text-[#6B4226] border-stone-300 rounded-md focus:ring-[#6B4226] cursor-pointer">
                    <div>
                        <span class="text-xs font-bold text-[#2D241E] block">Tayangkan Produk Sekarang</span>
                        <span class="text-[11px] text-[#8A7C70] block">Produk akan langsung dapat dicari dan dibeli oleh pembeli di NusantaraMart.</span>
                    </div>
                </label>
            </div>

            <!-- Submit & Cancel Buttons -->
            <div class="pt-4 border-t border-[#F2EAE0] flex items-center justify-between gap-4">
                <a href="{{ route('seller.products.index') }}" class="text-xs font-bold text-[#8A7C70] hover:text-[#2D241E] transition">
                    ← Batalkan
                </a>

                <button type="submit" 
                        class="px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm rounded-2xl shadow-md transition transform active:scale-95 cursor-pointer">
                    Simpan & Publikasikan Produk 🚀
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
function multiPhotoUpload() {
    return {
        photos: [],
        dragging: false,
        handleFiles(event) {
            const files = Array.from(event.target.files);
            this.addFiles(files);
        },
        handleDrop(event) {
            this.dragging = false;
            const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            this.addFiles(files);
        },
        addFiles(files) {
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photos.push({ file: file, preview: e.target.result });
                    this.syncInput();
                };
                reader.readAsDataURL(file);
            });
        },
        removePhoto(index) {
            this.photos.splice(index, 1);
            this.syncInput();
        },
        syncInput() {
            try {
                const dt = new DataTransfer();
                this.photos.forEach(p => dt.items.add(p.file));
                const input = document.getElementById('product_images');
                if (input) input.files = dt.files;
            } catch(e) {}
        }
    };
}

function variantsManager() {
    return {
        variants: [],
        nextKey: 0,
        addVariant() {
            this.variants.push({ key: this.nextKey++, name: '', price: '', stock: '', preview: null });
        },
        removeVariant(key) {
            this.variants = this.variants.filter(v => v.key !== key);
        },
        handleVariantImage(event, variant) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => { variant.preview = e.target.result; };
                reader.readAsDataURL(file);
            }
        }
    };
}
</script>
@endpush
@endsection
