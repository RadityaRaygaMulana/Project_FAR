@extends('layouts.app')

@section('title', 'Keranjang Belanja — NusantaraMart')

@section('content')
<div class="py-8 sm:py-12 bg-[#FAF8F5] min-h-[85vh]"
     x-data="{
         selectAll: true,
         toggleSelectAll() {
             this.items.forEach(i => i.selected = this.selectAll);
             this.saveCart();
         },
         updateSelection() {
             this.selectAll = this.items.length > 0 && this.items.every(i => i.selected);
             this.saveCart();
         },
         get selectedItems() {
             return this.items.filter(i => i.selected !== false);
         },
         get selectedCount() {
             return this.selectedItems.reduce((sum, i) => sum + i.quantity, 0);
         },
         get selectedAmount() {
             return this.selectedItems.reduce((sum, i) => sum + (i.price * i.quantity), 0);
         },
         removeSelected() {
             this.items = this.items.filter(i => i.selected === false);
             this.saveCart();
         },
         proceedToCheckout() {
             if (this.selectedCount === 0) {
                 alert('Silakan pilih minimal 1 produk untuk dicheckout!');
                 return;
             }
             window.location.href = '{{ route('checkout.show') }}';
         }
     }"
     x-init="items.forEach(i => { if (i.selected === undefined) i.selected = true; }); updateSelection();">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header with Inline Circular Back Button & Actions -->
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
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
                        <span class="text-[#6B4226] font-semibold">Keranjang Belanja</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#2D241E] tracking-tight flex items-center gap-2">
                        <span>Keranjang Belanja Kamu</span>
                        <span class="text-xl">🛍️</span>
                    </h1>
                </div>
            </div>
            <a href="{{ route('home') }}#katalog" class="text-xs font-bold text-[#6B4226] hover:text-[#54321B] transition flex items-center gap-1.5 self-start sm:self-auto">
                <span>← Lanjut Belanja Produk Lain</span>
            </a>
        </div>

        <!-- Empty Cart State -->
        <div x-show="items.length === 0" x-cloak class="bg-white rounded-3xl p-12 text-center border border-[#EAE1D7] shadow-2xs max-w-lg mx-auto my-12">
            <div class="w-24 h-24 rounded-full bg-[#FAF7F2] border border-[#EAE1D7] flex items-center justify-center text-5xl mx-auto mb-4 animate-bounce">
                🛒
            </div>
            <h2 class="text-xl font-bold text-[#2D241E] mb-1">Keranjang Belanja Masih Kosong</h2>
            <p class="text-xs text-[#7A6C60] max-w-sm mx-auto mb-6">Yuk temukan produk gadget, fashion, kuliner, dan kebutuhan rumah tangga impianmu di katalog NusantaraMart!</p>
            <a href="{{ route('home') }}#katalog" 
               class="px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-2xl text-xs sm:text-sm shadow-xs transition inline-block">
                Mulai Belanja Sekarang 🛍️
            </a>
        </div>

        <!-- Cart Container -->
        <div x-show="items.length > 0" class="space-y-4">
            
            <!-- Free Shipping Promo Banner -->
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#EAE1D7] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🚚</span>
                    <div>
                        <span class="font-bold text-[#6B4226]">Promo Bebas Ongkir NusantaraMart</span>
                        <p class="text-[#7A6C60] text-[11px]" x-text="selectedAmount >= 100000 ? '🎉 Selamat! Pesananmu memenuhi syarat Bebas Ongkir (min. 100rb)' : 'Tambah ' + formatRupiah(100000 - selectedAmount) + ' lagi untuk nikmati Bebas Ongkir!'"></p>
                    </div>
                </div>
                <div class="w-full sm:w-48 bg-[#FAF7F2] rounded-full h-2 overflow-hidden border border-[#EAE1D7]">
                    <div class="bg-[#6B4226] h-2 rounded-full transition-all duration-300" :style="'width: ' + Math.min(100, (selectedAmount / 100000) * 100) + '%'"></div>
                </div>
            </div>

            <!-- SELECTION TOOLBAR CARD -->
            <div class="bg-white p-4 sm:px-6 rounded-2xl border border-[#EAE1D7] flex items-center justify-between shadow-2xs">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" 
                           x-model="selectAll" 
                           @change="toggleSelectAll()" 
                           class="w-4.5 h-4.5 text-[#6B4226] border-stone-300 rounded-md focus:ring-[#6B4226] cursor-pointer">
                    <span class="text-xs sm:text-sm font-bold text-[#2D241E]">
                        Pilih Semua (<span x-text="items.length"></span> Produk)
                    </span>
                </label>

                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="removeSelected()" 
                            x-show="selectedCount > 0" 
                            class="text-xs text-red-600 font-bold hover:underline cursor-pointer flex items-center gap-1">
                        <span>🗑️</span>
                        <span>Hapus Pilihan (<span x-text="selectedCount"></span>)</span>
                    </button>
                    <span class="text-xs font-semibold text-[#8A7C70] hidden sm:inline" x-text="selectedCount + ' produk terpilih'"></span>
                </div>
            </div>

            <!-- INDIVIDUAL PRODUCT SELECTION CARDS LIST -->
            <div class="space-y-3 sm:space-y-4">
                <template x-for="(item, index) in items" :key="item.cartKey || item.id">
                    <div class="rounded-2xl sm:rounded-3xl p-4 sm:p-5 border transition-all duration-200 relative group"
                         :class="item.selected !== false 
                             ? 'bg-white border-[#6B4226]/40 shadow-xs ring-1 ring-[#6B4226]/10' 
                             : 'bg-[#FAF8F5]/70 border-[#EAE1D7] opacity-60 hover:opacity-90'">
                        
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            
                            <!-- Left: Checkbox, Product Thumbnail & Info -->
                            <div class="flex items-start sm:items-center gap-3.5 flex-1 min-w-0">
                                <!-- Checkbox -->
                                <div class="pt-1 sm:pt-0 shrink-0">
                                    <input type="checkbox" 
                                           x-model="item.selected" 
                                           @change="updateSelection()"
                                           class="w-5 h-5 text-[#6B4226] border-stone-300 rounded-md focus:ring-[#6B4226] cursor-pointer">
                                </div>

                                <!-- Product Thumbnail Stage -->
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-b from-[#FAF8F5] to-[#FAF4ED] border border-[#F2EAE0] flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                                    <template x-if="item.image_url">
                                        <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image_url">
                                        <span class="text-3xl sm:text-4xl" x-text="item.icon || '🛍️'"></span>
                                    </template>
                                </div>

                                <!-- Title, Brand & Price Info -->
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                              :class="item.badge === 'Mall' ? 'bg-[#D9381E] text-white' : 'bg-[#6B4226] text-white'"
                                              x-text="item.badge || 'Official'"></span>
                                        <span class="text-[11px] font-medium text-[#8A7C70] uppercase tracking-wider truncate" 
                                              x-text="item.brand || 'NusantaraMart'"></span>
                                    </div>

                                    <h3 class="font-bold text-sm text-[#2D241E] leading-snug line-clamp-2" x-text="item.name"></h3>

                                    <!-- Variant Tag -->
                                    <div x-show="item.variant_name" class="pt-0.5">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] px-2 py-0.5 rounded-lg shadow-2xs">
                                            <span>✨</span>
                                            <span x-text="'Varian: ' + item.variant_name"></span>
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2 pt-0.5">
                                        <span class="text-xs font-bold text-[#6B4226]" x-text="formatRupiah(item.price)"></span>
                                        <span class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded font-bold border border-emerald-200/60">
                                            🚚 Bebas Ongkir
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Quantity Controller, Subtotal & Delete Action -->
                            <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-5 pt-3 sm:pt-0 border-t sm:border-t-0 border-[#F2EAE0] shrink-0">
                                
                                <!-- Quantity Controller -->
                                <div class="flex items-center border border-[#EAE1D7] bg-[#FAF8F5] rounded-xl overflow-hidden shadow-2xs">
                                    <button type="button" 
                                            @click="updateQty(item.cartKey || item.id, -1)" 
                                            class="w-8 h-8 flex items-center justify-center text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#6B4226] transition font-bold text-sm cursor-pointer">-</button>
                                    <span class="w-9 text-center text-xs font-mono font-bold text-[#2D241E]" x-text="item.quantity"></span>
                                    <button type="button" 
                                            @click="updateQty(item.cartKey || item.id, 1)" 
                                            class="w-8 h-8 flex items-center justify-center text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#6B4226] transition font-bold text-sm cursor-pointer">+</button>
                                </div>

                                <!-- Subtotal Amount -->
                                <div class="text-right min-w-[110px]">
                                    <span class="text-[10px] text-[#8A7C70] block">Subtotal:</span>
                                    <span class="text-sm sm:text-base font-black text-[#6B4226]" 
                                          x-text="formatRupiah(item.price * item.quantity)"></span>
                                </div>

                                <!-- Trash Button -->
                                <button type="button" 
                                        @click="removeItem(item.cartKey || item.id)" 
                                        class="p-2 text-stone-300 hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer"
                                        title="Hapus Produk dari Keranjang">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>

                            </div>

                        </div>
                    </div>
                </template>
            </div>

            <!-- Voucher Bar Strip Card -->
            <div class="p-4 bg-white rounded-2xl border border-[#EAE1D7] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="flex items-center gap-2 text-[#5A4B40]">
                    <span class="text-lg">🎟️</span>
                    <span class="font-bold">Voucher Marketplace:</span>
                    <span class="text-[#7A6C60]">Punya kode voucher diskon? Kamu bisa memasukkannya di halaman checkout!</span>
                </div>
            </div>

            <!-- Sticky / Action Bottom Bar -->
            <div class="sticky bottom-4 z-30 bg-white rounded-3xl border border-[#EAE1D7] shadow-xl p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <!-- Left selection summary -->
                <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-start">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-[#5A4B40]">
                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="w-4 h-4 text-[#6B4226] border-stone-300 rounded focus:ring-[#6B4226] cursor-pointer">
                        <span>Pilih Semua (<span x-text="items.length"></span>)</span>
                    </label>
                    <button @click="items = []; saveCart();" class="text-xs text-[#8A7C70] hover:text-red-600 transition cursor-pointer">
                        Kosongkan
                    </button>
                </div>

                <!-- Right Price & Checkout Button -->
                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
                    <div class="text-right">
                        <p class="text-xs text-[#7A6C60] font-medium">Total (<span x-text="selectedCount"></span> produk):</p>
                        <p class="text-xl sm:text-2xl font-black text-[#6B4226]" x-text="formatRupiah(selectedAmount)"></p>
                    </div>

                    @guest
                        <a href="{{ route('login') }}" 
                           class="px-6 sm:px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-2xl text-xs sm:text-sm shadow-xs transition transform active:scale-95 whitespace-nowrap">
                            Masuk untuk Checkout 🔒
                        </a>
                    @else
                        <button type="button" 
                                @click="proceedToCheckout()"
                                :disabled="selectedCount === 0"
                                :class="selectedCount === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:scale-102 active:scale-95 cursor-pointer'"
                                class="px-6 sm:px-10 py-3.5 sm:py-4 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-2xl text-sm shadow-sm transition transform whitespace-nowrap flex items-center gap-2">
                            <span>Checkout (<span x-text="selectedCount"></span>)</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    @endguest
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
