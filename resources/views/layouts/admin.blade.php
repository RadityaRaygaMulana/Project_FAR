<!DOCTYPE html>
<html lang="id" class="scroll-smooth bg-[#F8FAFC]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard — NusantaraMart')</title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Alpine.js for Reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js for System Visual Telemetry -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
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
<body class="bg-[#F8FAFC] text-[#1E293B] antialiased min-h-screen flex flex-col selection:bg-[#6B4226] selection:text-white"
      x-data="{ sidebarOpen: false }">

    <!-- TOP HEADER -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                
                <!-- Left: Mobile Menu Toggle & Brand -->
                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="sidebarOpen = !sidebarOpen" 
                            class="md:hidden p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-[#6B4226] flex items-center justify-center text-white text-lg shadow-xs group-hover:scale-105 transition">
                            🖥️
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="text-lg font-black tracking-tight text-slate-900">
                                    Nusantara<span class="text-[#6B4226]">Mart</span>
                                </span>
                                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 font-extrabold text-[9px] uppercase tracking-wider border border-amber-200">
                                    System Monitor
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-medium">Pusat Pemantauan Sistem & Pengawasan Toko</span>
                        </div>
                    </a>
                </div>

                <!-- Right: Quick Action: View Store as Customer & Profile Dropdown -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 bg-emerald-50 hover:bg-emerald-100/80 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-200 transition active:scale-95 shadow-2xs"
                       title="Buka Halaman Belanja Pelanggan">
                        <span>🛍️</span>
                        <span class="hidden sm:inline">Halaman Belanja Pelanggan</span>
                    </a>

                    <!-- Admin Profile Info & Logout -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-bold text-slate-900 leading-tight">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-slate-500 font-mono">System Administrator</p>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer"
                                    title="Keluar (Logout)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- ADMIN BODY WITH SIDEBAR & MAIN CONTAINER -->
    <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
        
        <!-- DESKTOP SIDEBAR -->
        <aside class="w-64 shrink-0 hidden md:block space-y-6">
            <nav class="bg-white rounded-2xl border border-slate-200 p-3 shadow-xs space-y-1">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="text-base">⚡</span>
                    <span>Operasi & Status Sistem</span>
                </a>

                @php
                    $pendingStoresCount = \App\Models\Store::where('status', 'pending')->count();
                @endphp
                <a href="{{ route('admin.stores') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.stores*') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-base">🏬</span>
                        <span>Kelola Mitra Toko</span>
                    </div>
                    @if($pendingStoresCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.stores*') ? 'bg-amber-300 text-amber-950' : 'bg-amber-100 text-amber-900 border border-amber-300' }}" title="{{ $pendingStoresCount }} toko menunggu verifikasi">
                            {{ $pendingStoresCount }}
                        </span>
                    @endif
                </a>
                
                <a href="{{ route('admin.logs') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.logs*') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="text-base">📋</span>
                    <span>Catatan Log Sistem</span>
                </a>

                <a href="{{ route('admin.database') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.database*') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="text-base">💾</span>
                    <span>Database & Storage</span>
                </a>

                <a href="{{ route('admin.users') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.users*') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="text-base">👥</span>
                    <span>Kelola Pengguna</span>
                </a>

                <a href="{{ route('admin.vouchers.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.vouchers*') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="text-base">🎟️</span>
                    <span>Kelola Voucher & Jadwal</span>
                </a>

                <a href="{{ route('admin.redeem-codes.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.redeem-codes*') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="text-base">🎁</span>
                    <span>Kode Redeem Promo</span>
                </a>

                @php
                    $suspendedCount = \App\Models\Store::where('is_suspended', true)->count() + \App\Models\User::where('is_suspended', true)->count();
                    $pendingAppealsCount = \App\Models\SuspensionAppeal::where('status', 'pending')->count();
                @endphp
                <a href="{{ route('admin.monitor') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.monitor*') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-base">🔍</span>
                        <span>Pengawasan Toko</span>
                    </div>
                    @if($suspendedCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.monitor*') ? 'bg-rose-300 text-rose-950' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                            {{ $suspendedCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.appeals.index') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('admin.appeals*') ? 'bg-[#6B4226] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-base">⚖️</span>
                        <span>Pengajuan Banding</span>
                    </div>
                    @if($pendingAppealsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.appeals*') ? 'bg-amber-300 text-amber-950' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">
                            {{ $pendingAppealsCount }}
                        </span>
                    @endif
                </a>
            </nav>

            <!-- Quick System Server Health Card -->
            <div class="bg-slate-900 text-white rounded-2xl p-4 shadow-sm space-y-2 border border-slate-800">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-400">Server Health</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </div>
                <div class="text-[11px] text-slate-300 space-y-1 font-mono">
                    <p class="flex items-center justify-between"><span>Database:</span> <span class="text-emerald-400 font-bold">Online</span></p>
                    <p class="flex items-center justify-between"><span>PHP:</span> <span class="text-slate-200">{{ PHP_VERSION }}</span></p>
                    <p class="flex items-center justify-between"><span>Anti-Cache:</span> <span class="text-amber-300 font-bold">Active</span></p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('home') }}" class="w-full py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-xl text-[11px] font-bold text-white flex items-center justify-center gap-1.5 transition">
                        <span>🛍️ Mode Belanja Toko</span>
                        <span>→</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- MOBILE SLIDE-OVER SIDEBAR -->
        <div x-show="sidebarOpen" 
             x-cloak 
             @click.self="sidebarOpen = false" 
             class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs md:hidden flex">
            <div class="w-72 bg-white h-full p-4 space-y-4 shadow-2xl flex flex-col justify-between"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full">
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🖥️</span>
                            <span class="font-black text-slate-900 text-sm">System Operations</span>
                        </div>
                        <button type="button" @click="sidebarOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">✕</button>
                    </div>

                    <nav class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>⚡</span> <span>Operasi & Status</span>
                        </a>
                        <a href="{{ route('admin.stores') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.stores*') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <div class="flex items-center gap-3">
                                <span>🏬</span> <span>Kelola Mitra Toko</span>
                            </div>
                            @if(isset($pendingStoresCount) && $pendingStoresCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ request()->routeIs('admin.stores*') ? 'bg-amber-300 text-amber-950' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">
                                    {{ $pendingStoresCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.logs') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.logs*') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>📋</span> <span>Catatan Log</span>
                        </a>
                        <a href="{{ route('admin.database') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.database*') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>💾</span> <span>Database & Storage</span>
                        </a>
                        <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.users*') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>👥</span> <span>Kelola Pengguna</span>
                        </a>
                        <a href="{{ route('admin.vouchers.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.vouchers*') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>🎟️</span> <span>Kelola Voucher</span>
                        </a>
                        <a href="{{ route('admin.redeem-codes.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.redeem-codes*') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>🎁</span> <span>Kode Redeem Promo</span>
                        </a>
                        <a href="{{ route('admin.monitor') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.monitor*') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <div class="flex items-center gap-3"><span>🔍</span> <span>Pengawasan Toko</span></div>
                            @if(isset($suspendedCount) && $suspendedCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300">{{ $suspendedCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.appeals.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.appeals*') ? 'bg-[#6B4226] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                            <div class="flex items-center gap-3"><span>⚖️</span> <span>Pengajuan Banding</span></div>
                            @if(isset($pendingAppealsCount) && $pendingAppealsCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">{{ $pendingAppealsCount }}</span>
                            @endif
                        </a>
                    </nav>
                </div>

                <div class="pt-4 border-t border-slate-200">
                    <a href="{{ route('home') }}" class="w-full py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-xs">
                        <span>🛍️ Mode Belanja Toko</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- MAIN ADMIN CONTENT -->
        <main class="flex-1 min-w-0 space-y-6">
            
            <!-- AUTO-DISMISS FLASH SUCCESS BANNER (5 SECONDS) -->
            @if(session('admin_success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-semibold rounded-2xl flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">✅</span>
                        <span>{{ session('admin_success') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold p-1 cursor-pointer">
                        ✕
                    </button>
                </div>
            @endif

            <!-- AUTO-DISMISS FLASH ERROR BANNER -->
            @if(session('admin_error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="p-4 bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm font-semibold rounded-2xl flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">⚠️</span>
                        <span>{{ session('admin_error') }}</span>
                    </div>
                    <button type="button" @click="show = false" class="text-rose-700 hover:text-rose-900 text-xs font-bold p-1 cursor-pointer">
                        ✕
                    </button>
                </div>
            @endif

            @yield('content')
        </main>

    </div>

    @stack('scripts')
</body>
</html>
