@extends('layouts.app')

@section('title', 'Promo Terbatas & Flash Sale — NusantaraMart')

@section('content')
<div class="min-h-screen bg-[#FAF8F5] pb-16 space-y-6" 
     x-data="promoLimitedPage('{{ $activeSession['ends_at'] }}', {
        category: '{{ $selectedCategory }}',
        min_discount: '{{ $minDiscount ?: '' }}',
        max_price: '{{ $maxPrice ?: '' }}',
        stock_filter: '{{ $stockFilter }}',
        sort: '{{ $sort }}',
        total: {{ $products->total() }}
     })"
     x-init="initComponent()">

    <!-- 1. BREADCRUMBS -->
    <div class="bg-white border-b border-[#EAE1D7] py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" 
                   class="w-8 h-8 rounded-full bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0"
                   title="Kembali ke Beranda">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex items-center gap-2 text-xs text-[#8A7C70]">
                    <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition font-medium">Beranda</a>
                    <span>/</span>
                    <span class="text-[#2D241E] font-bold">Promo Terbatas</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- 2. HERO FLASH SALE (SIMPEL & ELEGAN, TIDAK NORAK) -->
        <div class="bg-[#2D241E] text-white rounded-2xl p-5 sm:p-7 border border-[#443529] shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <!-- Left: Headline & Info -->
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 text-amber-300 text-xs font-bold tracking-wide">
                        <span>⚡</span>
                        <span>Flash Sale NusantaraMart</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-snug">
                        Promo Terbatas Spesial Hari Ini
                    </h1>
                    <p class="text-xs sm:text-sm text-[#C8BCB0] leading-relaxed">
                        Produk pilihan dengan potongan harga terbaik. Stok terbatas dan diperbarui setiap sesi.
                    </p>
                </div>

                <!-- Right: Compact Countdown Timer -->
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4 shrink-0">
                    <div class="text-center sm:text-left">
                        <span class="text-[11px] uppercase tracking-wider text-amber-300 font-bold block">
                            Berakhir Dalam
                        </span>
                        <span class="text-xs text-[#C8BCB0]">
                            {{ $activeSession['title'] }} ({{ $activeSession['time'] }} WIB)
                        </span>
                    </div>

                    <!-- Clean Digit Boxes -->
                    <div class="flex items-center gap-1.5 font-mono text-white font-black text-lg sm:text-xl">
                        <span class="px-2.5 py-1.5 rounded-lg bg-black/40 border border-white/10 min-w-[36px] text-center" x-text="hours">00</span>
                        <span class="text-amber-400">:</span>
                        <span class="px-2.5 py-1.5 rounded-lg bg-black/40 border border-white/10 min-w-[36px] text-center" x-text="minutes">00</span>
                        <span class="text-amber-400">:</span>
                        <span class="px-2.5 py-1.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-400/30 min-w-[36px] text-center" x-text="seconds">00</span>
                    </div>
                </div>
            </div>

            <!-- Clean Session Segmented Control -->
            <div class="mt-6 pt-5 border-t border-white/10">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach($flashSessions as $session)
                        <div class="px-3.5 py-2.5 rounded-xl border text-xs flex items-center justify-between {{ $session['status'] === 'active' ? 'bg-white/15 border-amber-400/80 text-white font-bold' : ($session['status'] === 'ended' ? 'bg-black/20 border-transparent text-[#9E9084]' : 'bg-white/5 border-white/10 text-[#C8BCB0]') }}">
                            <span>{{ $session['title'] }} ({{ $session['time'] }})</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-md font-bold {{ $session['status'] === 'active' ? 'bg-amber-400 text-[#2D241E]' : ($session['status'] === 'ended' ? 'bg-white/10 text-[#9E9084]' : 'bg-white/10 text-white') }}">
                                {{ $session['status'] === 'active' ? 'Sedang Berlangsung' : ($session['status'] === 'ended' ? 'Selesai' : 'Segera Hadir') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. FILTER BAR & KATEGORI (INSTAN TANPA REFRESH HALAMAN) -->
        <div class="space-y-3">
            
            <!-- Quick Filter Chips & Sort -->
            <div class="bg-white rounded-2xl border border-[#EAE1D7] p-3 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-2xs">
                
                <!-- Quick Filter Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-semibold hide-scrollbar">
                    <!-- Semua Promo -->
                    <button type="button" 
                            @click="setQuickFilter('')"
                            :class="!filters.min_discount && !filters.max_price && !filters.stock_filter ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        Semua Promo
                    </button>

                    <!-- Diskon 50%+ -->
                    <button type="button" 
                            @click="setQuickFilter('min_discount', 50)"
                            :class="filters.min_discount == 50 ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        Diskon 50%+
                    </button>

                    <!-- Diskon 30%+ -->
                    <button type="button" 
                            @click="setQuickFilter('min_discount', 30)"
                            :class="filters.min_discount == 30 ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        Diskon 30%+
                    </button>

                    <!-- < Rp 50.000 -->
                    <button type="button" 
                            @click="setQuickFilter('max_price', 50000)"
                            :class="filters.max_price == 50000 ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        &lt; Rp 50.000
                    </button>

                    <!-- Stok Terbatas -->
                    <button type="button" 
                            @click="setQuickFilter('stock_filter', 'limited')"
                            :class="filters.stock_filter == 'limited' ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        Stok Terbatas
                    </button>
                </div>

                <!-- Total Counter & Sort Select -->
                <div class="flex items-center justify-between md:justify-end gap-3 pt-2 md:pt-0 border-t md:border-t-0 border-[#F2EAE0] text-xs">
                    <span class="text-[#8A7C70] whitespace-nowrap">
                        <strong class="text-[#2D241E]" x-text="totalCount">0</strong> Produk Promo
                    </span>

                    <div class="flex items-center gap-1.5">
                        <label for="sortPromoSelect" class="text-[#8A7C70] hidden sm:inline">Urutkan:</label>
                        <select id="sortPromoSelect" 
                                x-model="filters.sort"
                                @change="applyFilters()"
                                class="px-2.5 py-1.5 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] text-xs font-semibold text-[#2D241E] focus:outline-none focus:ring-1 focus:ring-[#6B4226] cursor-pointer">
                            <option value="highest_discount">Diskon Tertinggi</option>
                            <option value="cheapest">Harga Terendah</option>
                            <option value="priciest">Harga Tertinggi</option>
                            <option value="popular">Paling Banyak Terjual</option>
                            <option value="rating">Rating Tertinggi</option>
                            <option value="newest">Terbaru</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Category Pills Row (Tanpa Reload) -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-semibold hide-scrollbar">
                <button type="button" 
                        @click="setCategory('')"
                        :class="!filters.category ? 'bg-[#2D241E] text-white' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                        class="px-3.5 py-1.5 rounded-xl transition shrink-0 cursor-pointer">
                    Semua Kategori
                </button>
                @foreach($categories as $cat)
                    <button type="button" 
                            @click="setCategory('{{ $cat->slug }}')"
                            :class="filters.category === '{{ $cat->slug }}' ? 'bg-[#2D241E] text-white' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3.5 py-1.5 rounded-xl transition shrink-0 flex items-center gap-1 cursor-pointer">
                        <span>{{ $cat->icon }}</span>
                        <span>{{ $cat->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 4. PRODUCT GRID CONTAINER (DYNAMIC AJAX SWAP) -->
        <div class="relative min-h-[240px]">
            <!-- Loading Overlay -->
            <div x-show="loading" 
                 x-cloak
                 class="absolute inset-0 bg-[#FAF8F5]/70 backdrop-blur-2xs z-20 flex items-center justify-center rounded-2xl">
                <div class="w-8 h-8 rounded-full border-2 border-[#6B4226] border-t-transparent animate-spin"></div>
            </div>

            <!-- Products Content -->
            <div id="promoProductsContainer" 
                 :class="loading ? 'opacity-40 transition-opacity' : 'opacity-100 transition-opacity'"
                 x-html="productsHtml">
                @include('partials.promo-products-grid')
            </div>
        </div>

        <!-- 5. JAMINAN NUSANTARAMART (SIMPEL & TIDAK MENCOLOK) -->
        <div class="bg-white rounded-2xl border border-[#EAE1D7] p-4 sm:p-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div class="flex items-center gap-2.5 p-2">
                    <span class="text-lg">🛡️</span>
                    <div>
                        <span class="font-bold text-[#2D241E] block">100% Original</span>
                        <span class="text-[11px] text-[#8A7C70]">Distributor resmi</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 p-2">
                    <span class="text-lg">📦</span>
                    <div>
                        <span class="font-bold text-[#2D241E] block">Pengemasan Aman</span>
                        <span class="text-[11px] text-[#8A7C70]">Kardus & bubble wrap</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 p-2">
                    <span class="text-lg">🚚</span>
                    <div>
                        <span class="font-bold text-[#2D241E] block">Pengiriman Cepat</span>
                        <span class="text-[11px] text-[#8A7C70]">Kirim tepat waktu</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 p-2">
                    <span class="text-lg">💬</span>
                    <div>
                        <span class="font-bold text-[#2D241E] block">Layanan Responsif</span>
                        <span class="text-[11px] text-[#8A7C70]">Chat bantuan aktif</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function promoLimitedPage(targetIsoDate, initialFilters) {
        return {
            hours: '00',
            minutes: '00',
            seconds: '00',
            timerInterval: null,
            loading: false,

            filters: {
                category: initialFilters.category || '',
                min_discount: initialFilters.min_discount || '',
                max_price: initialFilters.max_price || '',
                stock_filter: initialFilters.stock_filter || '',
                sort: initialFilters.sort || 'highest_discount'
            },
            totalCount: initialFilters.total || 0,
            productsHtml: '',

            initComponent() {
                // Initialize countdown timer
                this.updateTimer();
                this.timerInterval = setInterval(() => {
                    this.updateTimer();
                }, 1000);

                // Preload current rendered grid into productsHtml
                const initialContainer = document.getElementById('promoProductsContainer');
                if (initialContainer) {
                    this.productsHtml = initialContainer.innerHTML;
                }

                // Handle browser back/forward buttons
                window.addEventListener('popstate', (event) => {
                    this.fetchFromUrl(window.location.href);
                });
            },

            updateTimer() {
                const now = new Date().getTime();
                const target = new Date(targetIsoDate).getTime();
                let diff = target - now;

                if (diff <= 0) {
                    diff = 3600 * 4 * 1000;
                }

                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);

                this.hours = String(h).padStart(2, '0');
                this.minutes = String(m).padStart(2, '0');
                this.seconds = String(s).padStart(2, '0');
            },

            setQuickFilter(key, value) {
                if (!key) {
                    // Reset all quick filters
                    this.filters.min_discount = '';
                    this.filters.max_price = '';
                    this.filters.stock_filter = '';
                } else if (key === 'min_discount') {
                    this.filters.min_discount = (this.filters.min_discount == value) ? '' : value;
                } else if (key === 'max_price') {
                    this.filters.max_price = (this.filters.max_price == value) ? '' : value;
                } else if (key === 'stock_filter') {
                    this.filters.stock_filter = (this.filters.stock_filter == value) ? '' : value;
                }

                this.applyFilters();
            },

            setCategory(slug) {
                this.filters.category = (this.filters.category === slug) ? '' : slug;
                this.applyFilters();
            },

            resetAllFilters() {
                this.filters.category = '';
                this.filters.min_discount = '';
                this.filters.max_price = '';
                this.filters.stock_filter = '';
                this.filters.sort = 'highest_discount';
                this.applyFilters();
            },

            applyFilters() {
                const baseUrl = '{{ route('promo.limited') }}';
                const url = new URL(baseUrl, window.location.origin);

                if (this.filters.category) url.searchParams.set('category', this.filters.category);
                if (this.filters.min_discount) url.searchParams.set('min_discount', this.filters.min_discount);
                if (this.filters.max_price) url.searchParams.set('max_price', this.filters.max_price);
                if (this.filters.stock_filter) url.searchParams.set('stock_filter', this.filters.stock_filter);
                if (this.filters.sort && this.filters.sort !== 'highest_discount') {
                    url.searchParams.set('sort', this.filters.sort);
                }

                // Update address bar without refreshing!
                window.history.pushState(null, '', url.toString());

                this.fetchData(url.toString());
            },

            handlePaginationClick(e) {
                const link = e.target.closest('a');
                if (link && link.href) {
                    e.preventDefault();
                    window.history.pushState(null, '', link.href);
                    this.fetchData(link.href);
                }
            },

            fetchFromUrl(fullUrl) {
                const url = new URL(fullUrl);
                this.filters.category = url.searchParams.get('category') || '';
                this.filters.min_discount = url.searchParams.get('min_discount') || '';
                this.filters.max_price = url.searchParams.get('max_price') || '';
                this.filters.stock_filter = url.searchParams.get('stock_filter') || '';
                this.filters.sort = url.searchParams.get('sort') || 'highest_discount';

                this.fetchData(fullUrl);
            },

            fetchData(urlStr) {
                this.loading = true;
                fetch(urlStr, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    this.productsHtml = data.html;
                    this.totalCount = data.total;
                    this.loading = false;
                })
                .catch(err => {
                    console.error('Failed to filter promo products:', err);
                    this.loading = false;
                });
            },

            addToCartDirectly(product) {
                window.dispatchEvent(new CustomEvent('add-to-cart', {
                    detail: {
                        product: product,
                        quantity: 1
                    }
                }));
            }
        };
    }
</script>
@endsection
