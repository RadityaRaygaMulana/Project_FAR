@extends('layouts.app')

@section('title', ($searchQuery ? 'Jual ' . $searchQuery . ' Terlengkap & Harga Terbaik — ' : 'Katalog Hasil Pencarian — ') . 'NusantaraMart')

@section('content')
<div class="min-h-screen bg-[#FAF8F5] pb-16">

    <!-- 1. BREADCRUMBS & SEARCH BANNER -->
    <div class="bg-white border-b border-[#EAE1D7] py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-[#8A7C70]">
                <!-- Back button & Breadcrumbs -->
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('home') }}" 
                       class="w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                       title="Kembali ke Beranda">
                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition flex items-center gap-1 font-semibold">
                            <span>🏠</span> <span>Beranda</span>
                        </a>
                        <span>/</span>
                        <a href="{{ route('search') }}" class="hover:text-[#6B4226] transition font-semibold">
                            Pencarian
                        </a>
                        <span>/</span>
                        <span class="text-[#2D241E] font-black">
                            {{ $searchQuery ? '"' . $searchQuery . '"' : 'Semua Hasil' }}
                        </span>
                    </div>
                </div>

                <!-- Back button -->
                <a href="{{ route('search') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] text-xs font-bold rounded-xl border border-[#EAE1D7] transition w-fit">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Ubah Kata Kunci</span>
                </a>
            </div>

            <!-- Simple & Compact Title Summary -->
            <div class="mt-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                <div class="flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-[#8A7C70]">Hasil pencarian untuk:</span>
                    <span class="font-bold text-[#6B4226] text-sm">"{{ $searchQuery ?: 'Semua Produk' }}"</span>
                    <span class="text-[#8A7C70] font-medium text-xs">({{ $products->count() }} produk ditemukan)</span>
                </div>

                <!-- Active Filter Tags -->
                @if($selectedLocation || $selectedBadge || $selectedPayment || $selectedShipping || $selectedPromo || $selectedRating || $minPrice || $maxPrice)
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[10px] font-bold text-[#8A7C70]">Filter:</span>
                        @if($selectedLocation)
                            <span class="px-2 py-0.5 bg-[#FAF4ED] text-[#6B4226] text-[10px] font-bold rounded-lg border border-[#E8DED3]">
                                📍 {{ $selectedLocation }}
                            </span>
                        @endif
                        @if($selectedBadge)
                            <span class="px-2 py-0.5 bg-red-50 text-red-700 text-[10px] font-bold rounded-lg border border-red-200">
                                🛡️ {{ $selectedBadge }}
                            </span>
                        @endif
                        @if($selectedPromo)
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-lg border border-emerald-200">
                                🎁 {{ $selectedPromo === 'free_shipping' ? 'Bebas Ongkir' : 'Diskon' }}
                            </span>
                        @endif
                        @if($selectedRating)
                            <span class="px-2 py-0.5 bg-amber-50 text-amber-800 text-[10px] font-bold rounded-lg border border-amber-200">
                                ★ {{ $selectedRating }}+
                            </span>
                        @endif
                        <a href="{{ route('search.results', ['q' => $searchQuery]) }}" class="text-[10px] text-[#6B4226] font-bold hover:underline">
                            Reset
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- 2. MAIN RESULTS CANVAS -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- NusantaraMart Official Store Card Banner (If matching brand) -->
        @if($matchingStore)
            <div class="bg-gradient-to-r from-white via-[#FAF7F2] to-[#FAF4ED] rounded-2xl border border-[#EAE1D7] p-4 sm:p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    @if(!empty($matchingStore['logo_url']))
                        <img src="{{ $matchingStore['logo_url'] }}" 
                             alt="{{ $matchingStore['name'] }}" 
                             class="w-13 h-13 rounded-2xl object-cover border border-[#EAE1D7] shadow-xs shrink-0 bg-white" />
                    @else
                        <div class="w-13 h-13 rounded-2xl bg-red-600 text-white flex items-center justify-center text-2xl font-black shadow-sm shrink-0">
                            🏬
                        </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="bg-red-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase">{{ $matchingStore['badge'] ?? 'MALL' }}</span>
                            <h3 class="font-extrabold text-sm sm:text-base text-[#2D241E]">{{ $matchingStore['name'] }}</h3>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] text-[#8A7C70] mt-1 flex-wrap">
                            <span class="text-amber-600 font-bold">★ {{ $matchingStore['rating'] }} Rating Toko</span>
                            <span>•</span>
                            <span>💬 Respon Chat {{ $matchingStore['chat_response'] }}</span>
                            <span>•</span>
                            <span>📦 {{ $matchingStore['products_count'] }} Produk</span>
                            <span>•</span>
                            <span>👥 {{ $matchingStore['followers'] }}</span>
                        </div>
                    </div>
                </div>

                <a href="{{ $matchingStore['url'] ?? route('search.results', ['q' => $matchingStore['brand']]) }}" 
                   class="px-5 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0 text-center">
                    Kunjungi Toko Resmi ›
                </a>
            </div>
        @endif

        <!-- Filter + Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start"
             x-data="{ 
                 showMoreLocation: false, 
                 showMoreShipping: false, 
                 showMorePromo: false, 
                 showMoreRating: false 
             }">
            
            <!-- LEFT SIDEBAR FILTER (NUSANTARAMART) -->
            <div class="lg:col-span-3 bg-white rounded-2xl border border-[#EAE1D7] p-5 shadow-2xs space-y-5 text-[#2D241E]">
                
                <!-- Filter Header -->
                <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                    <h3 class="font-extrabold text-sm text-[#2D241E] flex items-center gap-2 uppercase tracking-wider">
                        <svg class="w-4 h-4 text-[#6B4226]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>Filter</span>
                    </h3>
                    @if($selectedLocation || $selectedBadge || $selectedPayment || $selectedShipping || $selectedPromo || $selectedRating || $minPrice || $maxPrice)
                        <a href="{{ route('search.results', ['q' => $searchQuery]) }}" class="text-[11px] text-[#6B4226] font-bold hover:underline">
                            HAPUS SEMUA
                        </a>
                    @endif
                </div>

                <!-- Filter Form -->
                <form action="{{ route('search.results') }}" method="GET" class="space-y-5 text-xs" id="filterForm">
                    <input type="hidden" name="q" value="{{ $searchQuery }}">
                    <input type="hidden" name="sort" value="{{ $selectedSort }}">

                    <!-- 1. LOKASI -->
                    <div class="space-y-2.5">
                        <h4 class="font-bold text-xs text-[#2D241E]">Lokasi</h4>
                        <div class="space-y-1.5 text-xs text-[#5A4B40]">
                            @php
                                $mainLocations = ['Jabodetabek', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'Kota Bandung'];
                                $extraLocations = ['DKI Jakarta', 'DI Yogyakarta', 'Bali', 'Sumatera Utara', 'Sulawesi Selatan'];
                            @endphp

                            @foreach($mainLocations as $loc)
                                <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                    <input type="radio" name="location" value="{{ $loc }}" 
                                           {{ $selectedLocation === $loc ? 'checked' : '' }} 
                                           onchange="this.form.submit()" 
                                           class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                    <span class="{{ $selectedLocation === $loc ? 'font-bold text-[#6B4226]' : '' }}">{{ $loc }}</span>
                                </label>
                            @endforeach

                            <!-- Extra locations (Toggled by Lainnya) -->
                            <div x-show="showMoreLocation" x-cloak class="space-y-1.5 pt-0.5">
                                @foreach($extraLocations as $loc)
                                    <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                        <input type="radio" name="location" value="{{ $loc }}" 
                                               {{ $selectedLocation === $loc ? 'checked' : '' }} 
                                               onchange="this.form.submit()" 
                                               class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                        <span class="{{ $selectedLocation === $loc ? 'font-bold text-[#6B4226]' : '' }}">{{ $loc }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <button type="button" 
                                    @click="showMoreLocation = !showMoreLocation" 
                                    class="text-[11px] text-[#8A7C70] hover:text-[#6B4226] font-semibold flex items-center gap-1 pt-1 cursor-pointer">
                                <span x-text="showMoreLocation ? 'Lebih Sedikit ⌃' : 'Lainnya ⌵'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. TIPE PENJUAL -->
                    <div class="space-y-2.5 pt-4 border-t border-[#F2EAE0]">
                        <h4 class="font-bold text-xs text-[#2D241E]">Tipe Penjual</h4>
                        <div class="space-y-1.5 text-xs text-[#5A4B40]">
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="badge" value="Mall" 
                                       {{ $selectedBadge === 'Mall' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-red-600 text-white text-[9px] font-black px-1.5 py-0.2 rounded">Mall</span>
                                    <span class="{{ $selectedBadge === 'Mall' ? 'font-bold text-[#6B4226]' : '' }}">Nusantara Mall</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="badge" value="Star+" 
                                       {{ $selectedBadge === 'Star+' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-amber-600 text-white text-[9px] font-bold px-1.5 py-0.2 rounded">Star+</span>
                                    <span class="{{ $selectedBadge === 'Star+' ? 'font-bold text-[#6B4226]' : '' }}">Star+ Seller</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="badge" value="Star" 
                                       {{ $selectedBadge === 'Star' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-amber-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded">Star</span>
                                    <span class="{{ $selectedBadge === 'Star' ? 'font-bold text-[#6B4226]' : '' }}">Star Seller</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="badge" value="Official" 
                                       {{ $selectedBadge === 'Official' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1.5">
                                    <span class="bg-[#6B4226] text-white text-[9px] font-bold px-1.5 py-0.2 rounded">Official</span>
                                    <span class="{{ $selectedBadge === 'Official' ? 'font-bold text-[#6B4226]' : '' }}">Official Store</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 3. METODE PEMBAYARAN -->
                    <div class="space-y-2.5 pt-4 border-t border-[#F2EAE0]">
                        <h4 class="font-bold text-xs text-[#2D241E]">Metode Pembayaran</h4>
                        <div class="space-y-1.5 text-xs text-[#5A4B40]">
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="payment" value="cod" 
                                       {{ $selectedPayment === 'cod' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <span class="{{ $selectedPayment === 'cod' ? 'font-bold text-[#6B4226]' : '' }}">COD (Bayar di Tempat)</span>
                            </label>
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="payment" value="spaylater" 
                                       {{ $selectedPayment === 'spaylater' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <span class="{{ $selectedPayment === 'spaylater' ? 'font-bold text-[#6B4226]' : '' }}">Nusantara PayLater</span>
                            </label>
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="payment" value="transfer" 
                                       {{ $selectedPayment === 'transfer' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <span class="{{ $selectedPayment === 'transfer' ? 'font-bold text-[#6B4226]' : '' }}">Transfer Bank & VA</span>
                            </label>
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="payment" value="cicilan" 
                                       {{ $selectedPayment === 'cicilan' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <span class="{{ $selectedPayment === 'cicilan' ? 'font-bold text-[#6B4226]' : '' }}">Cicilan 0%</span>
                            </label>
                        </div>
                    </div>

                    <!-- 4. OPSI PENGIRIMAN -->
                    <div class="space-y-2.5 pt-4 border-t border-[#F2EAE0]">
                        <h4 class="font-bold text-xs text-[#2D241E]">Opsi Pengiriman</h4>
                        <div class="space-y-1.5 text-xs text-[#5A4B40]">
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="shipping" value="reguler" 
                                       {{ $selectedShipping === 'reguler' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <span class="{{ $selectedShipping === 'reguler' ? 'font-bold text-[#6B4226]' : '' }}">Reguler</span>
                            </label>
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="shipping" value="instant" 
                                       {{ $selectedShipping === 'instant' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <span class="{{ $selectedShipping === 'instant' ? 'font-bold text-[#6B4226]' : '' }}">Instant (2 Jam)</span>
                            </label>
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="shipping" value="hemat" 
                                       {{ $selectedShipping === 'hemat' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <span class="{{ $selectedShipping === 'hemat' ? 'font-bold text-[#6B4226]' : '' }}">Hemat</span>
                            </label>
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="shipping" value="kargo" 
                                       {{ $selectedShipping === 'kargo' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <span class="{{ $selectedShipping === 'kargo' ? 'font-bold text-[#6B4226]' : '' }}">Kargo</span>
                            </label>

                            <!-- Extra shipping (Toggled by Lainnya) -->
                            <div x-show="showMoreShipping" x-cloak class="space-y-1.5 pt-0.5">
                                <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                    <input type="radio" name="shipping" value="sameday" 
                                           {{ $selectedShipping === 'sameday' ? 'checked' : '' }} 
                                           onchange="this.form.submit()" 
                                           class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                    <span>Sameday</span>
                                </label>
                                <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                    <input type="radio" name="shipping" value="nextday" 
                                           {{ $selectedShipping === 'nextday' ? 'checked' : '' }} 
                                           onchange="this.form.submit()" 
                                           class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                    <span>Next Day</span>
                                </label>
                                <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                    <input type="radio" name="shipping" value="pickup" 
                                           {{ $selectedShipping === 'pickup' ? 'checked' : '' }} 
                                           onchange="this.form.submit()" 
                                           class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                    <span>Ambil di Tempat</span>
                                </label>
                            </div>

                            <button type="button" 
                                    @click="showMoreShipping = !showMoreShipping" 
                                    class="text-[11px] text-[#8A7C70] hover:text-[#6B4226] font-semibold flex items-center gap-1 pt-1 cursor-pointer">
                                <span x-text="showMoreShipping ? 'Lebih Sedikit ⌃' : 'Lainnya ⌵'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 5. PROGRAM PROMO -->
                    <div class="space-y-2.5 pt-4 border-t border-[#F2EAE0]">
                        <h4 class="font-bold text-xs text-[#2D241E]">Program Promo</h4>
                        <div class="space-y-1.5 text-xs text-[#5A4B40]">
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="promo" value="free_shipping" 
                                       {{ $selectedPromo === 'free_shipping' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1">
                                    <span>🚚</span>
                                    <span class="{{ $selectedPromo === 'free_shipping' ? 'font-bold text-[#6B4226]' : '' }}">Bebas Ongkir Extra</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="promo" value="cashback" 
                                       {{ $selectedPromo === 'cashback' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1">
                                    <span>💰</span>
                                    <span class="{{ $selectedPromo === 'cashback' ? 'font-bold text-[#6B4226]' : '' }}">Cashback XTRA</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="promo" value="discount" 
                                       {{ $selectedPromo === 'discount' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1">
                                    <span>⚡</span>
                                    <span class="{{ $selectedPromo === 'discount' ? 'font-bold text-[#6B4226]' : '' }}">Diskon & Flash Sale</span>
                                </div>
                            </label>

                            <!-- Extra promo (Toggled by Lainnya) -->
                            <div x-show="showMorePromo" x-cloak class="space-y-1.5 pt-0.5">
                                <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                    <input type="radio" name="promo" value="voucher" 
                                           {{ $selectedPromo === 'voucher' ? 'checked' : '' }} 
                                           onchange="this.form.submit()" 
                                           class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                    <span>Voucher Toko</span>
                                </label>
                                <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                    <input type="radio" name="promo" value="bundling" 
                                           {{ $selectedPromo === 'bundling' ? 'checked' : '' }} 
                                           onchange="this.form.submit()" 
                                           class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                    <span>Bundling Hemat</span>
                                </label>
                            </div>

                            <button type="button" 
                                    @click="showMorePromo = !showMorePromo" 
                                    class="text-[11px] text-[#8A7C70] hover:text-[#6B4226] font-semibold flex items-center gap-1 pt-1 cursor-pointer">
                                <span x-text="showMorePromo ? 'Lebih Sedikit ⌃' : 'Lainnya ⌵'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 6. BATAS HARGA -->
                    <div class="space-y-2.5 pt-4 border-t border-[#F2EAE0]">
                        <h4 class="font-bold text-xs text-[#2D241E]">Batas Harga</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="Rp MIN"
                                       class="w-full px-2.5 py-2 text-xs bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 font-medium">
                                <span class="text-[#8A7C70] text-xs font-bold">-</span>
                                <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Rp MAKS"
                                       class="w-full px-2.5 py-2 text-xs bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 font-medium">
                            </div>
                            <button type="submit" 
                                    class="w-full py-2 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-black rounded-xl transition shadow-xs cursor-pointer uppercase tracking-wider">
                                Terapkan
                            </button>
                        </div>
                    </div>

                    <!-- 7. PENILAIAN -->
                    <div class="space-y-2.5 pt-4 border-t border-[#F2EAE0]">
                        <h4 class="font-bold text-xs text-[#2D241E]">Penilaian</h4>
                        <div class="space-y-2 text-xs text-[#5A4B40]">
                            <!-- 5 Bintang -->
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="rating" value="5" 
                                       {{ $selectedRating === '5' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1 text-amber-500 text-sm leading-none">
                                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                </div>
                            </label>

                            <!-- 4 Bintang ke atas -->
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="rating" value="4" 
                                       {{ $selectedRating === '4' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex items-center gap-0.5 text-amber-500 text-sm leading-none">
                                        <span>★</span><span>★</span><span>★</span><span>★</span><span class="text-stone-300">★</span>
                                    </div>
                                    <span class="text-[11px] text-[#8A7C70] font-medium">ke atas</span>
                                </div>
                            </label>

                            <!-- 3 Bintang ke atas -->
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="rating" value="3" 
                                       {{ $selectedRating === '3' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex items-center gap-0.5 text-amber-500 text-sm leading-none">
                                        <span>★</span><span>★</span><span>★</span><span class="text-stone-300">★</span><span class="text-stone-300">★</span>
                                    </div>
                                    <span class="text-[11px] text-[#8A7C70] font-medium">ke atas</span>
                                </div>
                            </label>

                            <!-- 2 Bintang ke atas -->
                            <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                <input type="radio" name="rating" value="2" 
                                       {{ $selectedRating === '2' ? 'checked' : '' }} 
                                       onchange="this.form.submit()" 
                                       class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex items-center gap-0.5 text-amber-500 text-sm leading-none">
                                        <span>★</span><span>★</span><span class="text-stone-300">★</span><span class="text-stone-300">★</span><span class="text-stone-300">★</span>
                                    </div>
                                    <span class="text-[11px] text-[#8A7C70] font-medium">ke atas</span>
                                </div>
                            </label>

                            <!-- Extra 1 Bintang (Toggled by Lainnya) -->
                            <div x-show="showMoreRating" x-cloak class="space-y-1.5 pt-0.5">
                                <label class="flex items-center gap-2.5 py-1 px-1 rounded-lg hover:bg-[#FAF7F2] cursor-pointer transition">
                                    <input type="radio" name="rating" value="1" 
                                           {{ $selectedRating === '1' ? 'checked' : '' }} 
                                           onchange="this.form.submit()" 
                                           class="w-4 h-4 rounded text-[#6B4226] focus:ring-[#6B4226]/30 border-stone-300">
                                    <div class="flex items-center gap-1.5">
                                        <div class="flex items-center gap-0.5 text-amber-500 text-sm leading-none">
                                            <span>★</span><span class="text-stone-300">★</span><span class="text-stone-300">★</span><span class="text-stone-300">★</span><span class="text-stone-300">★</span>
                                        </div>
                                        <span class="text-[11px] text-[#8A7C70] font-medium">ke atas</span>
                                    </div>
                                </label>
                            </div>

                            <button type="button" 
                                    @click="showMoreRating = !showMoreRating" 
                                    class="text-[11px] text-[#8A7C70] hover:text-[#6B4226] font-semibold flex items-center gap-1 pt-1 cursor-pointer">
                                <span x-text="showMoreRating ? 'Lebih Sedikit ⌃' : 'Lainnya ⌵'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 8. BOTTOM HAPUS SEMUA BUTTON -->
                    <div class="pt-4 border-t border-[#F2EAE0]">
                        <a href="{{ route('search.results', ['q' => $searchQuery]) }}" 
                           class="w-full block py-2.5 text-center bg-[#FAF8F5] hover:bg-red-50 text-[#6B4226] hover:text-red-600 text-xs font-black rounded-xl border border-[#EAE1D7] hover:border-red-200 transition shadow-2xs">
                            HAPUS SEMUA
                        </a>
                    </div>

                </form>

            </div>

            <!-- RIGHT MAIN RESULTS (9 Cols) -->
            <div class="lg:col-span-9 space-y-4">
                
                <!-- Sorting Bar Tabs -->
                <div class="bg-white rounded-2xl border border-[#EAE1D7] p-3 sm:p-4 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-[#7A6C60]">Urutkan:</span>
                        <div class="flex flex-wrap items-center gap-1.5 font-bold">
                            <a href="{{ route('search.results', array_merge(request()->query(), ['sort' => 'popular'])) }}" 
                               class="px-3.5 py-1.5 rounded-xl transition {{ $selectedSort === 'popular' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]' }}">
                                Terkait / Populer
                            </a>
                            <a href="{{ route('search.results', array_merge(request()->query(), ['sort' => 'newest'])) }}" 
                               class="px-3.5 py-1.5 rounded-xl transition {{ $selectedSort === 'newest' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]' }}">
                                Terbaru
                            </a>
                            <a href="{{ route('search.results', array_merge(request()->query(), ['sort' => 'highest_discount'])) }}" 
                               class="px-3.5 py-1.5 rounded-xl transition {{ $selectedSort === 'highest_discount' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]' }}">
                                Terlaris / Diskon
                            </a>
                            <a href="{{ route('search.results', array_merge(request()->query(), ['sort' => 'cheapest'])) }}" 
                               class="px-3.5 py-1.5 rounded-xl transition {{ $selectedSort === 'cheapest' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]' }}">
                                Harga: Termurah
                            </a>
                            <a href="{{ route('search.results', array_merge(request()->query(), ['sort' => 'priciest'])) }}" 
                               class="px-3.5 py-1.5 rounded-xl transition {{ $selectedSort === 'priciest' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]' }}">
                                Harga: Tertinggi
                            </a>
                        </div>
                    </div>

                    <div class="text-[#8A7C70] text-[11px] self-end sm:self-auto font-medium">
                        Total <strong>{{ $products->count() }}</strong> produk
                    </div>
                </div>

                <!-- Products Grid -->
                @if($products->isEmpty())
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-12 text-center shadow-2xs space-y-4">
                        <div class="w-20 h-20 rounded-full bg-[#FAF7F2] border border-[#EAE1D7] flex items-center justify-center text-4xl mx-auto">
                            🔍
                        </div>
                        <h3 class="text-lg font-bold text-[#2D241E]">Tidak Ada Produk yang Cocok</h3>
                        <p class="text-xs text-[#7A6C60] max-w-sm mx-auto">
                            Maaf, produk "{{ $searchQuery }}" tidak ditemukan dengan filter yang dipilih. Coba reset filter atau gunakan kata kunci lain.
                        </p>
                        <div class="flex items-center justify-center gap-3 pt-2">
                            <a href="{{ route('search') }}" class="px-5 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition shadow-xs">
                                Cari Kata Kunci Lain
                            </a>
                            <a href="{{ route('search.results') }}" class="px-5 py-2.5 bg-white hover:bg-[#FAF4ED] text-[#6B4226] text-xs font-bold rounded-xl border border-[#EAE1D7] transition shadow-xs">
                                Lihat Semua Produk
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-4">
                        @foreach($products as $product)
                                <a href="{{ route('product.detail', $product->slug) }}" class="block group/link">
                                    <!-- Image Stage -->
                                    <div class="relative h-36 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0] flex items-center justify-center p-3 overflow-hidden mb-2.5 group-hover:scale-102 transition">
                                        @if($product->discount_price)
                                            <span class="absolute top-2 left-2 bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-2xs">
                                                -{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%
                                            </span>
                                        @endif
                                        <span class="absolute top-2 right-2 {{ $product->badge === 'Mall' ? 'bg-red-600' : ($product->badge === 'Star+' ? 'bg-amber-600' : 'bg-[#6B4226]') }} text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-2xs">
                                            {{ $product->badge }}
                                        </span>
                                        @if($product->product_image_url)
                                            <div class="w-full h-full p-2 flex items-center justify-center">
                                                <img src="{{ $product->product_image_url }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain rounded-xl group-hover:scale-105 transition">
                                            </div>
                                        @else
                                            <div class="text-4xl filter drop-shadow-2xs group-hover:scale-105 transition">
                                                {{ $product->category->icon ?? '🛍️' }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="space-y-1 mb-2">
                                        <h3 class="font-bold text-xs text-[#2D241E] line-clamp-2 leading-snug group-hover/link:text-[#6B4226] transition min-h-[32px]" title="{{ $product->name }}">
                                            {{ $product->name }}
                                        </h3>
                                        
                                        <!-- Price Breakdown -->
                                        <div class="pt-0.5">
                                            <div class="min-h-[14px] flex items-center">
                                                @if($product->hasDiscount())
                                                    <p class="text-[10px] text-[#9E9084] line-through leading-none">{{ $product->formatted_price }}</p>
                                                @endif
                                            </div>
                                            <p class="font-black text-sm text-[#6B4226] mt-0.5">{{ $product->formatted_effective_price }}</p>
                                        </div>

                                        <!-- Free Shipping & Promotion Pill -->
                                        <div class="pt-1 flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 text-emerald-700 text-[9px] font-bold rounded border border-emerald-200">
                                                <span>🚚</span> <span>Bebas Ongkir</span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Rating, Sold, and City Origin Stats -->
                                    <div class="flex items-center justify-between text-[10px] text-[#7A6C60] pt-1.5 pb-2 border-t border-[#F2EAE0]">
                                        <div class="flex items-center gap-1">
                                            <span class="text-[#B87A45] font-bold">★ {{ number_format($product->rating, 1) }}</span>
                                            <span class="text-stone-300">•</span>
                                            <span>{{ $product->sold_count > 1000 ? round($product->sold_count/1000, 1).'k' : $product->sold_count }} terjual</span>
                                        </div>
                                        <span class="text-[9px] text-[#8A7C70] truncate max-w-[80px]">{{ $product->origin_city }}</span>
                                    </div>
                                </a>

                                <button @click="addToCart({
                                            id: {{ $product->id }},
                                            name: '{{ addslashes($product->name) }}',
                                            price: {{ $product->effective_price }},
                                            brand: '{{ addslashes($product->brand) }}',
                                            badge: '{{ $product->badge }}',
                                            icon: '{{ $product->category->icon ?? '🛍️' }}'
                                        })"
                                        class="w-full py-2 px-3 bg-[#FAF8F5] hover:bg-[#6B4226] hover:text-white text-[#5A4B40] font-bold rounded-xl text-[11px] border border-[#EAE1D7] transition transform active:scale-95 flex items-center justify-center gap-1 cursor-pointer">
                                    <span>+ Keranjang</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

        </div>

        <!-- 3. REKOMENDASI PRODUK LAINNYA -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-2xs space-y-4 mt-8">
            <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                <div>
                    <h3 class="font-bold text-base sm:text-lg text-[#2D241E] flex items-center gap-2">
                        <span>Rekomendasi Produk Pilihan Untukmu</span>
                        <span>✨</span>
                    </h3>
                    <p class="text-xs text-[#7A6C60]">Inspirasi belanja dari brand-brand terpercaya dengan rating tertinggi</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">
                @foreach($recommendedProducts as $rec)
                    <div class="bg-[#FAF8F5] rounded-2xl border border-[#EAE1D7] p-3 shadow-2xs flex flex-col justify-between group">
                        <a href="{{ route('product.detail', $rec->slug) }}" class="block group/rec">
                            <div class="h-28 rounded-xl bg-white border border-[#F2EAE0] flex items-center justify-center text-3xl mb-2 group-hover/rec:scale-105 transition">
                                {{ $rec->category->icon ?? '🛍️' }}
                            </div>
                            <h4 class="font-bold text-xs text-[#2D241E] line-clamp-2 group-hover/rec:text-[#6B4226] transition min-h-[32px]">{{ $rec->name }}</h4>
                            <p class="text-xs font-black text-[#6B4226] mt-0.5">{{ $rec->formatted_effective_price }}</p>
                        </a>
                        <button @click="addToCart({
                                    id: {{ $rec->id }},
                                    name: '{{ addslashes($rec->name) }}',
                                    price: {{ $rec->effective_price }},
                                    brand: '{{ addslashes($rec->brand) }}',
                                    badge: '{{ $rec->badge }}',
                                    icon: '{{ $rec->category->icon ?? '🛍️' }}'
                                })"
                                class="mt-2 w-full py-1.5 bg-white hover:bg-[#6B4226] hover:text-white text-[#5A4B40] text-[10px] font-bold rounded-lg border border-[#EAE1D7] transition cursor-pointer">
                            + Keranjang
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
