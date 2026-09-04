@extends('layouts.app')

@section('title', 'NusantaraMart — Marketplace Belanja Online Pilihan Terlengkap')

@section('content')
<div class="space-y-8 sm:space-y-12 pb-16" 
     x-data="{ 
         showCategoryExplorer: false, 
         showCatalogFilterModal: false,
         init() {
             this.$watch('showCategoryExplorer', val => {
                 document.body.style.overflow = (val || this.showCatalogFilterModal) ? 'hidden' : '';
             });
             this.$watch('showCatalogFilterModal', val => {
                 document.body.style.overflow = (val || this.showCategoryExplorer) ? 'hidden' : '';
             });
         }
     }">

    <!-- 1. HERO BANNER SECTION (2-Column Banner Grid in Rich Chocolate #6B4226) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-4">
            
            <!-- Left Main Banner: Nusantara Pilih Lokal (8 Cols) -->
            <div class="lg:col-span-8 relative rounded-3xl overflow-hidden bg-gradient-to-r from-[#6B4226] via-[#7D4F2E] to-[#54321B] text-white p-6 sm:p-8 shadow-sm flex flex-col justify-between min-h-[290px] sm:min-h-[320px] border border-[#54321B]">
                <!-- Ambient soft patterns -->
                <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-black/20 rounded-full blur-2xl pointer-events-none"></div>

                <!-- Top Title & Brand Showcase -->
                <div class="relative z-10 space-y-4">
                    <div class="text-center sm:text-left space-y-1">
                        <div class="inline-flex items-center gap-2">
                            <span class="text-xl sm:text-2xl font-black text-white tracking-tight">
                                Nusantara<span class="text-[#FDECD2]">PilihLokal</span>
                            </span>
                            <span class="text-xl">❤️</span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#F5EBE1] font-medium">
                            Rumah Produk Pilihan & Brand Unggulan No. 1 di Indonesia
                        </p>
                    </div>

                    <!-- Local Brand Showcase Tiles -->
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                        <div class="px-3 py-1 bg-white/15 backdrop-blur-sm rounded-xl border border-white/20 text-xs font-bold text-white shadow-2xs">
                            Eiger
                        </div>
                        <div class="px-3 py-1 bg-white/15 backdrop-blur-sm rounded-xl border border-white/20 text-xs font-bold text-white shadow-2xs">
                            Erigo
                        </div>
                        <div class="px-3 py-1 bg-white/15 backdrop-blur-sm rounded-xl border border-white/20 text-xs font-bold text-white shadow-2xs">
                            Somethinc
                        </div>
                        <div class="px-3 py-1 bg-white/15 backdrop-blur-sm rounded-xl border border-white/20 text-xs font-bold text-white shadow-2xs">
                            Ventela
                        </div>
                        <div class="px-3 py-1 bg-white/15 backdrop-blur-sm rounded-xl border border-white/20 text-xs font-bold text-white shadow-2xs">
                            Kahf
                        </div>
                        <div class="px-3 py-1 bg-white/15 backdrop-blur-sm rounded-xl border border-white/20 text-xs font-bold text-white shadow-2xs">
                            Fore
                        </div>
                        <a href="{{ route('search.results', ['q' => 'Lokal']) }}" class="px-3 py-1 bg-[#FDECD2] hover:bg-white text-[#6B4226] font-extrabold text-[11px] rounded-xl transition shadow-xs">
                            + RIBUAN BRAND PILIHAN LAINNYA →
                        </a>
                    </div>
                </div>

                <!-- Bottom 3 Feature Strips -->
                <div class="relative z-10 pt-6 mt-4 border-t border-white/15 grid grid-cols-1 sm:grid-cols-3 gap-3 text-left">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-sm shrink-0">
                            🏷️
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Produk Unik</p>
                            <p class="text-[10px] text-[#F5EBE1]">Asli Kreasi Indonesia</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-sm shrink-0">
                            💡
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Inovasi Lokal</p>
                            <p class="text-[10px] text-[#F5EBE1]">Rasa & Kualitas Global</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-sm shrink-0">
                            ⭐
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Kualitas Unggulan</p>
                            <p class="text-[10px] text-[#F5EBE1]">Jaminan 100% Teruji</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Consolidated Banner (4 Cols) -->
            <div class="lg:col-span-4 relative rounded-3xl overflow-hidden bg-gradient-to-br from-[#54321B] via-[#6B4226] to-[#422210] text-white p-6 sm:p-7 shadow-sm flex flex-col justify-between min-h-[290px] sm:min-h-[320px] border border-[#54321B] group">
                <!-- Ambient soft pattern -->
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-black/20 rounded-full blur-xl pointer-events-none"></div>

                <!-- Top Badges & Title -->
                <div class="relative z-10 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-[#FDECD2] bg-white/15 px-2.5 py-1 rounded-lg border border-white/20">
                            🏬 Official Store
                        </span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-200 bg-white/15 px-2.5 py-1 rounded-lg border border-white/20">
                            ⚡ Diskon s.d 80%
                        </span>
                    </div>

                    <div>
                        <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight leading-tight">
                            Nusantara Mall
                        </h3>
                        <p class="text-xs text-[#FDECD2] font-black tracking-wider uppercase mt-0.5">
                            100% Original & Bergaransi
                        </p>
                    </div>

                    <p class="text-xs text-[#F5EBE1] leading-relaxed">
                        Belanja aneka kebutuhan harian dan brand resmi dengan promo berkah bebas ongkir se-Indonesia.
                    </p>
                </div>

                <!-- Bottom Action / CTA Button -->
                <div class="relative z-10 pt-4 border-t border-white/15 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-[#F5EBE1]">Bebas Ongkir</p>
                        <p class="text-xs font-bold text-white">Se-Indonesia 🚚</p>
                    </div>
                    <a href="{{ route('search.results', ['badge' => 'Mall']) }}" 
                       class="px-4 py-2 bg-[#FDECD2] hover:bg-white text-[#6B4226] font-bold text-xs rounded-xl transition shadow-xs flex items-center gap-1.5 group-hover:scale-105">
                        <span>Belanja Mall</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- 2. 10 FITUR & SHORTCUT NAVIGASI (Tema Coklat NusantaraMart) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-2xs overflow-x-auto hide-scrollbar">
            <div class="grid grid-cols-5 sm:grid-cols-10 gap-3 min-w-[650px] sm:min-w-0">
                
                <!-- 1. Official Brand -->
                <a href="{{ route('official.brand') }}" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        🏬
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Official Brand</span>
                </a>

                <!-- 2. Produk Trending -->
                <a href="{{ route('products.trending') }}" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        🔥
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Produk Trending</span>
                </a>

                <!-- 3. Top Up & Tagihan -->
                <a href="{{ route('topup.bills') }}" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        📱
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Top Up & Tagihan</span>
                </a>

                <!-- 4. Promo Terbatas -->
                <a href="{{ route('promo.limited') }}" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        ⚡
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Promo Terbatas</span>
                </a>

                <!-- 5. Kebutuhan Pokok -->
                <a href="{{ route('kebutuhan.pokok') }}" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        🛒
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Kebutuhan Pokok</span>
                </a>

                <!-- 6. Gratis Ongkir -->
                <a href="#katalog" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        🚚
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Gratis Ongkir</span>
                </a>

                <!-- 7. Penawaran Spesial -->
                <a href="{{ route('search.results', ['sort' => 'highest_discount']) }}" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        🏷️
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Penawaran Spesial</span>
                </a>

                <!-- 8. Voucher -->
                <a href="#katalog" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        🎟️
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Voucher</span>
                </a>

                <!-- 9. Produk Baru -->
                <a href="{{ route('search.results', ['sort' => 'newest']) }}" class="flex flex-col items-center text-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-[#6B4226] group-hover:text-white transition shadow-2xs mb-2">
                        ✨
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Produk Baru</span>
                </a>

                <!-- 10. Eksplorasi Kategori (Membuka Modal Card Semua Kategori) -->
                <button type="button" 
                        @click="showCategoryExplorer = true" 
                        class="flex flex-col items-center text-center group cursor-pointer focus:outline-none">
                    <div class="w-12 h-12 rounded-2xl bg-[#FAF4ED] border border-[#E8DED3] flex items-center justify-center group-hover:scale-110 group-hover:bg-[#6B4226] transition shadow-2xs mb-2">
                        <svg class="w-6 h-6 text-[#6B4226] group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3.5" y="3.5" width="7" height="7" rx="2" stroke-width="2"></rect>
                            <rect x="13.5" y="3.5" width="7" height="7" rx="2" stroke-width="2"></rect>
                            <rect x="3.5" y="13.5" width="7" height="7" rx="2" stroke-width="2"></rect>
                            <rect x="13.5" y="13.5" width="7" height="7" rx="2" stroke-width="2"></rect>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition leading-tight">Eksplorasi Kategori</span>
                </button>

            </div>
        </div>
    </section>

    <!-- 3. MODAL CARD EKSPLORASI SEMUA KATEGORI & LAYANAN (Interactive Explorer Modal) -->
    @php
        $marketplaceCategories = [
            ['name' => 'Elektronik & Gadget', 'icon' => '📱', 'query' => 'Elektronik'],
            ['name' => 'Komputer & Laptop', 'icon' => '💻', 'query' => 'Komputer'],
            ['name' => 'Fashion & Busana', 'icon' => '👕', 'query' => 'Fashion'],
            ['name' => 'Sepatu & Alas Kaki', 'icon' => '👟', 'query' => 'Sepatu'],
            ['name' => 'Tas & Aksesoris', 'icon' => '🎒', 'query' => 'Tas'],
            ['name' => 'Kecantikan & Skincare', 'icon' => '💄', 'query' => 'Kecantikan'],
            ['name' => 'Makanan & Kuliner', 'icon' => '🍜', 'query' => 'Makanan'],
            ['name' => 'Rumah Tangga & Dapur', 'icon' => '🏠', 'query' => 'Rumah Tangga'],
            ['name' => 'Furnitur & Dekorasi', 'icon' => '🛋️', 'query' => 'Furnitur'],
            ['name' => 'Olahraga & Outdoor', 'icon' => '⚽', 'query' => 'Olahraga'],
            ['name' => 'Hobi & Gaming', 'icon' => '🎮', 'query' => 'Hobi'],
            ['name' => 'Otomotif & Aksesoris', 'icon' => '🚗', 'query' => 'Otomotif'],
            ['name' => 'Buku & Alat Tulis', 'icon' => '📚', 'query' => 'Buku'],
            ['name' => 'Ibu, Bayi & Anak', 'icon' => '🍼', 'query' => 'Ibu Bayi'],
            ['name' => 'Kesehatan & Medis', 'icon' => '💊', 'query' => 'Kesehatan'],
            ['name' => 'Kerajinan Nusantara', 'icon' => '🎁', 'query' => 'Kerajinan'],
        ];
    @endphp
    <div x-show="showCategoryExplorer" 
         x-cloak
         @keydown.escape.window="showCategoryExplorer = false"
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop Blur -->
        <div x-show="showCategoryExplorer" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showCategoryExplorer = false"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
            <div x-show="showCategoryExplorer" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.away="showCategoryExplorer = false"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-5xl border border-[#EAE1D7] max-h-[90vh] flex flex-col">
                
                <!-- Modal Header -->
                <div class="p-6 sm:p-8 bg-[#FAF7F2] border-b border-[#EAE1D7] flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-[#6B4226] text-white flex items-center justify-center text-2xl shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3.5" y="3.5" width="7" height="7" rx="2" stroke-width="2"></rect>
                                <rect x="13.5" y="3.5" width="7" height="7" rx="2" stroke-width="2"></rect>
                                <rect x="3.5" y="13.5" width="7" height="7" rx="2" stroke-width="2"></rect>
                                <rect x="13.5" y="13.5" width="7" height="7" rx="2" stroke-width="2"></rect>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-[#2D241E] tracking-tight">
                                Eksplorasi Semua Kategori & Layanan
                            </h3>
                            <p class="text-xs text-[#7A6C60]">
                                Temukan fitur belanja, menu transaksi, dan seluruh departemen produk pilihan di NusantaraMart.
                            </p>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <button type="button" 
                            @click="showCategoryExplorer = false" 
                            class="w-10 h-10 rounded-2xl bg-white border border-[#EAE1D7] text-[#5A4B40] hover:text-red-600 hover:border-red-200 transition flex items-center justify-center font-bold text-lg shadow-2xs self-end sm:self-auto cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Modal Scrollable Body -->
                <div class="p-6 sm:p-8 overflow-y-auto space-y-8 divide-y divide-[#F2EAE0]">
                    
                    <!-- Section 1: Menu & Layanan Transaksi Utama -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-wider text-[#6B4226] bg-[#FAF4ED] px-3 py-1 rounded-full border border-[#6B4226]/20">
                                🌟 Fitur & Layanan Belanja Utama
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                            <!-- 1. Official Brand -->
                            <a href="{{ route('official.brand') }}" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    🏬
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Official Brand</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Jaminan 100% Original dari brand dan distributor resmi.</p>
                                </div>
                            </a>

                            <!-- 2. Produk Trending -->
                            <a href="{{ route('products.trending') }}" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    🔥
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Produk Trending</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Koleksi produk paling laris dan banyak dicari minggu ini.</p>
                                </div>
                            </a>

                            <!-- 3. Top Up & Tagihan -->
                            <a href="{{ route('topup.bills') }}" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    📱
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Top Up & Tagihan</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Pulsa, Paket Data, PLN, BPJS, PDAM & Voucher Game.</p>
                                </div>
                            </a>

                            <!-- 4. Promo Terbatas -->
                            <a href="{{ route('promo.limited') }}" @click="showCategoryExplorer = false" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    ⚡
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Promo Terbatas</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Kejar diskon kilat flash sale s/d 80% dengan waktu terbatas.</p>
                                </div>
                            </a>

                            <!-- 5. Kebutuhan Pokok -->
                            <a href="{{ route('kebutuhan.pokok') }}" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    🛒
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Kebutuhan Pokok</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Sembako, bahan bumbu dapur, dan keperluan supermarket.</p>
                                </div>
                            </a>

                            <!-- 6. Gratis Ongkir -->
                            <a href="#katalog" @click="showCategoryExplorer = false" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    🚚
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Gratis Ongkir</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Pengiriman tanpa biaya ongkir ke seluruh penjuru Indonesia.</p>
                                </div>
                            </a>

                            <!-- 7. Penawaran Spesial -->
                            <a href="{{ route('search.results', ['sort' => 'highest_discount']) }}" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    🏷️
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Penawaran Spesial</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Diskon toko eksklusif, cashback koin, dan bundling hemat.</p>
                                </div>
                            </a>

                            <!-- 8. Voucher -->
                            <a href="#katalog" @click="showCategoryExplorer = false" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    🎟️
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Voucher Belanja</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Klaim kupon potongan harga hingga Rp 25.000 untuk checkout.</p>
                                </div>
                            </a>

                            <!-- 9. Produk Baru -->
                            <a href="{{ route('search.results', ['sort' => 'newest']) }}" class="p-4 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:border-[#6B4226]/40 transition flex items-start gap-3.5 group">
                                <div class="w-11 h-11 rounded-xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-105 transition shrink-0">
                                    ✨
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition">Produk Baru</h4>
                                    <p class="text-[11px] text-[#7A6C60] mt-0.5 leading-snug">Koleksi produk rilisan anyar dan new arrivals terkini.</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Section 2: Semua Departemen Produk Belanja Lengkap -->
                    <div class="pt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-[#6B4226] bg-[#FAF4ED] px-3 py-1 rounded-full border border-[#6B4226]/20">
                                🛍️ Seluruh Departemen Produk Belanja
                            </span>
                            <span class="text-xs text-[#8A7C70] font-medium">Klik untuk telusuri produk</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5">
                            @foreach($marketplaceCategories as $cat)
                                <a href="{{ route('search.results', ['q' => $cat['query']]) }}" 
                                   class="p-4 rounded-2xl border border-[#EAE1D7] bg-white hover:bg-[#FAF4ED] hover:border-[#6B4226]/50 transition flex items-center gap-3.5 group shadow-2xs">
                                    <div class="w-12 h-12 rounded-xl bg-[#FAF8F5] border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shrink-0">
                                        {{ $cat['icon'] }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] group-hover:text-[#6B4226] transition truncate">
                                            {{ $cat['name'] }}
                                        </h4>
                                        <p class="text-[11px] text-[#8A7C70] mt-0.5">Jelajahi Produk →</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- 4. SEKSI KATEGORI PRODUK UTAMA (16 Kategori Netral Terpusat Simetris Sempurna) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-7 shadow-2xs space-y-4 relative"
             x-data="{
                 canScrollLeft: false,
                 canScrollRight: false,
                 updateScroll() {
                     const el = this.$refs.categoryContainer;
                     if (!el) return;
                     const hasOverflow = el.scrollWidth > (el.clientWidth + 10);
                     this.canScrollLeft = hasOverflow && el.scrollLeft > 15;
                     this.canScrollRight = hasOverflow && ((el.scrollLeft + el.clientWidth) < (el.scrollWidth - 15));
                 },
                 scrollLeft() {
                     this.$refs.categoryContainer.scrollBy({ left: -320, behavior: 'smooth' });
                     setTimeout(() => this.updateScroll(), 350);
                 },
                 scrollRight() {
                     this.$refs.categoryContainer.scrollBy({ left: 320, behavior: 'smooth' });
                     setTimeout(() => this.updateScroll(), 350);
                 }
             }"
             x-init="$nextTick(() => { updateScroll(); });"
             @resize.window="updateScroll()">
            
            <!-- Category Section Header -->
            <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#6B4226]"></span>
                    <h2 class="text-sm sm:text-base font-extrabold uppercase tracking-wider text-[#2D241E]">
                        Kategori Belanja Pilihan
                    </h2>
                </div>
                <button type="button" @click="showCategoryExplorer = true" class="text-xs font-bold text-[#6B4226] hover:underline cursor-pointer">
                    Semua Kategori & Layanan →
                </button>
            </div>

            <!-- Carousel & Grid Wrapper -->
            <div class="relative">
                
                <!-- Circular Left Scroll Button (<) -->
                <button type="button" 
                        x-show="canScrollLeft" 
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-75"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-75"
                        @click="scrollLeft()" 
                        class="absolute -left-3 sm:-left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] shadow-lg flex items-center justify-center font-bold hover:scale-110 active:scale-95 transition z-20 cursor-pointer"
                        title="Geser Kategori ke Kiri">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <!-- Circular Right Scroll Button (>) -->
                <button type="button" 
                        x-show="canScrollRight" 
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-75"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-75"
                        @click="scrollRight()" 
                        class="absolute -right-3 sm:-right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] shadow-lg flex items-center justify-center font-bold hover:scale-110 active:scale-95 transition z-20 cursor-pointer"
                        title="Geser Kategori ke Kanan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Responsive Grid: Full Balanced 8 Columns on Desktop / Smooth Scroll on Mobile -->
                <div x-ref="categoryContainer" 
                     @scroll.passive="updateScroll()"
                     class="overflow-x-auto lg:overflow-visible scroll-smooth pb-2 lg:pb-0 hide-scrollbar">
                    <div class="grid grid-flow-col auto-cols-[120px] grid-rows-2 lg:grid-flow-row lg:grid-cols-8 lg:grid-rows-2 gap-3 sm:gap-3.5 w-max lg:w-full items-stretch">
                        @foreach($marketplaceCategories as $cat)
                            <a href="{{ route('search.results', ['q' => $cat['query']]) }}" 
                               class="p-3 rounded-2xl border border-[#EAE1D7] hover:border-[#6B4226]/50 bg-[#FAF8F5] hover:bg-[#FAF4ED] transition text-center flex flex-col items-center justify-center group h-[115px] shadow-2xs">
                                <div class="w-11 h-11 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl shadow-2xs group-hover:scale-110 transition mb-2">
                                    {{ $cat['icon'] }}
                                </div>
                                <span class="text-[11px] font-bold text-[#2D241E] group-hover:text-[#6B4226] transition line-clamp-2 leading-tight text-center">
                                    {{ $cat['name'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- 5. REKOMENDASI BELANJA (Katalog Produk Pilihan NusantaraMart Premium) -->
    <section id="katalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-8 shadow-2xs space-y-6">
            
            <!-- Clean Header with Section Title & Filter Button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-[#F2EAE0]">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#6B4226]"></span>
                        <h2 class="text-xl sm:text-2xl font-black text-[#2D241E] tracking-tight flex items-center gap-2">
                            <span>Rekomendasi Belanja Pilihan</span>
                            <span class="text-2xl">✨</span>
                        </h2>
                    </div>
                    <p class="text-xs text-[#7A6C60] mt-1">Temukan beragam produk berkualitas dan teruji dari berbagai merchant terpercaya</p>
                </div>

                <!-- Action Button: Open Filter Card Modal -->
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    @php
                        $activeCats = (array) request('categories', request('category', []));
                        $activeCats = array_filter(is_array($activeCats) ? $activeCats : [$activeCats]);

                        $activeBadges = (array) request('badges', request('badge', []));
                        $activeBadges = array_filter(is_array($activeBadges) ? $activeBadges : [$activeBadges]);

                        $activeFiltersCount = count($activeCats) + count($activeBadges);
                        if (request('sort') && request('sort') !== 'popular') $activeFiltersCount++;
                        if (request('min_price') || request('max_price')) $activeFiltersCount++;
                    @endphp

                    @if($activeFiltersCount > 0)
                        <a href="{{ route('home') }}#katalog" 
                           class="px-3.5 py-2 rounded-2xl bg-[#FAF7F2] hover:bg-[#F2EAE0] text-[#8A7C70] hover:text-[#6B4226] text-xs font-bold transition border border-[#EAE1D7]">
                            ✕ Reset Filter
                        </a>
                    @endif

                    <button type="button" 
                            @click="showCatalogFilterModal = true"
                            class="px-4 sm:px-5 py-2.5 rounded-2xl bg-[#6B4226] hover:bg-[#54321B] text-white text-xs sm:text-sm font-bold transition shadow-sm flex items-center gap-2 cursor-pointer group">
                        <svg class="w-4 h-4 text-amber-200 group-hover:rotate-12 transition transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>Filter & Urutkan</span>
                        @if($activeFiltersCount > 0)
                            <span class="w-5 h-5 rounded-full bg-white text-[#6B4226] text-[10px] font-black flex items-center justify-center">
                                {{ $activeFiltersCount }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Active Filter Badges Summary (if any) -->
            @if($activeFiltersCount > 0 || request('q'))
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="text-[11px] font-bold text-[#8A7C70] mr-1">Filter Aktif:</span>
                    
                    @if(request('q'))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226]/20 rounded-xl text-xs font-bold">
                            <span>Kata Kunci: "{{ request('q') }}"</span>
                        </span>
                    @endif

                    @if(request('sort') && request('sort') !== 'popular')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226]/20 rounded-xl text-xs font-bold">
                            <span>Urutan: 
                                {{ request('sort') === 'highest_discount' ? 'Diskon Terbesar' : (request('sort') === 'cheapest' ? 'Termurah' : (request('sort') === 'highest_price' ? 'Harga Tertinggi' : 'Terbaru')) }}
                            </span>
                        </span>
                    @endif

                    @foreach($activeCats as $cSlug)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226]/20 rounded-xl text-xs font-bold">
                            <span>Kategori: {{ ucwords(str_replace('-', ' ', $cSlug)) }}</span>
                        </span>
                    @endforeach

                    @foreach($activeBadges as $b)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226]/20 rounded-xl text-xs font-bold">
                            <span>Toko: {{ $b }}</span>
                        </span>
                    @endforeach

                    @if(request('min_price') || request('max_price'))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FAF4ED] text-[#6B4226] border border-[#6B4226]/20 rounded-xl text-xs font-bold">
                            <span>Harga: Rp {{ number_format(request('min_price', 0), 0, ',', '.') }} - {{ request('max_price') ? 'Rp '.number_format(request('max_price'), 0, ',', '.') : 'Maks' }}</span>
                        </span>
                    @endif
                </div>
            @endif

            <!-- MODAL CARD FILTER & URUTKAN PRODUK (Multi-Select Supported) -->
            <div x-show="showCatalogFilterModal" 
                 x-cloak
                 @keydown.escape.window="showCatalogFilterModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 aria-labelledby="modal-filter-title" 
                 role="dialog" 
                 aria-modal="true">
                
                <!-- Backdrop Blur -->
                <div x-show="showCatalogFilterModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="showCatalogFilterModal = false"
                     class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>

                <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
                    <div x-show="showCatalogFilterModal" 
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         @click.away="showCatalogFilterModal = false"
                         class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-2xl border border-[#EAE1D7] max-h-[90vh] flex flex-col">
                        
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('home') }}#katalog" class="flex flex-col h-full max-h-[90vh]">
                            @if(request('q'))
                                <input type="hidden" name="q" value="{{ request('q') }}">
                            @endif

                            <!-- Modal Header -->
                            <div class="p-5 sm:p-6 bg-[#FAF7F2] border-b border-[#EAE1D7] flex items-center justify-between shrink-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#6B4226] text-white flex items-center justify-center text-xl shadow-2xs">
                                        🎛️
                                    </div>
                                    <div>
                                        <h3 class="text-lg sm:text-xl font-extrabold text-[#2D241E] tracking-tight">
                                            Filter & Urutkan Produk
                                        </h3>
                                        <p class="text-xs text-[#7A6C60]">Bisa pilih lebih dari satu kategori & tipe toko</p>
                                    </div>
                                </div>

                                <button type="button" 
                                        @click="showCatalogFilterModal = false" 
                                        class="w-9 h-9 rounded-xl bg-white border border-[#EAE1D7] text-[#5A4B40] hover:text-red-600 transition flex items-center justify-center font-bold text-base cursor-pointer">
                                    ✕
                                </button>
                            </div>

                            <!-- Modal Scrollable Body -->
                            <div class="p-5 sm:p-6 overflow-y-auto space-y-6 divide-y divide-[#F2EAE0]">
                                
                                <!-- 1. Urutkan Produk -->
                                <div class="space-y-3">
                                    <label class="text-xs font-black uppercase tracking-wider text-[#6B4226] flex items-center gap-1.5">
                                        <span>🌟 Urutkan Berdasarkan</span>
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="sort" value="popular" class="peer sr-only" {{ request('sort', 'popular') === 'popular' ? 'checked' : '' }}>
                                            <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#6B4226] peer-checked:text-white peer-checked:border-[#6B4226] text-xs font-bold text-[#2D241E] text-center transition">
                                                🔥 Terpopuler
                                            </div>
                                        </label>

                                        <label class="cursor-pointer">
                                            <input type="radio" name="sort" value="highest_discount" class="peer sr-only" {{ request('sort') === 'highest_discount' ? 'checked' : '' }}>
                                            <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#6B4226] peer-checked:text-white peer-checked:border-[#6B4226] text-xs font-bold text-[#2D241E] text-center transition">
                                                🏷️ Diskon Terbesar
                                            </div>
                                        </label>

                                        <label class="cursor-pointer">
                                            <input type="radio" name="sort" value="cheapest" class="peer sr-only" {{ request('sort') === 'cheapest' ? 'checked' : '' }}>
                                            <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#6B4226] peer-checked:text-white peer-checked:border-[#6B4226] text-xs font-bold text-[#2D241E] text-center transition">
                                                💰 Harga Termurah
                                            </div>
                                        </label>

                                        <label class="cursor-pointer">
                                            <input type="radio" name="sort" value="highest_price" class="peer sr-only" {{ request('sort') === 'highest_price' ? 'checked' : '' }}>
                                            <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#6B4226] peer-checked:text-white peer-checked:border-[#6B4226] text-xs font-bold text-[#2D241E] text-center transition">
                                                💎 Harga Tertinggi
                                            </div>
                                        </label>

                                        <label class="cursor-pointer">
                                            <input type="radio" name="sort" value="newest" class="peer sr-only" {{ request('sort') === 'newest' ? 'checked' : '' }}>
                                            <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#6B4226] peer-checked:text-white peer-checked:border-[#6B4226] text-xs font-bold text-[#2D241E] text-center transition">
                                                ✨ Produk Terbaru
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- 2. Kategori Produk (Multiple Select Toggle Cards) -->
                                <div class="pt-5 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-black uppercase tracking-wider text-[#6B4226] flex items-center gap-1.5">
                                            <span>🛍️ Pilih Kategori (Bisa Lebih Dari 1)</span>
                                        </label>
                                        <span class="text-[11px] text-[#8A7C70] font-medium">Bisa pilih banyak</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-56 overflow-y-auto pr-1">
                                        @foreach($categories as $category)
                                            @php
                                                $isCatChecked = in_array($category->slug, $selectedCategories ?? []);
                                            @endphp
                                            <label class="cursor-pointer">
                                                <input type="checkbox" name="categories[]" value="{{ $category->slug }}" class="peer sr-only" {{ $isCatChecked ? 'checked' : '' }}>
                                                <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#6B4226] peer-checked:text-white peer-checked:border-[#6B4226] text-xs font-bold text-[#2D241E] transition flex items-center justify-center gap-2 text-center shadow-2xs">
                                                    <span class="text-base shrink-0">{{ $category->icon }}</span>
                                                    <span class="truncate">{{ $category->name }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- 3. Tipe Toko & Badge (Multiple Select Toggle Cards) -->
                                <div class="pt-5 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-black uppercase tracking-wider text-[#6B4226] flex items-center gap-1.5">
                                            <span>🏬 Tipe Merchant & Badge (Bisa Lebih Dari 1)</span>
                                        </label>
                                        <span class="text-[11px] text-[#8A7C70] font-medium">Bisa pilih banyak</span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <!-- Mall -->
                                        @php $isMallChecked = in_array('Mall', $selectedBadges ?? []); @endphp
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="badges[]" value="Mall" class="peer sr-only" {{ $isMallChecked ? 'checked' : '' }}>
                                            <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#D9381E] peer-checked:text-white peer-checked:border-[#D9381E] text-xs font-bold text-[#2D241E] transition flex items-center justify-center gap-2 text-center shadow-2xs">
                                                <span>🏬</span>
                                                <span>Mall (100% Ori)</span>
                                            </div>
                                        </label>

                                        <!-- Official -->
                                        @php $isOfficialChecked = in_array('Official', $selectedBadges ?? []); @endphp
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="badges[]" value="Official" class="peer sr-only" {{ $isOfficialChecked ? 'checked' : '' }}>
                                            <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#1E56A0] peer-checked:text-white peer-checked:border-[#1E56A0] text-xs font-bold text-[#2D241E] transition flex items-center justify-center gap-2 text-center shadow-2xs">
                                                <span>🛡️</span>
                                                <span>Official Store</span>
                                            </div>
                                        </label>

                                        <!-- Star+ -->
                                        @php $isStarChecked = in_array('Star+', $selectedBadges ?? []); @endphp
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="badges[]" value="Star+" class="peer sr-only" {{ $isStarChecked ? 'checked' : '' }}>
                                            <div class="p-3 rounded-2xl border border-[#EAE1D7] bg-[#FAF8F5] peer-checked:bg-[#D4820A] peer-checked:text-white peer-checked:border-[#D4820A] text-xs font-bold text-[#2D241E] transition flex items-center justify-center gap-2 text-center shadow-2xs">
                                                <span>⭐</span>
                                                <span>Star+ Merchant</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- 4. Rentang Harga (Rp) -->
                                <div class="pt-5 space-y-3">
                                    <label class="text-xs font-black uppercase tracking-wider text-[#6B4226] flex items-center gap-1.5">
                                        <span>💵 Rentang Harga (Rp)</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <span class="text-[10px] text-[#8A7C70] block mb-1">Harga Minimum</span>
                                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Contoh: 10000" class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs focus:ring-2 focus:ring-[#6B4226] focus:outline-none">
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-[#8A7C70] block mb-1">Harga Maksimum</span>
                                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Contoh: 500000" class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs focus:ring-2 focus:ring-[#6B4226] focus:outline-none">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Modal Footer -->
                            <div class="p-5 sm:p-6 bg-[#FAF7F2] border-t border-[#EAE1D7] flex items-center justify-between gap-3 shrink-0">
                                <a href="{{ route('home') }}#katalog" 
                                   class="px-5 py-2.5 rounded-2xl border border-[#EAE1D7] bg-white hover:bg-red-50 text-[#8A7C70] hover:text-red-600 text-xs font-bold transition">
                                    Reset Semua
                                </a>
                                <button type="submit" 
                                        class="px-7 py-2.5 rounded-2xl bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold transition shadow-xs cursor-pointer">
                                    Terapkan Filter
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

            <!-- Main Product Grid (Ultra-Premium Card Showcase) -->
            @if($products->isEmpty())
                <div class="py-16 text-center space-y-3 bg-[#FAF8F5] rounded-3xl border border-[#EAE1D7] p-8">
                    <span class="text-5xl">🛍️</span>
                    <h3 class="font-extrabold text-base text-[#2D241E]">
                        {{ request('q') || request('category') || request('badge') ? 'Produk Tidak Ditemukan' : 'Etalase Toko Masih Kosong' }}
                    </h3>
                    <p class="text-xs text-[#7A6C60] max-w-sm mx-auto leading-relaxed">
                        {{ request('q') || request('category') || request('badge') ? 'Coba cari dengan kata kunci lain atau pilih salah satu kategori di atas.' : 'Belum ada produk yang dijual saat ini. Pengguna dapat mendaftar menjadi penjual resmi melalui Seller Center.' }}
                    </p>
                    @if(request('q') || request('category') || request('badge'))
                        <a href="{{ route('home') }}#katalog" class="inline-block px-5 py-2.5 bg-[#6B4226] text-white text-xs font-bold rounded-xl mt-2 hover:bg-[#54321B] transition shadow-xs">
                            Tampilkan Semua Produk
                        </a>
                    @else
                        <a href="{{ route('seller.register') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#6B4226] text-white text-xs font-bold rounded-xl mt-2 hover:bg-[#54321B] transition shadow-xs">
                            <span>🏪 Buka Toko Gratis</span>
                        </a>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3.5 sm:gap-4">
                    @foreach($products as $product)
                        <a href="{{ route('product.detail', $product->slug) }}" 
                           class="bg-white rounded-3xl border border-[#EAE1D7] p-3 sm:p-3.5 shadow-2xs hover:shadow-xl hover:border-[#6B4226]/40 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group relative overflow-hidden block">
                            
                            <div>
                                <!-- Visual Stage Container -->
                                <div class="relative h-36 sm:h-40 rounded-2xl bg-gradient-to-b from-[#FAF8F5] to-[#FAF4ED] border border-[#F2EAE0] flex items-center justify-center p-3 overflow-hidden mb-3 group-hover:scale-102 transition duration-300">
                                    
                                    <!-- Discount Badge (Top Left) -->
                                    @if($product->discount_price)
                                        <div class="absolute top-2 left-2 z-10 flex items-center gap-1">
                                            <span class="bg-[#D9381E] text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-2xs">
                                                -{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Store Badge (Top Right) -->
                                    <div class="absolute top-2 right-2 z-10">
                                        @if(strtolower($product->badge) === 'mall')
                                            <span class="bg-[#D9381E] text-white text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">
                                                Mall
                                            </span>
                                        @elseif(strtolower($product->badge) === 'official')
                                            <span class="bg-[#1E56A0] text-white text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">
                                                Official
                                            </span>
                                        @else
                                            <span class="bg-[#D4820A] text-white text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">
                                                Star+
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Product Icon / Image -->
                                    @if($product->product_image_url)
                                        <div class="w-full h-full p-2 flex items-center justify-center">
                                            <img src="{{ $product->product_image_url }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain rounded-xl group-hover:scale-105 transition duration-300">
                                        </div>
                                    @else
                                        <div class="text-4xl sm:text-5xl filter drop-shadow-sm group-hover:scale-110 transition duration-300">
                                            {{ $product->category->icon ?? '🛍️' }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Info -->
                                <div class="space-y-1.5 mb-2.5">
                                    <!-- Product Title -->
                                    <h3 class="font-bold text-xs text-[#2D241E] line-clamp-2 leading-snug group-hover:text-[#6B4226] transition min-h-[32px]" title="{{ $product->name }}">
                                        {{ $product->name }}
                                    </h3>

                                    <!-- Price Block (Crossed-out Price on Top, Main Price Below) -->
                                    <div class="pt-0.5">
                                        <div class="min-h-[14px] flex items-center">
                                            @if($product->hasDiscount())
                                                <span class="text-[10px] text-[#9E9084] line-through leading-none">
                                                    {{ $product->formatted_price }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-baseline gap-1.5 flex-wrap mt-0.5">
                                            <span class="font-black text-sm sm:text-base text-[#6B4226]">
                                                {{ $product->formatted_effective_price }}
                                            </span>
                                        </div>

                                        <!-- Free Shipping Tag -->
                                        <div class="inline-flex items-center gap-1 text-[9px] font-extrabold text-[#2E7D32] bg-[#E8F5E9] px-1.5 py-0.5 rounded mt-1">
                                            <span>🚚 Bebas Ongkir</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rating & Social Proof -->
                            <div class="flex items-center justify-between text-[10px] text-[#7A6C60] pt-2 border-t border-[#F2EAE0] mt-auto">
                                <div class="flex items-center gap-1">
                                    <span class="text-[#E67E22] font-black text-xs">★</span>
                                    <span class="font-bold text-[#2D241E]">{{ number_format($product->rating, 1) }}</span>
                                </div>
                                <span class="font-medium text-[#8A7C70]">
                                    {{ $product->sold_count > 1000 ? round($product->sold_count/1000, 1).'k' : $product->sold_count }} terjual
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </section>

    <!-- 6. JAMINAN BELANJA & FAQ SECTION -->
    <section id="faq" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-10 shadow-2xs grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#EAE1D7] text-[#6B4226] flex items-center justify-center text-2xl shrink-0">
                    🛡️
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#2D241E]">Jaminan 100% Original</h3>
                    <p class="text-xs text-[#7A6C60] mt-1 leading-relaxed">Produk di NusantaraMart terjamin kualitas dan keasliannya langsung dari penjual terverifikasi.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#EAE1D7] text-[#6B4226] flex items-center justify-center text-2xl shrink-0">
                    🚚
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#2D241E]">Bebas Ongkir Se-Indonesia</h3>
                    <p class="text-xs text-[#7A6C60] mt-1 leading-relaxed">Nikmati pengiriman aman ke seluruh penjuru negeri dengan asuransi pengiriman terpercaya.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#FAF7F2] border border-[#EAE1D7] text-[#6B4226] flex items-center justify-center text-2xl shrink-0">
                    💳
                </div>
                <div>
                    <h3 class="font-bold text-sm text-[#2D241E]">Pembayaran Mudah & Aman</h3>
                    <p class="text-xs text-[#7A6C60] mt-1 leading-relaxed">Dukungan QRIS instan, Virtual Account BCA & Mandiri, serta Bayar di Tempat (COD).</p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
