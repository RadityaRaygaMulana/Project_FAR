@extends('layouts.app')

@section('title', 'Checkout Pembayaran — NusantaraMart')

@section('content')
<div class="py-8 sm:py-12 bg-[#FAF8F5] min-h-[85vh]"
     x-data="{
         isSubmitting: false,
         cartItems: [],
         couponInput: '',
         appliedCoupon: '',
         discountAmount: 0,
         couponMsg: '',
         couponMsgType: '',
         paymentMethod: 'qris',

         initCheckout() {
             const authId = {{ Auth::id() ? Auth::id() : 'null' }};
             const key = authId ? ('nusantaramart_cart_user_' + authId) : 'nusantaramart_cart_guest';
             const saved = localStorage.getItem(key);
             if (saved) {
                 try {
                     this.cartItems = JSON.parse(saved);
                 } catch(e) {
                     this.cartItems = [];
                 }
             }
             if ((!this.cartItems || this.cartItems.length === 0) && window.snackCart && Array.isArray(window.snackCart.items)) {
                 this.cartItems = window.snackCart.items;
             }
         },

         get checkoutItems() {
             const list = (Array.isArray(this.cartItems) && this.cartItems.length > 0) ? this.cartItems : (Array.isArray(this.items) ? this.items : []);
             return list.filter(i => i.selected !== false);
         },
         get checkoutCount() {
             return this.checkoutItems.reduce((sum, i) => sum + i.quantity, 0);
         },
         get checkoutAmount() {
             return this.checkoutItems.reduce((sum, i) => sum + (i.price * i.quantity), 0);
         },
         get shippingCost() {
             if (this.appliedCoupon === 'GRATISONGKIR' || this.checkoutAmount >= 100000) {
                 return 0;
             }
             return 15000;
         },
         get grandTotal() {
             const base = this.checkoutAmount + this.shippingCost - (this.appliedCoupon === 'GRATISONGKIR' ? 0 : this.discountAmount);
             return Math.max(0, base);
         },

         applyCoupon() {
             const code = this.couponInput.trim().toUpperCase();
             if (!code) return;

             if (code === 'SNACKSERU') {
                 this.appliedCoupon = 'SNACKSERU';
                 this.discountAmount = 10000;
                 this.couponMsg = 'Voucher SNACKSERU berhasil! Potongan Rp 10.000 🎉';
                 this.couponMsgType = 'success';
             } else if (code === 'HEMAT20') {
                 this.appliedCoupon = 'HEMAT20';
                 this.discountAmount = Math.min(25000, Math.round(this.checkoutAmount * 0.20));
                 this.couponMsg = 'Voucher HEMAT20 berhasil! Diskon 20% 🎉';
                 this.couponMsgType = 'success';
             } else if (code === 'GRATISONGKIR') {
                 this.appliedCoupon = 'GRATISONGKIR';
                 this.discountAmount = 15000;
                 this.couponMsg = 'Voucher GRATISONGKIR aktif! 🚚';
                 this.couponMsgType = 'success';
             } else {
                 this.couponMsg = 'Kode voucher tidak valid.';
                 this.couponMsgType = 'error';
             }
         },

         removeCoupon() {
             this.appliedCoupon = '';
             this.discountAmount = 0;
             this.couponInput = '';
             this.couponMsg = '';
         }
     }"
     x-init="initCheckout()">
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Empty Checkout State -->
        <div x-show="checkoutCount === 0" 
             x-cloak 
             class="py-16 text-center max-w-md mx-auto space-y-4">
            <div class="w-20 h-20 bg-amber-50 border border-amber-200 text-amber-600 rounded-3xl mx-auto flex items-center justify-center text-3xl shadow-2xs">
                🛒
            </div>
            <div class="space-y-1">
                <h3 class="font-black text-lg text-[#2D241E]">Belum Ada Produk untuk Dicheckout</h3>
                <p class="text-xs text-[#7A6C60] leading-relaxed">
                    Keranjang belanja kamu masih kosong atau belum ada produk yang dicentang untuk dibeli.
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <a href="{{ route('cart') }}" class="px-4 py-2.5 bg-white hover:bg-[#FAF8F5] text-[#6B4226] border border-[#EAE1D7] rounded-xl text-xs font-bold transition shadow-xs">
                    ← Lihat Keranjang
                </a>
                <a href="{{ route('home') }}" class="px-5 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white rounded-xl text-xs font-bold transition shadow-xs">
                    Mulai Belanja 🛍️
                </a>
            </div>
        </div>

        <div x-show="checkoutCount > 0">
            <!-- Breadcrumb & Step Progress with Inline Back Button -->
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#EAE1D7]">
                <div class="flex items-center gap-3">
                    <a href="{{ route('cart') }}" 
                       class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                       title="Kembali ke Keranjang">
                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="flex items-center gap-2 text-xs font-medium text-[#8A7C70]">
                        <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition">Beranda</a>
                        <span>/</span>
                        <a href="{{ route('cart') }}" class="hover:text-[#6B4226] transition">Keranjang</a>
                        <span>/</span>
                        <span class="text-[#6B4226] font-semibold">Checkout Pembayaran</span>
                    </div>
                </div>

                <!-- Steps -->
                <div class="flex items-center gap-2 text-xs font-medium text-[#8A7C70]">
                    <span class="text-[#8A7C70]">1. Keranjang</span>
                    <span>→</span>
                    <span class="text-[#6B4226] font-bold bg-[#FAF4ED] px-2.5 py-0.5 rounded-full border border-[#6B4226]/20">2. Checkout</span>
                    <span>→</span>
                    <span>3. Selesai</span>
                </div>
            </div>

            <!-- Checkout Main Form -->
            <form action="{{ route('checkout') }}" method="POST" @submit="isSubmitting = true">
                @csrf

                <!-- Hidden items array from Alpine -->
                <template x-for="(item, index) in checkoutItems" :key="item.cartKey || item.id">
                    <div>
                        <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.id">
                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                        <input type="hidden" :name="'items[' + index + '][variant_id]'" :value="item.variant_id || ''">
                        <input type="hidden" :name="'items[' + index + '][variant_name]'" :value="item.variant_name || ''">
                    </div>
                </template>

            <!-- Hidden coupon & payment method -->
            <input type="hidden" name="coupon_code" :value="appliedCoupon">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Column: Address, Items & Payment Selection (7 Cols) -->
                <div class="lg:col-span-7 space-y-6">
                    
                    @php
                        $user = Auth::user();
                        $hasAddress = $user && $user->hasCompleteAddress();
                        $deliveryAddress = $user ? ($user->formatted_address ?: $user->default_address) : '';
                    @endphp

                    <!-- 1. Alamat Pengiriman Section -->
                    @if($hasAddress)
                        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs relative overflow-hidden">
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#6B4226]"></div>

                            <div class="flex items-center justify-between gap-4 mb-4 pb-3 border-b border-[#F2EAE0]">
                                <div class="flex items-center gap-2 text-[#6B4226] font-bold text-sm uppercase tracking-wider">
                                    <span class="text-lg">📍</span>
                                    <span>Alamat Pengiriman Pesanan</span>
                                </div>
                                <a href="{{ route('settings', ['view' => 'address', 'return_to' => 'checkout']) }}" 
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-[#6B4226] hover:text-[#54321B] bg-[#FAF4ED] hover:bg-[#F2EAE0] px-3 py-1.5 rounded-xl border border-[#6B4226]/20 transition cursor-pointer"
                                   title="Ubah alamat di pengaturan">
                                    <span>✏️</span>
                                    <span>Ubah Alamat</span>
                                </a>
                            </div>

                            <!-- Hidden Form Inputs for Checkout Payload -->
                            <input type="hidden" name="customer_name" value="{{ $user->recipient_name }}">
                            <input type="hidden" name="customer_phone" value="{{ $user->recipient_phone }}">
                            <input type="hidden" name="customer_address" value="{{ $deliveryAddress }}">

                            <!-- Address Info Display Card -->
                            <div class="p-4 rounded-2xl bg-[#FAF8F5] border border-[#EAE1D7] space-y-2.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md bg-[#6B4226] text-white text-[10px] font-black uppercase tracking-wider">
                                        {{ $user->address_label ?: 'Utama' }}
                                    </span>
                                    <span class="font-black text-sm text-[#2D241E]">
                                        {{ $user->recipient_name }}
                                    </span>
                                    <span class="text-xs text-[#7A6C60] font-medium">
                                        ({{ $user->recipient_phone }})
                                    </span>
                                </div>

                                <p class="text-xs text-[#5A4B40] leading-relaxed">
                                    {{ $deliveryAddress }}
                                </p>

                                @if($user->map_notes)
                                    <div class="pt-1 flex items-start gap-1.5 text-[11px] text-[#8A7C70] border-t border-[#EAE1D7]/60">
                                        <span class="text-[#6B4226] font-bold shrink-0">📍 Catatan Kurir:</span>
                                        <span>{{ $user->map_notes }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Optional Specific Order Note -->
                            <div class="mt-4">
                                <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider mb-1">
                                    Catatan Tambahan untuk Penjual / Kurir (Opsional)
                                </label>
                                <input type="text" 
                                       name="customer_notes" 
                                       value="{{ old('customer_notes', $user->map_notes) }}" 
                                       placeholder="Contoh: Tolong bungkus bubble wrap tebal, kirim sebelum jam 5 sore"
                                       class="w-full px-4 py-2.5 text-xs bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] transition">
                            </div>
                        </div>
                    @else
                        <!-- State: Belum Ada Alamat Pengiriman (Nuansa Coklat NusantaraMart yang Elegan) -->
                        <div class="bg-gradient-to-br from-[#FAF5EF] via-white to-[#FAF5EF] rounded-3xl border-2 border-[#D9C3B0] p-6 sm:p-7 shadow-[0_4px_20px_rgba(107,66,38,0.06)] relative overflow-hidden">
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#6B4226] via-[#8C5832] to-[#6B4226]"></div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-5">
                                <div class="w-14 h-14 rounded-2xl bg-[#FAF4ED] border border-[#D9C3B0] flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                                    <span>📍</span>
                                </div>
                                <div class="space-y-2 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#6B4226] text-white text-[10px] font-black px-2.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">
                                            Alamat Belum Diatur
                                        </span>
                                    </div>
                                    <h3 class="font-black text-base sm:text-lg text-[#2D241E]">
                                        Alamat Pengiriman Belum Lengkap
                                    </h3>
                                    <p class="text-xs sm:text-sm text-[#7A6C60] leading-relaxed">
                                        Kamu belum mengatur alamat pengiriman di akun kamu. Silakan lengkapi nama penerima, nomor telepon, dan alamat lengkap di menu <strong class="text-[#4A2E1B]">Pengaturan</strong> agar pesanan dapat dikirimkan ke lokasi yang tepat.
                                    </p>
                                    <div class="pt-2">
                                        <a href="{{ route('settings', ['view' => 'address', 'return_to' => 'checkout']) }}" 
                                           class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 cursor-pointer">
                                            <span>⚙️</span>
                                            <span>Lengkapi Alamat di Pengaturan Sekarang</span>
                                            <span>→</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 2. Review Produk yang Dipesan -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                            <div class="flex items-center gap-2 font-bold text-sm text-[#2D241E]">
                                <span>📦</span>
                                <span>Produk yang Dipesan (<span x-text="checkoutCount"></span> item)</span>
                            </div>
                            <a href="{{ route('cart') }}" class="text-xs text-[#6B4226] font-bold hover:underline">
                                Ubah Keranjang
                            </a>
                        </div>

                            <template x-for="item in checkoutItems" :key="item.cartKey || item.id">
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] flex items-center justify-center shrink-0 overflow-hidden">
                                            <template x-if="item.image_url">
                                                <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.image_url">
                                                <span class="text-2xl" x-text="item.icon || '🛍️'"></span>
                                            </template>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-medium text-[#8A7C70] uppercase" x-text="item.brand || 'Official Store'"></span>
                                            <h4 class="font-bold text-xs sm:text-sm text-[#2D241E]" x-text="item.name"></h4>
                                            <p x-show="item.variant_name" class="text-[11px] font-bold text-[#6B4226]" x-text="'Varian: ' + item.variant_name"></p>
                                            <p class="text-[11px] text-[#8A7C70]" x-text="item.quantity + 'x @ ' + formatRupiah(item.price)"></p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-xs sm:text-sm text-[#6B4226]" x-text="formatRupiah(item.price * item.quantity)"></span>
                                </div>
                            </template>
                    </div>

                    <!-- 3. Pilihan Metode Pembayaran -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-4">
                        <div class="flex items-center gap-2 font-bold text-sm text-[#2D241E] pb-3 border-b border-[#F2EAE0]">
                            <span>💳</span>
                            <span>Pilih Metode Pembayaran</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- QRIS -->
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition"
                                   :class="paymentMethod === 'qris' ? 'border-[#6B4226] bg-[#FAF4ED] ring-1 ring-[#6B4226]' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED]'">
                                <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="sr-only">
                                <span class="text-2xl mt-0.5">📱</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-[#2D241E]">QRIS Instan</p>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-800 shrink-0">Langsung Lunas</span>
                                    </div>
                                    <p class="text-[10px] text-[#7A6C60] leading-tight mt-0.5">BCA Mobile, Mandiri, BRI, GoPay, OVO, DANA</p>
                                </div>
                            </label>

                            <!-- COD -->
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition"
                                   :class="paymentMethod === 'cod' ? 'border-[#6B4226] bg-[#FAF4ED] ring-1 ring-[#6B4226]' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED]'">
                                <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="sr-only">
                                <span class="text-2xl mt-0.5">💵</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-[#2D241E]">COD (Bayar di Tempat)</p>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-amber-100 text-amber-800 shrink-0">Bayar saat Kurir Tiba</span>
                                    </div>
                                    <p class="text-[10px] text-[#7A6C60] leading-tight mt-0.5">Bayar tunai ke kurir saat pesanan sampai di alamat</p>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Vouchers & Order Summary (5 Cols) -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <!-- Voucher Diskon Card -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs uppercase tracking-wider text-[#2D241E] flex items-center gap-1.5">
                                <span>🎟️</span>
                                <span>Voucher Diskon</span>
                            </span>
                            <span class="text-[11px] text-[#8A7C70]">Kode promo</span>
                        </div>

                        <div class="flex gap-2">
                            <input type="text" 
                                   x-model="couponInput" 
                                   :disabled="appliedCoupon !== ''"
                                   placeholder="Masukkan kode voucher..."
                                   class="flex-1 px-4 py-2 text-xs bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 uppercase font-mono font-bold">
                            <button type="button" 
                                    x-show="!appliedCoupon"
                                    @click="applyCoupon()" 
                                    class="px-4 py-2 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition cursor-pointer">
                                Pakai
                            </button>
                            <button type="button" 
                                    x-show="appliedCoupon"
                                    x-cloak
                                    @click="removeCoupon()" 
                                    class="px-3 py-2 bg-red-50 text-red-800 border border-red-200 text-xs font-bold rounded-xl transition cursor-pointer">
                                Hapus
                            </button>
                        </div>

                        <!-- Feedback -->
                        <div x-show="couponMsg" x-cloak 
                             :class="couponMsgType === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'"
                             class="p-2.5 rounded-xl border text-[11px] font-semibold">
                            <span x-text="couponMsg"></span>
                        </div>
                    </div>

                    <!-- Payment Summary Box -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-4">
                        <h3 class="font-bold text-sm text-[#2D241E] pb-3 border-b border-[#F2EAE0]">
                            Ringkasan Pembayaran
                        </h3>

                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between text-[#7A6C60]">
                                <span>Total Belanja (<span x-text="checkoutCount"></span> produk):</span>
                                <span class="font-bold text-[#2D241E]" x-text="formatRupiah(checkoutAmount)"></span>
                            </div>

                            <div class="flex justify-between text-[#7A6C60]">
                                <span>Ongkos Kirim (Flat):</span>
                                <span class="font-bold text-emerald-700" x-text="shippingCost === 0 ? 'GRATIS (Promo)' : formatRupiah(15000)"></span>
                            </div>

                            <div x-show="discountAmount > 0 && appliedCoupon !== 'GRATISONGKIR'" x-cloak class="flex justify-between text-emerald-700 font-bold">
                                <span>Potongan Kupon (<span x-text="appliedCoupon"></span>):</span>
                                <span x-text="'- ' + formatRupiah(discountAmount)"></span>
                            </div>

                            <div class="border-t border-dashed border-[#EAE1D7] pt-3 flex justify-between items-center text-sm">
                                <span class="font-bold text-[#2D241E]">Total Pembayaran:</span>
                                <span class="font-black text-2xl text-[#6B4226]" x-text="formatRupiah(grandTotal)"></span>
                            </div>
                        </div>

                        @if($hasAddress)
                            <button type="submit" 
                                    :disabled="isSubmitting || checkoutCount === 0"
                                    class="w-full py-4 px-6 bg-[#6B4226] hover:bg-[#54321B] disabled:opacity-60 text-white font-bold rounded-2xl shadow-xs transition transform active:scale-98 text-sm flex items-center justify-center gap-2 mt-2 cursor-pointer">
                                <span x-show="!isSubmitting">Pesan Sekarang 🚀</span>
                                <span x-show="isSubmitting" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Memproses Pesanan...</span>
                                </span>
                            </button>
                        @else
                            <a href="{{ route('settings', ['view' => 'address', 'return_to' => 'checkout']) }}" 
                               class="w-full py-4 px-6 bg-gradient-to-r from-[#6B4226] to-[#54321B] hover:from-[#54321B] hover:to-[#3E2412] text-white font-bold rounded-2xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-98 text-sm flex items-center justify-center gap-2 mt-2 cursor-pointer text-center group">
                                <span>📍 Lengkapi Alamat Dahulu Sebelum Bayar</span>
                                <span class="transition-transform group-hover:translate-x-1">→</span>
                            </a>
                            <p class="text-[11px] text-center text-[#8A7C70] font-medium leading-tight">
                                Tombol pemesanan akan aktif setelah alamat pengiriman dilengkapi di menu pengaturan.
                            </p>
                        @endif

                        <p class="text-[11px] text-center text-[#9E9084] leading-tight">
                            Dengan mengklik "Pesan Sekarang", kamu menyetujui syarat & ketentuan belanja di NusantaraMart.
                        </p>
                    </div>

                </div>

            </div>
        </form>
        </div>

    </div>
</div>
@endsection
