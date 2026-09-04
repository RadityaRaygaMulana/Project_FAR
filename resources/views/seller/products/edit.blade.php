@extends('seller.layout')

@section('title', 'Ubah Produk: ' . $product->name . ' — Seller Center')

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
                Ubah Data Produk ✏️
            </h1>
            <p class="text-xs text-[#8A7C70]">Perbarui harga, stok, foto, varian, atau rincian deskripsi produk</p>
        </div>
    </div>

    <!-- Product Edit Form Card -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-xs space-y-6">
        <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

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
                       value="{{ old('name', $product->name) }}" 
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
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
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
                           value="{{ old('weight_grams', $product->weight_grams) }}" 
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
                           value="{{ old('price', $product->price) }}" 
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
                           value="{{ old('discount_price', $product->discount_price) }}" 
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
                           value="{{ old('stock', $product->stock) }}" 
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
                          class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs sm:text-sm text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20 leading-relaxed">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Foto Produk & Galeri (Bisa Lebih Dari 1 Foto) -->
            @php
                $existingPhotos = [];
                if ($product->image_path) {
                    $existingPhotos[] = [
                        'path' => $product->image_path,
                        'url' => Storage::disk('public')->exists($product->image_path) ? Storage::url($product->image_path) : ($product->image_url ?: '')
                    ];
                }
                if (is_array($product->gallery_images)) {
                    foreach ($product->gallery_images as $gPath) {
                        if ($gPath && $gPath !== $product->image_path) {
                            $existingPhotos[] = [
                                'path' => $gPath,
                                'url' => Storage::disk('public')->exists($gPath) ? Storage::url($gPath) : $gPath
                            ];
                        }
                    }
                }
            @endphp
            <div x-data="multiPhotoUploadEdit({{ json_encode($existingPhotos) }})">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-[#2D241E]">
                        Foto Produk & Galeri
                        <span class="font-normal text-[#8A7C70] ml-1">(Bisa upload lebih dari 1 foto · Maks. 2MB per foto)</span>
                    </label>
                    <span class="text-[11px] text-[#6B4226] font-bold" x-text="(existingList.length + newPhotos.length) + ' foto total'"></span>
                </div>

                <div class="space-y-3">
                    <!-- Drop Zone / Picker for New Photos -->
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
                            <span class="text-xs font-bold text-[#6B4226]">+ Tambah Foto Baru (Bisa Banyak)</span>
                            <span class="text-[11px] text-[#8A7C70]">Klik untuk memilih atau seret foto ke sini (JPG, PNG, WEBP)</span>
                            <input type="file" id="product_images" name="images[]" multiple accept="image/*" class="hidden" @change="handleFiles($event)">
                        </label>
                    </div>

                    <!-- Hidden Inputs for Retained Existing Photos -->
                    <template x-for="(item, idx) in existingList" :key="item.path">
                        <input type="hidden" name="existing_gallery_images[]" :value="item.path">
                    </template>

                    <!-- Unified Previews Grid (Existing + New) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 pt-1" x-show="existingList.length > 0 || newPhotos.length > 0">
                        <!-- Existing Photos -->
                        <template x-for="(item, index) in existingList" :key="'exist_' + index">
                            <div class="relative group rounded-2xl overflow-hidden border border-[#EAE1D7] bg-white aspect-square shadow-2xs">
                                <img :src="item.url" alt="Foto Produk" class="w-full h-full object-cover">
                                <span x-show="index === 0" class="absolute top-1.5 left-1.5 bg-[#6B4226] text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-xs">
                                    ⭐ Utama
                                </span>
                                <button type="button" 
                                        @click.prevent="removeExisting(index)"
                                        class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs font-bold shadow transition cursor-pointer"
                                        title="Hapus foto ini">
                                    ✕
                                </button>
                            </div>
                        </template>

                        <!-- Newly Added Photos -->
                        <template x-for="(photo, index) in newPhotos" :key="'new_' + index">
                            <div class="relative group rounded-2xl overflow-hidden border-2 border-dashed border-[#6B4226] bg-white aspect-square shadow-2xs">
                                <img :src="photo.preview" alt="Foto Baru" class="w-full h-full object-cover">
                                <span class="absolute top-1.5 left-1.5 bg-emerald-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-xs">
                                    + Baru
                                </span>
                                <button type="button" 
                                        @click.prevent="removeNewPhoto(index)"
                                        class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs font-bold shadow transition cursor-pointer"
                                        title="Hapus foto baru ini">
                                    ✕
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                @error('images')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="text-[11px] text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Varian Produk -->
            <div x-data="variantsManagerEdit({{ $product->variants->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $v->price ?? '', 'stock' => $v->stock, 'preview' => $v->variant_image_url ?? null]) ->toJson() }})"
                 class="border-t border-[#F2EAE0] pt-6">
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

                <div class="space-y-3">
                    <template x-for="(variant, index) in variants" :key="variant.key">
                        <div class="flex gap-3 items-start bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl p-4">

                            <!-- Hidden existing ID -->
                            <input x-show="false" type="hidden" :name="'variants[' + index + '][id]'" :value="variant.id || ''">

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

                    <div x-show="variants.length === 0"
                         class="text-center py-6 text-[#8A7C70] text-xs">
                        Belum ada varian. Klik <strong class="text-[#6B4226]">+ Tambah Varian</strong> jika produk ini memiliki pilihan.
                    </div>
                </div>
            </div>

            <!-- Publish Status Checkbox -->
            <div class="pt-2 border-t border-[#F2EAE0]">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" 
                           name="is_available" 
                           value="1" 
                           {{ old('is_available', $product->is_available) ? 'checked' : '' }}
                           class="w-5 h-5 text-[#6B4226] border-stone-300 rounded-md focus:ring-[#6B4226] cursor-pointer">
                    <div>
                        <span class="text-xs font-bold text-[#2D241E] block">Produk Aktif Tayang</span>
                        <span class="text-[11px] text-[#8A7C70] block">Jika dicentang, produk dapat dicari dan dibeli oleh pelanggan.</span>
                    </div>
                </label>
            </div>

            <!-- Submit & Cancel Buttons -->
            <div class="pt-4 border-t border-[#F2EAE0] flex items-center justify-between gap-4">
                <a href="{{ route('seller.products.index') }}" class="text-xs font-bold text-[#8A7C70] hover:text-[#2D241E] transition">
                    ← Kembali ke Katalog
                </a>

                <button type="submit" 
                        class="px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm rounded-2xl shadow-md transition transform active:scale-95 cursor-pointer">
                    Simpan Perubahan Produk 💾
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
function multiPhotoUploadEdit(existing) {
    return {
        existingList: Array.isArray(existing) ? existing : [],
        newPhotos: [],
        dragging: false,
        removeExisting(index) {
            this.existingList.splice(index, 1);
        },
        handleFiles(event) {
            const files = Array.from(event.target.files);
            this.addNewFiles(files);
        },
        handleDrop(event) {
            this.dragging = false;
            const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            this.addNewFiles(files);
        },
        addNewFiles(files) {
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newPhotos.push({ file: file, preview: e.target.result });
                    this.syncInput();
                };
                reader.readAsDataURL(file);
            });
        },
        removeNewPhoto(index) {
            this.newPhotos.splice(index, 1);
            this.syncInput();
        },
        syncInput() {
            try {
                const dt = new DataTransfer();
                this.newPhotos.forEach(p => dt.items.add(p.file));
                const input = document.getElementById('product_images');
                if (input) input.files = dt.files;
            } catch(e) {}
        }
    };
}

function variantsManagerEdit(existing) {
    return {
        variants: existing.map((v, i) => ({ ...v, key: i })),
        nextKey: existing.length,
        addVariant() {
            this.variants.push({ key: this.nextKey++, id: null, name: '', price: '', stock: '', preview: null });
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
