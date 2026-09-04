@extends('layouts.app')

@section('title', 'Official Brand & Store — Jaminan 100% Produk Original NusantaraMart')

@section('content')
<div class="min-h-screen bg-[#FAF8F5] pb-16 space-y-8">

    <!-- 1. BREADCRUMBS & TOP BAR -->
    <div class="bg-white border-b border-[#EAE1D7] py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" 
                   class="w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-2xs flex items-center justify-center transition group active:scale-95 cursor-pointer shrink-0"
                   title="Kembali ke Beranda">
                    <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-xs font-medium text-[#8A7C70]">
                        <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition flex items-center gap-1 font-semibold">
                            <span>🏠</span> <span>Beranda</span>
                        </a>
                        <span>/</span>
                        <span class="text-[#6B4226] font-bold">Official Brand & Store</span>
                    </div>
                    <h1 class="text-lg sm:text-xl font-black text-[#2D241E] tracking-tight">
                        Portal Official Brand Resmi 🏬
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- 2. HERO OFFICIAL BRAND BANNER -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#2D241E] via-[#4A2411] to-[#8C271E] text-white p-6 sm:p-8 lg:p-10 shadow-md">
            <!-- Background Decorative Texture & Glow -->
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
            <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-rose-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/4 -bottom-10 w-48 h-48 rounded-full bg-amber-500/20 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left Details -->
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-amber-200 text-xs font-black tracking-wider uppercase">
                        <span>🏬</span>
                        <span>Official Brand & Verified Store</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight">
                        Pusat Belanja Brand & Toko Resmi Terpercaya
                    </h2>

                    <p class="text-xs sm:text-sm text-amber-100/80 leading-relaxed max-w-xl">
                        Koleksi lengkap produk pilihan langsung dari distributor resmi dan mitra toko terverifikasi dengan jaminan keaslian dan standar mutu terbaik.
                    </p>

                    <!-- Quick Highlight Badges -->
                    <div class="flex items-center gap-2 sm:gap-3 flex-wrap pt-1 text-xs">
                        <span class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 text-white font-bold flex items-center gap-1.5">
                            <span>🛡️</span>
                            <span>Jaminan 100% Asli</span>
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 text-white font-bold flex items-center gap-1.5">
                            <span>🚚</span>
                            <span>Pengiriman Cepat Prioritas</span>
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 text-white font-bold flex items-center gap-1.5">
                            <span>✨</span>
                            <span>Garansi Resmi Terjamin</span>
                        </span>
                    </div>
                </div>

                <!-- Right Visual Element -->
                <div class="hidden sm:flex lg:flex flex-col items-center justify-center p-6 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center shrink-0 min-w-[200px] shadow-inner space-y-2">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-400 to-rose-600 text-white flex items-center justify-center text-3xl shadow-md">
                        🏬
                    </div>
                    <div>
                        <span class="text-xl sm:text-2xl font-black text-white block">
                            Official Mall
                        </span>
                        <span class="text-[11px] text-amber-200/90 font-medium block">
                            Mitra Toko Terverifikasi
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. MITRA TOKO RESMI TERVERIFIKASI (OFFICIAL STORES SHOWCASE) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-[#2D241E] flex items-center gap-2">
                        <span>🏬 Mitra Toko Resmi Pilihan</span>
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-md uppercase border border-blue-200">
                            Verified Store ✓
                        </span>
                    </h3>
                    <p class="text-xs text-[#8A7C70]">Belanja langsung ke etalase toko resmi untuk melihat koleksi lengkap</p>
                </div>
            </div>

            @if($officialStores->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($officialStores as $st)
                        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 shadow-xs hover:border-[#6B4226]/40 hover:shadow-md transition-all flex flex-col justify-between space-y-4 group">
                            <!-- Store Header -->
                            <div class="flex items-start gap-3.5">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#6B4226] to-[#452713] text-white flex items-center justify-center text-2xl font-bold shadow-xs shrink-0 relative overflow-hidden">
                                    @if($st->logo_url)
                                        <img src="{{ $st->logo_url }}" alt="{{ $st->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span>🏬</span>
                                    @endif
                                    <span class="absolute -bottom-1 -right-1 w-4.5 h-4.5 bg-blue-500 text-white rounded-full text-[9px] font-black flex items-center justify-center border-2 border-white shadow-2xs" title="Official Store">✓</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-black text-sm text-[#2D241E] group-hover:text-[#6B4226] transition truncate" title="{{ $st->name }}">
                                            {{ $st->name }}
                                        </h4>
                                    </div>
                                    <p class="text-xs text-[#8A7C70] flex items-center gap-1 mt-0.5">
                                        <span>📍 {{ $st->city }}</span>
                                        <span>•</span>
                                        <span class="text-amber-600 font-bold">★ {{ number_format($st->rating, 1) }}</span>
                                    </p>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                        {{ $st->products_count }} Produk Aktif
                                    </span>
                                </div>
                            </div>

                            <!-- Store Action Buttons -->
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[#F2EAE0]">
                                <form action="{{ route('chat.start') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="store_id" value="{{ $st->id }}">
                                    <button type="submit" 
                                            class="w-full py-2 px-3 rounded-xl bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] font-bold text-xs flex items-center justify-center gap-1 transition active:scale-95 cursor-pointer">
                                        <span>💬</span>
                                        <span>Chat</span>
                                    </button>
                                </form>

                                <a href="{{ route('store.show', urlencode($st->name)) }}" 
                                   class="py-2 px-3 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs flex items-center justify-center gap-1 transition active:scale-95 shadow-2xs">
                                    <span>Kunjungi ›</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-3xl border border-[#EAE1D7] p-8 text-center text-[#8A7C70] space-y-2">
                    <span class="text-4xl block">🏬</span>
                    <p class="text-xs font-bold text-[#2D241E]">Belum ada Mitra Toko Resmi yang terdaftar.</p>
                    <p class="text-[11px]">Buka tokomu sekarang dan jadilah mitra terverifikasi pertama di NusantaraMart!</p>
                </div>
            @endif
        </div>

        <!-- 4. KATALOG PRODUK OFFICIAL BRAND -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-xs space-y-6">
            
            <!-- Category Filter Pills & Sort Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#F2EAE0] pb-5">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-[#2D241E]">
                        Katalog Produk Brand Resmi
                    </h3>
                    <p class="text-xs text-[#8A7C70] mt-0.5">Semua produk resmi dengan garansi keaslian 100%</p>
                </div>

                <!-- Sort Selector -->
                <form action="{{ route('official.brand') }}" method="GET" class="flex items-center gap-2 text-xs font-bold text-[#5A4B40]">
                    @if($selectedCategory)
                        <input type="hidden" name="category" value="{{ $selectedCategory }}">
                    @endif
                    <label for="sortSelect" class="shrink-0 text-[#8A7C70]">Urutkan:</label>
                    <select id="sortSelect" 
                            name="sort" 
                            onchange="this.form.submit()" 
                            class="px-3 py-2 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] text-xs font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 cursor-pointer">
                        <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>🔥 Paling Populer</option>
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>✨ Terbaru</option>
                        <option value="cheapest" {{ $sort === 'cheapest' ? 'selected' : '' }}>💰 Harga Terendah</option>
                        <option value="priciest" {{ $sort === 'priciest' ? 'selected' : '' }}>💎 Harga Tertinggi</option>
                    </select>
                </form>
            </div>

            <!-- Category Pills Explorer -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 text-xs font-bold">
                <a href="{{ route('official.brand', ['sort' => $sort]) }}" 
                   class="px-4 py-2 rounded-xl transition shrink-0 {{ !$selectedCategory ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]' }}">
                    Semua Kategori
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('official.brand', ['category' => $cat->slug, 'sort' => $sort]) }}" 
                       class="px-4 py-2 rounded-xl transition shrink-0 flex items-center gap-1.5 {{ $selectedCategory === $cat->slug ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]' }}">
                        <span>{{ $cat->icon }}</span>
                        <span>{{ $cat->name }}</span>
                    </a>
                @endforeach
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($products as $item)
                        <div class="bg-white rounded-2xl border border-[#EAE1D7] overflow-hidden hover:border-[#6B4226]/50 hover:shadow-md transition-all flex flex-col justify-between group">
                            <!-- Image / Thumbnail -->
                            <div class="relative aspect-square bg-[#FAF8F5] overflow-hidden flex items-center justify-center">
                                @if($item->product_image_url)
                                    <img src="{{ $item->product_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <span class="text-4xl sm:text-5xl group-hover:scale-110 transition-transform">
                                        {{ $item->category->icon ?? '🛍️' }}
                                    </span>
                                @endif

                                <!-- Official Badge Tag -->
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-[#D9381E] text-white text-[9px] font-black uppercase tracking-wider shadow-xs">
                                    Official
                                </span>
                            </div>

                            <!-- Info Body -->
                            <div class="p-3.5 space-y-2 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] text-[#8A7C70] font-medium block truncate">
                                        {{ $item->store->name ?? $item->brand ?? 'NusantaraMart Official' }}
                                    </span>
                                    <a href="{{ route('product.detail', $item->slug) }}" class="font-bold text-xs sm:text-sm text-[#2D241E] hover:text-[#6B4226] line-clamp-2 transition mt-0.5" title="{{ $item->name }}">
                                        {{ $item->name }}
                                    </a>
                                </div>

                                <div class="space-y-1 pt-1">
                                    <p class="text-sm sm:text-base font-black text-[#6B4226]">
                                        {{ $item->formatted_effective_price }}
                                    </p>
                                    @if($item->discount_price)
                                        <div class="flex items-center gap-1.5 text-[10px]">
                                            <span class="line-through text-[#8A7C70]">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                            <span class="font-bold text-rose-600 bg-rose-50 px-1 rounded">-{{ $item->discount_percentage }}%</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between text-[10px] text-[#8A7C70] pt-2 border-t border-[#F2EAE0]">
                                    <span class="text-amber-600 font-bold">★ {{ number_format($item->rating ?? 5.0, 1) }}</span>
                                    <span>{{ $item->sold_count ?? 0 }} Terjual</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                @if($products->hasPages())
                    <div class="pt-4">
                        {{ $products->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="p-12 text-center rounded-3xl bg-[#FAF8F5] border border-dashed border-[#EAE1D7] space-y-3">
                    <span class="text-5xl block">🏬</span>
                    <h4 class="text-sm font-black text-[#2D241E]">
                        Katalog Produk Sedang Dipersiapkan
                    </h4>
                    <p class="text-xs text-[#8A7C70] max-w-md mx-auto">
                        @if($selectedCategory)
                            Belum ada produk resmi untuk kategori terpilih. Silakan cek kategori lain atau klik "Semua Kategori".
                        @else
                            Mitra Toko Resmi sedang memperbarui inventaris produk mereka. Kunjungi toko resmi di bagian atas untuk informasi produk terbaru!
                        @endif
                    </p>
                    <a href="{{ route('official.brand') }}" class="inline-block px-5 py-2.5 rounded-xl bg-[#6B4226] text-white text-xs font-bold shadow-xs hover:bg-[#54321B] transition">
                        Lihat Semua Kategori 🔄
                    </a>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
