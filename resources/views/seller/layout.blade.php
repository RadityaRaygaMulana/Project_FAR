<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FAF8F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Seller Center — NusantaraMart')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Alpine.js for Reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine.js cloak helper -->
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col antialiased text-[#2D241E] bg-[#FAF8F5]">

    <!-- SELLER CENTER TOPBAR -->
    <header class="bg-white border-b border-[#EAE1D7] sticky top-0 z-40 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Left: Brand + Store Identity -->
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-2 text-[#6B4226] font-black text-base sm:text-lg tracking-tight">
                    <span class="w-9 h-9 rounded-xl bg-[#6B4226] text-white flex items-center justify-center text-lg shadow-xs">🏪</span>
                    <span class="hidden sm:inline">Seller Center</span>
                </a>
                <span class="text-[#EAE1D7] hidden sm:inline">|</span>
                @php $store = auth()->user()->store; @endphp
                @if($store)
                    <a href="{{ route('seller.settings') }}" class="flex items-center gap-2 bg-[#FAF8F5] hover:bg-[#FAF4ED] border border-[#EAE1D7] px-2.5 py-1 rounded-xl transition group" title="Pengaturan Toko">
                        @if($store->logo_url)
                            <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="w-6 h-6 rounded-lg object-cover border border-[#EAE1D7]">
                        @else
                            <span class="w-6 h-6 rounded-lg bg-[#6B4226] text-white flex items-center justify-center text-[10px] font-bold">
                                {{ $store->initials }}
                            </span>
                        @endif
                        <span class="text-xs font-black text-[#2D241E] group-hover:text-[#6B4226] truncate max-w-[120px] sm:max-w-[180px]">
                            {{ $store->name }}
                        </span>
                        @if($store->isSuspended())
                            <span class="bg-red-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                                🚫 Ditangguhkan
                            </span>
                        @elseif($store->isClosed())
                            <span class="bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                                ⏸️ Tutup Sementara
                            </span>
                        @else
                            <span class="bg-emerald-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                                🟢 Buka
                            </span>
                        @endif
                    </a>
                @endif
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                @if($store)
                    <a href="{{ route('store.show', urlencode($store->name)) }}" 
                       target="_blank"
                       class="px-3 py-1.5 rounded-xl border border-[#EAE1D7] hover:border-[#6B4226]/40 text-xs font-bold text-[#5A4B40] hover:text-[#6B4226] bg-white transition flex items-center gap-1.5 shadow-2xs">
                        <span>Lihat Toko Publik</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif

                <a href="{{ route('home') }}" 
                   class="px-3 py-1.5 rounded-xl bg-[#FAF8F5] hover:bg-[#F2EAE0] text-[#6B4226] text-xs font-bold transition flex items-center gap-1">
                    <span>Belanja 🛍️</span>
                </a>

                <!-- User Profile Badge -->
                <div class="flex items-center gap-2 pl-2 border-l border-[#EAE1D7]">
                    <div class="w-8 h-8 rounded-full bg-[#6B4226] text-white flex items-center justify-center text-xs font-black">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-xs font-bold text-[#2D241E] hidden md:inline">{{ auth()->user()->name }}</span>
                </div>
            </div>

        </div>

        <!-- SUB-NAVIGATION BAR (PAGES TABS) -->
        <div class="bg-[#FAF8F5] border-t border-[#EAE1D7] px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex items-center gap-2 overflow-x-auto py-2 text-xs font-bold">
                <a href="{{ route('seller.dashboard') }}" 
                   class="px-3.5 py-1.5 rounded-xl transition shrink-0 flex items-center gap-1.5 {{ request()->routeIs('seller.dashboard') ? 'bg-[#6B4226] text-white shadow-2xs' : 'text-[#5A4B40] hover:bg-white hover:text-[#6B4226]' }}">
                    <span>📊</span>
                    <span>Ringkasan Toko</span>
                </a>

                <a href="{{ route('seller.products.index') }}" 
                   class="px-3.5 py-1.5 rounded-xl transition shrink-0 flex items-center gap-1.5 {{ request()->routeIs('seller.products.index') ? 'bg-[#6B4226] text-white shadow-2xs' : 'text-[#5A4B40] hover:bg-white hover:text-[#6B4226]' }}">
                    <span>📦</span>
                    <span>Katalog Produk</span>
                </a>

                <a href="{{ route('seller.products.create') }}" 
                   class="px-3.5 py-1.5 rounded-xl transition shrink-0 flex items-center gap-1.5 {{ request()->routeIs('seller.products.create') ? 'bg-[#6B4226] text-white shadow-2xs' : 'text-[#5A4B40] hover:bg-white hover:text-[#6B4226]' }}">
                    <span>➕</span>
                    <span>Tambah Produk Baru</span>
                </a>

                <a href="{{ route('seller.orders') }}" 
                   x-data="sellerOrderBadge()"
                   class="px-3.5 py-1.5 rounded-xl transition shrink-0 flex items-center gap-1.5 {{ request()->routeIs('seller.orders') ? 'bg-[#6B4226] text-white shadow-2xs' : 'text-[#5A4B40] hover:bg-white hover:text-[#6B4226]' }}">
                    <span>📑</span>
                    <span>Pesanan Masuk</span>
                    <span x-show="pendingOrdersCount > 0"
                          x-cloak
                          x-text="pendingOrdersCount"
                          class="px-1.5 py-0.5 bg-rose-600 text-white text-[10px] font-bold rounded-full ml-0.5 shadow-2xs"></span>
                </a>

                <a href="{{ route('seller.chat.index') }}" 
                   x-data="sellerChatBadge()"
                   class="px-3.5 py-1.5 rounded-xl transition shrink-0 flex items-center gap-1.5 {{ request()->routeIs('seller.chat.*') ? 'bg-[#6B4226] text-white shadow-2xs' : 'text-[#5A4B40] hover:bg-white hover:text-[#6B4226]' }}">
                    <span>💬</span>
                    <span>Chat Pelanggan</span>
                    <span x-show="unreadCount > 0"
                          x-cloak
                          x-text="unreadCount"
                          class="px-1.5 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full ml-0.5"></span>
                </a>

                <a href="{{ route('seller.settings') }}" 
                   class="px-3.5 py-1.5 rounded-xl transition shrink-0 flex items-center gap-1.5 {{ request()->routeIs('seller.settings') ? 'bg-[#6B4226] text-white shadow-2xs' : 'text-[#5A4B40] hover:bg-white hover:text-[#6B4226]' }}">
                    <span>⚙️</span>
                    <span>Pengaturan Toko</span>
                </a>
        </div>
    </header>

    @if($store && $store->isClosed())
        <!-- CLOSED STORE (MODE LIBUR) NOTICE BAR -->
        <div class="bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 text-white px-4 sm:px-6 lg:px-8 py-2.5 shadow-sm border-b border-amber-400/40">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 text-center sm:text-left">
                    <span class="text-xl shrink-0">⏸️</span>
                    <div>
                        <span class="font-extrabold uppercase tracking-wide bg-amber-800/60 px-2 py-0.5 rounded text-[10px] mr-1.5">Mode Libur</span>
                        <span><strong>Status Toko Saat Ini Tutup Sementara:</strong> Pelanggan tetap dapat melihat katalog produk, tetapi tidak dapat memesan barang hingga kamu membuka toko kembali.</span>
                    </div>
                </div>
                <form action="{{ route('seller.store.toggle_status') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-1.5 bg-white hover:bg-amber-50 text-amber-900 font-black rounded-xl text-xs shadow-md transition transform active:scale-95 flex items-center gap-1.5 cursor-pointer">
                        <span>▶️</span>
                        <span>Buka Toko Kembali Sekarang</span>
                    </button>
                </form>
            </div>
        </div>
    @elseif($store && $store->isSuspended())
        <!-- SUSPENDED NOTICE BAR -->
        <div class="bg-gradient-to-r from-red-600 via-rose-600 to-red-600 text-white px-4 sm:px-6 lg:px-8 py-2.5 shadow-sm border-b border-red-400/40">
            <div class="max-w-7xl mx-auto flex items-center gap-2.5 text-xs">
                <span class="text-xl shrink-0">🚫</span>
                <div>
                    <span class="font-extrabold uppercase tracking-wide bg-red-900/60 px-2 py-0.5 rounded text-[10px] mr-1.5">Toko Ditangguhkan</span>
                    <span>Toko kamu sedang ditangguhkan oleh administrator. {{ $store->suspension_reason ? 'Alasan: "'.$store->suspension_reason.'"' : '' }} {{ $store->suspended_until ? '(Hingga '.$store->suspended_until->translatedFormat('d M Y H:i').')' : '(Permanen hingga dicabut admin)' }}.</span>
                </div>
            </div>
        </div>
    @endif

    <!-- FLASH MESSAGES (AUTO-DISMISS AFTER 5 SECONDS) -->
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
    </div>

    <!-- MAIN BODY CONTENT -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 w-full space-y-6">
        @yield('content')
    </main>

    <!-- SELLER FOOTER -->
    <footer class="bg-white border-t border-[#EAE1D7] py-6 text-center text-xs text-[#8A7C70] mt-auto">
        <p>© {{ date('Y') }} NusantaraMart Seller Center • Platform Mitra Dagang Resmi Indonesia</p>
    </footer>

    <script>
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

                if (trail.length > 0 && trail[trail.length - 1] === currentPath) {
                    return;
                }

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
                } else if (document.referrer && document.referrer.indexOf(window.location.host) !== -1 && document.referrer !== window.location.href) {
                    window.location.href = document.referrer;
                } else {
                    window.location.href = fallbackUrl;
                }
            }
        };

        window.addEventListener('DOMContentLoaded', () => {
            window.smartNav.recordVisit();
        });

        function sellerChatBadge() {
            return {
                unreadCount: 0,
                init() {
                    this.checkUnread();
                    setInterval(() => this.checkUnread(), 8000);
                },
                checkUnread() {
                    fetch('{{ route('seller.chat.unread_count') }}')
                        .then(r => r.json())
                        .then(d => { this.unreadCount = d.unread_count || 0; })
                        .catch(() => {});
                }
            };
        }

        function playOrderChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const audioCtx = new AudioContext();
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }

                // Tone 1 (D5)
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

                // Tone 2 (A5 - High cheerful chime)
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
            } catch(e) {
                console.warn('Notification audio note:', e);
            }
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
                        .then(d => {
                            this.pendingOrdersCount = d.pending_orders_count || 0;
                        })
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
                                    // New order detected!
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
    </script>

    <!-- REALTIME NEW ORDER FLOATING ALERT TOAST (SELLER) -->
    <div x-data="sellerOrderNotifier({{ auth()->user()->store?->id ?? 'null' }})"
         x-cloak
         x-show="showToast"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed bottom-5 right-5 z-50 max-w-sm w-full bg-white/95 backdrop-blur-md border-2 border-emerald-500 rounded-3xl shadow-2xl p-4.5 flex items-start gap-3.5"
         style="display: none;">
        <!-- Icon with pulse ring -->
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

    @stack('scripts')
</body>
</html>
