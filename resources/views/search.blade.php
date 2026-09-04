@extends('layouts.app')

@section('title', 'Pencarian & Eksplorasi Produk — NusantaraMart')

@section('content')
<div class="min-h-screen bg-[#FAF8F5] pb-16"
     x-data="searchDiscoveryComponent('{{ addslashes($searchQuery ?? '') }}')">

    <!-- 1. FULL-WIDTH NUSANTARAMART STICKY SEARCH HEADER -->
    <div class="sticky top-0 z-50 bg-white/98 backdrop-blur-md border-b border-[#EAE1D7] shadow-xs">
        <div class="w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-3.5 sm:py-4">
            <div class="flex items-center gap-3 sm:gap-6">
                
                <!-- Brand Logo (Links to Home) -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0 group" title="NusantaraMart">
                    <div class="w-9 h-9 rounded-xl bg-[#6B4226] group-hover:bg-[#54321B] flex items-center justify-center text-white text-lg shadow-2xs transition">
                        🛍️
                    </div>
                    <span class="text-base sm:text-lg font-black tracking-tight text-[#2D241E] hidden sm:inline">
                        Nusantara<span class="text-[#6B4226]">Mart</span>
                    </span>
                </a>

                <!-- Full-Width Centered Search Input Form with Live Suggestions -->
                <form @submit.prevent="submitSearch()" class="flex-1 relative" @click.outside="showSuggestions = false">
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 sm:pl-5 flex items-center pointer-events-none text-[#8A7C70]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               x-model="searchKeyword" 
                               x-ref="searchInput"
                               @input="fetchSuggestions()"
                               @focus="fetchSuggestions()"
                               @click="fetchSuggestions()"
                               @keydown.escape="showSuggestions = false"
                               placeholder="Cari produk apa saja di NusantaraMart..."
                               class="w-full pl-12 sm:pl-14 pr-24 sm:pr-28 py-3 sm:py-3.5 bg-[#FAF7F2] hover:bg-[#F5F0E8] focus:bg-white text-[#2D241E] placeholder:text-[#9E9084] text-xs sm:text-sm rounded-2xl border border-[#E8DED3] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition shadow-2xs">
                        
                        <div class="absolute right-2 flex items-center gap-1.5">
                            <button type="button" 
                                    x-show="searchKeyword.length > 0" 
                                    x-cloak
                                    @click="searchKeyword = ''; suggestions = []; showSuggestions = false; $refs.searchInput.focus();" 
                                    class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg text-xs cursor-pointer">
                                ✕
                            </button>
                            <button type="submit" 
                                    class="px-4 sm:px-5 py-2 bg-[#6B4226] hover:bg-[#54321B] text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                Cari
                            </button>
                        </div>
                    </div>

                    <!-- Live Search Suggestions Dropdown -->
                    <div x-show="showSuggestions && (suggestions.length > 0 || storeQuery)"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="transform opacity-0 -translate-y-2 scale-98"
                         x-transition:enter-end="transform opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="transform opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="transform opacity-0 -translate-y-2 scale-98"
                         class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-2xl border border-[#EAE1D7] shadow-xl z-50 overflow-hidden divide-y divide-[#F2EAE0]">
                        
                        <!-- 1. Store Search Match Item (Cari Toko "keyword") -->
                        <template x-if="storeQuery">
                            <a :href="'{{ route('search.results') }}?q=' + encodeURIComponent(searchKeyword.trim())" 
                               @click="saveSearch(searchKeyword.trim()); showSuggestions = false;"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-[#FAF4ED] text-[#6B4226] text-xs font-bold transition cursor-pointer group">
                                <div class="w-7 h-7 rounded-xl bg-[#FAF4ED] text-[#6B4226] group-hover:bg-[#6B4226] group-hover:text-white flex items-center justify-center text-sm transition shrink-0 shadow-2xs">
                                    🏪
                                </div>
                                <span class="truncate" x-text="storeQuery"></span>
                            </a>
                        </template>

                        <!-- 2. Matching Keyword Suggestions List -->
                        <div class="py-1">
                            <template x-for="item in suggestions" :key="item">
                                <button type="button" 
                                        @click="selectSuggestion(item)"
                                        class="w-full px-4 py-2.5 flex items-center justify-between text-left text-xs text-[#5A4B40] hover:bg-[#FAF7F2] hover:text-[#6B4226] transition cursor-pointer group">
                                    <div class="flex items-center gap-3 truncate">
                                        <svg class="w-3.5 h-3.5 text-[#C4B6A6] group-hover:text-[#6B4226] transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <span class="truncate text-xs text-[#4A3B32]" x-html="highlightMatch(item, searchKeyword)"></span>
                                    </div>
                                    <span class="text-[#D8C4B6] group-hover:text-[#6B4226] text-xs opacity-0 group-hover:opacity-100 transition">
                                        ↖
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                </form>

                <!-- Quick Cart Access Button -->
                <a href="{{ route('cart') }}" 
                   class="relative p-2.5 text-[#5A4B40] hover:text-[#6B4226] hover:bg-[#FAF4ED] rounded-2xl transition flex items-center gap-2 group border border-[#EAE1D7] shrink-0"
                   title="Keranjang Belanja">
                    <svg class="w-5 h-5 group-hover:scale-105 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span x-show="totalCount > 0" 
                          x-cloak
                          x-text="totalCount" 
                          class="absolute -top-1.5 -right-1.5 bg-[#6B4226] text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-white shadow-xs"></span>
                </a>

                @auth
                    <!-- User Circular Avatar Dropdown with Avatar Photo -->
                    <div class="relative shrink-0" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false" 
                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs sm:text-sm flex items-center justify-center border-2 border-white shadow-xs hover:ring-2 hover:ring-[#6B4226]/40 transition transform active:scale-95 cursor-pointer overflow-hidden"
                                title="Menu Akun Saya ({{ Auth::user()->name }})">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            @endif
                        </button>

                        <div x-show="open" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-2xl border border-[#EAE1D7] shadow-xl py-2 z-50 divide-y divide-[#F2EAE0] text-[#2D241E]">
                            <div class="px-4 py-2.5">
                                <p class="text-[11px] text-[#8A7C70] font-medium">Masuk sebagai</p>
                                <p class="text-xs font-black text-[#2D241E] truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-[#8A7C70] font-mono">{{ Auth::user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('my.orders') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-[#5A4B40] hover:bg-[#FAF4ED] hover:text-[#6B4226] transition">
                                    <span>📦</span> <span>Pesanan Saya</span>
                                </a>
                                <a href="{{ route('settings') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-[#5A4B40] hover:bg-[#FAF4ED] hover:text-[#6B4226] transition">
                                    <span>⚙️</span> <span>Pengaturan Akun</span>
                                </a>
                            </div>
                            <div class="py-1">
                                <form action="{{ route('logout') }}" method="POST" onsubmit="handleLogoutCart()">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50 transition text-left cursor-pointer">
                                        <span>🚪</span> <span>Keluar (Logout)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth

            </div>
        </div>
    </div>

    <!-- 2. FULL-WIDTH SEARCH DISCOVERY CANVAS -->
    <div class="w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-4">

        <!-- Back to Home Button & Breadcrumbs (In Content Area, Not In Header) -->
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" 
               class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
               title="Kembali ke Beranda">
                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            <div class="flex items-center gap-2 text-xs text-[#8A7C70]">
                <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition">Beranda</a>
                <span>/</span>
                <span class="text-[#6B4226] font-semibold">Pencarian & Eksplorasi</span>
            </div>
        </div>

        <!-- Shopee Discovery Bar (Riwayat & Tren Pencarian Populer) -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-2xs space-y-5">
            
            <!-- Riwayat Pencarian Terakhir (Jika Ada) -->
            <div x-show="recentSearches.length > 0" x-cloak class="space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-[#8A7C70] flex items-center gap-1.5">
                        <span>🕒</span>
                        <span>Pencarian Terakhir Kamu</span>
                    </span>
                    <button type="button" @click="clearRecentSearches()" class="text-[11px] text-[#8A7C70] hover:text-red-600 transition cursor-pointer">
                        Hapus Riwayat
                    </button>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="item in recentSearches" :key="item">
                        <a :href="'{{ route('search.results') }}?q=' + encodeURIComponent(item)" 
                           class="px-3.5 py-1.5 bg-[#FAF8F5] hover:bg-[#FAF4ED] hover:text-[#6B4226] text-[#5A4B40] text-xs rounded-xl border border-[#EAE1D7] transition flex items-center gap-1 font-medium">
                            <span x-text="item"></span>
                        </a>
                    </template>
                </div>
            </div>

            <!-- Tren Pencarian Populer -->
            <div class="space-y-2.5">
                <span class="font-bold text-xs text-[#6B4226] flex items-center gap-1.5">
                    <span>🔥</span>
                    <span>Tren Pencarian Terpopuler di NusantaraMart</span>
                </span>
                <div class="flex flex-wrap gap-2">
                    @foreach($trendingKeywords as $index => $keyword)
                        <a href="{{ route('search.results', ['q' => $keyword]) }}" 
                           @click="saveSearch('{{ $keyword }}')"
                           class="px-3.5 py-2 bg-[#FAF7F2] hover:bg-[#FAF4ED] hover:text-[#6B4226] text-[#5A4B40] rounded-xl border border-[#EAE1D7] transition text-xs font-semibold flex items-center gap-2 group">
                            <span class="w-4.5 h-4.5 rounded-full {{ $index < 3 ? 'bg-[#6B4226] text-white' : 'bg-stone-200 text-stone-700' }} text-[9px] font-black flex items-center justify-center shadow-2xs">
                                {{ $index + 1 }}
                            </span>
                            <span class="group-hover:translate-x-0.5 transition">{{ $keyword }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- 3. KATEGORI POPULER NUSANTARAMART -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                <div>
                    <h3 class="font-bold text-base sm:text-lg text-[#2D241E] flex items-center gap-2">
                        <span>Kategori Belanja Pilihan</span>
                        <span>🏷️</span>
                    </h3>
                    <p class="text-xs text-[#7A6C60]">Jelajahi beragam kebutuhan dari kategori terbaik</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @foreach($categories as $cat)
                    <a href="{{ route('search.results', ['category' => $cat->slug]) }}" 
                       class="p-3.5 bg-[#FAF8F5] hover:bg-[#FAF4ED] border border-[#EAE1D7] rounded-2xl flex flex-col items-center justify-center text-center transition group hover:shadow-sm">
                        <div class="text-3xl mb-2 group-hover:scale-110 transition">
                            {{ $cat->icon }}
                        </div>
                        <h4 class="font-bold text-xs text-[#2D241E] group-hover:text-[#6B4226] transition line-clamp-1">
                            {{ $cat->name }}
                        </h4>
                        <span class="text-[10px] text-[#8A7C70] mt-0.5">{{ $cat->products_count }} Produk</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- 4. REKOMENDASI PRODUK TERLARIS DI NUSANTARAMART -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                <div>
                    <h3 class="font-bold text-base sm:text-lg text-[#2D241E] flex items-center gap-2">
                        <span>Produk Paling Banyak Dicari</span>
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

@push('scripts')
<script>
function searchDiscoveryComponent(initialQuery) {
    return {
        searchKeyword: initialQuery || '',
        recentSearches: [],
        suggestions: [],
        storeQuery: '',
        showSuggestions: false,
        debounceTimer: null,
        init() {
            const saved = localStorage.getItem('nusantaramart_recent_searches');
            if (saved) {
                try {
                    this.recentSearches = JSON.parse(saved);
                } catch(e) {
                    this.recentSearches = [];
                }
            }
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                    if (this.searchKeyword) {
                        const len = this.searchKeyword.length;
                        this.$refs.searchInput.setSelectionRange(len, len);
                    }
                }
                if (this.searchKeyword && this.searchKeyword.trim().length > 0) {
                    this.fetchSuggestions();
                }
            });
        },
        saveSearch(term) {
            if (!term || !term.trim()) return;
            term = term.trim();
            this.recentSearches = [term, ...this.recentSearches.filter(s => s.toLowerCase() !== term.toLowerCase())].slice(0, 8);
            localStorage.setItem('nusantaramart_recent_searches', JSON.stringify(this.recentSearches));
        },
        clearRecentSearches() {
            this.recentSearches = [];
            localStorage.removeItem('nusantaramart_recent_searches');
        },
        fetchSuggestions() {
            const q = this.searchKeyword ? this.searchKeyword.trim() : '';
            if (!q) {
                this.suggestions = [];
                this.storeQuery = '';
                this.showSuggestions = false;
                return;
            }
            this.showSuggestions = true;
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                fetch('{{ route('search.suggestions') }}?q=' + encodeURIComponent(q))
                    .then(res => res.json())
                    .then(data => {
                        this.suggestions = data.suggestions || [];
                        this.storeQuery = data.store_query || '';
                        this.showSuggestions = true;
                    })
                    .catch(() => {
                        this.suggestions = [];
                    });
            }, 80);
        },
        selectSuggestion(item) {
            this.searchKeyword = item;
            this.showSuggestions = false;
            this.submitSearch();
        },
        highlightMatch(text, query) {
            if (!query || !query.trim() || !text) return text || '';
            const q = query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp('(' + q + ')', 'gi');
            return text.replace(regex, '<b class="font-black text-[#2D241E]">$1</b>');
        },
        submitSearch() {
            if (this.searchKeyword.trim()) {
                this.showSuggestions = false;
                this.saveSearch(this.searchKeyword);
                window.location.href = '{{ route('search.results') }}?q=' + encodeURIComponent(this.searchKeyword.trim());
            }
        }
    };
}
</script>
@endpush
