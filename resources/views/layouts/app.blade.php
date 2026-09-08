<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NusantaraMart — Marketplace Belanja Online Pilihan')</title>
    <meta name="description" content="NusantaraMart adalah marketplace belanja online modern dan terpercaya di Indonesia. Dapatkan produk pilihan dengan promo gratis ongkir dan jaminan keaslian.">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Alpine.js for Reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #FAF8F5;
            color: #2D241E;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-[#FAF8F5] text-[#2D241E] antialiased selection:bg-[#6B4226] selection:text-white"
      x-data="snackCart()"
      x-init="initCart()"
      @add-to-cart.window="addToCart($event.detail.product, $event.detail.quantity)"
      @require-auth.window="showLoginRequiredModal($event.detail)">

    <!-- Main Navigation Header (Hidden on Search Discovery Page) -->
    @unless(request()->routeIs('search'))
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#EAE1D7] shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4 sm:gap-8">
                
                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group shrink-0">
                    <div class="w-10 h-10 rounded-2xl bg-[#6B4226] flex items-center justify-center text-white text-xl shadow-sm group-hover:scale-105 transition duration-300">
                        🛍️
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl sm:text-2xl font-black tracking-tight text-[#2D241E] leading-none">
                            Nusantara<span class="text-[#6B4226]">Mart</span>
                        </span>
                        <span class="text-[9px] uppercase tracking-widest text-[#8A7C70] font-bold mt-1">Official Marketplace</span>
                    </div>
                </a>

                <!-- Global Search Input (Only on Home Page) -->
                @if(request()->routeIs('home'))
                <div class="flex-1 max-w-2xl hidden md:block">
                    <form action="{{ route('search') }}" method="GET" class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 sm:pl-5 flex items-center pointer-events-none text-[#8A7C70]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               name="q"
                               value="{{ request('q', '') }}"
                               oninput="window.location.href = '{{ route('search') }}?q=' + encodeURIComponent(this.value)"
                               placeholder="Cari produk apa saja di NusantaraMart..."
                               class="w-full pl-12 sm:pl-14 pr-24 sm:pr-28 py-3 sm:py-3.5 bg-[#FAF7F2] hover:bg-[#F5F0E8] focus:bg-white text-[#2D241E] placeholder:text-[#9E9084] text-xs sm:text-sm rounded-2xl border border-[#E8DED3] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition shadow-2xs">
                        
                        <div class="absolute right-2 flex items-center">
                            <button type="submit" 
                                    class="px-4 sm:px-5 py-2 bg-[#6B4226] hover:bg-[#54321B] text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                Cari
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Right Action Icons: Chat, Cart & Profile -->
                <div class="flex items-center gap-2.5 shrink-0">

                    <!-- Chat Button with Counter -->
                    @auth
                        <a href="{{ route('chat.index') }}" 
                           x-data="chatBadge()"
                           class="relative w-10 h-10 rounded-2xl bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#5A4B40] hover:text-[#6B4226] border border-[#EAE1D7] hover:border-[#6B4226]/40 flex items-center justify-center transition active:scale-95 shadow-2xs group"
                           title="Chat & Pesan Penjual">
                            <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h8M8 14h5M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <!-- Live badge count -->
                            <span x-show="unreadCount > 0" 
                                  x-cloak
                                  x-text="unreadCount" 
                                  class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 bg-[#6B4226] text-white font-black text-[10px] rounded-full flex items-center justify-center border-2 border-white shadow-xs animate-scale"></span>
                        </a>
                    @endauth

                    <!-- Cart Button with Counter -->
                    <a href="{{ route('cart') }}" 
                       class="relative w-10 h-10 rounded-2xl bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#5A4B40] hover:text-[#6B4226] border border-[#EAE1D7] hover:border-[#6B4226]/40 flex items-center justify-center transition active:scale-95 shadow-2xs group"
                       title="Keranjang Belanja">
                        <svg class="w-5 h-5 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <!-- Live badge count from Alpine -->
                        <span x-show="totalCount > 0" 
                              x-cloak
                              x-text="totalCount" 
                              class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 bg-[#6B4226] text-white font-black text-[10px] rounded-full flex items-center justify-center border-2 border-white shadow-xs animate-scale"></span>
                    </a>

                    <!-- User Authentication Control -->
                    @guest
                        <div class="flex items-center gap-2 ml-1">
                            <a href="{{ route('login') }}" 
                               class="px-4 py-2 text-xs font-bold text-[#5A4B40] hover:text-[#6B4226] transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" 
                               class="px-4 py-2 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs rounded-xl shadow-xs transition transform active:scale-95">
                                Daftar Akun
                            </a>
                        </div>
                    @else
                        <!-- Dedicated Admin Quick Button in Navbar (Simpel & Serasi) -->
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" 
                               class="relative w-10 h-10 rounded-2xl bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#5A4B40] hover:text-[#6B4226] border border-[#EAE1D7] hover:border-[#6B4226]/40 flex items-center justify-center transition active:scale-95 shadow-2xs group"
                               title="Panel Dashboard Admin">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-white"></span>
                            </a>
                        @endif

                        <!-- Logged In User Circular Avatar Button -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    @click.away="open = false" 
                                    class="w-10 h-10 rounded-full bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-sm flex items-center justify-center border-2 border-white shadow-xs hover:ring-2 hover:ring-[#6B4226]/40 transition transform active:scale-95 cursor-pointer overflow-hidden"
                                    title="Menu Akun Saya ({{ Auth::user()->name }})">
                                @if(Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                                @else
                                    <span>{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                @endif
                            </button>

                            <!-- Dropdown Menu -->
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
                                    <div class="flex items-center justify-between">
                                        <p class="text-[11px] text-[#8A7C70] font-medium">Masuk sebagai</p>
                                        @if(Auth::user()->isAdmin())
                                            <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-900 font-extrabold text-[9px]">ADMIN</span>
                                        @elseif(Auth::user()->isSeller())
                                            <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-900 font-extrabold text-[9px]">PENJUAL</span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-black text-[#2D241E] truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-[10px] text-[#8A7C70] font-mono">{{ Auth::user()->email }}</p>
                                </div>

                                @if(Auth::user()->isAdmin())
                                    <div class="py-1 bg-amber-50/50">
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-800 hover:bg-amber-100/70 transition">
                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            <span>Panel Operasi Admin</span>
                                        </a>
                                    </div>
                                @endif

                                <div class="py-1">
                                    @if(Auth::user()->isSeller())
                                        <a href="{{ route('seller.dashboard') }}" 
                                           class="flex items-center justify-between px-4 py-2 text-xs font-bold text-[#6B4226] hover:bg-[#FAF4ED] transition">
                                            <div class="flex items-center gap-2.5">
                                                <span>🏬</span>
                                                <span>Seller Center Toko</span>
                                            </div>
                                            <span x-data="sellerOrderBadge()"
                                                  x-show="pendingOrdersCount > 0"
                                                  x-cloak
                                                  x-text="pendingOrdersCount"
                                                  class="px-1.5 py-0.2 bg-rose-600 text-white text-[9px] font-bold rounded-full ml-1 shadow-2xs"></span>
                                        </a>
                                    @endif

                                    <a href="{{ route('my.orders') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-[#5A4B40] hover:bg-[#FAF4ED] hover:text-[#6B4226] transition">
                                        <span>📦</span>
                                        <span>Pesanan Saya</span>
                                    </a>
                                    <a href="{{ route('settings') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-[#5A4B40] hover:bg-[#FAF4ED] hover:text-[#6B4226] transition">
                                        <span>⚙️</span>
                                        <span>Pengaturan Akun</span>
                                    </a>
                                </div>

                                <div class="py-1">
                                    <form action="{{ route('logout') }}" method="POST" onsubmit="handleLogoutCart()">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50 transition text-left cursor-pointer">
                                            <span>🚪</span>
                                            <span>Keluar (Logout)</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endguest
                </div>

            </div>
        </div>
    </header>
    @endunless

    <!-- Global Flash Messages (Auto-dismiss after 5 seconds) -->
    @unless(request()->routeIs('order.detail'))
        @if(session('success') || session('warning') || session('error') || session('status'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
                @if(session('success'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         x-init="setTimeout(() => show = false, 5000)"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-4"
                         x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                         class="overflow-hidden p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold flex items-center justify-between gap-2.5 shadow-2xs mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">✅</span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-800 font-bold p-1 cursor-pointer transition" title="Tutup Notifikasi">✕</button>
                    </div>
                @endif

                @if(session('warning'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         x-init="setTimeout(() => show = false, 5000)"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-4"
                         x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                         class="overflow-hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm font-bold flex items-center justify-between gap-2.5 shadow-2xs mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">⚠️</span>
                            <span>{{ session('warning') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-amber-500 hover:text-amber-800 font-bold p-1 cursor-pointer transition" title="Tutup Notifikasi">✕</button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         x-init="setTimeout(() => show = false, 5000)"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-4"
                         x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                         class="overflow-hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-bold flex items-center justify-between gap-2.5 shadow-2xs mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">🚫</span>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-800 font-bold p-1 cursor-pointer transition" title="Tutup Notifikasi">✕</button>
                    </div>
                @endif

                @if(session('status'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         x-init="setTimeout(() => show = false, 5000)"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-4"
                         x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                         class="overflow-hidden p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-xs sm:text-sm font-bold flex items-center justify-between gap-2.5 shadow-2xs mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">ℹ️</span>
                            <span>{{ session('status') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-sky-500 hover:text-sky-800 font-bold p-1 cursor-pointer transition" title="Tutup Notifikasi">✕</button>
                    </div>
                @endif
            </div>
        @endif
    @endunless

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Quick Toast Notification with Link to Cart -->
    <div x-show="toast.show" 
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-[#6B4226] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3.5 border border-[#54321B]">
        <span class="text-2xl">🛍️</span>
        <div class="flex items-center gap-3">
            <p class="text-xs font-bold" x-text="toast.message"></p>
            <a href="{{ route('cart') }}" class="px-3 py-1 bg-white hover:bg-[#FAF4ED] text-[#6B4226] text-xs font-bold rounded-lg transition shrink-0">
                Lihat Keranjang →
            </a>
        </div>
    </div>

    <!-- Clean & Minimalist Auth Modal -->
    <div x-show="loginModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="login-modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="loginModalOpen" 
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"
             @click="closeAuthModal()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="loginModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm border border-[#EAE1D7] p-6">
                
                <!-- Close Button -->
                <button type="button" 
                        @click="closeAuthModal()"
                        class="absolute top-4 right-4 text-[#8A7C70] hover:text-[#2D241E] p-1 rounded-lg hover:bg-[#FAF8F5] transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <div class="text-center pt-2">
                    <!-- Clean Simple Icon -->
                    <div class="w-12 h-12 mx-auto rounded-full bg-[#FAF4ED] text-[#6B4226] flex items-center justify-center text-2xl mb-3.5">
                        <span x-text="authModal.icon || '🔒'"></span>
                    </div>

                    <!-- Clean Title & Message -->
                    <h3 class="text-base sm:text-lg font-bold text-[#2D241E] mb-1.5" 
                        id="login-modal-title"
                        x-text="authModal.title">
                    </h3>
                    <p class="text-xs sm:text-sm text-[#7A6C60] leading-relaxed mb-6"
                       x-text="authModal.message">
                    </p>

                    <!-- Simple Action Buttons -->
                    <div class="space-y-2">
                        <a href="{{ route('login') }}"
                           class="w-full py-2.5 px-4 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs sm:text-sm flex items-center justify-center transition cursor-pointer shadow-2xs">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}"
                           class="w-full py-2.5 px-4 rounded-xl border border-[#EAE1D7] hover:bg-[#FAF8F5] text-[#5A4B40] font-semibold text-xs sm:text-sm flex items-center justify-center transition cursor-pointer">
                            Daftar Akun
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ultra-Premium Marketplace Footer (Hidden on Official Invoice Detail) -->
    @unless(request()->routeIs('order.detail'))
    <footer class="bg-gradient-to-b from-[#54321B] via-[#422210] to-[#2D160A] text-[#FAF4ED] pt-14 pb-12 border-t border-[#6B4226]/40 mt-20 relative overflow-hidden">
        
        <!-- Decorative Ambient Glow -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-px bg-gradient-to-r from-transparent via-amber-300/40 to-transparent"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- MAIN FOOTER CONTENT GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-10 pb-12 border-b border-white/10">
                
                <!-- Column 1: Brand & Bio (4 cols) -->
                <div class="lg:col-span-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-[#6B4226] to-[#8C5835] border border-amber-300/30 text-white flex items-center justify-center text-2xl shadow-lg">
                            🛍️
                        </div>
                        <div>
                            <span class="text-2xl font-black tracking-tight text-white">Nusantara<span class="text-amber-300">Mart</span></span>
                            <span class="block text-[10px] font-bold text-amber-200 uppercase tracking-widest">Marketplace Pilihan Indonesia</span>
                        </div>
                    </div>

                    <p class="text-xs text-[#E0D0C5] leading-relaxed pr-4">
                        Platform e-commerce serba ada terpercaya di Indonesia. Menyediakan jutaan produk berkualitas mulai dari gadget, fashion, kuliner hingga kebutuhan harian dengan perlindungan pembeli 100%.
                    </p>

                    <!-- Trust Badges Pills -->
                    <div class="flex flex-wrap gap-2 pt-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 hover:bg-white/15 border border-white/15 rounded-full text-[11px] font-bold text-amber-200 transition">
                            <span>✓</span> Official Store Partner
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 hover:bg-white/15 border border-white/15 rounded-full text-[11px] font-bold text-emerald-300 transition">
                            <span>🔒</span> SSL 256-Bit Encrypted
                        </span>
                    </div>
                </div>

                <!-- Column 2: Kategori Belanja (3 cols) -->
                <div class="lg:col-span-3 space-y-3.5">
                    <h4 class="font-extrabold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-300"></span>
                        <span>Kategori Belanja</span>
                    </h4>
                    <ul class="space-y-2 text-xs text-[#D8C4B6]">
                        <li>
                            <a href="{{ route('home') }}?category=elektronik-gadget#katalog" class="hover:text-white hover:translate-x-1 transition inline-flex items-center gap-2">
                                <span>📱</span> <span>Elektronik & Gadget</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('home') }}?category=fashion-pakaian#katalog" class="hover:text-white hover:translate-x-1 transition inline-flex items-center gap-2">
                                <span>👗</span> <span>Fashion & Busana</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('home') }}?category=makanan-minuman#katalog" class="hover:text-white hover:translate-x-1 transition inline-flex items-center gap-2">
                                <span>🍜</span> <span>Makanan & Kuliner</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('home') }}?category=kecantikan-skincare#katalog" class="hover:text-white hover:translate-x-1 transition inline-flex items-center gap-2">
                                <span>💄</span> <span>Kecantikan & Skincare</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('home') }}?category=rumah-tangga-dapur#katalog" class="hover:text-white hover:translate-x-1 transition inline-flex items-center gap-2">
                                <span>🏠</span> <span>Rumah Tangga & Dapur</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('home') }}?category=kerajinan-nusantara#katalog" class="hover:text-white hover:translate-x-1 transition inline-flex items-center gap-2">
                                <span>🎁</span> <span>Kerajinan Nusantara</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 3: Layanan Pelanggan (2 cols) -->
                <div class="lg:col-span-2 space-y-3.5">
                    <h4 class="font-extrabold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-300"></span>
                        <span>Bantuan & CS</span>
                    </h4>
                    <ul class="space-y-2 text-xs text-[#D8C4B6]">
                        <li><a href="{{ route('home') }}#faq" class="hover:text-white hover:underline transition">Pusat Bantuan (FAQ)</a></li>
                        <li><a href="{{ route('home') }}#faq" class="hover:text-white hover:underline transition">Panduan Pembayaran</a></li>
                        <li><a href="{{ route('home') }}#faq" class="hover:text-white hover:underline transition">Lacak Pengiriman</a></li>
                        <li><a href="{{ route('home') }}#faq" class="hover:text-white hover:underline transition">Kebijakan Garansi</a></li>
                        <li>
                            <a href="https://wa.me/6281234567890?text=Halo%20NusantaraMart%20mau%20tanya%20produk" target="_blank" class="inline-flex items-center gap-1.5 text-emerald-400 font-bold hover:text-emerald-300 transition">
                                <span>💬</span> <span>WhatsApp 24/7</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 4: Metode Pembayaran & Kurir Pengiriman (3 cols) -->
                <div class="lg:col-span-3 space-y-4">
                    <!-- Payments -->
                    <div>
                        <h4 class="font-extrabold text-xs text-white uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                            <span>💳</span> <span>Metode Pembayaran</span>
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1.5 bg-white rounded-xl text-[#2D241E] text-xs font-black shadow-2xs flex items-center gap-1.5">
                                <span>📱</span> <span>QRIS Instan</span>
                            </span>
                            <span class="px-3 py-1.5 bg-white rounded-xl text-[#6B4226] text-xs font-black shadow-2xs flex items-center gap-1.5">
                                <span>💵</span> <span>COD (Bayar di Tempat)</span>
                            </span>
                        </div>
                    </div>

                    <!-- Couriers -->
                    <div class="pt-1">
                        <h4 class="font-extrabold text-xs text-white uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                            <span>🚚</span> <span>Mitra Pengiriman Resmi</span>
                        </h4>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="px-2 py-1 bg-white/15 border border-white/10 rounded-lg text-white text-[10px] font-bold">J&T Express</span>
                            <span class="px-2 py-1 bg-white/15 border border-white/10 rounded-lg text-white text-[10px] font-bold">SiCepat</span>
                            <span class="px-2 py-1 bg-white/15 border border-white/10 rounded-lg text-white text-[10px] font-bold">JNE</span>
                            <span class="px-2 py-1 bg-white/15 border border-white/10 rounded-lg text-white text-[10px] font-bold">Ninja Xpress</span>
                            <span class="px-2 py-1 bg-white/15 border border-white/10 rounded-lg text-white text-[10px] font-bold">Anteraja</span>
                            <span class="px-2 py-1 bg-white/15 border border-white/10 rounded-lg text-white text-[10px] font-bold">GoSend</span>
                            <span class="px-2 py-1 bg-white/15 border border-white/10 rounded-lg text-white text-[10px] font-bold">GrabExpress</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- 3. BOTTOM COPYRIGHT & SECURITY BAR -->
            <div class="pt-8 flex flex-col sm:flex-row justify-between items-center text-xs text-[#D8C4B6] gap-4">
                <p>© 2026 <strong class="text-white">NusantaraMart Official</strong>. All rights reserved.</p>
                
                <div class="flex items-center gap-4 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-emerald-300 font-bold">
                        <span>🛡️</span> <span>Transaksi 100% Aman Terproteksi</span>
                    </span>
                    <span class="text-white/30 hidden sm:inline">•</span>
                    <span class="text-[#D8C4B6] hidden sm:inline">Hak Cipta Dilindungi Undang-Undang</span>
                </div>
            </div>

        </div>
    </footer>
    @endunless

    <!-- Cart Javascript Handler -->
    <script>
        const currentAuthUserId = {{ Auth::id() ? Auth::id() : 'null' }};
        const currentAuthStoreId = {{ Auth::user()?->store?->id ? Auth::user()->store->id : 'null' }};

        function getCartStorageKey() {
            return currentAuthUserId ? `nusantaramart_cart_user_${currentAuthUserId}` : 'nusantaramart_cart_guest';
        }

        function handleLogoutCart() {
            try {
                // Clear guest cart and any residual global keys upon logout
                localStorage.removeItem('nusantaramart_cart_guest');
                localStorage.removeItem('nusantaramart_cart');
                localStorage.removeItem('snackaroo_cart');
            } catch (e) {
                console.error(e);
            }
        }

        function snackCart() {
            return {
                items: [],
                loginModalOpen: false,
                authModal: {
                    icon: '🛒',
                    title: 'Masuk Terlebih Dahulu',
                    message: 'Kamu perlu masuk (login) ke akunmu terlebih dahulu untuk memasukkan produk ke keranjang belanja dan menikmati transaksi yang aman.'
                },
                toast: {
                    show: false,
                    message: ''
                },
                showLoginRequiredModal(options = {}) {
                    this.authModal.icon = (options && options.icon) || '🛒';
                    this.authModal.title = (options && options.title) || 'Masuk Terlebih Dahulu';
                    this.authModal.message = (options && options.message) || 'Kamu perlu masuk (login) ke akunmu terlebih dahulu untuk memasukkan produk ke keranjang belanja dan menikmati transaksi yang aman.';
                    this.loginModalOpen = true;
                },
                closeAuthModal() {
                    this.loginModalOpen = false;
                },
                initCart() {
                    window.snackCart = this;

                    // Guest users cannot store or add items to cart
                    if (!currentAuthUserId) {
                        this.items = [];
                        try {
                            localStorage.removeItem('nusantaramart_cart_guest');
                            localStorage.removeItem('nusantaramart_cart');
                            localStorage.removeItem('snackaroo_cart');
                        } catch (e) {}
                        return;
                    }

                    const key = getCartStorageKey();

                    // For logged-in user: migrate once from legacy global key if user cart not yet initialized
                    const userSaved = localStorage.getItem(key);
                    if (!userSaved) {
                        const oldShared = localStorage.getItem('nusantaramart_cart') || localStorage.getItem('snackaroo_cart');
                        if (oldShared) {
                            localStorage.setItem(key, oldShared);
                        }
                    }

                    // Always clean up legacy shared keys so they never leak to guests or other users
                    localStorage.removeItem('nusantaramart_cart');
                    localStorage.removeItem('snackaroo_cart');

                    const saved = localStorage.getItem(key);
                    if (saved) {
                        try {
                            this.items = JSON.parse(saved);
                        } catch(e) {
                            this.items = [];
                        }
                    } else {
                        this.items = [];
                    }
                },
                saveCart() {
                    if (!currentAuthUserId) return;
                    const key = getCartStorageKey();
                    localStorage.setItem(key, JSON.stringify(this.items));
                },
                addToCart(product, quantity = 1) {
                    if (!currentAuthUserId) {
                        this.showLoginRequiredModal();
                        return;
                    }

                    if (currentAuthStoreId && product.store_id && Number(product.store_id) === Number(currentAuthStoreId)) {
                        this.showToast('Kamu tidak dapat membeli produk dari tokomu sendiri. 🏪');
                        return;
                    }

                    const qty = parseInt(quantity) || 1;
                    const cartKey = product.variant_id ? `${product.id}_${product.variant_id}` : `${product.id}`;
                    const existing = this.items.find(i => (i.cartKey || i.id) == cartKey);
                    if (existing) {
                        existing.quantity += qty;
                        existing.selected = true;
                    } else {
                        this.items.push({
                            id: product.id,
                            store_id: product.store_id || null,
                            cartKey: cartKey,
                            name: product.name,
                            price: product.price,
                            brand: product.brand || 'NusantaraMart',
                            badge: product.badge || 'Official',
                            icon: product.icon || '🛍️',
                            image_url: product.image_url || null,
                            variant_id: product.variant_id || null,
                            variant_name: product.variant_name || null,
                            quantity: qty,
                            selected: true
                        });
                    }
                    this.saveCart();
                    const variantText = product.variant_name ? ` (${product.variant_name})` : '';
                    this.showToast(`"${product.name}${variantText}" (${qty} item) ditambahkan ke keranjang belanja! 🛍️`);
                },
                buyNow(product, quantity = 1) {
                    if (!currentAuthUserId) {
                        this.showLoginRequiredModal();
                        return;
                    }

                    if (currentAuthStoreId && product.store_id && Number(product.store_id) === Number(currentAuthStoreId)) {
                        this.showToast('Kamu tidak dapat membeli produk dari tokomu sendiri. 🏪');
                        return;
                    }

                    const qty = parseInt(quantity) || 1;
                    const cartKey = product.variant_id ? `${product.id}_${product.variant_id}` : `${product.id}`;
                    
                    // Unselect other items so only this product is purchased now
                    this.items.forEach(i => i.selected = false);

                    const existing = this.items.find(i => (i.cartKey || i.id) == cartKey);
                    if (existing) {
                        existing.quantity = qty;
                        existing.selected = true;
                    } else {
                        this.items.push({
                            id: product.id,
                            store_id: product.store_id || null,
                            cartKey: cartKey,
                            name: product.name,
                            price: product.price,
                            brand: product.brand || 'NusantaraMart',
                            badge: product.badge || 'Official',
                            icon: product.icon || '🛍️',
                            image_url: product.image_url || null,
                            variant_id: product.variant_id || null,
                            variant_name: product.variant_name || null,
                            quantity: qty,
                            selected: true
                        });
                    }
                    this.saveCart();
                },
                updateQty(key, delta) {
                    const item = this.items.find(i => (i.cartKey || i.id) == key);
                    if (item) {
                        item.quantity += delta;
                        if (item.quantity <= 0) {
                            this.items = this.items.filter(i => (i.cartKey || i.id) != key);
                        }
                        this.saveCart();
                    }
                },
                removeItem(key) {
                    this.items = this.items.filter(i => (i.cartKey || i.id) != key);
                    this.saveCart();
                },
                get totalCount() {
                    return this.items.reduce((sum, item) => sum + item.quantity, 0);
                },
                get totalAmount() {
                    return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                formatRupiah(num) {
                    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
                },
                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                }
            }
        }

        window.showAuthModal = function(options = {}) {
            if (window.snackCart && typeof window.snackCart.showLoginRequiredModal === 'function') {
                window.snackCart.showLoginRequiredModal(options);
            } else {
                window.location.href = '{{ route('login') }}';
            }
        };

        window.addToCartGlobal = function(product, quantity = 1) {
            if (!currentAuthUserId) {
                window.showAuthModal({
                    icon: '🛒',
                    title: 'Masukkan ke Keranjang',
                    message: 'Yuk masuk ke akunmu terlebih dahulu untuk menyimpan produk pilihanmu ke keranjang belanja.'
                });
                return;
            }
            if (window.snackCart) {
                window.snackCart.addToCart(product, quantity);
            }
        };

        function navSearchComponent(initialQuery) {
            return {
                query: initialQuery || '',
                suggestions: [],
                storeQuery: '',
                showSuggestions: false,
                debounceTimer: null,
                fetchNavSuggestions() {
                    const q = this.query ? this.query.trim() : '';
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
                selectNavSuggestion(item) {
                    this.query = item;
                    this.showSuggestions = false;
                    window.location.href = '{{ route('search.results') }}?q=' + encodeURIComponent(item);
                },
                highlightNavMatch(text, qStr) {
                    if (!qStr || !qStr.trim() || !text) return text || '';
                    const q = qStr.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const regex = new RegExp('(' + q + ')', 'gi');
                    return text.replace(regex, '<b class="font-black text-[#2D241E]">$1</b>');
                }
            };
        }

        // SMART PAGE NAVIGATOR (Anti-Loop Navigation & Breadcrumb Trail Stack)
        window.smartNav = {
            getTrail() {
                try {
                    return JSON.parse(sessionStorage.getItem('nm_nav_trail') || '[]');
                } catch(e) {
                    return [];
                }
            },
            setTrail(trail) {
                try {
                    sessionStorage.setItem('nm_nav_trail', JSON.stringify(trail.slice(-15)));
                } catch(e) {}
            },
            recordVisit() {
                const currentPath = window.location.pathname + window.location.search;
                let trail = this.getTrail();

                // Ignore consecutive duplicates
                if (trail.length > 0 && trail[trail.length - 1] === currentPath) {
                    return;
                }

                // If path already exists earlier in trail, prune the loop!
                const existingIndex = trail.indexOf(currentPath);
                if (existingIndex !== -1) {
                    trail = trail.slice(0, existingIndex);
                }

                trail.push(currentPath);
                this.setTrail(trail);
            },
            goBack(fallbackUrl = '{{ route('home') }}') {
                let trail = this.getTrail();
                const currentPath = window.location.pathname + window.location.search;

                // Pop current page
                while (trail.length > 0 && trail[trail.length - 1] === currentPath) {
                    trail.pop();
                }

                let target = null;
                while (trail.length > 0) {
                    const candidate = trail.pop();
                    if (candidate && candidate !== currentPath) {
                        target = candidate;
                        break;
                    }
                }

                this.setTrail(trail);

                if (target) {
                    window.location.href = target;
                } else if (document.referrer && !document.referrer.includes(window.location.pathname)) {
                    window.location.href = document.referrer;
                } else {
                    window.location.href = fallbackUrl;
                }
            }
        };

        // Automatically record page visit on load
        window.smartNav.recordVisit();

        function chatBadge() {
            return {
                unreadCount: 0,
                init() {
                    @auth
                        this.checkUnread();
                        setInterval(() => this.checkUnread(), 8000);
                    @endauth
                },
                checkUnread() {
                    fetch('{{ route('chat.unread_count') }}')
                        .then(r => r.json())
                        .then(d => { this.unreadCount = d.unread_count || 0; })
                        .catch(() => {});
                }
            };
        }

        @if(Auth::check() && Auth::user()->isSeller())
        function playOrderChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const audioCtx = new AudioContext();
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, audioCtx.currentTime);
                gain1.gain.setValueAtTime(0.2, audioCtx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.28);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start(audioCtx.currentTime);
                osc1.stop(audioCtx.currentTime + 0.28);

                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, audioCtx.currentTime + 0.14);
                gain2.gain.setValueAtTime(0.25, audioCtx.currentTime + 0.14);
                gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start(audioCtx.currentTime + 0.14);
                osc2.stop(audioCtx.currentTime + 0.5);
            } catch(e) {}
        }

        function sellerOrderBadge() {
            return {
                pendingOrdersCount: 0,
                init() {
                    this.checkOrders();
                    window.addEventListener('seller-order-count-updated', (e) => {
                        this.pendingOrdersCount = e.detail?.count || 0;
                    });
                },
                checkOrders() {
                    fetch('{{ route('seller.orders.notifications_check') }}')
                        .then(r => r.json())
                        .then(d => { this.pendingOrdersCount = d.pending_orders_count || 0; })
                        .catch(() => {});
                }
            };
        }

        function sellerOrderNotifier(storeId) {
            return {
                showToast: false,
                orderData: null,
                dismissTimer: null,
                init() {
                    if (!storeId) return;
                    this.poll();
                    setInterval(() => this.poll(), 8000);
                },
                poll() {
                    fetch('{{ route('seller.orders.notifications_check') }}')
                        .then(r => r.json())
                        .then(d => {
                            window.dispatchEvent(new CustomEvent('seller-order-count-updated', {
                                detail: { count: d.pending_orders_count || 0 }
                            }));

                            if (d.latest_order && d.latest_order.id) {
                                const key = 'seen_seller_orders_' + storeId;
                                const seenIds = JSON.parse(localStorage.getItem(key) || '[]');
                                
                                if (!seenIds.includes(d.latest_order.id)) {
                                    seenIds.push(d.latest_order.id);
                                    if (seenIds.length > 50) seenIds.shift();
                                    localStorage.setItem(key, JSON.stringify(seenIds));

                                    this.orderData = d.latest_order;
                                    this.showToast = true;
                                    playOrderChime();

                                    clearTimeout(this.dismissTimer);
                                    this.dismissTimer = setTimeout(() => {
                                        this.showToast = false;
                                    }, 12000);
                                }
                            }
                        })
                        .catch(() => {});
                },
                dismissToast() {
                    this.showToast = false;
                    clearTimeout(this.dismissTimer);
                }
            };
        }
        @endif
    </script>

    <!-- Floating Quick Chat Dock (Bottom Right) -->
    @auth
        @if(!request()->routeIs('chat.*'))
            <a href="{{ route('chat.index') }}" 
               x-data="chatBadge()"
               class="fixed bottom-6 right-6 z-40 bg-[#6B4226] hover:bg-[#54321B] text-white px-4 py-3 rounded-full shadow-lg hover:shadow-xl transition-all flex items-center gap-2.5 active:scale-95 group cursor-pointer border-2 border-white">
                <div class="relative">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span x-show="unreadCount > 0"
                          x-cloak
                          x-text="unreadCount"
                          class="absolute -top-2 -right-2 w-4.5 h-4.5 bg-red-500 text-white font-bold text-[10px] rounded-full flex items-center justify-center border border-white"></span>
                </div>
                <span class="text-xs font-black tracking-wide hidden sm:inline">Chat Penjual</span>
            </a>
        @endif
    @endauth

    @if(Auth::check() && Auth::user()->isSeller())
        <!-- REALTIME NEW ORDER FLOATING ALERT TOAST (SELLER) -->
        <div x-data="sellerOrderNotifier({{ Auth::user()->store?->id ?? 'null' }})"
             x-cloak
             x-show="showToast"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed bottom-22 right-6 z-50 max-w-sm w-full bg-white/95 backdrop-blur-md border-2 border-emerald-500 rounded-3xl shadow-2xl p-4.5 flex items-start gap-3.5"
             style="display: none;">
            <div class="relative shrink-0">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center text-2xl shadow-md">
                    🛍️
                </div>
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white animate-ping"></span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300">
                        Pesanan Baru Masuk! 🎉
                    </span>
                    <button type="button" @click="dismissToast()" class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold transition cursor-pointer">✕</button>
                </div>
                <h4 class="text-xs font-black text-[#2D241E] truncate" x-text="'Kode: #' + (orderData?.order_code || '')"></h4>
                <p class="text-[11px] text-[#5A4B40] mt-0.5 leading-snug">
                    Dari <strong class="text-[#2D241E]" x-text="orderData?.customer_name || 'Pelanggan'"></strong> • <span class="font-bold text-emerald-700" x-text="orderData?.grand_total || ''"></span>
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <a :href="orderData?.url || '{{ route('seller.orders') }}'"
                       class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 flex items-center gap-1">
                        <span>Buka Pesanan</span>
                        <span>→</span>
                    </a>
                    <button type="button" @click="dismissToast()" class="px-2.5 py-1.5 text-[11px] text-[#8A7C70] hover:text-[#2D241E] font-semibold cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
