@extends('layouts.app')

@section('title', $product->name . ' — NusantaraMart')

@section('content')
@php
    $allPhotos = $product->allImageUrls();
    $firstPhoto = !empty($allPhotos) ? $allPhotos[0] : null;
    $variantsData = $product->variants->map(function($v) use ($product) {
        return [
            'id' => $v->id,
            'name' => $v->name,
            'price' => $v->price ?: $product->effective_price,
            'formatted_price' => 'Rp ' . number_format($v->price ?: $product->effective_price, 0, ',', '.'),
            'stock' => $v->stock,
            'image' => $v->variant_image_url
        ];
    });
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8" 
     x-data="{ 
         qty: 1, 
         hasVariants: {{ $product->variants->isNotEmpty() ? 'true' : 'false' }},
         variants: {{ Js::from($variantsData) }},
         selectedVariant: null,
         basePrice: {{ $product->effective_price }},
         baseStock: {{ max(1, $product->stock) }},
         activePhoto: '{{ $firstPhoto }}',
         showVariantCardModal: false,
         modalMode: 'cart',
         validationError: '',
         isOwnProduct: {{ (Auth::check() && $product->isOwnedBy(Auth::user())) ? 'true' : 'false' }},
         isStoreClosed: {{ ($product->store && $product->store->isClosed()) ? 'true' : 'false' }},
         isStoreSuspended: {{ ($product->store && $product->store->isSuspended()) ? 'true' : 'false' }},

         // Store Follow State
         isFollowing: {{ $isFollowingStore ? 'true' : 'false' }},
         followersCount: {{ $storeFollowersCount }},
         followingLoading: false,

         // Product Reviews Lightbox & Filter
         selectedStarFilter: 'all',
         showPhotoModal: false,
         activeReviewPhoto: null,

         get currentPrice() {
             return this.selectedVariant ? this.selectedVariant.price : this.basePrice;
         },
         get currentStock() {
             return this.selectedVariant ? this.selectedVariant.stock : this.baseStock;
         },
         get currentPhoto() {
             return this.activePhoto;
         },
         get subtotal() { 
             return this.qty * this.currentPrice; 
         },
         increase() {
             if (this.qty < this.currentStock) this.qty++;
         },
         decrease() {
             if (this.qty > 1) this.qty--;
         },
         selectVariant(v) {
             if (!v) return;
             if (this.selectedVariant && this.selectedVariant.id === v.id) {
                 if (v.image && this.activePhoto !== v.image) {
                     this.activePhoto = v.image;
                     return;
                 }
                 this.selectedVariant = null;
                 this.activePhoto = '{{ $firstPhoto }}';
                 return;
             }
             this.selectedVariant = v;
             this.validationError = '';
             if (this.qty > v.stock) {
                 this.qty = Math.max(1, v.stock);
             }
             if (v.image) {
                 this.activePhoto = v.image;
             }
         },
         openCardModal(mode = 'cart') {
             if (this.isStoreSuspended) {
                 alert('Toko ini sedang dinonaktifkan oleh administrator dan tidak dapat menerima pesanan.');
                 return;
             }
             if (this.isStoreClosed) {
                 alert('Toko sedang tutup sementara (mode libur) dan belum dapat melayani pesanan saat ini.');
                 return;
             }
             if (this.isOwnProduct) {
                 alert('Kamu tidak dapat membeli produk dari tokomu sendiri.');
                 return;
             }
             @guest
                 window.showAuthModal({
                     icon: mode === 'buy' ? '⚡' : '🛒',
                     title: mode === 'buy' ? 'Beli Produk Sekarang' : 'Masukkan ke Keranjang',
                     message: mode === 'buy'
                         ? 'Yuk masuk ke akunmu terlebih dahulu untuk langsung membeli produk pilihanmu dan checkout dengan aman.'
                         : 'Yuk masuk ke akunmu terlebih dahulu untuk menyimpan produk ini ke keranjang belanja kamu.'
                 });
                 return;
             @endguest

             this.modalMode = mode;
             if (this.hasVariants && !this.selectedVariant && this.variants.length > 0) {
                 this.selectVariant(this.variants[0]);
             }
             this.showVariantCardModal = true;
         },
         closeCardModal() {
             this.showVariantCardModal = false;
             this.validationError = '';
         },
         submitCardAction() {
             @guest
                 window.showAuthModal({
                     icon: this.modalMode === 'buy' ? '⚡' : '🛒',
                     title: this.modalMode === 'buy' ? 'Beli Produk Sekarang' : 'Masukkan ke Keranjang',
                     message: this.modalMode === 'buy'
                         ? 'Yuk masuk ke akunmu terlebih dahulu untuk langsung membeli produk pilihanmu dan checkout dengan aman.'
                         : 'Yuk masuk ke akunmu terlebih dahulu untuk menyimpan produk ini ke keranjang belanja kamu.'
                 });
                 return;
             @endguest

             if (this.hasVariants && !this.selectedVariant) {
                 this.validationError = 'Silakan pilih varian terlebih dahulu!';
                 return;
             }
             if (this.isOwnProduct) {
                 alert('Kamu tidak dapat membeli produk dari tokomu sendiri.');
                 return;
             }
             const productPayload = {
                 id: {{ $product->id }},
                 store_id: {{ $product->store_id ? $product->store_id : 'null' }},
                 name: '{{ addslashes($product->name) }}',
                 price: this.currentPrice,
                 brand: '{{ addslashes($product->brand ?? 'NusantaraMart') }}',
                 badge: '{{ $product->badge }}',
                 icon: '{{ $product->category->icon ?? '🛍️' }}',
                 image_url: (this.selectedVariant && this.selectedVariant.image) ? this.selectedVariant.image : this.currentPhoto,
                 variant_id: this.selectedVariant ? this.selectedVariant.id : null,
                 variant_name: this.selectedVariant ? this.selectedVariant.name : null,
                 allowed_payment_methods: {{ json_encode($product->allowed_payment_methods) }},
                 is_free_shipping: {{ $product->is_free_shipping ? 'true' : 'false' }},
                 free_shipping_min_spend: {{ (int) ($product->free_shipping_min_spend ?? 0) }},
                 allow_vouchers: {{ $product->allow_vouchers ? 'true' : 'false' }},
                 slug: '{{ $product->slug }}',
                 store_name: '{{ addslashes($product->store ? $product->store->name : ($product->brand ?? 'NusantaraMart')) }}',
                 store_slug: '{{ $product->store ? $product->store->slug : '' }}',
                 store_city: '{{ addslashes($product->store ? ($product->store->city ?? 'Indonesia') : 'Indonesia') }}'
             };

             if (this.modalMode === 'buy') {
                 if (window.snackCart && typeof window.snackCart.buyNow === 'function') {
                     window.snackCart.buyNow(productPayload, this.qty);
                 } else if (window.snackCart) {
                     window.snackCart.addToCart(productPayload, this.qty);
                 }
                 this.showVariantCardModal = false;
                 window.location.href = '{{ route('checkout.show') }}';
                 return;
             }

             if (window.snackCart) {
                 window.snackCart.addToCart(productPayload, this.qty);
             } else if (typeof window.addToCartGlobal === 'function') {
                 window.addToCartGlobal(productPayload, this.qty);
             } else {
                 window.dispatchEvent(new CustomEvent('add-to-cart', {
                     detail: { product: productPayload, quantity: this.qty }
                 }));
             }

             this.showVariantCardModal = false;
         },
         addToCart(product, qty = 1) {
             @guest
                 window.showAuthModal({
                     icon: '🛒',
                     title: 'Masukkan ke Keranjang',
                     message: 'Yuk masuk ke akunmu terlebih dahulu untuk menyimpan produk ini ke keranjang belanja kamu.'
                 });
                 return;
             @endguest

             if (window.snackCart) {
                 window.snackCart.addToCart(product, qty);
             } else if (typeof window.addToCartGlobal === 'function') {
                 window.addToCartGlobal(product, qty);
             }
         },

         async toggleFollow() {
             @guest
                 window.showAuthModal({
                     icon: '🏪',
                     title: 'Ikuti Toko Resmi',
                     message: 'Yuk masuk ke akunmu terlebih dahulu untuk mengikuti toko ini dan dapatkan info produk serta voucher diskon eksklusif!'
                 });
                 return;
             @endguest

             if (this.followingLoading) return;
             this.followingLoading = true;

             try {
                 const res = await fetch('{{ $store ? route('store.toggle_follow', $store->slug) : '#' }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     }
                 });
                 const data = await res.json();
                 if (res.ok) {
                     this.isFollowing = data.is_following;
                     this.followersCount = data.followers_count;
                     if (window.snackCart) {
                         window.snackCart.showToast(data.message);
                     }
                 } else {
                     alert(data.error || 'Gagal mengubah status mengikuti toko.');
                 }
             } catch (e) {
                 console.error(e);
             } finally {
                 this.followingLoading = false;
             }
         }
     }">

    <!-- TOP NAVIGATION & BACK BUTTON BAR -->
    <div class="flex items-center gap-3 sm:gap-4 pb-1">
        <button type="button" 
                onclick="window.smartNav.goBack('{{ route('home') }}')" 
                class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                title="Kembali">
            <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <!-- BREADCRUMBS NAVIGATION -->
        <nav class="flex items-center gap-2 text-xs text-[#8A7C70] overflow-x-auto py-1">
            <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition flex items-center gap-1 shrink-0">
                <span>🏠</span>
                <span>Beranda</span>
            </a>
            <span>/</span>
            @if($product->category)
                <a href="{{ route('home', ['category' => $product->category->slug]) }}#katalog" class="hover:text-[#6B4226] transition shrink-0">
                    {{ $product->category->name }}
                </a>
                <span>/</span>
            @endif
            <span class="text-[#2D241E] font-bold truncate max-w-xs sm:max-w-md">{{ $product->name }}</span>
        </nav>
    </div>

    <!-- MAIN PRODUCT SHOWCASE CARD (SHOPEE / TOKOPEDIA PDP STYLE) -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-8 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
            
            <!-- LEFT COLUMN: PRODUCT VISUAL STAGE & ASSURANCES (5 COLS) -->
            <div class="lg:col-span-5 space-y-4">
                <!-- Visual Container -->
                <div class="relative h-80 sm:h-96 rounded-3xl bg-gradient-to-b from-[#FAF8F5] to-[#FAF4ED] border border-[#F2EAE0] flex items-center justify-center p-4 overflow-hidden group shadow-inner">
                    <!-- Discount Badge -->
                    @if($product->hasDiscount())
                        <div class="absolute top-4 left-4 z-10">
                            <span class="bg-[#D9381E] text-white text-xs font-black px-3 py-1 rounded-full shadow-xs tracking-wider">
                                -{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}% HEMAT
                            </span>
                        </div>
                    @endif

                    <!-- Store Official Badge -->
                    <div class="absolute top-4 right-4 z-10">
                        @if(strtolower($product->badge) === 'mall')
                            <span class="bg-[#D9381E] text-white text-xs font-black px-2.5 py-1 rounded-lg uppercase tracking-wider shadow-xs">
                                Nusantara Mall
                            </span>
                        @elseif(strtolower($product->badge) === 'official')
                            <span class="bg-[#1E56A0] text-white text-xs font-black px-2.5 py-1 rounded-lg uppercase tracking-wider shadow-xs">
                                Official Store
                            </span>
                        @else
                            <span class="bg-[#D4820A] text-white text-xs font-black px-2.5 py-1 rounded-lg uppercase tracking-wider shadow-xs">
                                Star+ Terpercaya
                            </span>
                        @endif
                    </div>

                    <!-- Visual Iconography / Image -->
                    <template x-if="currentPhoto">
                        <img :src="currentPhoto" alt="{{ $product->name }}" class="w-full h-full object-contain rounded-2xl select-none group-hover:scale-105 transition duration-300">
                    </template>
                    <template x-if="!currentPhoto">
                        <div class="text-7xl sm:text-8xl filter drop-shadow-md group-hover:scale-110 transition duration-300 transform select-none">
                            {{ $product->category->icon ?? '🛍️' }}
                        </div>
                    </template>
                </div>

                <!-- Thumbnails Gallery Row (If Multiple Photos or Variant Photos Exist) -->
                @if(count($allPhotos) > 1 || $product->variants->whereNotNull('image_path')->isNotEmpty())
                    <div class="flex items-center gap-2.5 overflow-x-auto pb-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                        @foreach($allPhotos as $idx => $photoUrl)
                            <button type="button" 
                                    @click="activePhoto = '{{ $photoUrl }}'"
                                    :class="currentPhoto === '{{ $photoUrl }}' ? 'ring-2 ring-[#6B4226] border-[#6B4226] scale-105' : 'border-[#EAE1D7] hover:border-[#6B4226]/40 opacity-70 hover:opacity-100'"
                                    class="w-16 h-16 rounded-xl border-2 bg-[#FAF8F5] overflow-hidden shrink-0 transition-all cursor-pointer shadow-2xs">
                                <img src="{{ $photoUrl }}" alt="Foto {{ $idx + 1 }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach

                        @foreach($product->variants as $variant)
                            @if($variant->variant_image_url)
                                <button type="button" 
                                        @click="selectVariant(variants.find(v => v.id === {{ $variant->id }}))"
                                        :class="currentPhoto === '{{ $variant->variant_image_url }}' ? 'ring-2 ring-[#6B4226] border-[#6B4226] scale-105' : 'border-[#EAE1D7] hover:border-[#6B4226]/40 opacity-70 hover:opacity-100'"
                                        class="w-16 h-16 rounded-xl border-2 bg-[#FAF8F5] overflow-hidden shrink-0 transition-all cursor-pointer shadow-2xs relative"
                                        title="Varian: {{ $variant->name }}">
                                    <img src="{{ $variant->variant_image_url }}" alt="{{ $variant->name }}" class="w-full h-full object-cover">
                                    <span class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[8px] truncate px-0.5 text-center">{{ $variant->name }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif

                <!-- Trust Guarantee Assurance Badges -->
                <div class="grid grid-cols-3 gap-2.5 pt-2">
                    <div class="p-3 bg-[#FAF8F5] rounded-2xl border border-[#F2EAE0] text-center space-y-1">
                        <span class="text-lg block">🛡️</span>
                        <p class="text-[11px] font-bold text-[#2D241E] leading-tight">100% Original</p>
                        <p class="text-[9px] text-[#8A7C70]">Garansi Uang Kembali</p>
                    </div>
                    <div class="p-3 bg-[#FAF8F5] rounded-2xl border border-[#F2EAE0] text-center space-y-1">
                        <span class="text-lg block">🚚</span>
                        @if($product->is_free_shipping)
                            <p class="text-[11px] font-bold text-emerald-700 leading-tight">Bebas Ongkir</p>
                            <p class="text-[9px] text-[#8A7C70]">{{ $product->free_shipping_min_spend > 0 ? 'Min. ' . 'Rp ' . number_format($product->free_shipping_min_spend, 0, ',', '.') : 'Tanpa Min. Belanja' }}</p>
                        @else
                            <p class="text-[11px] font-bold text-[#2D241E] leading-tight">Ongkir Standar</p>
                            <p class="text-[9px] text-[#8A7C70]">Mulai Rp 15.000</p>
                        @endif
                    </div>
                    <div class="p-3 bg-[#FAF8F5] rounded-2xl border border-[#F2EAE0] text-center space-y-1">
                        <span class="text-lg block">🔄</span>
                        <p class="text-[11px] font-bold text-[#2D241E] leading-tight">Retur 7 Hari</p>
                        <p class="text-[9px] text-[#8A7C70]">Klaim Mudah & Cepat</p>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: PRODUCT INFO, SPECS & PURCHASE (7 COLS) -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    
                    <!-- Product Title -->
                    <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] leading-snug">
                        {{ $product->name }}
                    </h1>

                    <!-- Social Proof / Rating & Sold Row -->
                    <div class="flex items-center gap-3 sm:gap-4 flex-wrap text-xs text-[#7A6C60] border-b border-[#F2EAE0] pb-4">
                        <div class="flex items-center gap-1 bg-amber-50 text-amber-900 px-2.5 py-1 rounded-lg border border-amber-200/80 font-black">
                            <span class="text-amber-500">★</span>
                            <span>{{ number_format($product->rating, 1) }}</span>
                            <span class="text-[10px] text-amber-700 font-normal">({{ number_format($product->sold_count > 0 ? (int)($product->sold_count / 3) : 85) }} ulasan)</span>
                        </div>
                        <span class="text-[#D1C7BD]">•</span>
                        <div>
                            <span class="font-extrabold text-[#2D241E]">{{ number_format($product->sold_count) }}</span>
                            <span>Terjual</span>
                        </div>
                        <span class="text-[#D1C7BD]">•</span>
                        <div>
                            <span class="font-extrabold {{ $product->stock > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $product->stock > 0 ? 'Stok Tersedia (' . $product->stock . ' unit)' : 'Stok Habis' }}
                            </span>
                        </div>
                    </div>

                    <!-- Price Banner Box (Tokopedia/Shopee Style) -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-[#FAF8F5] border border-[#F2EAE0] space-y-1">
                        <div class="flex items-baseline gap-3 flex-wrap">
                            <span class="text-2xl sm:text-3xl font-black text-[#D9381E] tracking-tight">
                                {{ $product->formatted_effective_price }}
                            </span>
                            @if($product->hasDiscount())
                                <span class="text-sm sm:text-base text-[#9E9084] line-through">
                                    {{ $product->formatted_price }}
                                </span>
                                <span class="px-2 py-0.5 bg-[#D9381E]/10 text-[#D9381E] text-xs font-black rounded-md">
                                    Hemat {{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-[#8A7C70] flex items-center gap-1.5 pt-1">
                            <span class="text-emerald-600 font-bold">✓ Harga Terbaik</span>
                            <span>• Berlaku promo & cashback NusantaraMart</span>
                        </p>
                    </div>

                    <!-- Spesifikasi Informasi Produk Table -->
                    <div class="space-y-2 pt-1 text-xs">
                        <p class="font-bold text-[#2D241E] uppercase tracking-wider text-[11px] text-[#8A7C70]">Spesifikasi Produk</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 bg-white rounded-2xl border border-[#F2EAE0] p-4 text-xs">
                            <div class="flex items-center justify-between sm:justify-start gap-3">
                                <span class="text-[#8A7C70] w-28 shrink-0">Kategori:</span>
                                <a href="{{ route('home', ['category' => $product->category->slug ?? '']) }}#katalog" class="font-bold text-[#6B4226] hover:underline">
                                    {{ $product->category->name ?? 'Umum' }}
                                </a>
                            </div>
                            <div class="flex items-center justify-between sm:justify-start gap-3">
                                <span class="text-[#8A7C70] w-28 shrink-0">Berat Bersih:</span>
                                <span class="font-bold text-[#2D241E]">{{ $product->weight_grams }} gram</span>
                            </div>
                            <div class="flex items-center justify-between sm:justify-start gap-3">
                                <span class="text-[#8A7C70] w-28 shrink-0">Kondisi:</span>
                                <span class="font-bold text-emerald-700">Baru (100% Segel)</span>
                            </div>
                            <div class="flex items-center justify-between sm:justify-start gap-3">
                                <span class="text-[#8A7C70] w-28 shrink-0">Dikirim Dari:</span>
                                <span class="font-bold text-[#2D241E]">{{ $storeLocation }}</span>
                            </div>
                            @if($product->spiciness_level > 0)
                                <div class="flex items-center justify-between sm:justify-start gap-3">
                                    <span class="text-[#8A7C70] w-28 shrink-0">Level Pedas:</span>
                                    <span class="font-bold text-rose-600">Level {{ $product->spiciness_level }} 🔥</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between sm:justify-start gap-3 col-span-1 sm:col-span-2 pt-2 border-t border-[#F2EAE0]">
                                <span class="text-[#8A7C70] w-28 shrink-0">Pembayaran:</span>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($product->allowsPaymentMethod('qris'))
                                        <span class="px-2 py-0.5 rounded-lg bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226]/20 font-bold text-[11px] flex items-center gap-1">
                                            <span>📱</span>
                                            <span>QRIS</span>
                                        </span>
                                    @endif
                                    @if($product->allowsPaymentMethod('cod'))
                                        <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[11px] flex items-center gap-1">
                                            <span>💵</span>
                                            <span>Bisa COD</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between sm:justify-start gap-3 col-span-1 sm:col-span-2 pt-2 border-t border-[#F2EAE0]">
                                <span class="text-[#8A7C70] w-28 shrink-0">Pengiriman:</span>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($product->is_free_shipping)
                                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-[11px] flex items-center gap-1">
                                            <span>🚚</span>
                                            <span>Bebas Ongkir {{ $product->free_shipping_min_spend > 0 ? '(Min. Belanja Rp ' . number_format($product->free_shipping_min_spend, 0, ',', '.') . ')' : '(Tanpa Min. Belanja)' }}</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-lg bg-[#FAF8F5] text-[#5A4B40] border border-[#EAE1D7] font-medium text-[11px] flex items-center gap-1">
                                            <span>🚚</span>
                                            <span>Ongkir Standar (Mulai Rp 15.000)</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between sm:justify-start gap-3 col-span-1 sm:col-span-2 pt-2 border-t border-[#F2EAE0]">
                                <span class="text-[#8A7C70] w-28 shrink-0">Voucher Diskon:</span>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($product->allow_vouchers)
                                        <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-900 border border-amber-200 font-bold text-[11px] flex items-center gap-1">
                                            <span>🎟️</span>
                                            <span>Mendukung Voucher Promo & Diskon</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-lg bg-stone-100 text-stone-600 border border-stone-200 font-medium text-[11px] flex items-center gap-1">
                                            <span>🚫</span>
                                            <span>Tidak Dapat Menggunakan Voucher</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 1. Inline Variant Selection Card (If Variants Exist) -->
                    @if($product->variants->isNotEmpty())
                        <div class="p-4 bg-[#FAF8F5] rounded-2xl border border-[#EAE1D7] space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-[#2D241E] flex items-center gap-1.5">
                                    <span>🎨</span>
                                    <span>Pilihan Varian Produk</span>
                                    <span class="text-red-500 font-black">*</span>
                                </label>
                                <span class="text-xs font-bold text-[#6B4226]" x-show="selectedVariant" x-text="'Terpilih: ' + selectedVariant.name"></span>
                                <span class="text-[11px] text-[#8A7C70]" x-show="!selectedVariant">Pilih salah satu varian</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                <template x-for="variant in variants" :key="variant.id">
                                    <button type="button"
                                            @click="selectVariant(variant)"
                                            :class="selectedVariant && selectedVariant.id === variant.id 
                                                ? 'border-[#6B4226] bg-[#FAF4ED] ring-2 ring-[#6B4226] shadow-2xs font-black' 
                                                : 'border-[#EAE1D7] bg-white hover:border-[#6B4226]/50 hover:bg-[#FAF8F5]'"
                                            class="p-2.5 rounded-2xl border flex items-center gap-2.5 text-left transition cursor-pointer group relative">
                                        <template x-if="variant.image">
                                            <img :src="variant.image" :alt="variant.name" class="w-10 h-10 rounded-xl object-cover border border-[#EAE1D7] shrink-0">
                                        </template>
                                        <template x-if="!variant.image">
                                            <div class="w-10 h-10 rounded-xl bg-[#FAF4ED] border border-[#EAE1D7] flex items-center justify-center text-xs font-black text-[#6B4226] shrink-0">
                                                <span x-text="variant.name.substring(0, 2).toUpperCase()"></span>
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-[#2D241E] truncate" x-text="variant.name"></p>
                                            <p class="text-[10px] text-[#8A7C70]" x-text="'Stok: ' + variant.stock"></p>
                                        </div>
                                        <template x-if="selectedVariant && selectedVariant.id === variant.id">
                                            <span class="absolute -top-1.5 -right-1.5 w-4.5 h-4.5 bg-[#6B4226] text-white rounded-full flex items-center justify-center text-[9px] font-bold shadow-2xs">✓</span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif

                    <!-- 2. Quantity Controller & Subtotal Preview -->
                    <div class="p-4 bg-white rounded-2xl border border-[#F2EAE0] space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#2D241E] mb-1">Jumlah Pesanan</label>
                                <div class="flex items-center border border-[#EAE1D7] rounded-xl overflow-hidden bg-[#FAF8F5]">
                                    <button type="button" 
                                            @click="decrease()" 
                                            :disabled="qty <= 1"
                                            class="w-9 h-9 flex items-center justify-center font-bold text-base text-[#5A4B40] hover:bg-[#F2EAE0] disabled:opacity-40 cursor-pointer transition">
                                        -
                                    </button>
                                    <span class="w-12 text-center font-mono font-bold text-sm text-[#2D241E]" x-text="qty"></span>
                                    <button type="button" 
                                            @click="increase()" 
                                            :disabled="qty >= currentStock"
                                            class="w-9 h-9 flex items-center justify-center font-bold text-base text-[#5A4B40] hover:bg-[#F2EAE0] disabled:opacity-40 cursor-pointer transition">
                                        +
                                    </button>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] text-[#8A7C70] block">Estimasi Subtotal:</span>
                                <span class="text-lg font-black text-[#6B4226]" 
                                      x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <!-- Purchase Action Buttons (Opens Variant Card Modal) -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2">
                            <!-- Chat Seller Button -->
                            @guest
                                <button type="button"
                                        @click="window.showAuthModal({ icon: '💬', title: 'Chat dengan Penjual', message: 'Yuk masuk ke akunmu terlebih dahulu untuk bertanya langsung seputar produk ini kepada penjual toko.' })"
                                        title="Tanyakan produk ini langsung ke penjual"
                                        class="sm:col-span-3 w-full py-3.5 px-3 rounded-xl border-2 border-[#6B4226] bg-[#FAF4ED] text-[#6B4226] hover:bg-[#F5EBE1] font-black text-xs flex items-center justify-center gap-1.5 transition active:scale-[0.98] cursor-pointer shadow-2xs">
                                    <span class="text-base">💬</span>
                                    <span>Chat</span>
                                </button>
                            @else
                                <form action="{{ route('chat.start') }}" method="POST" class="sm:col-span-3">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    @if($product->store_id)
                                        <input type="hidden" name="store_id" value="{{ $product->store_id }}">
                                    @endif
                                    <button type="submit"
                                            title="Tanyakan produk ini langsung ke penjual"
                                            class="w-full py-3.5 px-3 rounded-xl border-2 border-[#6B4226] bg-[#FAF4ED] text-[#6B4226] hover:bg-[#F5EBE1] font-black text-xs flex items-center justify-center gap-1.5 transition active:scale-[0.98] cursor-pointer shadow-2xs">
                                        <span class="text-base">💬</span>
                                        <span>Chat</span>
                                    </button>
                                </form>
                            @endguest

                            @if(Auth::check() && $product->isOwnedBy(Auth::user()))
                                <div class="sm:col-span-9 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 select-none shadow-2xs">
                                    <span class="text-base">🏪</span>
                                    <span>Ini adalah produk tokomu sendiri (tidak dapat dibeli).</span>
                                </div>
                            @elseif($product->store && $product->store->isSuspended())
                                <div class="sm:col-span-9 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 select-none shadow-2xs">
                                    <span class="text-base">🚫</span>
                                    <span>Toko ini sedang dinonaktifkan sementara oleh Admin.</span>
                                </div>
                            @elseif($product->store && $product->store->isClosed())
                                <div class="sm:col-span-9 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm font-bold flex items-center justify-center gap-2 select-none shadow-2xs">
                                    <span class="text-base">🏖️</span>
                                    <span>Toko sedang libur / tutup sementara dan belum melayani pesanan.</span>
                                </div>
                            @else
                                <!-- Add to Cart Button (Opens Card Modal for Variant Selection) -->
                                <button type="button"
                                        @click="openCardModal('cart')"
                                        class="sm:col-span-4 w-full py-3.5 px-4 rounded-xl border-2 border-[#6B4226] bg-white text-[#6B4226] hover:bg-[#FAF4ED] font-black text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-[0.98] cursor-pointer shadow-2xs">
                                    <span>🛒</span>
                                    <span>+ Keranjang</span>
                                </button>

                                <!-- Buy Now Button (Opens Card Modal) -->
                                <button type="button"
                                        @click="openCardModal('buy')"
                                        class="sm:col-span-5 w-full py-3.5 px-4 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-[0.98] cursor-pointer shadow-md">
                                    <span>⚡</span>
                                    <span>Beli Sekarang</span>
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- SELLER / STORE PROFILE INFORMATION STRIP (SHOPEE & TOKOPEDIA STYLE) -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Left: Store Identity -->
            <div class="flex items-center gap-4">
                <!-- Store Avatar / Logo -->
                <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-[#6B4226] to-[#452713] text-white flex items-center justify-center text-2xl font-black shadow-md shrink-0 overflow-hidden">
                    @if($product->store?->logo_url)
                        <img src="{{ $product->store->logo_url }}" alt="{{ $storeName }}" class="w-full h-full object-cover">
                    @else
                        <span>🏬</span>
                    @endif
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-blue-500 rounded-full text-white flex items-center justify-center text-[10px] font-bold border-2 border-white shadow-2xs" title="Official Verified Store">✓</span>
                </div>

                <!-- Store Names & Badges -->
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-black text-[#2D241E] leading-tight">
                            {{ $storeName }}
                        </h2>
                        @if(strtolower($product->badge) === 'mall')
                            <span class="bg-[#D9381E] text-white text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider">
                                Mall
                            </span>
                        @else
                            <span class="bg-[#1E56A0] text-white text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider">
                                Official Store
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-[#8A7C70] flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $storeLastActive }}</span>
                        </span>
                        <span>•</span>
                        <span>📍 {{ $storeCity }}</span>
                    </p>
                </div>
            </div>

            <!-- Middle: Store Performance Stats -->
            <div class="grid grid-cols-3 gap-4 sm:gap-8 py-3 lg:py-0 border-y lg:border-y-0 lg:border-x border-[#F2EAE0] lg:px-8 text-center sm:text-left">
                <div>
                    <span class="text-[11px] text-[#8A7C70] block">Penilaian Toko</span>
                    <div class="flex items-baseline gap-1 justify-center sm:justify-start">
                        <span class="text-sm font-black text-[#6B4226]">★ {{ number_format($storeRating, 1) }}</span>
                        <span class="text-[11px] font-semibold text-[#8A7C70]">
                            ({{ $storeRatingCount > 0 ? ($storeRatingCount >= 1000 ? round($storeRatingCount / 1000, 1) . 'rb' : $storeRatingCount . ' ulasan') : '0 ulasan' }})
                        </span>
                    </div>
                </div>
                <div>
                    <span class="text-[11px] text-[#8A7C70] block">Produk Terjual</span>
                    <span class="text-sm font-black text-[#2D241E]">{{ $storeSoldTotal }} Terjual</span>
                    <span class="text-[10px] text-[#8A7C70] block">({{ $storeProductsCount }} Produk di Toko)</span>
                </div>
                <div>
                    <span class="text-[11px] text-[#8A7C70] block">Kecepatan Respon</span>
                    <span class="text-sm font-black text-emerald-700">{{ $storeChatPerformance['full_label'] }}</span>
                </div>
            </div>

            <!-- Right: Action Buttons "Ikuti", "Chat Penjual" & "Kunjungi Toko" -->
            <div class="flex flex-col sm:flex-row items-center gap-2.5 sm:self-center w-full sm:w-auto">
                @if($store)
                    <!-- Follow Seller Button -->
                    <button type="button" 
                            @click="toggleFollow()" 
                            :disabled="followingLoading"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl border-2 font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 shadow-2xs whitespace-nowrap cursor-pointer"
                            :class="isFollowing 
                                ? 'border-[#6B4226] bg-[#FAF4ED] text-[#6B4226] hover:bg-[#F2EAE0]' 
                                : 'border-[#6B4226] bg-[#6B4226] text-white hover:bg-[#54321B]'">
                        <span x-text="isFollowing ? '✓' : '➕'"></span>
                        <span x-text="isFollowing ? 'Mengikuti' : 'Ikuti Toko'"></span>
                        <span x-show="followersCount > 0" class="text-[10px] font-mono opacity-80" x-text="'(' + followersCount + ')'"></span>
                    </button>
                @endif

                <form action="{{ route('chat.start') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if($product->store_id)
                        <input type="hidden" name="store_id" value="{{ $product->store_id }}">
                    @endif
                    <button type="submit" 
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white border-2 border-[#EAE1D7] hover:border-[#6B4226] text-[#2D241E] hover:text-[#6B4226] font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 shadow-2xs whitespace-nowrap cursor-pointer">
                        <span>💬</span>
                        <span>Chat Penjual</span>
                    </button>
                </form>

                <a href="{{ route('store.show', urlencode($storeName)) }}" 
                   class="w-full sm:w-auto px-4 py-2.5 rounded-xl border-2 border-[#6B4226] text-[#6B4226] hover:bg-[#FAF4ED] font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 shadow-2xs whitespace-nowrap cursor-pointer">
                    <span>🏪</span>
                    <span>Lihat Toko ›</span>
                </a>
            </div>

        </div>
    </div>

    <!-- PRODUCT DETAILS & DESCRIPTIONS CARD -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-7 shadow-xs space-y-3.5">
        <div class="border-b border-[#F2EAE0] pb-2.5">
            <h2 class="text-base sm:text-lg font-black text-[#2D241E] tracking-tight">
                Deskripsi & Informasi Lengkap Produk
            </h2>
        </div>

        <div class="text-xs sm:text-sm text-[#2D241E] leading-relaxed whitespace-pre-line pt-0.5">
            {{ $product->description }}
        </div>

        <div class="pt-3 border-t border-[#F2EAE0] space-y-2 text-xs">
            <h4 class="font-bold text-[#2D241E] flex items-center gap-1.5">
                <span>🛡️</span>
                <span>Keunggulan Belanja di {{ $storeName }}:</span>
            </h4>
            <ul class="list-disc list-inside space-y-1.5 text-[#5A4B40]">
                @foreach($storeAdvantages as $advantage)
                    <li>{!! $advantage !!}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- PENILAIAN & ULASAN PRODUK (AUTHENTIC SHOPEE/TOKOPEDIA REVIEWS SECTION) -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-xs space-y-6" id="ulasan">
        
        <!-- Header with Title and "Tulis Ulasan" button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#F2EAE0] pb-4">
            <div>
                <h2 class="text-base sm:text-lg font-black text-[#2D241E] tracking-tight flex items-center gap-2">
                    <span>⭐</span>
                    <span>Penilaian & Ulasan Pembeli</span>
                </h2>
                <p class="text-xs text-[#8A7C70] mt-0.5">
                    Ulasan asli dan terverifikasi dari pelanggan yang telah memesan produk ini
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[11px] text-[#6B4226] bg-[#FAF4ED] px-3 py-1.5 rounded-xl border border-[#EAE1D7] flex items-center gap-1.5 font-bold">
                    <span>🛍️</span>
                    <span>Penilaian Pembeli Terverifikasi</span>
                </span>
            </div>
        </div>

        <!-- Rating Summary Box (Shopee style) -->
        <div class="bg-[#FAF4ED]/70 rounded-2xl p-5 sm:p-6 border border-[#EAE1D7] flex flex-col md:flex-row items-center gap-6">
            <!-- Left: Overall Rating Score -->
            <div class="text-center md:text-left shrink-0 md:pr-6 md:border-r border-[#EAE1D7]">
                <div class="flex items-baseline justify-center md:justify-start gap-1">
                    <span class="text-3xl sm:text-4xl font-black text-[#6B4226]">
                        {{ number_format($averageRating, 1) }}
                    </span>
                    <span class="text-sm font-bold text-[#8A7C70]">/ 5.0</span>
                </div>
                <!-- 5 Stars Graphic -->
                <div class="flex items-center justify-center md:justify-start gap-0.5 text-amber-500 text-base my-1">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($averageRating))
                            <span>★</span>
                        @else
                            <span class="text-amber-200">★</span>
                        @endif
                    @endfor
                </div>
                <p class="text-xs font-semibold text-[#8A7C70]">
                    {{ $reviewsCount }} Penilaian Terverifikasi
                </p>
            </div>

            <!-- Middle: Star Breakdown Bars -->
            <div class="flex-1 w-full max-w-sm space-y-1.5 text-xs">
                @foreach([5, 4, 3, 2, 1] as $star)
                    @php
                        $count = $ratingDistribution[$star] ?? 0;
                        $percent = $reviewsCount > 0 ? round(($count / $reviewsCount) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="w-12 text-[#8A7C70] font-semibold flex items-center gap-0.5 shrink-0">
                            <span>{{ $star }}</span>
                            <span class="text-amber-500">★</span>
                        </span>
                        <div class="flex-1 h-2 rounded-full bg-[#EAE1D7] overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                        <span class="w-8 text-right font-mono text-[11px] text-[#8A7C70] shrink-0">{{ $count }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Right: Interactive Filter Pills -->
            <div class="flex-1 flex flex-wrap gap-2 justify-center md:justify-start">
                <button type="button" 
                        @click="selectedStarFilter = 'all'"
                        :class="selectedStarFilter === 'all' ? 'bg-[#6B4226] text-white border-[#6B4226]' : 'bg-white text-[#5A4B40] border-[#EAE1D7] hover:border-[#6B4226]'"
                        class="px-3.5 py-1.5 rounded-xl border text-xs font-bold transition shadow-2xs cursor-pointer">
                    Semua ({{ $reviewsCount }})
                </button>
                @foreach([5, 4, 3, 2, 1] as $star)
                    <button type="button" 
                            @click="selectedStarFilter = '{{ $star }}'"
                            :class="selectedStarFilter === '{{ $star }}' ? 'bg-[#6B4226] text-white border-[#6B4226]' : 'bg-white text-[#5A4B40] border-[#EAE1D7] hover:border-[#6B4226]'"
                            class="px-3.5 py-1.5 rounded-xl border text-xs font-bold transition shadow-2xs cursor-pointer flex items-center gap-1">
                        <span>{{ $star }}</span>
                        <span class="text-amber-500">★</span>
                        <span>({{ $ratingDistribution[$star] ?? 0 }})</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Reviews List -->
        @if($reviews->count() > 0)
            <div class="divide-y divide-[#F2EAE0]">
                @foreach($reviews as $rev)
                    <div class="py-5 space-y-2.5"
                         x-show="selectedStarFilter === 'all' || selectedStarFilter === '{{ $rev->rating }}'"
                         x-data="{
                             isLiked: {{ $rev->isLikedBy(Auth::user()) ? 'true' : 'false' }},
                             likesCount: {{ (int) ($rev->likes_count ?? $rev->likes()->count()) }},
                             isLiking: false,
                             showComments: false,
                             showReplyInput: false,
                             commentsCount: {{ (int) ($rev->comments_count ?? $rev->comments()->count()) }},
                             commentText: '',
                             isSubmittingComment: false,
                             newComments: [],

                             toggleLike() {
                                  @guest
                                      window.showAuthModal({
                                          icon: '❤️',
                                          title: 'Sukai Ulasan Pembeli',
                                          message: 'Yuk masuk ke akunmu terlebih dahulu untuk memberikan apresiasi like pada ulasan pembeli ini.'
                                      });
                                      return;
                                  @endguest

                                  if (this.isLiking) return;
                                  this.isLiking = true;

                                  fetch('{{ route('reviews.like', $rev->id) }}', {
                                      method: 'POST',
                                      headers: {
                                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                          'X-Requested-With': 'XMLHttpRequest',
                                          'Accept': 'application/json'
                                      }
                                  })
                                  .then(r => r.json())
                                  .then(data => {
                                      this.isLiking = false;
                                      if (data.success) {
                                          this.isLiked = data.liked;
                                          this.likesCount = data.likes_count;
                                      }
                                  })
                                  .catch(() => {
                                      this.isLiking = false;
                                  });
                              },

                              openReplyInput() {
                                  @guest
                                      window.showAuthModal({
                                          icon: '💬',
                                          title: 'Tulis Komentar Ulasan',
                                          message: 'Yuk masuk ke akunmu terlebih dahulu untuk menulis tanggapan atau komentar pada ulasan ini.'
                                      });
                                      return;
                                  @endguest

                                  this.showReplyInput = true;
                                  this.showComments = true;
                                  this.$nextTick(() => {
                                      this.$refs.commentInput?.focus();
                                  });
                              },

                              submitComment() {
                                  @guest
                                      window.showAuthModal({
                                          icon: '💬',
                                          title: 'Tulis Komentar Ulasan',
                                          message: 'Yuk masuk ke akunmu terlebih dahulu untuk menulis tanggapan atau komentar pada ulasan ini.'
                                      });
                                      return;
                                  @endguest

                                 const text = this.commentText.trim();
                                 if (text.length < 2) return;

                                 this.isSubmittingComment = true;
                                 fetch('{{ route('reviews.comments.store', $rev->id) }}', {
                                     method: 'POST',
                                     headers: {
                                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                         'X-Requested-With': 'XMLHttpRequest',
                                         'Content-Type': 'application/json',
                                         'Accept': 'application/json'
                                     },
                                     body: JSON.stringify({ comment: text })
                                 })
                                 .then(r => r.json())
                                 .then(data => {
                                     this.isSubmittingComment = false;
                                     if (data.success) {
                                         this.newComments.push(data.comment);
                                         this.commentsCount = data.comments_count;
                                         this.commentText = '';
                                         this.showComments = true;
                                         this.showReplyInput = false;
                                     }
                                 })
                                 .catch(() => {
                                     this.isSubmittingComment = false;
                                 });
                             }
                         }"
                         x-transition>
                        <!-- Reviewer Info Header -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <!-- Avatar -->
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#6B4226] to-[#452713] text-white font-bold text-xs flex items-center justify-center shadow-xs shrink-0">
                                    {{ strtoupper(substr($rev->user->name ?? 'Pembeli', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-xs text-[#2D241E]">
                                            {{ $rev->user->name ?? 'Pembeli NusantaraMart' }}
                                        </h4>
                                        <span class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-1.5 py-0.5 rounded-full">
                                            ✓ Terverifikasi
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-[#8A7C70]">
                                        <!-- Stars -->
                                        <span class="text-amber-500 font-bold">
                                            @for($s = 1; $s <= 5; $s++)
                                                {{ $s <= $rev->rating ? '★' : '☆' }}
                                            @endfor
                                        </span>
                                        <span>•</span>
                                        <span>{{ $rev->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($rev->variant_name)
                                <span class="text-[11px] bg-amber-50 border border-amber-200 text-amber-900 font-medium px-2 py-0.5 rounded-lg shrink-0">
                                    ✨ Varian: {{ $rev->variant_name }}
                                </span>
                            @endif
                        </div>

                        <!-- Review Text -->
                        @if($rev->review)
                            <p class="text-xs sm:text-sm text-[#3D3028] leading-relaxed pl-13">
                                {{ $rev->review }}
                            </p>
                        @endif

                        <!-- Review Photos Gallery -->
                        @if(!empty($rev->photo_urls))
                            <div class="flex flex-wrap gap-2 pl-13 pt-1">
                                @foreach($rev->photo_urls as $photoUrl)
                                    <button type="button"
                                            @click="activeReviewPhoto = '{{ $photoUrl }}'; showPhotoModal = true"
                                            class="relative group rounded-2xl overflow-hidden border border-[#EAE1D7] hover:border-[#6B4226] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/30 transition-all shadow-2xs hover:shadow-md cursor-pointer">
                                        <img src="{{ $photoUrl }}" 
                                             alt="Foto ulasan {{ $rev->user->name ?? 'Pembeli' }}" 
                                             class="w-16 h-16 sm:w-20 sm:h-20 object-cover group-hover:scale-105 transition-transform duration-200">
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-xs">
                                            🔍
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <!-- Action Bar: Like & Reply Buttons (YouTube & TikTok Clean Style) -->
                        <div class="flex items-center gap-4 pl-13 pt-2 text-xs text-[#7A6C60]">
                            <!-- Like Button -->
                            <button type="button" 
                                    @click="toggleLike()"
                                    :disabled="isLiking"
                                    class="inline-flex items-center gap-1.5 hover:text-[#2D241E] transition cursor-pointer group"
                                    :class="isLiked ? 'text-rose-600 font-bold' : 'text-[#7A6C60] font-medium'">
                                <svg class="w-4 h-4 transition-transform group-active:scale-125" 
                                     :class="isLiked ? 'text-rose-600 fill-rose-600' : 'text-[#8A7C70] group-hover:text-[#2D241E]'"
                                     :fill="isLiked ? 'currentColor' : 'none'" 
                                     stroke="currentColor" 
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span x-show="likesCount > 0" x-text="likesCount" class="text-xs"></span>
                                <span x-show="likesCount === 0" class="text-[11px]">Suka</span>
                            </button>

                            <!-- Reply / Balas Button -->
                            <button type="button" 
                                    @click="openReplyInput()"
                                    class="font-semibold text-[11px] sm:text-xs text-[#7A6C60] hover:text-[#2D241E] transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span>Balas</span>
                            </button>
                        </div>

                        <!-- Replies Dropdown Toggle (YouTube Signature: "▼ X balasan") -->
                        <div class="pl-13 pt-0.5" x-show="commentsCount > 0">
                            <button type="button" 
                                    @click="showComments = !showComments" 
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-[#6B4226] hover:text-[#54321B] hover:underline transition cursor-pointer py-1">
                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="showComments ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                                <span x-text="showComments ? 'Sembunyikan balasan' : (commentsCount + ' balasan')"></span>
                            </button>
                        </div>

                        <!-- Thread Container (YouTube / TikTok Style - Clean & Unboxed) -->
                        <div x-show="showComments || showReplyInput" x-cloak x-transition class="pl-13 pt-1 space-y-3">
                            <!-- Replies List -->
                            <div x-show="showComments" class="space-y-3">
                                @foreach($rev->comments as $comm)
                                    @php
                                        $isCommSeller = ($product->store && $product->store->user_id === $comm->user_id);
                                    @endphp
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#6B4226] to-[#452713] text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">
                                            {{ strtoupper(substr($comm->user->name ?? 'P', 0, 2)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap leading-tight">
                                                <span class="font-bold text-xs text-[#2D241E]">{{ $comm->user->name ?? 'Pengguna' }}</span>
                                                @if($isCommSeller)
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226]/20">
                                                        Penjual
                                                    </span>
                                                @endif
                                                <span class="text-[11px] text-[#8A7C70]">• {{ $comm->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-xs sm:text-sm text-[#3D3028] mt-1 leading-relaxed">
                                                {{ $comm->comment }}
                                            </p>
                                            <div class="flex items-center gap-3 mt-1 text-[11px] text-[#8A7C70]">
                                                <button type="button" 
                                                        @click="openReplyInput(); commentText = '@' + '{{ $comm->user->name ?? 'Pengguna' }}' + ' '"
                                                        class="font-semibold hover:text-[#2D241E] transition cursor-pointer">
                                                    Balas
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Newly submitted comments (Appended via AJAX) -->
                                <template x-for="c in newComments" :key="c.id">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#6B4226] to-[#452713] text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5" x-text="c.user_initial">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap leading-tight">
                                                <span class="font-bold text-xs text-[#2D241E]" x-text="c.user_name"></span>
                                                <template x-if="c.is_seller">
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226]/20">
                                                        Penjual
                                                    </span>
                                                </template>
                                                <span class="text-[11px] text-emerald-700 font-semibold">• Baru saja</span>
                                            </div>
                                            <p class="text-xs sm:text-sm text-[#3D3028] mt-1 leading-relaxed" x-text="c.comment"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Comment/Reply Input Form (YouTube Minimal Flat Style) -->
                            <div x-show="showReplyInput" x-cloak class="pt-2">
                                @auth
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-[#6B4226] text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-1">
                                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <form @submit.prevent="submitComment()">
                                                <input type="text" 
                                                       x-ref="commentInput"
                                                       x-model="commentText" 
                                                       :disabled="isSubmittingComment"
                                                       placeholder="Tambahkan balasan..."
                                                       class="w-full text-xs sm:text-sm bg-transparent border-b border-[#D5C9BD] focus:border-[#6B4226] py-1.5 focus:outline-none text-[#2D241E] placeholder:text-[#9E9084] transition">
                                                <div class="flex items-center justify-end gap-2 mt-2">
                                                    <button type="button" 
                                                            @click="commentText = ''; showReplyInput = false" 
                                                            class="px-3 py-1.5 text-xs font-semibold text-[#7A6C60] hover:text-[#2D241E] rounded-full hover:bg-black/5 transition cursor-pointer">
                                                        Batal
                                                    </button>
                                                    <button type="submit" 
                                                            :disabled="isSubmittingComment || commentText.trim().length < 2"
                                                            class="px-4 py-1.5 text-xs font-bold rounded-full transition cursor-pointer flex items-center gap-1.5"
                                                            :class="commentText.trim().length >= 2 ? 'bg-[#6B4226] hover:bg-[#54321B] text-white shadow-xs' : 'bg-[#EAE1D7] text-[#9E9084] cursor-not-allowed'">
                                                        <span x-show="!isSubmittingComment">Balas</span>
                                                        <span x-show="isSubmittingComment" class="inline-block animate-spin">⏳</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2.5 py-1">
                                        <div class="w-7 h-7 rounded-full bg-neutral-200 text-neutral-500 font-bold text-[10px] flex items-center justify-center shrink-0">
                                            👤
                                        </div>
                                        <div class="flex-1 min-w-0 flex items-center justify-between border-b border-[#D5C9BD] py-1.5">
                                            <span class="text-xs text-[#9E9084]">Tambahkan balasan...</span>
                                            <button type="button" 
                                                    @click="window.showAuthModal({ icon: '💬', title: 'Tulis Komentar Ulasan', message: 'Yuk masuk ke akunmu terlebih dahulu untuk menanggapi ulasan pembeli ini.' })" 
                                                    class="text-xs font-bold text-[#6B4226] hover:underline cursor-pointer">
                                                Masuk
                                            </button>
                                        </div>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-10 px-4 space-y-3">
                <div class="w-16 h-16 rounded-full bg-[#FAF4ED] text-amber-600 text-3xl flex items-center justify-center mx-auto shadow-inner">
                    ⭐
                </div>
                <div class="space-y-1">
                    <h3 class="font-bold text-sm text-[#2D241E]">
                        Belum Ada Penilaian untuk Produk Ini
                    </h3>
                    <p class="text-xs text-[#8A7C70] max-w-sm mx-auto">
                        Penilaian hanya dapat diberikan oleh pembeli yang telah memesan dan menerima produk ini dengan baik.
                    </p>
                </div>
            </div>
        @endif

    </div>

    <!-- RELATED RECOMMENDED PRODUCTS (SERUPA DI KATEGORI INI) -->
    @if($relatedProducts->count() > 0)
        <div class="space-y-4 pt-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-[#2D241E] tracking-tight">
                        Produk Serupa yang Mungkin Kamu Suka
                    </h3>
                    <p class="text-xs text-[#8A7C70]">Rekomendasi pilihan terbaik dari kategori {{ $product->category->name ?? 'sejenis' }}</p>
                </div>
                <a href="{{ route('home') }}#katalog" class="text-xs font-bold text-[#6B4226] hover:underline">
                    Lihat Semua Produk →
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5 sm:gap-4">
                @foreach($relatedProducts as $rel)
                    <div class="bg-white rounded-2xl border border-[#EAE1D7] p-3 shadow-2xs hover:shadow-lg hover:border-[#6B4226]/40 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                        <a href="{{ route('product.detail', $rel->slug) }}" class="block">
                            <!-- Visual Stage -->
                            <div class="h-28 rounded-xl bg-gradient-to-b from-[#FAF8F5] to-[#FAF4ED] border border-[#F2EAE0] flex items-center justify-center text-3xl mb-2 group-hover:scale-105 transition">
                                {{ $rel->category->icon ?? '🛍️' }}
                            </div>
                            
                            <!-- Title -->
                            <h4 class="font-bold text-xs text-[#2D241E] line-clamp-2 group-hover:text-[#6B4226] transition min-h-[32px]" title="{{ $rel->name }}">
                                {{ $rel->name }}
                            </h4>

                            <!-- Price -->
                            <p class="text-xs font-black text-[#D9381E] mt-1">{{ $rel->formatted_effective_price }}</p>
                        </a>

                        <button @click="addToCart({
                                    id: {{ $rel->id }},
                                    name: '{{ addslashes($rel->name) }}',
                                    price: {{ $rel->effective_price }},
                                    brand: '{{ addslashes($rel->brand ?? 'NusantaraMart') }}',
                                    badge: '{{ $rel->badge }}',
                                    icon: '{{ $rel->category->icon ?? '🛍️' }}'
                                })"
                                class="mt-2 w-full py-1.5 px-2 bg-[#6B4226] hover:bg-[#54321B] text-white text-[11px] font-bold rounded-lg shadow-2xs transition cursor-pointer active:scale-95">
                            + Keranjang
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- CARD MODAL PILIH VARIAN (POP-UP / BOTTOM SHEET SHOPEE & TOKOPEDIA STYLE) -->
    <div x-show="showVariantCardModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="closeCardModal()">
        
        <!-- Backdrop -->
        <div x-show="showVariantCardModal" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" 
             @click="closeCardModal()"></div>

        <div class="fixed inset-0 z-10 flex items-end sm:items-center justify-center p-0 sm:p-4 text-center sm:text-left">
            <!-- Modal Card Container -->
            <div x-show="showVariantCardModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95"
                 @click.outside="closeCardModal()"
                 class="relative bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl border border-[#EAE1D7] overflow-hidden flex flex-col max-h-[90vh]">
                
                <!-- Card Header with Product & Variant Quick Info -->
                <div class="p-4 sm:p-5 border-b border-[#F2EAE0] flex items-start gap-4 relative bg-[#FAF8F5]">
                    <!-- Product / Variant Thumbnail -->
                    <div class="w-20 h-20 rounded-2xl bg-white border border-[#EAE1D7] flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                        <template x-if="currentPhoto">
                            <img :src="currentPhoto" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!currentPhoto">
                            <span class="text-3xl">{{ $product->category->icon ?? '🛍️' }}</span>
                        </template>
                    </div>

                    <!-- Price & Stock Info -->
                    <div class="min-w-0 flex-1 pr-6 space-y-1">
                        <h4 class="font-extrabold text-sm text-[#2D241E] line-clamp-1" title="{{ $product->name }}">
                            {{ $product->name }}
                        </h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-lg font-black text-[#D9381E]" x-text="'Rp ' + currentPrice.toLocaleString('id-ID')"></span>
                            @if($product->hasDiscount())
                                <span class="text-xs text-[#8A7C70] line-through">{{ $product->formatted_price }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-[#7A6C60] flex items-center gap-1.5">
                            <span>Sisa Stok:</span>
                            <span class="font-bold text-[#2D241E]" x-text="currentStock + ' unit'"></span>
                        </p>
                        <template x-if="selectedVariant">
                            <p class="text-[11px] font-bold text-[#6B4226]" x-text="'Varian terpilih: ' + selectedVariant.name"></p>
                        </template>
                    </div>

                    <!-- Close Button -->
                    <button type="button" 
                            @click="closeCardModal()"
                            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-stone-200/80 hover:bg-stone-300 text-[#5A4B40] flex items-center justify-center text-sm font-bold transition cursor-pointer"
                            title="Tutup">
                        ✕
                    </button>
                </div>

                <!-- Card Body (Scrollable if many variants) -->
                <div class="p-4 sm:p-6 overflow-y-auto space-y-5 text-left">
                    
                    <!-- Variant Options (if any) -->
                    <template x-if="hasVariants">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black text-[#2D241E] uppercase tracking-wider flex items-center gap-1.5">
                                    <span>🎨</span>
                                    <span>Pilih Varian Produk</span>
                                    <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] text-red-600 font-bold" x-show="validationError" x-text="validationError"></span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                <template x-for="variant in variants" :key="variant.id">
                                    <button type="button"
                                            @click="selectVariant(variant)"
                                            :class="selectedVariant && selectedVariant.id === variant.id 
                                                ? 'border-[#6B4226] bg-[#FAF4ED] ring-2 ring-[#6B4226] shadow-xs font-black' 
                                                : 'border-[#EAE1D7] bg-white hover:border-[#6B4226]/50 hover:bg-[#FAF8F5]'"
                                            class="p-2.5 rounded-2xl border flex items-center gap-2.5 text-left transition cursor-pointer relative group">
                                        <template x-if="variant.image">
                                            <img :src="variant.image" :alt="variant.name" class="w-10 h-10 rounded-xl object-cover border border-[#EAE1D7] shrink-0">
                                        </template>
                                        <template x-if="!variant.image">
                                            <div class="w-10 h-10 rounded-xl bg-[#FAF4ED] border border-[#EAE1D7] flex items-center justify-center text-xs font-black text-[#6B4226] shrink-0">
                                                <span x-text="variant.name.substring(0, 2).toUpperCase()"></span>
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-[#2D241E] truncate" x-text="variant.name"></p>
                                            <p class="text-[10px] text-[#8A7C70]" x-text="'Stok: ' + variant.stock"></p>
                                        </div>
                                        <template x-if="selectedVariant && selectedVariant.id === variant.id">
                                            <span class="absolute -top-1.5 -right-1.5 w-4.5 h-4.5 bg-[#6B4226] text-white rounded-full flex items-center justify-center text-[9px] font-bold shadow-2xs">✓</span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Quantity Section -->
                    <div class="pt-2 border-t border-[#F2EAE0] flex items-center justify-between">
                        <div>
                            <label class="block text-xs font-bold text-[#2D241E]">Jumlah Pembelian</label>
                            <span class="text-[11px] text-[#8A7C70]" x-text="'Tersedia ' + currentStock + ' unit'"></span>
                        </div>

                        <div class="flex items-center border border-[#EAE1D7] rounded-xl overflow-hidden bg-[#FAF8F5]">
                            <button type="button" 
                                    @click="decrease()" 
                                    :disabled="qty <= 1"
                                    class="w-9 h-9 flex items-center justify-center font-bold text-base text-[#5A4B40] hover:bg-[#F2EAE0] disabled:opacity-40 cursor-pointer transition">
                                -
                            </button>
                            <span class="w-12 text-center font-mono font-bold text-sm text-[#2D241E]" x-text="qty"></span>
                            <button type="button" 
                                    @click="increase()" 
                                    :disabled="qty >= currentStock"
                                    class="w-9 h-9 flex items-center justify-center font-bold text-base text-[#5A4B40] hover:bg-[#F2EAE0] disabled:opacity-40 cursor-pointer transition">
                                +
                            </button>
                        </div>
                    </div>

                    <!-- Subtotal Preview Box -->
                    <div class="p-3.5 rounded-2xl bg-[#FAF8F5] border border-[#EAE1D7] flex items-center justify-between">
                        <span class="text-xs font-bold text-[#7A6C60]">Subtotal Pesanan:</span>
                        <span class="text-base font-black text-[#6B4226]" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Card Footer Actions -->
                <div class="p-4 sm:p-5 border-t border-[#F2EAE0] bg-white">
                    <button type="button"
                            @click="submitCardAction()"
                            class="w-full py-3.5 px-5 rounded-2xl font-black text-sm flex items-center justify-center gap-2 transition active:scale-[0.98] cursor-pointer shadow-md"
                            :class="modalMode === 'buy' ? 'bg-[#D9381E] hover:bg-[#B32B15] text-white' : 'bg-[#6B4226] hover:bg-[#54321B] text-white'">
                        <span x-text="modalMode === 'buy' ? '⚡' : '🛒'"></span>
                        <span x-text="modalMode === 'buy' ? 'Konfirmasi Beli Sekarang' : 'Masukkan ke Keranjang'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>



    <!-- Photo Lightbox Modal for Review Photos -->
    <div x-show="showPhotoModal" 
         x-cloak 
         @keydown.window.escape="showPhotoModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-hidden"
         role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="showPhotoModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showPhotoModal = false"
             class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity cursor-pointer"></div>

        <!-- Image Content Box -->
        <div x-show="showPhotoModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative max-w-3xl max-h-[85vh] z-10 flex flex-col items-center">
            
            <button type="button" 
                    @click="showPhotoModal = false"
                    class="absolute -top-12 right-0 sm:-right-10 text-white/80 hover:text-white bg-black/40 hover:bg-black/60 rounded-full w-9 h-9 flex items-center justify-center text-lg font-bold transition cursor-pointer">
                ✕
            </button>
            <img :src="activeReviewPhoto" alt="Pratinjau Foto Ulasan" 
                 class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl border border-white/10">
        </div>
    </div>

</div>
@endsection
