@extends('layouts.app')

@section('title', 'Penawaran Spesial & Promo Diskon Terbaik — NusantaraMart')

@section('content')
<div class="min-h-screen bg-[#FAF8F5] pb-16 space-y-6" 
     x-data="specialOffersPage({
        category: '{{ $selectedCategory }}',
        min_discount: '{{ $minDiscount ?: '' }}',
        max_price: '{{ $maxPrice ?: '' }}',
        badge: '{{ $selectedBadge }}',
        only_discount: {{ $onlyDiscount ? 'true' : 'false' }},
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
                    <span class="text-[#2D241E] font-bold">Penawaran Spesial</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- 2. HERO BANNER -->
        <div class="bg-[#2D241E] text-white rounded-2xl p-5 sm:p-7 border border-[#443529] shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <!-- Left: Headline & Description -->
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 text-amber-300 text-xs font-bold tracking-wide">
                        <span>🏷️</span>
                        <span>Penawaran Spesial NusantaraMart</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-snug">
                        Koleksi Penawaran Spesial & Diskon Eksklusif
                    </h1>
                    <p class="text-xs sm:text-sm text-[#C8BCB0] leading-relaxed">
                        Dapatkan produk pilihan dengan potongan harga terbaik, penawaran toko resmi, dan promo istimewa setiap hari.
                    </p>
                </div>

                <!-- Right: Clean Highlights -->
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4 shrink-0 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🔥</span>
                        <div>
                            <span class="font-bold block text-white">Diskon Hingga</span>
                            <span class="text-[#9E9084] text-[11px]">Potongan s/d 70%</span>
                        </div>
                    </div>
                    <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-base">💎</span>
                        <div>
                            <span class="font-bold block text-white">Produk Pilihan</span>
                            <span class="text-[#9E9084] text-[11px]">Kurasi terpercaya</span>
                        </div>
                    </div>
                    <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-base">🛡️</span>
                        <div>
                            <span class="font-bold block text-white">100% Asli</span>
                            <span class="text-[#9E9084] text-[11px]">Garansi original</span>
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
                            :class="!filters.min_discount && !filters.max_price && !filters.badge && !filters.only_discount ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap">
                        Semua Penawaran
                    </button>

                    <!-- Diskon 50%+ -->
                    <button type="button" 
                            @click="setMinDiscount(50)"
                            :class="filters.min_discount == 50 ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap flex items-center gap-1">
                        <span>🔥</span>
                        <span>Diskon 50%+</span>
                    </button>

                    <!-- Diskon 30%+ -->
                    <button type="button" 
                            @click="setMinDiscount(30)"
                            :class="filters.min_discount == 30 ? 'bg-[#6B4226] text-white' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer whitespace-nowrap flex items-center gap-1">
                        <span>⚡</span>
                        <span>Diskon 30%+</span>
                    </button>

                    <!-- Badge Official Store -->
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
                <div class="flex items-center justify-between md:justify-end gap-3 pt-2 md:pt-0 border-t md:border-t-0 border-[#F2EAE0]">
                    <span class="text-xs text-[#8A7C70] whitespace-nowrap">
                        <strong class="text-[#2D241E]" x-text="totalCount"></strong> Produk Ditemukan
                    </span>

                    <div class="flex items-center gap-1.5">
                        <label for="sortSelect" class="text-xs text-[#8A7C70] whitespace-nowrap hidden sm:inline">Urutkan:</label>
                        <select id="sortSelect" 
                                x-model="filters.sort" 
                                @change="applyFilters()"
                                class="bg-[#FAF8F5] border border-[#EAE1D7] text-[#2D241E] text-xs rounded-xl px-2.5 py-1.5 focus:ring-1 focus:ring-[#6B4226] focus:border-[#6B4226] cursor-pointer">
                            <option value="highest_discount">🔥 Diskon Tertinggi</option>
                            <option value="popular">📦 Paling Populer</option>
                            <option value="cheapest">💰 Harga Terendah</option>
                            <option value="priciest">💎 Harga Tertinggi</option>
                            <option value="rating">⭐ Rating Tertinggi</option>
                            <option value="newest">✨ Terbaru</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <!-- 4. PRODUCT GRID (INSTANT AJAX REPLACEMENT & COMPACT CARDS) -->
        <div class="relative">
            <!-- Loading Overlay -->
            <div x-show="loading" 
                 x-transition.opacity
                 class="absolute inset-0 bg-[#FAF8F5]/70 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-2xl min-h-[300px]"
                 style="display: none;">
                <div class="bg-white px-5 py-3 rounded-2xl shadow-sm border border-[#EAE1D7] flex items-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-[#6B4226]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs font-bold text-[#2D241E]">Memuat penawaran...</span>
                </div>
            </div>

            <!-- Dynamic Products Container -->
            <div x-ref="gridContainer">
                <template x-if="productsHtml">
                    <div x-html="productsHtml"></div>
                </template>
                <template x-if="!productsHtml">
                    <div>
                        @include('partials.special-offers-products-grid', ['products' => $products])
                    </div>
                </template>
            </div>
        </div>

        <!-- 5. SHOPPING GUARANTEES (CLEAN & TRUSTWORTHY) -->
        <div class="bg-white rounded-2xl border border-[#EAE1D7] p-5 sm:p-6 shadow-2xs">
            <h3 class="font-bold text-sm text-[#2D241E] mb-4 flex items-center gap-2">
                <span>🛡️</span>
                <span>Jaminan Belanja Spesial di NusantaraMart</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="flex items-start gap-3 p-3 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0]">
                    <span class="text-2xl shrink-0">✨</span>
                    <div>
                        <h4 class="font-bold text-[#2D241E]">100% Produk Original</h4>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5 leading-snug">Produk langsung dari produsen, distributor, & toko resmi terverifikasi.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0]">
                    <span class="text-2xl shrink-0">🏷️</span>
                    <div>
                        <h4 class="font-bold text-[#2D241E]">Diskon Nyata</h4>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5 leading-snug">Potongan harga asli tanpa manipulasi harga awal sebelum promo.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0]">
                    <span class="text-2xl shrink-0">📦</span>
                    <div>
                        <h4 class="font-bold text-[#2D241E]">Pengemasan Aman</h4>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5 leading-snug">Kardus tebal & bubble wrap tanpa biaya tambahan untuk setiap pesanan.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0]">
                    <span class="text-2xl shrink-0">💬</span>
                    <div>
                        <h4 class="font-bold text-[#2D241E]">Pelayanan Responsif</h4>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5 leading-snug">Bantuan cepat untuk kendala pemesanan, lacak paket, dan keluhan.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function specialOffersPage(initialConfig) {
        return {
            filters: {
                category: initialConfig.category || '',
                min_discount: initialConfig.min_discount || '',
                max_price: initialConfig.max_price || '',
                badge: initialConfig.badge || '',
                only_discount: initialConfig.only_discount || false,
                sort: initialConfig.sort || 'highest_discount'
            },
            totalCount: initialConfig.total || 0,
            loading: false,
            productsHtml: null,

            initComponent() {
                window.addEventListener('popstate', (e) => {
                    this.fetchFromUrl(window.location.href);
                });
            },

            setCategory(slug) {
                this.filters.category = slug;
                this.applyFilters();
            },

            setMinDiscount(percentage) {
                this.filters.min_discount = (this.filters.min_discount == percentage) ? '' : percentage;
                this.applyFilters();
            },

            setQuickFilter(type) {
                if (type === '') {
                    this.filters.min_discount = '';
                    this.filters.max_price = '';
                    this.filters.badge = '';
                    this.filters.only_discount = false;
                }
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
                this.filters.min_discount = '';
                this.filters.max_price = '';
                this.filters.badge = '';
                this.filters.only_discount = false;
                this.filters.sort = 'highest_discount';
                this.applyFilters();
            },

            applyFilters() {
                const baseUrl = '{{ route('penawaran.spesial') }}';
                const url = new URL(baseUrl, window.location.origin);

                if (this.filters.category) url.searchParams.set('category', this.filters.category);
                if (this.filters.min_discount) url.searchParams.set('min_discount', this.filters.min_discount);
                if (this.filters.max_price) url.searchParams.set('max_price', this.filters.max_price);
                if (this.filters.badge) url.searchParams.set('badge', this.filters.badge);
                if (this.filters.only_discount) url.searchParams.set('only_discount', '1');
                if (this.filters.sort && this.filters.sort !== 'highest_discount') {
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
                this.filters.min_discount = url.searchParams.get('min_discount') || '';
                this.filters.max_price = url.searchParams.get('max_price') || '';
                this.filters.badge = url.searchParams.get('badge') || '';
                this.filters.only_discount = url.searchParams.get('only_discount') === '1';
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
                    console.error('Failed to filter special offers products:', err);
                    this.loading = false;
                });
            }
        };
    }
</script>
@endsection
