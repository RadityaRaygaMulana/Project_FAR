@extends('layouts.app')

@section('title', $storeInfo['name'] . ' — Toko Resmi NusantaraMart')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- TOP NAVIGATION WITH CIRCULAR BACK BUTTON -->
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
            <span class="text-[#8A7C70] shrink-0">Toko Resmi</span>
            <span>/</span>
            <span class="text-[#2D241E] font-bold truncate max-w-xs sm:max-w-md">{{ $storeInfo['name'] }}</span>
        </nav>
    </div>

    <!-- HERO STORE BANNER & PROFILE HEADER (SHOPEE / TOKOPEDIA STYLE) -->
    <div class="bg-gradient-to-r from-[#2D241E] via-[#452713] to-[#6B4226] rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden"
         x-data="{
             isFollowing: {{ $isFollowingStore ? 'true' : 'false' }},
             followersCount: {{ $storeFollowersCount }},
             followingLoading: false,
             async toggleFollow() {
                 @guest
                     window.location.href = '{{ route('login') }}';
                     return;
                 @endguest

                 if (this.followingLoading) return;
                 this.followingLoading = true;

                 try {
                     const res = await fetch('{{ $storeModel ? route('store.toggle_follow', $storeModel->slug) : '#' }}', {
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
        <!-- Subtle Pattern Background Overlay -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Left: Store Identity -->
            <div class="flex items-center gap-4 sm:gap-5">
                <!-- Store Avatar -->
                <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-4xl sm:text-5xl shadow-inner shrink-0 overflow-hidden">
                    @if(!empty($storeInfo['logo_url']))
                        <img src="{{ $storeInfo['logo_url'] }}" alt="{{ $storeInfo['name'] }}" class="w-full h-full object-cover">
                    @else
                        <span>🏬</span>
                    @endif
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-500 rounded-full text-white flex items-center justify-center text-xs font-bold border-2 border-white shadow-xs" title="Official Verified Store">✓</span>
                </div>

                <!-- Store Info & Badges -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="bg-[#D9381E] text-white text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-2xs">
                            Nusantara Mall
                        </span>
                        <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full backdrop-blur-xs">
                            100% Original
                        </span>
                    </div>

                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight">
                        {{ $storeInfo['name'] }}
                    </h1>

                    <p class="text-xs text-amber-100/80 flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Online</span>
                        </span>
                        <span>•</span>
                        <span x-text="'👥 ' + (followersCount >= 1000 ? (followersCount / 1000).toFixed(1) + 'rb' : followersCount) + ' Pengikut'"></span>
                        <span>•</span>
                        <span>📍 {{ $storeInfo['location'] }}</span>
                    </p>

                    <!-- Store Actions: Follow & Chat -->
                    <div class="flex items-center gap-2.5 pt-1.5">
                        @if($storeModel)
                            <button type="button"
                                    @click="toggleFollow()"
                                    :disabled="followingLoading"
                                    class="px-4 py-1.5 rounded-xl font-bold text-xs flex items-center gap-1.5 transition active:scale-95 shadow-xs cursor-pointer"
                                    :class="isFollowing 
                                        ? 'bg-white/20 text-white border border-white/30 hover:bg-white/30' 
                                        : 'bg-white text-[#6B4226] hover:bg-amber-100'">
                                <span x-text="isFollowing ? '✓' : '➕'"></span>
                                <span x-text="isFollowing ? 'Mengikuti' : 'Ikuti Toko'"></span>
                            </button>
                        @endif

                        <form action="{{ route('chat.start') }}" method="POST">
                            @csrf
                            @if($storeModel)
                                <input type="hidden" name="store_id" value="{{ $storeModel->id }}">
                            @endif
                            <button type="submit" class="px-4 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs flex items-center gap-1.5 border border-white/30 transition active:scale-95 cursor-pointer">
                                <span>💬</span>
                                <span>Chat Toko</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Performance Metrics Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/15 text-center shrink-0">
                <div class="space-y-0.5">
                    <span class="text-[10px] uppercase text-amber-100/70 font-semibold block">Rating Toko</span>
                    <span class="text-sm sm:text-base font-black text-amber-300">★ {{ $storeInfo['rating'] }}</span>
                </div>
                <div class="space-y-0.5 border-l border-white/10">
                    <span class="text-[10px] uppercase text-amber-100/70 font-semibold block">Total Produk</span>
                    <span class="text-sm sm:text-base font-black text-white">{{ $products->count() }} Item</span>
                </div>
                <div class="space-y-0.5 border-l border-white/10">
                    <span class="text-[10px] uppercase text-amber-100/70 font-semibold block">Respon Chat</span>
                    <span class="text-sm sm:text-base font-black text-emerald-300">{{ $storeInfo['chat_response'] }}</span>
                </div>
                <div class="space-y-0.5 border-l border-white/10">
                    <span class="text-[10px] uppercase text-amber-100/70 font-semibold block">Bergabung</span>
                    <span class="text-sm sm:text-base font-black text-white">{{ $storeInfo['joined_since'] }}</span>
                </div>
            </div>

        </div>
    </div>

    <!-- CATALOG SECTION WITH INSTANT CLIENT-SIDE SORTING (NO PAGE RELOAD & NO HISTORY POLLUTION) -->
    <div x-data="{
        currentSort: 'popular',
        products: [
            @foreach($products as $product)
            {
                id: {{ $product->id }},
                name: {{ Js::from($product->name) }},
                slug: {{ Js::from($product->slug) }},
                price: {{ (float) $product->price }},
                discount_price: {{ $product->discount_price ? (float) $product->discount_price : 'null' }},
                effective_price: {{ (float) $product->effective_price }},
                formatted_price: {{ Js::from($product->formatted_price) }},
                formatted_effective_price: {{ Js::from($product->formatted_effective_price) }},
                has_discount: {{ $product->hasDiscount() ? 'true' : 'false' }},
                discount_percentage: {{ round((($product->price - ($product->discount_price ?? $product->price)) / $product->price) * 100) }},
                badge: {{ Js::from(strtolower($product->badge ?? 'star+')) }},
                icon: {{ Js::from($product->category->icon ?? '🛍️') }},
                image_url: {{ Js::from($product->product_image_url) }},
                rating: {{ (float) $product->rating }},
                sold_count: {{ (int) $product->sold_count }},
                sold_count_formatted: {{ Js::from($product->sold_count > 1000 ? round($product->sold_count/1000, 1).'k' : (string) $product->sold_count) }},
                created_at: {{ $product->created_at ? $product->created_at->timestamp : 0 }},
                detail_url: {{ Js::from(route('product.detail', $product->slug)) }}
            },
            @endforeach
        ],
        get sortedProducts() {
            let list = [...this.products];
            if (this.currentSort === 'cheapest') {
                return list.sort((a, b) => a.effective_price - b.effective_price);
            }
            if (this.currentSort === 'expensive') {
                return list.sort((a, b) => b.effective_price - a.effective_price);
            }
            if (this.currentSort === 'newest') {
                return list.sort((a, b) => b.created_at - a.created_at);
            }
            // popular default
            return list.sort((a, b) => b.sold_count - a.sold_count);
        }
    }" class="space-y-4">

        <!-- CATALOG HEADER & SORTING TOOLBAR -->
        <div class="bg-white rounded-2xl border border-[#EAE1D7] p-4 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-2xs">
            <div>
                <h2 class="text-sm sm:text-base font-extrabold text-[#2D241E]">
                    Semua Produk Toko ({{ $products->count() }})
                </h2>
                <p class="text-xs text-[#8A7C70]">Temukan aneka pilihan produk resmi bergaransi dari {{ $storeInfo['name'] }}</p>
            </div>

            <!-- Sorting Buttons (Instant Client-Side, No Page Reload) -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
                <span class="text-xs text-[#8A7C70] font-bold mr-1 shrink-0">Urutkan:</span>
                <button type="button" 
                        @click="currentSort = 'popular'"
                        :class="currentSort === 'popular' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer">
                    Terpopuler
                </button>
                <button type="button" 
                        @click="currentSort = 'newest'"
                        :class="currentSort === 'newest' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer">
                    Terbaru
                </button>
                <button type="button" 
                        @click="currentSort = 'cheapest'"
                        :class="currentSort === 'cheapest' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer">
                    Termurah
                </button>
                <button type="button" 
                        @click="currentSort = 'expensive'"
                        :class="currentSort === 'expensive' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 cursor-pointer">
                    Termahal
                </button>
            </div>
        </div>

        <!-- STORE PRODUCTS GRID -->
        @if($products->count() === 0)
            <div class="bg-white rounded-3xl p-12 text-center border border-[#EAE1D7] shadow-2xs max-w-md mx-auto my-6">
                <div class="w-16 h-16 rounded-full bg-[#FAF7F2] border border-[#EAE1D7] flex items-center justify-center text-3xl mx-auto mb-3">
                    📦
                </div>
                <h3 class="font-extrabold text-sm text-[#2D241E] mb-1">Belum Ada Produk</h3>
                <p class="text-xs text-[#7A6C60] mb-4">Toko ini sedang memperbarui katalog produknya.</p>
                <a href="{{ route('home') }}#katalog" class="px-5 py-2.5 bg-[#6B4226] text-white font-bold rounded-xl text-xs shadow-xs hover:bg-[#54321B] transition inline-block">
                    Lihat Semua Produk
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3.5 sm:gap-4">
                <template x-for="product in sortedProducts" :key="product.id">
                    <a :href="product.detail_url" 
                       class="bg-white rounded-3xl border border-[#EAE1D7] p-3 sm:p-3.5 shadow-2xs hover:shadow-xl hover:border-[#6B4226]/40 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group relative overflow-hidden block">
                        
                        <div>
                            <!-- Visual Stage Container -->
                            <div class="relative h-36 sm:h-40 rounded-2xl bg-gradient-to-b from-[#FAF8F5] to-[#FAF4ED] border border-[#F2EAE0] flex items-center justify-center p-3 overflow-hidden mb-3 group-hover:scale-102 transition duration-300">
                                
                                <!-- Discount Badge (Top Left) -->
                                <template x-if="product.has_discount">
                                    <div class="absolute top-2 left-2 z-10 flex items-center gap-1">
                                        <span class="bg-[#D9381E] text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-2xs"
                                              x-text="'-' + product.discount_percentage + '%'">
                                        </span>
                                    </div>
                                </template>

                                <!-- Store Badge (Top Right) -->
                                <div class="absolute top-2 right-2 z-10">
                                    <span x-show="product.badge === 'mall'" 
                                          class="bg-[#D9381E] text-white text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">
                                        Mall
                                    </span>
                                    <span x-show="product.badge === 'official'" 
                                          class="bg-[#1E56A0] text-white text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">
                                        Official
                                    </span>
                                    <span x-show="product.badge !== 'mall' && product.badge !== 'official'" 
                                          class="bg-[#D4820A] text-white text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">
                                        Star+
                                    </span>
                                </div>

                                <!-- Product Photo / Icon -->
                                <template x-if="product.image_url">
                                    <div class="w-full h-full p-2 flex items-center justify-center">
                                        <img :src="product.image_url" :alt="product.name" class="max-h-full max-w-full object-contain rounded-xl group-hover:scale-105 transition duration-300">
                                    </div>
                                </template>
                                <template x-if="!product.image_url">
                                    <div class="text-4xl sm:text-5xl filter drop-shadow-sm group-hover:scale-110 transition duration-300">
                                        <span x-text="product.icon"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Product Info -->
                            <div class="space-y-1.5 mb-2.5">
                                <!-- Product Title -->
                                <h3 class="font-bold text-xs text-[#2D241E] line-clamp-2 leading-snug group-hover:text-[#6B4226] transition min-h-[32px]" 
                                    :title="product.name"
                                    x-text="product.name">
                                </h3>

                                <!-- Price Block -->
                                <div class="pt-0.5">
                                    <div class="min-h-[14px] flex items-center">
                                        <template x-if="product.has_discount">
                                            <span class="text-[10px] text-[#9E9084] line-through leading-none"
                                                  x-text="product.formatted_price">
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex items-baseline gap-1.5 flex-wrap mt-0.5">
                                        <span class="font-black text-sm sm:text-base text-[#6B4226]"
                                              x-text="product.formatted_effective_price">
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
                                <span class="font-bold text-[#2D241E]" x-text="product.rating.toFixed(1)"></span>
                            </div>
                            <span class="font-medium text-[#8A7C70]" x-text="product.sold_count_formatted + ' terjual'"></span>
                        </div>
                    </a>
                </template>
            </div>
        @endif

    </div>

</div>
@endsection
