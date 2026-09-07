@extends('layouts.app')

@section('title', 'Gratis Ongkir se-Indonesia — NusantaraMart')

@section('content')
<div class="min-h-screen bg-[#FAF8F5] pb-16 space-y-6" 
     x-data="freeShippingPage({
        category: '{{ $selectedCategory }}',
        only_discount: {{ $onlyDiscount ? 'true' : 'false' }},
        max_price: '{{ $maxPrice ?: '' }}',
        badge: '{{ $selectedBadge }}',
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
                    <span class="text-[#2D241E] font-bold">Gratis Ongkir</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- 2. HERO BANNER (BERSIH, ELEGAN, TIDAK NORAK) -->
        <div class="bg-[#2D241E] text-white rounded-2xl p-5 sm:p-7 border border-[#443529] shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <!-- Left: Headline & Description -->
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 text-emerald-300 text-xs font-bold tracking-wide">
                        <span>🚚</span>
                        <span>Bebas Ongkir NusantaraMart</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-snug">
                        Koleksi Produk Bebas Ongkir se-Indonesia
                    </h1>
                    <p class="text-xs sm:text-sm text-[#C8BCB0] leading-relaxed">
                        Nikmati subsidi dan bebas biaya pengiriman ke seluruh penjuru Nusantara tanpa syarat rumit dan tanpa kupon tambahan.
                    </p>
                </div>

                <!-- Right: Clean Highlights -->
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4 shrink-0 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🚚</span>
                        <div>
                            <span class="font-bold block text-white">Subsidi Ongkir</span>
                            <span class="text-[#9E9084] text-[11px]">Hingga Rp 40.000</span>
                        </div>
                    </div>
                    <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-base">📍</span>
                        <div>
                            <span class="font-bold block text-white">Semua Wilayah</span>
                            <span class="text-[#9E9084] text-[11px]">Sabang sampai Merauke</span>
                        </div>
                    </div>
                    <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-base">⚡</span>
                        <div>
                            <span class="font-bold block text-white">Ekspedisi Cepat</span>
                            <span class="text-[#9E9084] text-[11px]">Kurir terpercaya</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. BAR KATEGORI & FILTER INSTAN (TANPA RELOAD) -->
        <div class="space-y-3">
            
            <!-- Category Selector Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-semibold hide-scrollbar">
                <button type="button" 
                        @click="setCategory('')"
                        :class="!filters.category ? 'bg-[#2D241E] text-white' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                        class="px-4 py-2 rounded-xl transition shrink-0 cursor-pointer shadow-2xs">
                    Semua Kategori
                </button>
                @foreach($categories as $cat)
                    <button type="button" 
                            @click="setCategory('{{ $cat->slug }}')"
                            :class="filters.category === '{{ $cat->slug }}' ? 'bg-[#2D241E] text-white' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-4 py-2 rounded-xl transition shrink-0 flex items-center gap-1.5 cursor-pointer shadow-2xs">
                        <span>{{ $cat->icon }}</span>
                        <span>{{ $cat->name }}</span>
                        @if($cat->products_count > 0)
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full" 
                                  :class="filters.category === '{{ $cat->slug }}' ? 'bg-white/20 text-white' : 'bg-[#FAF4ED] text-[#6B4226]'">
                                {{ $cat->products_count }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            <!-- Quick Filter Bar & Sort Select -->
            <div class="bg-white rounded-2xl border border-[#EAE1D7] p-3 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-2xs">
                
                <!-- Quick Filter Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-semibold hide-scrollbar">
                    <!-- Semua -->
                    <button type="button" 
                            @click="setQuickFilter('')"
                            :class="!filters.only_discount && !filters.max_price && !filters.badge ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        Semua Produk
                    </button>

                    <!-- Hanya Diskon -->
                    <button type="button" 
                            @click="toggleDiscountFilter()"
                            :class="filters.only_discount ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap flex items-center gap-1">
                        <span>⚡</span>
                        <span>Lagi Diskon</span>
                    </button>

                    <!-- Badge Official / Mall -->
                    <button type="button" 
                            @click="setBadgeFilter('Official')"
                            :class="filters.badge === 'Official' ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        ⭐ Official Store
                    </button>

                    <!-- < Rp 50.000 -->
                    <button type="button" 
                            @click="setMaxPrice(50000)"
                            :class="filters.max_price == 50000 ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        &lt; Rp 50.000
                    </button>

                    <!-- < Rp 100.000 -->
                    <button type="button" 
                            @click="setMaxPrice(100000)"
                            :class="filters.max_price == 100000 ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        &lt; Rp 100.000
                    </button>
                </div>

                <!-- Total Counter & Sort Select -->
                <div class="flex items-center justify-between md:justify-end gap-3 pt-2 md:pt-0 border-t md:border-t-0 border-[#F2EAE0] text-xs">
                    <span class="text-[#8A7C70] whitespace-nowrap">
                        <strong class="text-[#2D241E]" x-text="totalCount">0</strong> Produk Bebas Ongkir
                    </span>

                    <div class="flex items-center gap-1.5">
                        <label for="sortFreeShippingSelect" class="text-[#8A7C70] hidden sm:inline">Urutkan:</label>
                        <select id="sortFreeShippingSelect" 
                                x-model="filters.sort"
                                @change="applyFilters()"
                                class="px-2.5 py-1.5 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] text-xs font-semibold text-[#2D241E] focus:outline-none focus:ring-1 focus:ring-[#6B4226] cursor-pointer">
                            <option value="popular">Paling Banyak Terjual</option>
                            <option value="rating">Rating Tertinggi</option>
                            <option value="cheapest">Harga Terendah</option>
                            <option value="priciest">Harga Tertinggi</option>
                            <option value="highest_discount">Diskon Tertinggi</option>
                            <option value="newest">Terbaru</option>
                        </select>
                    </div>
                </div>
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
            <div id="freeShippingProductsContainer" 
                 :class="loading ? 'opacity-40 transition-opacity' : 'opacity-100 transition-opacity'"
                 x-html="productsHtml">
                @include('partials.free-shipping-products-grid')
            </div>
        </div>

        <!-- 5. JAMINAN NUSANTARAMART (SIMPEL & TIDAK MENCOLOK) -->
        <div class="bg-white rounded-2xl border border-[#EAE1D7] p-4 sm:p-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div class="flex items-center gap-2.5 p-2">
                    <span class="text-lg">🚚</span>
                    <div>
                        <span class="font-bold text-[#2D241E] block">Bebas Ongkir</span>
                        <span class="text-[11px] text-[#8A7C70]">Tanpa biaya kirim</span>
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
                    <span class="text-lg">⚡</span>
                    <div>
                        <span class="font-bold text-[#2D241E] block">Ekspedisi Resmi</span>
                        <span class="text-[11px] text-[#8A7C70]">Pelacakan real-time</span>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 p-2">
                    <span class="text-lg">🛡️</span>
                    <div>
                        <span class="font-bold text-[#2D241E] block">Garansi Tepat Waktu</span>
                        <span class="text-[11px] text-[#8A7C70]">Pesanan pasti sampai</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function freeShippingPage(initialFilters) {
        return {
            loading: false,

            filters: {
                category: initialFilters.category || '',
                only_discount: initialFilters.only_discount || false,
                max_price: initialFilters.max_price || '',
                badge: initialFilters.badge || '',
                sort: initialFilters.sort || 'popular'
            },
            totalCount: initialFilters.total || 0,
            productsHtml: '',

            initComponent() {
                const initialContainer = document.getElementById('freeShippingProductsContainer');
                if (initialContainer) {
                    this.productsHtml = initialContainer.innerHTML;
                }

                window.addEventListener('popstate', (event) => {
                    this.fetchFromUrl(window.location.href);
                });
            },

            setCategory(slug) {
                this.filters.category = (this.filters.category === slug) ? '' : slug;
                this.applyFilters();
            },

            setQuickFilter(type) {
                if (!type) {
                    this.filters.only_discount = false;
                    this.filters.max_price = '';
                    this.filters.badge = '';
                }
                this.applyFilters();
            },

            toggleDiscountFilter() {
                this.filters.only_discount = !this.filters.only_discount;
                this.applyFilters();
            },

            setBadgeFilter(badgeName) {
                this.filters.badge = (this.filters.badge === badgeName) ? '' : badgeName;
                this.applyFilters();
            },

            setMaxPrice(amount) {
                this.filters.max_price = (this.filters.max_price == amount) ? '' : amount;
                this.applyFilters();
            },

            resetAllFilters() {
                this.filters.category = '';
                this.filters.only_discount = false;
                this.filters.max_price = '';
                this.filters.badge = '';
                this.filters.sort = 'popular';
                this.applyFilters();
            },

            applyFilters() {
                const baseUrl = '{{ route('gratis.ongkir') }}';
                const url = new URL(baseUrl, window.location.origin);

                if (this.filters.category) url.searchParams.set('category', this.filters.category);
                if (this.filters.only_discount) url.searchParams.set('only_discount', '1');
                if (this.filters.max_price) url.searchParams.set('max_price', this.filters.max_price);
                if (this.filters.badge) url.searchParams.set('badge', this.filters.badge);
                if (this.filters.sort && this.filters.sort !== 'popular') {
                    url.searchParams.set('sort', this.filters.sort);
                }

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
                this.filters.only_discount = url.searchParams.get('only_discount') === '1';
                this.filters.max_price = url.searchParams.get('max_price') || '';
                this.filters.badge = url.searchParams.get('badge') || '';
                this.filters.sort = url.searchParams.get('sort') || 'popular';

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
                    console.error('Failed to filter free shipping products:', err);
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
