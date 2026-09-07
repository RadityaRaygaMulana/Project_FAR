@extends('layouts.app')

@section('title', 'Pengaturan Akun & Profil — NusantaraMart')

@section('content')
<div class="py-6 sm:py-10 bg-[#FAF8F5] min-h-screen font-sans antialiased text-[#2D241E]"
     x-data="settingsPage({
         initialView: '{{ request('view') ?: (request('tab') ?: ($errors->any() ? ($errors->has('recipient_name') || $errors->has('recipient_phone') || $errors->has('province') || $errors->has('city') || $errors->has('district') || $errors->has('address_detail') ? 'address' : ($errors->has('current_password') || $errors->has('password') ? 'security' : 'profile')) : (session('address_success') ? 'address' : (session('profile_success') ? 'profile' : (session('password_success') ? 'security' : 'menu'))))) }}',
         initialLabel: '{{ old('address_label', $user->address_label ?? 'Rumah') }}',
         initialProvince: '{{ old('province', $user->province ?? '') }}',
         initialCity: '{{ old('city', $user->city ?? '') }}',
         initialDistrict: '{{ old('district', $user->district ?? '') }}',
         initialPostalCode: '{{ old('postal_code', $user->postal_code ?? '') }}',
         initialLat: {{ $user->latitude !== null ? (float) $user->latitude : 'null' }},
         initialLng: {{ $user->longitude !== null ? (float) $user->longitude : 'null' }},
         hasMapPin: {{ $user->map_notes || $user->address_detail || $user->latitude ? 'true' : 'false' }}
     })">
    
    <div class="max-w-xl mx-auto px-4 sm:px-6">

        <!-- iOS Style Dynamic Banner Notifications (Auto Dismiss 5s) -->
        @if(session('profile_success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-4"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                 class="overflow-hidden mb-4 p-3.5 bg-white/95 backdrop-blur-md rounded-2xl border border-emerald-500/30 text-emerald-700 text-xs font-semibold flex items-center justify-between shadow-[0_2px_8px_rgba(0,0,0,0.06)]">
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                    <span>{{ session('profile_success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-stone-400 hover:text-stone-700 font-bold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('address_success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-4"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                 class="overflow-hidden mb-4 p-3.5 bg-white/95 backdrop-blur-md rounded-2xl border border-amber-500/30 text-amber-900 text-xs font-semibold flex items-center justify-between shadow-[0_2px_8px_rgba(0,0,0,0.06)]">
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold">📍</span>
                    <span>{{ session('address_success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-stone-400 hover:text-stone-700 font-bold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('password_success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-4"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                 class="overflow-hidden mb-4 p-3.5 bg-white/95 backdrop-blur-md rounded-2xl border border-emerald-500/30 text-emerald-700 text-xs font-semibold flex items-center justify-between shadow-[0_2px_8px_rgba(0,0,0,0.06)]">
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">🔒</span>
                    <span>{{ session('password_success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-stone-400 hover:text-stone-700 font-bold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('delete_error'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 class="overflow-hidden mb-4 p-3.5 bg-rose-50 rounded-2xl border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-[0_2px_8px_rgba(225,29,72,0.08)]">
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-bold">✕</span>
                    <span>{{ session('delete_error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-rose-400 hover:text-rose-700 font-bold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        <!-- ======================================================== -->
        <!-- 1. MAIN iOS SETTINGS MENU VIEW -->
        <div x-show="currentView === 'menu'" x-cloak class="space-y-4">
            <!-- Header with Inline Back Button -->
            <div class="flex items-center gap-3 pt-1 pb-1">
                <a href="{{ route('home') }}" 
                   class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                   title="Kembali ke Beranda">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-[28px] sm:text-[32px] font-bold text-[#000000] tracking-tight leading-tight">
                        Pengaturan
                    </h1>
                    <p class="text-xs text-[#8E8E93]">Pengaturan Akun & Profil</p>
                </div>
            </div>

            <!-- iOS Inset Search Bar -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#8E8E93]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Cari pengaturan..." 
                       class="w-full pl-9 pr-4 py-2 bg-[#E3E3E8] text-[15px] text-[#000000] placeholder-[#8E8E93] rounded-[10px] focus:outline-none focus:bg-[#D9D9DE] transition">
            </div>

            <!-- Apple-ID Style Profile Hero Card -->
            <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden mt-3">
                <button type="button" 
                        @click="currentView = 'profile'" 
                        class="w-full p-3.5 sm:p-4 flex items-center justify-between gap-3.5 text-left hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition cursor-pointer group">
                    <div class="flex items-center gap-3.5 truncate">
                        <!-- Avatar -->
                        <div class="w-15 h-15 rounded-full bg-gradient-to-tr from-[#6B4226] via-[#8C5835] to-[#A2845E] text-white flex items-center justify-center text-2xl font-bold shrink-0 shadow-sm border-2 border-white overflow-hidden">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="truncate">
                            <h2 class="font-semibold text-[19px] text-[#000000] leading-tight truncate">
                                {{ $user->name }}
                            </h2>
                            <p class="text-[13px] text-[#8E8E93] truncate mt-0.5">
                                {{ $user->email }}
                            </p>
                            <p class="text-[12px] text-[#007AFF] truncate mt-0.5 font-normal">
                                Data Diri, Foto & Keamanan Akun
                            </p>
                        </div>
                    </div>

                    <!-- iOS Chevron Right -->
                    <svg class="w-4 h-4 text-[#C7C7CC] shrink-0 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            @if($user->role === 'admin')
                <!-- SISTEM ADMINISTRASI & OPERASI -->
                <div class="pt-2">
                    <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                        Administrasi Sistem
                    </p>
                    <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center justify-between gap-3 p-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-xs shrink-0">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[15px] font-semibold text-slate-900">Panel Operasi Admin</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-900 border border-amber-200">Akses Penuh</span>
                                    </div>
                                    <p class="text-[12px] text-[#8E8E93] mt-0.5">Kelola pesanan, pengguna, database & telemetri sistem</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-400 group-hover:text-slate-700 transition">
                                <span class="text-xs font-medium">Buka Panel</span>
                                <svg class="w-4 h-4 text-[#C7C7CC] group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            @endif

            <!-- GROUP 1: TRANSAKSI & BELANJA -->
            <div class="pt-2">
                <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                    Transaksi & Belanja
                </p>
                <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden">
                    
                    <!-- Row 1: Pesanan Saya -->
                    <a href="{{ route('my.orders') }}" 
                       class="flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-[30px] h-[30px] rounded-[7px] bg-[#007AFF] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                📦
                            </div>
                            <span class="text-[15px] font-normal text-[#000000]">Pesanan Saya</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[14px] text-[#8E8E93]">Riwayat</span>
                            <svg class="w-3.5 h-3.5 text-[#C7C7CC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <div class="border-t border-[#E5E5EA] ml-[52px]"></div>

                    <!-- Row 2: Alamat Pengiriman Lengkap -->
                    <button type="button" 
                            @click="currentView = 'address'" 
                            class="w-full flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group text-left cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-[30px] h-[30px] rounded-[7px] bg-[#FF9500] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                📍
                            </div>
                            <div>
                                <span class="text-[15px] font-normal text-[#000000] block">Alamat Pengiriman Utama</span>
                                @if($user->recipient_name)
                                    <span class="text-[12px] text-[#8E8E93] block">Penerima: {{ $user->recipient_name }} ({{ $user->address_label ?? 'Rumah' }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 truncate">
                            <span class="text-[14px] text-[#8E8E93] truncate max-w-[130px]">{{ $user->city ? $user->city : ($user->default_address ? 'Sudah Diatur' : 'Belum Diatur') }}</span>
                            <svg class="w-3.5 h-3.5 text-[#C7C7CC] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>

                </div>
            </div>

            <!-- GROUP: MITRA TOKO & PENJUAL (SOPAN & ELEGAN) -->
            <div class="pt-2">
                <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                    Mitra Toko & Penjual
                </p>
                <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden">
                    @if($user->isSeller())
                        <!-- Approved Seller -->
                        <a href="{{ route('seller.dashboard') }}" 
                           class="flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-[30px] h-[30px] rounded-[7px] bg-[#6B4226] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                    🏬
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[15px] font-normal text-[#000000]">Seller Center</span>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Toko Aktif</span>
                                    </div>
                                    <span class="text-[12px] text-[#8E8E93] block">{{ $user->store->name }} • Kelola produk & pesanan</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-stone-400 group-hover:text-[#6B4226] transition">
                                <span class="text-[14px] text-[#6B4226] font-medium">Buka Toko</span>
                                <svg class="w-3.5 h-3.5 text-[#C7C7CC] group-hover:text-[#6B4226] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @elseif($user->store && $user->store->isPending())
                        <!-- Pending Approval -->
                        <a href="{{ route('seller.register') }}" 
                           class="flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-amber-50/50 active:bg-amber-100/50 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-[30px] h-[30px] rounded-[7px] bg-amber-500 text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                    ⏳
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[15px] font-normal text-[#000000]">Status Pengajuan Toko</span>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">Menunggu Review</span>
                                    </div>
                                    <span class="text-[12px] text-[#8E8E93] block">{{ $user->store->name }} • Sedang ditinjau Admin</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-amber-600 transition">
                                <span class="text-[14px] font-medium">Lihat Status</span>
                                <svg class="w-3.5 h-3.5 text-[#C7C7CC] group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @elseif($user->store && $user->store->isRejected())
                        <!-- Rejected -->
                        <a href="{{ route('seller.register') }}" 
                           class="flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-rose-50/50 active:bg-rose-100/50 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-[30px] h-[30px] rounded-[7px] bg-rose-500 text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                    ⚠️
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[15px] font-normal text-[#000000]">Perbaiki Data Toko</span>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Ditolak</span>
                                    </div>
                                    <span class="text-[12px] text-rose-600 block truncate max-w-[200px]">Ada catatan perbaikan dari Admin</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-rose-600 transition">
                                <span class="text-[14px] font-medium">Periksa</span>
                                <svg class="w-3.5 h-3.5 text-[#C7C7CC] group-hover:text-rose-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @else
                        <!-- No Store Yet (Clean & Unobtrusive) -->
                        <a href="{{ route('seller.register') }}" 
                           class="flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-[30px] h-[30px] rounded-[7px] bg-[#6B4226] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                    🏪
                                </div>
                                <div>
                                    <span class="text-[15px] font-normal text-[#000000] block">Mulai Berjualan</span>
                                    <span class="text-[12px] text-[#8E8E93] block">Buka toko gratis & jangkau pembeli di NusantaraMart</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-stone-400 group-hover:text-[#6B4226] transition">
                                <span class="text-[14px] text-[#6B4226] font-medium">Buka Toko</span>
                                <svg class="w-3.5 h-3.5 text-[#C7C7CC] group-hover:text-[#6B4226] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            <!-- GROUP 2: KEAMANAN & AKSES -->
            <div class="pt-2">
                <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                    Keamanan & Akses
                </p>
                <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden">
                    
                    <!-- Row 1: Kata Sandi -->
                    <button type="button" 
                            @click="currentView = 'security'" 
                            class="w-full flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group text-left cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-[30px] h-[30px] rounded-[7px] bg-[#5856D6] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                🔒
                            </div>
                            <span class="text-[15px] font-normal text-[#000000]">Kata Sandi & Keamanan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[14px] text-[#8E8E93]">Ubah Sandi</span>
                            <svg class="w-3.5 h-3.5 text-[#C7C7CC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>

                    <div class="border-t border-[#E5E5EA] ml-[52px]"></div>

                    <!-- Row 2: Tipe Akun Member (Interactive Subview) -->
                    <button type="button" 
                            @click="currentView = 'membership'" 
                            class="w-full flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group text-left cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-[30px] h-[30px] rounded-[7px] bg-[#34C759] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                🛡️
                            </div>
                            <span class="text-[15px] font-normal text-[#000000]">Tipe Akun Member</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[13px] font-semibold text-[#6B4226] bg-[#FAF4ED] px-2.5 py-0.5 rounded-full border border-[#6B4226]/20">
                                {{ $tierBadge ?? '🥇' }} {{ $tier ?? 'Gold Member' }}
                            </span>
                            <svg class="w-3.5 h-3.5 text-[#C7C7CC] group-hover:text-[#8E8E93] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>

                </div>
            </div>

            <!-- GROUP 3: LAYANAN & BANTUAN TRANSAKSI -->
            <div class="pt-2">
                <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                    Layanan & Bantuan
                </p>
                <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden divide-y divide-[#E5E5EA]">
                    
                    <!-- Row 1: Chat Toko & Penjual -->
                    <a href="{{ route('chat.index') }}" 
                       class="flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-[30px] h-[30px] rounded-[7px] bg-[#007AFF] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                💬
                            </div>
                            <div>
                                <span class="text-[15px] font-normal text-[#000000] block">Pesan & Obrolan Toko</span>
                                <span class="text-[12px] text-[#8E8E93] block">Konsultasi produk langsung dengan penjual</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[14px] text-[#007AFF] font-medium">Buka Chat</span>
                            <svg class="w-3.5 h-3.5 text-[#C7C7CC] group-hover:text-[#007AFF] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>

                    <!-- Row 2: Voucher & Cek Kode Promo -->
                    <button type="button" 
                            @click="currentView = 'vouchers'" 
                            class="w-full flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group text-left cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-[30px] h-[30px] rounded-[7px] bg-[#FF9500] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                🎟️
                            </div>
                            <div>
                                <span class="text-[15px] font-normal text-[#000000] block">Voucher & Kode Promo</span>
                                <span class="text-[12px] text-[#8E8E93] block">Cek dan tukarkan voucher diskon belanja</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[14px] text-[#8E8E93]">Tukar Kode</span>
                            <svg class="w-3.5 h-3.5 text-[#C7C7CC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Row 3: Status Akun & Pusat Bantuan Resolusi -->
                    <button type="button" 
                            @click="currentView = 'support'" 
                            class="w-full flex items-center justify-between gap-3 p-3 pl-3.5 hover:bg-[#F2F2F7]/70 active:bg-[#E5E5EA] transition group text-left cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-[30px] h-[30px] rounded-[7px] bg-[#34C759] text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
                                🛡️
                            </div>
                            <div>
                                <span class="text-[15px] font-normal text-[#000000] block">Status Akun & Pusat Resolusi</span>
                                <span class="text-[12px] text-[#8E8E93] block">Kesehatan akun, panduan komplain & retur</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($user->is_suspended)
                                <span class="text-[12px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">Dibatasi</span>
                            @else
                                <span class="text-[12px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Normal</span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-[#C7C7CC]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>

                </div>
            </div>

            <!-- GROUP 4: LOGOUT (iOS Style Red Button Card) -->
            <div class="pt-2">
                <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden">
                    <form action="{{ route('logout') }}" method="POST" onsubmit="if(typeof handleLogoutCart==='function') handleLogoutCart();">
                        @csrf
                        <button type="submit" 
                                class="w-full p-3.5 text-center text-[#FF3B30] hover:bg-red-50 active:bg-red-100 font-medium text-[16px] transition cursor-pointer">
                            Keluar dari Akun
                        </button>
                    </form>
                </div>
            </div>

            <!-- GROUP 5: ZONA BAHAYA (HAPUS AKUN & PENGELOLAAN TOKO) -->
            <div class="pt-2 pb-12">
                <p class="text-[13px] uppercase font-normal text-[#8E8E93] tracking-normal px-4 mb-1.5">
                    Zona Bahaya
                </p>
                <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-rose-100 overflow-hidden divide-y divide-[#E5E5EA]">
                    
                    @if($user->store)
                        <!-- Tutup / Kelola Status Toko -->
                        <a href="{{ route('seller.settings') }}" 
                           class="w-full flex items-center justify-between gap-3 p-3.5 hover:bg-amber-50/50 active:bg-amber-100/50 transition group text-left cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-[30px] h-[30px] rounded-[7px] bg-amber-500 text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)] shrink-0">
                                    🏪
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[15px] font-medium text-stone-800 block truncate">Status & Penutupan Toko</span>
                                    <span class="text-[12px] text-stone-500 block">Status saat ini: <strong class="{{ $user->store->isClosed() ? 'text-amber-600' : 'text-emerald-600' }}">{{ $user->store->isClosed() ? 'Tutup Sementara (Mode Libur)' : ($user->store->isApproved() ? 'Buka & Aktif' : ucfirst($user->store->status)) }}</strong></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[13px] text-amber-700 font-medium">Kelola</span>
                                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    @endif

                    <!-- Hapus Akun Sendiri -->
                    <button type="button" 
                            @click="deleteAccountModalOpen = true" 
                            class="w-full flex items-center justify-between gap-3 p-3.5 hover:bg-rose-50 active:bg-rose-100 transition group text-left cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-[30px] h-[30px] rounded-[7px] bg-rose-600 text-white flex items-center justify-center text-sm shadow-[0_1px_2px_rgba(0,0,0,0.12)] shrink-0">
                                🗑️
                            </div>
                            <div class="min-w-0">
                                <span class="text-[15px] font-medium text-rose-600 block">Hapus Akun Pengguna</span>
                                <span class="text-[12px] text-stone-500 block truncate">Hapus akun, riwayat, dan profil secara permanen</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-[13px] text-rose-600 font-semibold">Hapus Akun</span>
                            <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>

                </div>
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- 2. SUB-VIEW: PROFIL & DATA DIRI -->
        <!-- ======================================================== -->
        <div x-show="currentView === 'profile'" x-cloak class="space-y-4">
            
            <!-- Header with Inline Circular Back Button -->
            <div class="flex items-center gap-3 pt-1 pb-1">
                <button type="button" 
                        @click="currentView = 'menu'" 
                        class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                        title="Kembali">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-[24px] sm:text-[28px] font-bold text-[#000000] tracking-tight leading-tight">
                        Profil & Data Diri
                    </h1>
                    <p class="text-xs text-[#8E8E93]">Informasi Akun & Pengguna</p>
                </div>
            </div>

            <form action="{{ route('settings.profile') }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ avatarPreview: '{{ $user->avatar_url }}' }">
                @csrf
                @method('PUT')

                <input type="hidden" name="favorite_spiciness" value="{{ old('favorite_spiciness', $user->favorite_spiciness ?? 3) }}">

                <!-- Top Avatar Header with Interactive Photo Picker -->
                <div class="text-center py-2">
                    <div class="relative w-24 h-24 mx-auto">
                        <!-- Image or Initials Box -->
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" class="w-24 h-24 rounded-full object-cover shadow-sm border-2 border-white">
                        </template>
                        <template x-if="!avatarPreview">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-[#6B4226] to-[#8C5835] text-white flex items-center justify-center text-3xl font-bold shadow-sm border-2 border-white">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </template>

                        <!-- Camera Floating Badge Trigger -->
                        <label for="avatar_input" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center text-xs shadow-md border-2 border-white hover:scale-110 active:scale-95 transition cursor-pointer" title="Ubah Foto Profil">
                            📷
                        </label>
                        <input type="file" id="avatar_input" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="sr-only" @change="const file = $event.target.files[0]; if (file) { avatarPreview = URL.createObjectURL(file); }">
                    </div>

                    <label for="avatar_input" class="inline-block text-[13px] font-medium text-[#007AFF] hover:underline mt-2.5 cursor-pointer">
                        Edit / Ubah Foto
                    </label>
                    <p class="text-[11px] text-[#8E8E93] mt-0.5">JPG, PNG, atau WebP (Maks. 2MB)</p>
                    @error('avatar')
                        <p class="text-[11px] text-red-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Inset Form Group 1: Informasi Diri -->
                <div>
                    <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                        Informasi Akun
                    </p>
                    <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden divide-y divide-[#E5E5EA]">
                        
                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-28 text-[15px] text-[#000000] shrink-0 font-normal">Nama</span>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full text-[15px] text-[#000000] focus:outline-none bg-transparent">
                        </div>

                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-28 text-[15px] text-[#000000] shrink-0 font-normal">Username</span>
                            <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                                   class="w-full text-[15px] text-[#000000] focus:outline-none bg-transparent">
                        </div>

                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-28 text-[15px] text-[#000000] shrink-0 font-normal">Email</span>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full text-[15px] text-[#000000] focus:outline-none bg-transparent">
                        </div>

                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-28 text-[15px] text-[#000000] shrink-0 font-normal">WhatsApp</span>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890"
                                   class="w-full text-[15px] text-[#000000] placeholder-[#C7C7CC] focus:outline-none bg-transparent">
                        </div>

                    </div>
                </div>

                <!-- Shortcut to Alamat Pengiriman -->
                <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📍</span>
                        <div>
                            <p class="text-[14px] font-semibold text-[#000000]">Kelola Alamat Pengiriman</p>
                            <p class="text-[11px] text-[#8E8E93]">Provinsi, Kota, Kecamatan, Kode Pos & Titik Map</p>
                        </div>
                    </div>
                    <button type="button" @click="currentView = 'address'" class="text-[13px] text-[#007AFF] font-medium hover:underline cursor-pointer">
                        Atur Alamat ›
                    </button>
                </div>

                <!-- Save Action Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full p-3.5 bg-[#007AFF] hover:bg-[#0062CC] active:bg-[#0051A8] text-white font-medium text-[16px] rounded-[13px] shadow-xs transition cursor-pointer">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>

        </div>

        <!-- ======================================================== -->
        <!-- 3. SUB-VIEW: ALAMAT PENGIRIMAN LENGKAP & TITIK PETA (MAP) -->
        <!-- ======================================================== -->
        <div x-show="currentView === 'address'" x-cloak class="space-y-4">
            
            <!-- Header with Inline Circular Back Button -->
            <div class="flex items-center gap-3 pt-1 pb-1">
                @if(request('return_to') === 'checkout')
                    <a href="{{ route('checkout.show') }}" 
                       class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                       title="Kembali ke Checkout">
                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @else
                    <button type="button" 
                            @click="currentView = 'menu'" 
                            class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                            title="Kembali">
                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                @endif
                <div>
                    <h1 class="text-[24px] sm:text-[28px] font-bold text-[#000000] tracking-tight leading-tight">
                        Alamat Pengiriman
                    </h1>
                    <p class="text-xs text-[#8E8E93]">Detail Wilayah & Titik Peta GPS</p>
                </div>
            </div>

            @if(request('return_to') === 'checkout')
                <div class="p-3 bg-[#FAF4ED] border border-[#6B4226]/20 rounded-2xl flex items-center gap-2.5 text-xs text-[#6B4226] font-bold">
                    <span class="text-base">🛍️</span>
                    <span>Kamu sedang menyelesaikan pesanan. Silakan simpan alamat ini untuk lanjut ke checkout.</span>
                </div>
            @endif

            <form action="{{ route('settings.address') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_to" value="{{ request('return_to', old('return_to')) }}">

                <!-- 1. Label Alamat Pills -->
                <div>
                    <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                        Label Alamat
                    </p>
                    <input type="hidden" name="address_label" :value="addressLabel">
                    <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-3 flex items-center gap-2 overflow-x-auto">
                        <template x-for="lbl in ['Rumah', 'Kantor', 'Apartemen', 'Kost']" :key="lbl">
                            <button type="button" 
                                    @click="addressLabel = lbl"
                                    :class="addressLabel === lbl ? 'bg-[#6B4226] text-white font-bold shadow-xs' : 'bg-[#F2F2F7] text-[#5A4B40] hover:bg-[#E5E5EA] font-medium'"
                                    class="px-4 py-1.5 rounded-full text-xs transition cursor-pointer shrink-0"
                                    x-text="lbl">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 2. Informasi Penerima -->
                <div>
                    <div class="flex items-center justify-between px-4 mb-1.5">
                        <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal">
                            Kontak Penerima
                        </p>
                        <button type="button" 
                                @click="$refs.recipientNameInput.value = '{{ $user->name }}'; $refs.recipientPhoneInput.value = '{{ $user->phone }}';"
                                class="text-[11px] font-semibold text-[#007AFF] hover:underline cursor-pointer">
                            Gunakan Data Akun
                        </button>
                    </div>
                    <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden divide-y divide-[#E5E5EA]">
                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-32 text-[15px] text-[#000000] shrink-0 font-normal">Nama Penerima *</span>
                            <input type="text" x-ref="recipientNameInput" name="recipient_name" value="{{ old('recipient_name', $user->recipient_name ?? $user->name) }}" required placeholder="Contoh: Rian Pratama"
                                   class="w-full text-[15px] text-[#000000] placeholder-[#C7C7CC] focus:outline-none bg-transparent">
                        </div>
                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-32 text-[15px] text-[#000000] shrink-0 font-normal">No. HP / WA *</span>
                            <input type="tel" x-ref="recipientPhoneInput" name="recipient_phone" value="{{ old('recipient_phone', $user->recipient_phone ?? $user->phone) }}" required placeholder="Contoh: 081234567890"
                                   class="w-full text-[15px] text-[#000000] placeholder-[#C7C7CC] focus:outline-none bg-transparent">
                        </div>
                    </div>
                </div>

                <!-- 3. Wilayah Pengiriman (Provinsi, Kota, Kecamatan, Kode Pos) -->
                <div>
                    <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                        Wilayah Pengiriman
                    </p>
                    <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden divide-y divide-[#E5E5EA]">
                        
                        <!-- Provinsi -->
                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-32 text-[15px] text-[#000000] shrink-0 font-normal">Provinsi *</span>
                            <div class="relative w-full">
                                <select name="province" 
                                        x-model="selectedProvince" 
                                        @change="onProvinceChange()" 
                                        required 
                                        class="w-full text-[15px] text-[#000000] focus:outline-none bg-transparent cursor-pointer appearance-none pr-6 font-normal">
                                    <option value="" disabled :selected="!selectedProvince">-- Pilih Provinsi --</option>
                                    <template x-for="prov in availableProvinces" :key="prov">
                                        <option :value="prov" x-text="prov" :selected="prov === selectedProvince"></option>
                                    </template>
                                </select>
                                <div class="absolute right-0 inset-y-0 flex items-center pointer-events-none text-[#8E8E93]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Kota / Kabupaten -->
                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-32 text-[15px] text-[#000000] shrink-0 font-normal">Kota / Kab *</span>
                            <div class="relative w-full">
                                <select name="city" 
                                        x-model="selectedCity" 
                                        @change="onCityChange()" 
                                        :disabled="!selectedProvince"
                                        required 
                                        class="w-full text-[15px] text-[#000000] focus:outline-none bg-transparent cursor-pointer appearance-none pr-6 font-normal disabled:opacity-50">
                                    <option value="" disabled :selected="!selectedCity">-- Pilih Kota / Kabupaten --</option>
                                    <template x-for="cty in availableCities" :key="cty">
                                        <option :value="cty" x-text="cty" :selected="cty === selectedCity"></option>
                                    </template>
                                </select>
                                <div class="absolute right-0 inset-y-0 flex items-center pointer-events-none text-[#8E8E93]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Kecamatan -->
                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-32 text-[15px] text-[#000000] shrink-0 font-normal">Kecamatan *</span>
                            <div class="relative w-full">
                                <select name="district" 
                                        x-model="selectedDistrict" 
                                        @change="onDistrictChange()" 
                                        :disabled="!selectedCity"
                                        required 
                                        class="w-full text-[15px] text-[#000000] focus:outline-none bg-transparent cursor-pointer appearance-none pr-6 font-normal disabled:opacity-50">
                                    <option value="" disabled :selected="!selectedDistrict">-- Pilih Kecamatan --</option>
                                    <template x-for="dist in availableDistricts" :key="dist">
                                        <option :value="dist" x-text="dist" :selected="dist === selectedDistrict"></option>
                                    </template>
                                </select>
                                <div class="absolute right-0 inset-y-0 flex items-center pointer-events-none text-[#8E8E93]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Kode Pos -->
                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-32 text-[15px] text-[#000000] shrink-0 font-normal">Kode Pos</span>
                            <input type="text" name="postal_code" x-model="postalCode" placeholder="Contoh: 40132"
                                   class="w-full text-[15px] text-[#000000] placeholder-[#C7C7CC] focus:outline-none bg-transparent">
                        </div>

                    </div>
                </div>

                <!-- 4. Detail Alamat & Catatan Kurir -->
                <div>
                    <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                        Alamat Spesifik & Patokan
                    </p>
                    <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden divide-y divide-[#E5E5EA]">
                        
                        <!-- Detail Jalan / No Rumah -->
                        <div class="p-3.5">
                            <label class="block text-[11px] font-semibold text-[#8E8E93] uppercase tracking-wider mb-1">
                                Nama Jalan, Nomor Rumah, RT/RW, Blok/Unit *
                            </label>
                            <textarea name="address_detail" rows="2" required placeholder="Contoh: Jl. Dago Elang No. 45 RT 03/RW 07, Blok B2"
                                      class="w-full text-[15px] text-[#000000] placeholder-[#C7C7CC] focus:outline-none bg-transparent resize-none">{{ old('address_detail', $user->address_detail ?? $user->default_address) }}</textarea>
                        </div>

                        <!-- Catatan / Patokan Kurir -->
                        <div class="p-3.5">
                            <label class="block text-[11px] font-semibold text-[#8E8E93] uppercase tracking-wider mb-1">
                                Patokan Lokasi / Catatan Pengiriman Kurir
                            </label>
                            <input type="text" name="map_notes" value="{{ old('map_notes', $user->map_notes) }}" placeholder="Contoh: Rumah pagar hitam samping warung Bu Siti, seberang masjid"
                                   class="w-full text-[15px] text-[#000000] placeholder-[#C7C7CC] focus:outline-none bg-transparent">
                        </div>

                    </div>
                </div>

                <!-- 5. Real Google Maps Pinpoint Card (Compact & Non-Intrusive) -->
                <div>
                    <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                        Titik Peta & Pinpoint GPS
                    </p>
                    
                    <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-4 space-y-3">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl shadow-xs shrink-0 mt-0.5"
                                     :class="hasCustomPin ? 'bg-gradient-to-tr from-emerald-500 to-teal-600 text-white' : 'bg-[#F2EAE0] text-[#8A7C70]'">
                                    <span>📍</span>
                                </div>
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-[15px] font-semibold text-[#000000]">Titik Lokasi Pengiriman</h4>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md border"
                                              :class="hasCustomPin ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-stone-100 text-stone-600 border-stone-200'"
                                              x-text="hasCustomPin ? 'Terkunci ✓' : 'Belum Diatur'"></span>
                                    </div>
                                    <template x-if="hasCustomPin && currentLat !== null">
                                        <div>
                                            <p class="text-[13px] text-[#2D241E] font-medium line-clamp-2" 
                                               x-text="geoAddress || (selectedDistrict ? (selectedDistrict + ', ' + selectedCity + ', ' + selectedProvince) : 'Titik koordinat kustom')"></p>
                                            <p class="text-[11px] text-[#8E8E93] font-mono" 
                                               x-text="'GPS: ' + currentLat.toFixed(5) + ', ' + currentLng.toFixed(5)"></p>
                                        </div>
                                    </template>
                                    <template x-if="!hasCustomPin || currentLat === null">
                                        <div>
                                            <p class="text-[12px] text-[#8A7C70]">Belum ada titik GPS yang dipilih</p>
                                            <p class="text-[11px] text-[#B0A499] font-mono">GPS: -</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Grid -->
                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-[#F2EAE0]">
                            <!-- GPS Auto-Detect Button -->
                            <button type="button" 
                                    @click="getCurrentGpsLocation()"
                                    class="py-2.5 px-3 bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] text-xs font-bold rounded-xl border border-[#EAE1D7] transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🎯</span>
                                <span>Lokasi Saya</span>
                            </button>

                            <!-- Open Modal Map Button -->
                            <button type="button" 
                                    @click="openMapModal()"
                                    class="py-2.5 px-3 bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>🗺️</span>
                                <span>Atur di Peta</span>
                            </button>
                        </div>

                        <!-- Hidden GPS Coordinates inputs for backend -->
                        <input type="hidden" name="latitude" :value="currentLat !== null ? currentLat : ''">
                        <input type="hidden" name="longitude" :value="currentLng !== null ? currentLng : ''">
                    </div>
                </div>

                <!-- Save Action Button -->
                <div class="pt-2 pb-6">
                    <button type="submit" 
                            class="w-full p-3.5 bg-[#007AFF] hover:bg-[#0062CC] active:bg-[#0051A8] text-white font-medium text-[16px] rounded-[13px] shadow-xs transition cursor-pointer">
                        Simpan Alamat Pengiriman
                    </button>
                </div>
            </form>

        </div>

        <!-- DEDICATED GOOGLE MAPS PINPOINT MODAL -->
        <div x-show="showMapModal" 
             x-cloak
             @keydown.escape.window="closeMapModal()"
             class="fixed inset-0 z-50 overflow-hidden flex flex-col justify-end sm:justify-center items-center p-0 sm:p-4"
             role="dialog"
             aria-modal="true">
            
            <!-- Backdrop Blur -->
            <div x-show="showMapModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeMapModal()"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>

            <!-- Modal Box -->
            <div x-show="showMapModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95"
                 class="relative w-full max-w-2xl bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[85vh] z-10 border border-[#EAE1D7]">
                
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 bg-[#FAF7F2] border-b border-[#EAE1D7] flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#6B4226] text-white flex items-center justify-center text-sm shadow-2xs">
                            🗺️
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm sm:text-base text-[#2D241E]">Atur Titik Pin Alamat</h3>
                            <p class="text-[11px] text-[#8A7C70]">Geser peta atau pin merah untuk menandai rumah dengan presisi</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="closeMapModal()" 
                            class="p-2 text-[#8A7C70] hover:text-[#2D241E] hover:bg-black/5 rounded-full transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Google Maps Canvas Stage -->
                <div class="relative flex-1 min-h-[340px] sm:min-h-[400px] w-full bg-[#FAF8F5]">
                    <div id="googleMapsContainer" class="w-full h-full"></div>

                    <!-- Floating GPS Current Location FAB -->
                    <button type="button" 
                            @click="getCurrentGpsLocation()"
                            title="Gunakan Lokasi GPS Saya"
                            class="absolute top-4 right-4 z-10 p-2.5 sm:p-3 bg-white hover:bg-[#FAF7F2] active:bg-[#FAF4ED] text-[#007AFF] rounded-2xl shadow-lg border border-black/10 transition cursor-pointer flex items-center gap-1.5 font-bold text-xs">
                        <span>🎯</span>
                        <span class="hidden sm:inline">Lokasi Saya</span>
                    </button>
                </div>

                <!-- Modal Bottom Confirmation Bar -->
                <div class="p-4 sm:p-5 bg-white border-t border-[#EAE1D7] space-y-3 shrink-0">
                    <div class="flex items-start gap-2.5 text-xs text-[#2D241E]">
                        <span class="text-base mt-0.5">📍</span>
                        <div class="space-y-0.5 flex-1 min-w-0">
                            <template x-if="currentLat !== null && currentLng !== null">
                                <div>
                                    <p class="font-bold text-xs truncate" x-text="geoAddress || (selectedDistrict ? (selectedDistrict + ', ' + selectedCity) : 'Titik koordinat terpilih')"></p>
                                    <p class="text-[11px] text-[#8E8E93] font-mono" x-text="'Koordinat GPS: ' + currentLat.toFixed(6) + ', ' + currentLng.toFixed(6)"></p>
                                </div>
                            </template>
                            <template x-if="currentLat === null || currentLng === null">
                                <div>
                                    <p class="font-bold text-xs text-[#8A7C70]">Belum ada titik yang dipilih</p>
                                    <p class="text-[11px] text-[#B0A499]">Klik pada peta atau pilih "Lokasi Saya" untuk menentukan pin</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button type="button" 
                            @click="closeMapModal()" 
                            class="w-full py-3 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                        <span>Gunakan Titik Lokasi Ini ✓</span>
                    </button>
                </div>

            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 4. SUB-VIEW: KATA SANDI & KEAMANAN -->
        <!-- ======================================================== -->
        <div x-show="currentView === 'security'" x-cloak class="space-y-4">
            
            <!-- Header with Inline Circular Back Button -->
            <div class="flex items-center gap-3 pt-1 pb-1">
                <button type="button" 
                        @click="currentView = 'menu'" 
                        class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                        title="Kembali">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-[24px] sm:text-[28px] font-bold text-[#000000] tracking-tight leading-tight">
                        Kata Sandi & Keamanan
                    </h1>
                    <p class="text-xs text-[#8E8E93]">Ubah Kata Sandi Akun</p>
                </div>
            </div>

            <!-- Password Error Alert if any -->
            @if ($errors->has('current_password') || $errors->has('password'))
                <div class="p-3.5 bg-red-50/95 backdrop-blur-md border border-red-200 text-red-700 text-xs font-semibold rounded-2xl flex items-start gap-2.5 shadow-2xs">
                    <span class="text-base mt-0.5 shrink-0">⚠️</span>
                    <div class="space-y-1 flex-1">
                        @if ($errors->has('current_password'))
                            <p>{{ $errors->first('current_password') }}</p>
                        @endif
                        @if ($errors->has('password'))
                            <p>{{ $errors->first('password') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <form action="{{ route('settings.password') }}" method="POST" class="space-y-4 pt-1">
                @csrf
                @method('PUT')

                <div>
                    <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4 mb-1.5">
                        Ganti Password Akun
                    </p>
                    <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] overflow-hidden divide-y divide-[#E5E5EA]">
                        
                        <!-- Sandi Lama -->
                        <div class="px-4 py-2.5 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="w-32 text-[15px] {{ $errors->has('current_password') ? 'text-red-600 font-semibold' : 'text-[#000000] font-normal' }} shrink-0">Sandi Lama</span>
                                <div class="relative w-full flex items-center">
                                    <input :type="showCurrentPass ? 'text' : 'password'" name="current_password" required placeholder="••••••••"
                                           class="w-full text-[15px] {{ $errors->has('current_password') ? 'text-red-600' : 'text-[#000000]' }} placeholder-[#C7C7CC] focus:outline-none bg-transparent">
                                    <button type="button" @click="showCurrentPass = !showCurrentPass" class="text-xs text-[#007AFF] px-1 font-medium cursor-pointer">
                                        <span x-text="showCurrentPass ? 'Sembunyikan' : 'Lihat'"></span>
                                    </button>
                                </div>
                            </div>
                            @error('current_password')
                                <p class="text-[11px] text-red-500 font-medium pl-32">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sandi Baru -->
                        <div class="px-4 py-2.5 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="w-32 text-[15px] {{ $errors->has('password') ? 'text-red-600 font-semibold' : 'text-[#000000] font-normal' }} shrink-0">Sandi Baru</span>
                                <div class="relative w-full flex items-center">
                                    <input :type="showNewPass ? 'text' : 'password'" name="password" required placeholder="Min. 6 karakter"
                                           class="w-full text-[15px] {{ $errors->has('password') ? 'text-red-600' : 'text-[#000000]' }} placeholder-[#C7C7CC] focus:outline-none bg-transparent">
                                    <button type="button" @click="showNewPass = !showNewPass" class="text-xs text-[#007AFF] px-1 font-medium cursor-pointer">
                                        <span x-text="showNewPass ? 'Sembunyikan' : 'Lihat'"></span>
                                    </button>
                                </div>
                            </div>
                            @error('password')
                                <p class="text-[11px] text-red-500 font-medium pl-32">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Sandi -->
                        <div class="flex items-center px-4 py-2.5">
                            <span class="w-32 text-[15px] text-[#000000] shrink-0 font-normal">Konfirmasi</span>
                            <input :type="showNewPass ? 'text' : 'password'" name="password_confirmation" required placeholder="Ulangi sandi baru"
                                   class="w-full text-[15px] text-[#000000] placeholder-[#C7C7CC] focus:outline-none bg-transparent">
                        </div>

                    </div>
                    <p class="text-[12px] text-[#8E8E93] px-4 mt-1.5">
                        Gunakan kombinasi angka dan huruf untuk meningkatkan keamanan akun belanja kamu.
                    </p>
                </div>

                <!-- Save Action Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full p-3.5 bg-[#007AFF] hover:bg-[#0062CC] active:bg-[#0051A8] text-white font-medium text-[16px] rounded-[13px] shadow-xs transition cursor-pointer">
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>

        </div>

        <!-- ======================================================== -->
        <!-- 5. SUB-VIEW: VOUCHER & TUKAR KODE PROMO -->
        <!-- ======================================================== -->
        <div x-show="currentView === 'vouchers'" x-cloak class="space-y-4">
            
            <!-- Header with Inline Circular Back Button -->
            <div class="flex items-center gap-3 pt-1 pb-1">
                <button type="button" 
                        @click="currentView = 'menu'" 
                        class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                        title="Kembali">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-[24px] sm:text-[28px] font-bold text-[#000000] tracking-tight leading-tight">
                        Voucher & Kode Promo
                    </h1>
                    <p class="text-xs text-[#8E8E93]">Cek Keabsahan & Potongan Kupon Belanja</p>
                </div>
            </div>

            <!-- Input & Check Box -->
            <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-4 sm:p-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Masukkan Kode Promo / Voucher
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🎟️</span>
                            <input type="text" 
                                   x-model="redeemInput"
                                   @keydown.enter.prevent="checkRedeemCode()"
                                   placeholder="Contoh: MERDEKA50, DISKON10" 
                                   class="w-full pl-9 pr-3 py-2.5 text-sm uppercase font-mono font-bold tracking-wider border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] bg-slate-50 transition">
                        </div>
                        <button type="button" 
                                @click="checkRedeemCode()"
                                :disabled="redeemLoading || !redeemInput.trim()"
                                class="px-4 py-2.5 bg-[#6B4226] hover:bg-[#54321B] disabled:opacity-50 text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs active:scale-95 shrink-0 flex items-center gap-1.5">
                            <span x-show="!redeemLoading">Periksa</span>
                            <span x-show="redeemLoading" x-cloak class="inline-block animate-spin">⏳</span>
                        </button>
                    </div>
                </div>

                <!-- Result Box -->
                <template x-if="redeemResult">
                    <div class="pt-1">
                        <!-- Success Card -->
                        <template x-if="redeemResult.success">
                            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white">
                                        VOUCHER VALID
                                    </span>
                                    <span class="font-mono text-xs font-black text-emerald-900" x-text="redeemResult.code"></span>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-emerald-950" x-text="redeemResult.name"></h3>
                                    <p class="text-xs text-emerald-800 font-semibold mt-0.5" x-text="redeemResult.formatted_discount"></p>
                                </div>
                                <div class="pt-2 border-t border-emerald-200/60 flex items-center justify-between text-[11px] text-emerald-800">
                                    <span>Min. Belanja: <strong x-text="'Rp ' + Number(redeemResult.min_spend).toLocaleString('id-ID')"></strong></span>
                                    <a href="{{ route('checkout.show') }}" class="text-[#6B4226] font-bold hover:underline">Gunakan di Checkout →</a>
                                </div>
                            </div>
                        </template>

                        <!-- Error Card -->
                        <template x-if="!redeemResult.success">
                            <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-2.5 text-xs text-rose-900">
                                <span class="text-sm shrink-0">⚠️</span>
                                <span class="font-medium" x-text="redeemResult.message"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Panduan Penggunaan Voucher -->
            <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-4 sm:p-5 space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#6B4226]">Cara Menggunakan Voucher</h3>
                <div class="space-y-2 text-xs text-slate-600 leading-relaxed">
                    <div class="flex items-start gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#FAF4ED] text-[#6B4226] font-bold flex items-center justify-center shrink-0 text-[11px]">1</span>
                        <span>Pilih produk favorit kamu dan masukkan ke dalam keranjang belanja.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#FAF4ED] text-[#6B4226] font-bold flex items-center justify-center shrink-0 text-[11px]">2</span>
                        <span>Buka halaman checkout dan masukkan kode voucher yang valid pada kolom voucher promosi.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#FAF4ED] text-[#6B4226] font-bold flex items-center justify-center shrink-0 text-[11px]">3</span>
                        <span>Total pembayaran akan otomatis terpotong sesuai nilai diskon yang tertera.</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- 6. SUB-VIEW: STATUS AKUN & PUSAT RESOLUSI -->
        <!-- ======================================================== -->
        <div x-show="currentView === 'support'" x-cloak class="space-y-4">
            
            <!-- Header with Inline Circular Back Button -->
            <div class="flex items-center gap-3 pt-1 pb-1">
                <button type="button" 
                        @click="currentView = 'menu'" 
                        class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                        title="Kembali">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-[24px] sm:text-[28px] font-bold text-[#000000] tracking-tight leading-tight">
                        Status Akun & Resolusi
                    </h1>
                    <p class="text-xs text-[#8E8E93]">Pemeriksaan Kesehatan Akun & Bantuan Belanja</p>
                </div>
            </div>

            <!-- Card 1: Status Kesehatan Akun -->
            <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-4 sm:p-5 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700">Kesehatan Akun</span>
                    @if($user->is_suspended)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-200">
                            🚫 Dibatasi / Ditangguhkan
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Normal & Aktif</span>
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[11px] text-slate-400 font-semibold block">Email Terdaftar</span>
                        <p class="text-xs font-bold text-slate-800 mt-0.5 truncate">{{ $user->email }}</p>
                        <span class="text-[10px] font-bold text-emerald-600 mt-1 inline-block">
                            {{ $user->email_verified_at ? '✓ Terverifikasi' : '⚠️ Belum Verifikasi' }}
                        </span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[11px] text-slate-400 font-semibold block">Keamanan Kata Sandi</span>
                        <p class="text-xs font-bold text-slate-800 mt-0.5">Terenkripsi Kuat</p>
                        <button type="button" @click="currentView = 'security'" class="text-[10px] font-bold text-[#007AFF] hover:underline mt-1 inline-block">
                            Ubah Sandi →
                        </button>
                    </div>
                </div>

                @if($user->is_suspended)
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900 space-y-1">
                        <p class="font-black">Alasan Pembatasan Akun:</p>
                        <p class="italic">"{{ $user->suspension_reason }}"</p>
                    </div>
                @endif
            </div>

            <!-- Card 2: Panduan & Solusi Kendala Belanja -->
            <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-4 sm:p-5 space-y-3.5">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#6B4226]">Pusat Panduan & Solusi Kendala</h3>

                <div class="space-y-3 text-xs text-slate-700">
                    <div class="p-3 bg-[#FAF8F5] rounded-xl border border-[#EAE1D7] space-y-1">
                        <h4 class="font-bold text-slate-900 flex items-center gap-1.5">
                            <span>📦</span>
                            <span>Cara Mengajukan Retur / Pengembalian Barang</span>
                        </h4>
                        <p class="text-slate-600 leading-relaxed">
                            Bila barang pesanan tidak sesuai, rusak, atau salah varian, buka halaman <strong>Pesanan Saya</strong>, pilih pesanan terkait, dan klik tombol <em>Ajukan Pengembalian</em> maksimal 2x24 jam setelah paket diterima.
                        </p>
                        <a href="{{ route('my.orders') }}" class="text-[11px] font-bold text-[#6B4226] hover:underline inline-block pt-1">
                            Buka Pesanan Saya →
                        </a>
                    </div>

                    <div class="p-3 bg-[#FAF8F5] rounded-xl border border-[#EAE1D7] space-y-1">
                        <h4 class="font-bold text-slate-900 flex items-center gap-1.5">
                            <span>🛡️</span>
                            <span>Jaminan Perlindungan Transaksi Pembeli</span>
                        </h4>
                        <p class="text-slate-600 leading-relaxed">
                            Seluruh dana pembayaran kamu aman dalam rekening bersama resmi NusantaraMart hingga pesanan sampai dengan selamat dan kamu konfirmasi kelengkapannya.
                        </p>
                    </div>

                    <div class="p-3 bg-[#FAF8F5] rounded-xl border border-[#EAE1D7] space-y-1">
                        <h4 class="font-bold text-slate-900 flex items-center gap-1.5">
                            <span>⚖️</span>
                            <span>Prosedur Pengajuan Banding Akun / Toko</span>
                        </h4>
                        <p class="text-slate-600 leading-relaxed">
                            Jika akun atau toko mitra kamu mengalami kendala sanksi atau pembatasan, kamu dapat mengajukan banding resmi langsung melalui formulir banding yang tersedia di halaman login saat mengakses akun.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card 3: Kontak Layanan Pengaduan Resmi -->
            <div class="bg-white rounded-[13px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-4 sm:p-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">Butuh Bantuan Langsung?</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Diskusikan pertanyaan seputar pesanan langsung dengan penjual melalui fitur chat.</p>
                </div>
                <a href="{{ route('chat.index') }}" 
                   class="px-3.5 py-2 bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0 flex items-center gap-1.5 active:scale-95">
                    <span>💬</span>
                    <span>Chat Sekarang</span>
                </a>
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- 6. SUB-VIEW: TIPE AKUN & MEMBERSHIP NUSANTARAMART -->
        <!-- ======================================================== -->
        <div x-show="currentView === 'membership'" x-cloak class="space-y-5">
            
            <!-- Header with Inline Circular Back Button -->
            <div class="flex items-center gap-3 pt-1 pb-1">
                <button type="button" 
                        @click="currentView = 'menu'" 
                        class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                        title="Kembali">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-[24px] sm:text-[28px] font-bold text-[#000000] tracking-tight leading-tight">
                        Status & Keuntungan Member
                    </h1>
                    <p class="text-xs text-[#8E8E93]">Tipe Akun {{ $tier ?? 'Member' }}</p>
                </div>
            </div>

            <!-- LUXURY DIGITAL MEMBERSHIP CARD (DYNAMIC PER TIER) -->
            @php
                $cardGrad = match($tierKey ?? 'silver') {
                    'platinum' => 'bg-gradient-to-tr from-[#020617] via-[#0F172A] to-[#312E81]',
                    'gold' => 'bg-gradient-to-tr from-[#2A160A] via-[#54321B] to-[#A16207]',
                    default => 'bg-gradient-to-tr from-[#1E293B] via-[#334155] to-[#475569]',
                };
                $cardAccent = match($tierKey ?? 'silver') {
                    'platinum' => 'text-indigo-200/80',
                    'gold' => 'text-amber-200/80',
                    default => 'text-slate-200/80',
                };
            @endphp
            <div class="relative overflow-hidden rounded-[24px] {{ $cardGrad }} text-white p-6 shadow-xl border border-white/10">
                <!-- Ambient luxury glow -->
                <div class="absolute -right-10 -top-10 w-44 h-44 bg-white/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-44 h-44 bg-black/40 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 space-y-6">
                    <!-- Top Card Header: Brand & Tier Badge -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-lg border border-white/20">
                                🛍️
                            </div>
                            <div>
                                <h3 class="font-extrabold text-sm tracking-tight leading-tight">NusantaraMart</h3>
                                <p class="text-[10px] {{ $cardAccent }} font-bold uppercase tracking-wider">Official Member Privilege</p>
                            </div>
                        </div>
                        <div class="px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-xs font-extrabold flex items-center gap-1.5 shadow-sm">
                            <span>{{ $tierBadge ?? '🥈' }}</span>
                            <span>{{ $tier ?? 'Silver Member' }}</span>
                        </div>
                    </div>

                    <!-- Middle Card: Chip & Member Details -->
                    <div class="space-y-2 pt-1">
                        <div class="w-10 h-7 rounded-md bg-gradient-to-tr from-amber-200 to-yellow-400 opacity-90 shadow-inner flex items-center justify-center">
                            <div class="w-8 h-5 border border-black/20 rounded-xs grid grid-cols-2 gap-0.5 opacity-60"></div>
                        </div>
                        <div>
                            <p class="text-lg sm:text-xl font-extrabold tracking-wide text-white drop-shadow-xs">{{ $user->name }}</p>
                            <p class="text-xs {{ $cardAccent }} font-mono tracking-wider">ID: NM-{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }} • @<span>{{ $user->username ?? 'member' }}</span></p>
                        </div>
                    </div>

                    <!-- Bottom Card Footer: Joined Date & Status -->
                    <div class="pt-3 border-t border-white/15 flex items-center justify-between text-[11px] {{ $cardAccent }} font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Akun Terverifikasi Aktif</span>
                        </div>
                        <div>
                            <span>Bergabung Sejak {{ $user->created_at ? $user->created_at->translatedFormat('M Y') : '2026' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STATS / LOYALTY DASHBOARD GRID -->
            <div class="grid grid-cols-3 gap-2.5">
                <div class="bg-white rounded-2xl p-3.5 border border-[#EAE1D7] shadow-[0_1px_2px_rgba(0,0,0,0.04)] text-center space-y-1">
                    <p class="text-[11px] text-[#8E8E93] font-medium">Total Belanja</p>
                    <p class="text-xs sm:text-sm font-extrabold text-[#2D241E] truncate">Rp {{ number_format($totalSpent ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-3.5 border border-[#EAE1D7] shadow-[0_1px_2px_rgba(0,0,0,0.04)] text-center space-y-1">
                    <p class="text-[11px] text-[#8E8E93] font-medium">Transaksi</p>
                    <p class="text-xs sm:text-sm font-extrabold text-[#2D241E]">{{ $totalOrders ?? 0 }} Pesanan</p>
                </div>
                <div class="bg-white rounded-2xl p-3.5 border border-[#EAE1D7] shadow-[0_1px_2px_rgba(0,0,0,0.04)] text-center space-y-1">
                    <p class="text-[11px] text-[#8E8E93] font-medium">Koin Reward</p>
                    <p class="text-xs sm:text-sm font-extrabold text-amber-600 flex items-center justify-center gap-1">
                        <span>🪙</span>
                        <span>{{ number_format($memberPoints ?? 0, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <!-- 3 TIERS PROGRESSION & ROADMAP (SILVER / GOLD / PLATINUM) -->
            <div class="space-y-3">
                <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4">
                    Tingkatan Member NusantaraMart
                </p>

                <!-- Progression Bar if not top tier -->
                @if (!empty($nextTier))
                    <div class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-[0_1px_2px_rgba(0,0,0,0.04)] space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-[#2D241E]">Menuju {{ $nextTier }}</span>
                            <span class="font-bold text-[#6B4226]">{{ $progressPercent }}%</span>
                        </div>
                        <div class="w-full h-2.5 bg-[#F2EAE0] rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[#8A5A36] to-[#6B4226] rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                        </div>
                        <p class="text-[11px] text-[#8A7C70]">
                            Belanja <span class="font-bold text-[#2D241E]">Rp {{ number_format(max(0, $nextThreshold - $totalSpent), 0, ',', '.') }}</span> lagi untuk naik tingkat ke <span class="font-bold text-[#6B4226]">{{ $nextTier }}</span> dan nikmati lebih banyak keuntungan eksklusif!
                        </p>
                    </div>
                @endif

                <!-- 3 Tiers Comparison Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    
                    <!-- 1. SILVER TIER -->
                    <div class="rounded-2xl p-4 border transition {{ ($tierKey ?? 'silver') === 'silver' ? 'bg-[#F8FAFC] border-slate-400 ring-2 ring-slate-400/20 shadow-sm' : 'bg-white border-[#EAE1D7] opacity-80' }} space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-2xl">🥈</span>
                            @if (($tierKey ?? 'silver') === 'silver')
                                <span class="px-2 py-0.5 bg-slate-200 text-slate-800 text-[10px] font-extrabold rounded-md">Tier Kamu</span>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-[#2D241E]">Silver Member</h4>
                            <p class="text-[10px] text-[#8A7C70]">Belanja s.d Rp 250rb</p>
                        </div>
                        <ul class="text-[11px] text-[#55473C] space-y-1.5 pt-1 border-t border-slate-200">
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Voucher Sambutan</span></li>
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Gratis Ongkir Reguler</span></li>
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>1 Poin / Rp 1.000</span></li>
                        </ul>
                    </div>

                    <!-- 2. GOLD TIER -->
                    <div class="rounded-2xl p-4 border transition {{ ($tierKey ?? '') === 'gold' ? 'bg-[#FFFBEB] border-amber-400 ring-2 ring-amber-400/20 shadow-sm' : 'bg-white border-[#EAE1D7] opacity-80' }} space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-2xl">🥇</span>
                            @if (($tierKey ?? '') === 'gold')
                                <span class="px-2 py-0.5 bg-amber-200 text-amber-900 text-[10px] font-extrabold rounded-md">Tier Kamu</span>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-[#2D241E]">Gold Member</h4>
                            <p class="text-[10px] text-[#8A7C70]">Belanja Rp 250rb - 1jt</p>
                        </div>
                        <ul class="text-[11px] text-[#55473C] space-y-1.5 pt-1 border-t border-amber-200">
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Semua Fitur Silver</span></li>
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Voucher Bulanan 30%</span></li>
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Akses Awal Flash Sale</span></li>
                        </ul>
                    </div>

                    <!-- 3. PLATINUM TIER -->
                    <div class="rounded-2xl p-4 border transition {{ ($tierKey ?? '') === 'platinum' ? 'bg-[#F5F3FF] border-purple-400 ring-2 ring-purple-400/20 shadow-sm' : 'bg-white border-[#EAE1D7] opacity-80' }} space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-2xl">👑</span>
                            @if (($tierKey ?? '') === 'platinum')
                                <span class="px-2 py-0.5 bg-purple-200 text-purple-900 text-[10px] font-extrabold rounded-md">Tier Kamu</span>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-[#2D241E]">Platinum Member</h4>
                            <p class="text-[10px] text-[#8A7C70]">Belanja > Rp 1jt</p>
                        </div>
                        <ul class="text-[11px] text-[#55473C] space-y-1.5 pt-1 border-t border-purple-200">
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Semua Fitur Gold</span></li>
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Bebas Ongkir Bebas Batas</span></li>
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Voucher Eksklusif 50%</span></li>
                            <li class="flex items-center gap-1.5"><span>✓</span> <span>Layanan CS VIP Prioritas</span></li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- EXCLUSIVE PERKS & BENEFITS LIST -->
            <div class="space-y-2">
                <p class="text-[13px] uppercase font-normal text-[#6D6D72] tracking-normal px-4">
                    Keuntungan & Hak Istimewa Member
                </p>

                <div class="bg-white rounded-[16px] shadow-[0_1px_2px_rgba(0,0,0,0.04)] border border-black/[0.04] p-4 space-y-4">
                    
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg shrink-0 border border-amber-200/60">
                            🎟️
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-[14px] font-bold text-[#2D241E]">Voucher Diskon Eksklusif</h4>
                            <p class="text-[12px] text-[#7A6C60] leading-relaxed">Klaim kupon potongan harga hingga 50% setiap awal bulan dan momen festival belanja.</p>
                        </div>
                    </div>

                    <div class="border-t border-[#F2EAE0]"></div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg shrink-0 border border-emerald-200/60">
                            🚚
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-[14px] font-bold text-[#2D241E]">Bebas Ongkir ke Seluruh Indonesia</h4>
                            <p class="text-[12px] text-[#7A6C60] leading-relaxed">Subsidi gratis ongkir reguler maupun kargo tanpa syarat rumit untuk semua pesananmu.</p>
                        </div>
                    </div>

                    <div class="border-t border-[#F2EAE0]"></div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-lg shrink-0 border border-blue-200/60">
                            ⚡
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-[14px] font-bold text-[#2D241E]">Akses Awal Flash Sale</h4>
                            <p class="text-[12px] text-[#7A6C60] leading-relaxed">Nikmati akses 15 menit lebih awal untuk membeli produk diskon kilat sebelum kuota habis.</p>
                        </div>
                    </div>

                    <div class="border-t border-[#F2EAE0]"></div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-lg shrink-0 border border-purple-200/60">
                            🎁
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-[14px] font-bold text-[#2D241E]">Reward Ulang Tahun Spesial</h4>
                            <p class="text-[12px] text-[#7A6C60] leading-relaxed">Bonus koin belanja ganda dan hadiah kejutan eksklusif di hari ulang tahunmu.</p>
                        </div>
                    </div>

                    <div class="border-t border-[#F2EAE0]"></div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center text-lg shrink-0 border border-teal-200/60">
                            🛡️
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-[14px] font-bold text-[#2D241E]">Jaminan 100% Produk Original</h4>
                            <p class="text-[12px] text-[#7A6C60] leading-relaxed">Garansi retur mudah dan penggantian dana instan jika pesanan tidak sesuai.</p>
                        </div>
                    </div>

                    <div class="border-t border-[#F2EAE0]"></div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center text-lg shrink-0 border border-rose-200/60">
                            💬
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-[14px] font-bold text-[#2D241E]">Layanan Pelanggan Prioritas</h4>
                            <p class="text-[12px] text-[#7A6C60] leading-relaxed">Bantuan cepat tim CS dedicated 24 jam via live chat & WhatsApp resmi.</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ACTION BUTTON: SHOP & COLLECT POINTS -->
            <div class="pt-2 pb-6">
                <a href="{{ route('home') }}#katalog" 
                   class="w-full py-3.5 bg-[#6B4226] hover:bg-[#54321B] active:bg-[#3D2313] text-white font-bold text-[15px] rounded-[14px] shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>🛍️</span>
                    <span>Mulai Belanja & Kumpulkan Koin</span>
                </a>
            </div>

        </div>

    </div>

    {{-- MODAL HAPUS AKUN PENGGUNA --}}
    <div x-show="deleteAccountModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-5 border border-stone-100"
             @click.away="deleteAccountModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl shrink-0">
                    🗑️
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-stone-900">Hapus Akun Pengguna</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                </div>
                <button type="button" @click="deleteAccountModalOpen = false" class="text-stone-400 hover:text-stone-600 text-lg leading-none p-1 cursor-pointer">✕</button>
            </div>

            <!-- Warning Callout -->
            <div class="p-3.5 bg-rose-50 border border-rose-200/80 rounded-xl space-y-2 text-xs text-rose-800">
                <p class="font-semibold flex items-center gap-1.5 text-rose-900">
                    <span>⚠️</span> Perhatian Penting:
                </p>
                <ul class="list-disc list-inside space-y-1 text-[11px] text-rose-700 pl-1">
                    <li>Seluruh data profil, alamat tersimpan, dan riwayat pesanan akan dihapus.</li>
                    <li>Saldo Koin dan kupon voucher yang belum digunakan akan hangus.</li>
                    @if($user->store)
                        <li class="font-bold text-rose-950">Toko kamu (<strong>{{ $user->store->name }}</strong>) dan seluruh produknya akan ditutup dan dihapus secara permanen.</li>
                    @endif
                </ul>
            </div>

            <!-- Form -->
            <form action="{{ route('settings.delete_account') }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')

                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                        Konfirmasi Kata Sandi Akun <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showDeletePass ? 'text' : 'password'" 
                               name="password" 
                               required 
                               placeholder="Masukkan kata sandi saat ini..."
                               class="w-full text-xs sm:text-sm border border-stone-200 rounded-xl px-3 py-2.5 pr-16 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        <button type="button" 
                                @click="showDeletePass = !showDeletePass" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 text-xs font-semibold cursor-pointer">
                            <span x-text="showDeletePass ? 'Sembunyi' : 'Lihat'"></span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 pt-2">
                    <button type="button" 
                            @click="deleteAccountModalOpen = false" 
                            class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold text-stone-700 bg-stone-100 hover:bg-stone-200 transition text-center cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 transition shadow-sm text-center flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>🗑️</span>
                        <span>Hapus Permanen</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const INDONESIA_REGIONS = {
        'Jawa Barat': {
            'Kota Bandung': {
                'Coblong': '40132',
                'Sukajadi': '40161',
                'Cicendo': '40171',
                'Sumur Bandung': '40111',
                'Lengkong': '40261',
                'Andir': '40181',
                'Cibeunying Kaler': '40122',
                'Cibeunying Kidul': '40124',
                'Buahbatu': '40286',
                'Kiaracondong': '40285',
                'Regol': '40251',
                'Antapani': '40291',
                'Arcamanik': '40293',
                'Batununggal': '40266',
                'Bojongloa Kaler': '40232',
                'Bojongloa Kidul': '40235',
                'Cibiru': '40614',
                'Ujungberung': '40611',
                'Gedebage': '40294'
            },
            'Kabupaten Bandung': {
                'Soreang': '40911',
                'Baleendah': '40375',
                'Dayeuhkolot': '40258',
                'Bojongsoang': '40287',
                'Margahayu': '40225',
                'Ciwidey': '40973',
                'Pangalengan': '40378',
                'Banjaran': '40377',
                'Katapang': '40921',
                'Cicalengka': '40395',
                'Cileunyi': '40622',
                'Rancaekek': '40394'
            },
            'Kabupaten Bandung Barat': {
                'Lembang': '40391',
                'Padalarang': '40553',
                'Ngamprah': '40552',
                'Parongpong': '40559',
                'Batujajar': '40561',
                'Cisarua': '40551'
            },
            'Kota Cimahi': {
                'Cimahi Utara': '40512',
                'Cimahi Tengah': '40525',
                'Cimahi Selatan': '40534'
            },
            'Kota Bogor': {
                'Bogor Tengah': '16121',
                'Bogor Timur': '16143',
                'Bogor Selatan': '16132',
                'Bogor Barat': '16118',
                'Bogor Utara': '16152',
                'Tanah Sareal': '16161'
            },
            'Kabupaten Bogor': {
                'Cibinong': '16911',
                'Bojong Gede': '16922',
                'Cisarua': '16750',
                'Ciawi': '16720',
                'Citeureup': '16810',
                'Gunung Putri': '16961'
            },
            'Kota Bekasi': {
                'Bekasi Barat': '17145',
                'Bekasi Timur': '17113',
                'Bekasi Selatan': '17148',
                'Bekasi Utara': '17124',
                'Pondok Gede': '17411',
                'Jatiasih': '17423',
                'Medan Satria': '17132',
                'Mustika Jaya': '17158'
            },
            'Kabupaten Bekasi': {
                'Cikarang Pusat': '17530',
                'Cikarang Barat': '17520',
                'Cikarang Utara': '17534',
                'Cikarang Selatan': '17530',
                'Tambun Selatan': '17510',
                'Tambun Utara': '17510'
            },
            'Kota Depok': {
                'Pancoran Mas': '16436',
                'Beji': '16421',
                'Sukmajaya': '16412',
                'Cimanggis': '16452',
                'Cinere': '16514',
                'Sawangan': '16511',
                'Tapos': '16457'
            },
            'Kota Cirebon': {
                'Kejaksan': '45123',
                'Kesambi': '45134',
                'Harjamukti': '45143',
                'Lemahwungkuk': '45111',
                'Pekalipan': '45116'
            },
            'Kota Sukabumi': {
                'Cikole': '43111',
                'Citamiang': '43141',
                'Warudoyong': '43131',
                'Gunungpuyuh': '43123'
            },
            'Kota Tasikmalaya': {
                'Cihideung': '46122',
                'Cipedes': '46133',
                'Tawang': '46111',
                'Indihiang': '46151'
            },
            'Kabupaten Garut': {
                'Garut Kota': '44111',
                'Tarogong Kidul': '44151',
                'Tarogong Kaler': '44151'
            },
            'Kabupaten Karawang': {
                'Karawang Barat': '41311',
                'Karawang Timur': '41314',
                'Telukjambe Timur': '41361',
                'Klari': '41371'
            }
        },
        'DKI Jakarta': {
            'Jakarta Selatan': {
                'Kebayoran Baru': '12110',
                'Kebayoran Lama': '12240',
                'Cilandak': '12430',
                'Pasar Minggu': '12520',
                'Setiabudi': '12910',
                'Tebet': '12810',
                'Mampang Prapatan': '12790',
                'Pancoran': '12780',
                'Jagakarsa': '12620',
                'Pesanggrahan': '12320'
            },
            'Jakarta Pusat': {
                'Gambir': '10110',
                'Tanah Abang': '10210',
                'Menteng': '10310',
                'Senen': '10410',
                'Cempaka Putih': '10510',
                'Johar Baru': '10560',
                'Kemayoran': '10610',
                'Sawah Besar': '10710'
            },
            'Jakarta Barat': {
                'Kebon Jeruk': '11530',
                'Palmerah': '11480',
                'Grogol Petamburan': '11450',
                'Kembangan': '11610',
                'Cengkareng': '11730',
                'Kalideres': '11840',
                'Taman Sari': '11110',
                'Tambora': '11220'
            },
            'Jakarta Timur': {
                'Jatinegara': '13310',
                'Duren Sawit': '13440',
                'Kramat Jati': '13510',
                'Pasar Rebo': '13760',
                'Ciracas': '13740',
                'Cipayung': '13840',
                'Cakung': '13910',
                'Pulo Gadung': '13220',
                'Matraman': '13110'
            },
            'Jakarta Utara': {
                'Kelapa Gading': '14240',
                'Tanjung Priok': '14310',
                'Pademangan': '14420',
                'Penjaringan': '14450',
                'Koja': '14220',
                'Cilincing': '14120'
            },
            'Kepulauan Seribu': {
                'Kepulauan Seribu Selatan': '14510',
                'Kepulauan Seribu Utara': '14520'
            }
        },
        'Banten': {
            'Kota Tangerang': {
                'Tangerang': '15111',
                'Cipondoh': '15148',
                'Ciledug': '15151',
                'Karawaci': '15115',
                'Batuceper': '15122',
                'Pinang': '15145',
                'Larangan': '15154'
            },
            'Kota Tangerang Selatan': {
                'Serpong': '15310',
                'Serpong Utara': '15326',
                'Pondok Aren': '15224',
                'Ciputat': '15411',
                'Ciputat Timur': '15419',
                'Pamulang': '15417',
                'Setu': '15314'
            },
            'Kota Serang': {
                'Serang': '42111',
                'Cipocok Jaya': '42121',
                'Curug': '42171',
                'Taktakan': '42162',
                'Kasemen': '42191'
            },
            'Kota Cilegon': {
                'Cilegon': '42414',
                'Cibeber': '42423',
                'Purwakarta': '42437',
                'Grogol': '42436',
                'Jombang': '42411'
            },
            'Kabupaten Tangerang': {
                'Tigaraksa': '15720',
                'Kelapa Dua': '15810',
                'Curug': '15810',
                'Cikupa': '15710',
                'Balaraja': '15610'
            }
        },
        'Jawa Tengah': {
            'Kota Semarang': {
                'Semarang Tengah': '50132',
                'Semarang Barat': '50149',
                'Semarang Selatan': '50249',
                'Semarang Timur': '50125',
                'Semarang Utara': '50171',
                'Banyumanik': '50269',
                'Candisari': '50257',
                'Pedurungan': '50192',
                'Tembalang': '50275',
                'Ngaliyan': '50181'
            },
            'Kota Surakarta (Solo)': {
                'Banjarsari': '57139',
                'Jebres': '57126',
                'Laweyan': '57148',
                'Pasar Kliwon': '57118',
                'Serengan': '57155'
            },
            'Kota Magelang': {
                'Magelang Tengah': '56117',
                'Magelang Selatan': '56126',
                'Magelang Utara': '56116'
            },
            'Kota Salatiga': {
                'Sidorejo': '50711',
                'Tingkir': '50742',
                'Argomulyo': '50732',
                'Sidomukti': '50724'
            },
            'Kabupaten Banyumas': {
                'Purwokerto Timur': '53111',
                'Purwokerto Barat': '53131',
                'Purwokerto Selatan': '53141',
                'Purwokerto Utara': '53121'
            },
            'Kabupaten Kudus': {
                'Kota Kudus': '59311',
                'Jati': '59349',
                'Bae': '59352'
            }
        },
        'DI Yogyakarta': {
            'Kota Yogyakarta': {
                'Danurejan': '55211',
                'Gedongtengen': '55271',
                'Gondokusuman': '55221',
                'Gondomanan': '55121',
                'Jetis': '55231',
                'Kotagede': '55171',
                'Kraton': '55131',
                'Mantrijeron': '55141',
                'Mergangsan': '55151',
                'Ngampilan': '55261',
                'Pakualaman': '55112',
                'Tegalrejo': '55244',
                'Umbulharjo': '55161',
                'Wirobrajan': '55252'
            },
            'Kabupaten Sleman': {
                'Depok': '55281',
                'Mlati': '55284',
                'Ngaglik': '55581',
                'Gamping': '55294',
                'Kalasan': '55571',
                'Sleman': '55511'
            },
            'Kabupaten Bantul': {
                'Bantul': '55711',
                'Banguntapan': '55198',
                'Sewon': '55188',
                'Kasihan': '55181',
                'Piyungan': '55792'
            }
        },
        'Jawa Timur': {
            'Kota Surabaya': {
                'Gubeng': '60281',
                'Tegalsari': '60261',
                'Genteng': '60275',
                'Wonokromo': '60241',
                'Rungkut': '60293',
                'Sukolilo': '60111',
                'Mulyorejo': '60115',
                'Sawahan': '60251',
                'Dukuh Pakis': '60225',
                'Wiyung': '60228',
                'Tambaksari': '60136',
                'Kenjeran': '60129'
            },
            'Kota Malang': {
                'Klojen': '65111',
                'Blimbing': '65126',
                'Lowokwaru': '65141',
                'Sukun': '65147',
                'Kedungkandang': '65136'
            },
            'Kota Batu': {
                'Batu': '65311',
                'Bumiaji': '65332',
                'Junrejo': '65321'
            },
            'Kabupaten Sidoarjo': {
                'Sidoarjo': '61212',
                'Waru': '61256',
                'Gedangan': '61254',
                'Taman': '61257',
                'Candi': '61271'
            },
            'Kabupaten Gresik': {
                'Gresik': '61111',
                'Kebomas': '61121',
                'Manyar': '61151',
                'Driyorejo': '61177'
            }
        },
        'Bali': {
            'Kota Denpasar': {
                'Denpasar Barat': '80119',
                'Denpasar Timur': '80237',
                'Denpasar Selatan': '80227',
                'Denpasar Utara': '80115'
            },
            'Kabupaten Badung': {
                'Kuta': '80361',
                'Kuta Selatan': '80362',
                'Kuta Utara': '80361',
                'Mengwi': '80351',
                'Abiansemal': '80352'
            },
            'Kabupaten Gianyar': {
                'Ubud': '80571',
                'Gianyar': '80511',
                'Sukawati': '80582',
                'Blahbatuh': '80581'
            }
        },
        'Sumatera Utara': {
            'Kota Medan': {
                'Medan Kota': '20212',
                'Medan Petisah': '20112',
                'Medan Baru': '20153',
                'Medan Selayang': '20131',
                'Medan Sunggal': '20128',
                'Medan Helvetia': '20124',
                'Medan Barat': '20111',
                'Medan Timur': '20236',
                'Medan Denai': '20227',
                'Medan Amplas': '20148'
            },
            'Kota Pematangsiantar': {
                'Siantar Barat': '21111',
                'Siantar Timur': '21121',
                'Siantar Selatan': '21125',
                'Siantar Utara': '21143'
            },
            'Kabupaten Deli Serdang': {
                'Lubuk Pakam': '20511',
                'Percut Sei Tuan': '20371',
                'Sunggal': '20351'
            }
        },
        'Sumatera Barat': {
            'Kota Padang': {
                'Padang Barat': '25112',
                'Padang Timur': '25121',
                'Padang Selatan': '25131',
                'Padang Utara': '25173',
                'Koto Tangah': '25586',
                'Kuranji': '25157'
            },
            'Kota Bukittinggi': {
                'Guguk Panjang': '26111',
                'Mandiangin Koto Selayan': '26122',
                'Aur Birugo Tigo Baleh': '26131'
            }
        },
        'Riau': {
            'Kota Pekanbaru': {
                'Sukajadi': '28121',
                'Tampan': '28291',
                'Marpoyan Damai': '28282',
                'Payung Sekaki': '28292',
                'Bukit Raya': '28288',
                'Senapelan': '28151',
                'Tenayan Raya': '28285'
            },
            'Kota Dumai': {
                'Dumai Timur': '28811',
                'Dumai Barat': '28821',
                'Dumai Kota': '28811'
            }
        },
        'Sumatera Selatan': {
            'Kota Palembang': {
                'Ilir Barat I': '30139',
                'Ilir Timur I': '30126',
                'Sukarami': '30151',
                'Seberang Ulu I': '30251',
                'Kemuning': '30127',
                'Alang-Alang Lebar': '30154'
            }
        },
        'Lampung': {
            'Kota Bandar Lampung': {
                'Tanjung Karang Pusat': '35111',
                'Tanjung Karang Barat': '35152',
                'Teluk Betung Selatan': '35221',
                'Kedaton': '35141',
                'Way Halim': '35141',
                'Sukarame': '35131'
            },
            'Kota Metro': {
                'Metro Pusat': '34111',
                'Metro Barat': '34121',
                'Metro Timur': '34111'
            }
        },
        'Kalimantan Timur': {
            'Kota Balikpapan': {
                'Balikpapan Kota': '76111',
                'Balikpapan Selatan': '76114',
                'Balikpapan Tengah': '76122',
                'Balikpapan Utara': '76125',
                'Balikpapan Barat': '76131'
            },
            'Kota Samarinda': {
                'Samarinda Kota': '75111',
                'Samarinda Ulu': '75123',
                'Samarinda Utara': '75119',
                'Sungai Kunjang': '75126',
                'Palaran': '75241'
            }
        },
        'Sulawesi Selatan': {
            'Kota Makassar': {
                'Ujung Pandang': '90111',
                'Panakkukang': '90231',
                'Rappocini': '90222',
                'Tamalate': '90223',
                'Bontoala': '90151',
                'Makassar': '90141',
                'Tamalanrea': '90245',
                'Manggala': '90234'
            }
        }
    };

    function settingsPage(config) {
        return {
            currentView: config.initialView || 'menu',
            searchQuery: '',
            addressLabel: config.initialLabel || 'Rumah',
            mapPinned: config.hasMapPin,
            showCurrentPass: false,
            showNewPass: false,
            deleteAccountModalOpen: false,
            showDeletePass: false,

            // Voucher / Promo Code State
            redeemInput: '',
            redeemLoading: false,
            redeemResult: null,

            async checkRedeemCode() {
                const code = this.redeemInput.trim();
                if (!code) return;
                this.redeemLoading = true;
                this.redeemResult = null;
                try {
                    const res = await fetch(`/api/redeem-codes/check?code=${encodeURIComponent(code)}`);
                    const data = await res.json();
                    this.redeemResult = data;
                } catch (e) {
                    this.redeemResult = { success: false, message: 'Gagal menghubungi server untuk verifikasi kode voucher.' };
                } finally {
                    this.redeemLoading = false;
                }
            },

            // Cascading Regional Address State
            regionData: INDONESIA_REGIONS,
            selectedProvince: config.initialProvince || '',
            selectedCity: config.initialCity || '',
            selectedDistrict: config.initialDistrict || '',
            postalCode: config.initialPostalCode || '',

            // Google Maps State
            showMapModal: false,
            currentLat: (config.initialLat !== null && config.initialLat !== '' && !isNaN(config.initialLat)) ? parseFloat(config.initialLat) : null,
            currentLng: (config.initialLng !== null && config.initialLng !== '' && !isNaN(config.initialLng)) ? parseFloat(config.initialLng) : null,
            hasCustomPin: (config.initialLat !== null && config.initialLng !== null && config.initialLat !== '' && config.initialLng !== ''),
            geoAddress: '',
            mapInstance: null,
            markerInstance: null,
            geocoderInstance: null,

            init() {
                window.__settingsApp = this;

                // Only validate existing values if already set
                if (this.selectedProvince && this.regionData[this.selectedProvince]) {
                    const cities = this.availableCities;
                    if (!cities.includes(this.selectedCity)) {
                        this.selectedCity = '';
                        this.selectedDistrict = '';
                        this.postalCode = '';
                    } else {
                        const districts = this.availableDistricts;
                        if (!districts.includes(this.selectedDistrict)) {
                            this.selectedDistrict = '';
                            this.postalCode = '';
                        }
                    }
                } else {
                    this.selectedProvince = '';
                    this.selectedCity = '';
                    this.selectedDistrict = '';
                    this.postalCode = '';
                }

                // Initial reverse geocode if coordinates available
                setTimeout(() => {
                    if (typeof google !== 'undefined' && google.maps && this.hasCustomPin && this.currentLat !== null) {
                        this.reverseGeocode(this.currentLat, this.currentLng);
                    }
                }, 800);
            },

            openMapModal() {
                this.showMapModal = true;
                document.body.style.overflow = 'hidden';
                setTimeout(() => this.initMap(), 200);
            },

            closeMapModal() {
                this.showMapModal = false;
                document.body.style.overflow = '';
            },

            initMap() {
                const container = document.getElementById('googleMapsContainer');
                if (!container || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                const hasCoord = (this.currentLat !== null && this.currentLng !== null);
                const defaultPos = hasCoord 
                    ? { lat: this.currentLat, lng: this.currentLng } 
                    : { lat: -6.2088, lng: 106.8456 }; // General default view (Indonesia)

                if (!this.mapInstance) {
                    this.geocoderInstance = new google.maps.Geocoder();
                    
                    this.mapInstance = new google.maps.Map(container, {
                        center: defaultPos,
                        zoom: hasCoord ? 16 : 11,
                        gestureHandling: 'greedy',
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: false,
                        zoomControl: true,
                    });

                    this.markerInstance = new google.maps.Marker({
                        position: defaultPos,
                        map: this.mapInstance,
                        draggable: true,
                        visible: hasCoord,
                        animation: google.maps.Animation.DROP,
                        title: 'Lokasi Pengiriman Rumahmu',
                    });

                    // Update on marker drag
                    this.markerInstance.addListener('dragend', (event) => {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();
                        this.currentLat = lat;
                        this.currentLng = lng;
                        this.hasCustomPin = true;
                        this.markerInstance.setVisible(true);
                        this.reverseGeocode(lat, lng);
                    });

                    // Click to move or place pin
                    this.mapInstance.addListener('click', (event) => {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();
                        this.currentLat = lat;
                        this.currentLng = lng;
                        this.hasCustomPin = true;
                        this.markerInstance.setPosition(event.latLng);
                        this.markerInstance.setVisible(true);
                        this.reverseGeocode(lat, lng);
                    });

                    // Perform initial reverse geocode if coordinate exists
                    if (hasCoord) {
                        this.reverseGeocode(this.currentLat, this.currentLng);
                    } else if (this.selectedDistrict && this.selectedCity) {
                        // Center map roughly around selected district without saving coordinate prematurely
                        this.geocoderInstance.geocode({ address: this.selectedDistrict + ', ' + this.selectedCity + ', ' + this.selectedProvince + ', Indonesia' }, (results, status) => {
                            if (status === 'OK' && results[0]) {
                                this.mapInstance.setCenter(results[0].geometry.location);
                                this.mapInstance.setZoom(13);
                            }
                        });
                    }
                } else {
                    google.maps.event.trigger(this.mapInstance, 'resize');
                    if (hasCoord) {
                        this.mapInstance.setCenter({ lat: this.currentLat, lng: this.currentLng });
                        this.markerInstance.setPosition({ lat: this.currentLat, lng: this.currentLng });
                        this.markerInstance.setVisible(true);
                    }
                }
            },

            getCurrentGpsLocation() {
                if (!navigator.geolocation) {
                    alert('Fitur Geolocation tidak didukung oleh browsermu.');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        this.currentLat = lat;
                        this.currentLng = lng;
                        this.hasCustomPin = true;

                        if (this.mapInstance && this.markerInstance) {
                            const newPos = new google.maps.LatLng(lat, lng);
                            this.mapInstance.panTo(newPos);
                            this.mapInstance.setZoom(17);
                            this.markerInstance.setPosition(newPos);
                            this.markerInstance.setVisible(true);
                            this.reverseGeocode(lat, lng);
                        }
                    },
                    (err) => {
                        alert('Gagal mendeteksi lokasi GPS. Pastikan izin akses lokasi telah diaktifkan di browsermu.');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            },

            reverseGeocode(lat, lng) {
                if (!this.geocoderInstance) return;

                this.geocoderInstance.geocode({ location: { lat, lng } }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        this.geoAddress = results[0].formatted_address;
                    }
                });
            },

            get availableProvinces() {
                return Object.keys(this.regionData);
            },

            get availableCities() {
                if (!this.regionData[this.selectedProvince]) return [];
                return Object.keys(this.regionData[this.selectedProvince]);
            },

            get availableDistricts() {
                if (!this.regionData[this.selectedProvince] || !this.regionData[this.selectedProvince][this.selectedCity]) return [];
                return Object.keys(this.regionData[this.selectedProvince][this.selectedCity]);
            },

            onProvinceChange() {
                this.selectedCity = '';
                this.selectedDistrict = '';
                this.postalCode = '';
            },

            onCityChange() {
                this.selectedDistrict = '';
                this.postalCode = '';
            },

            onDistrictChange() {
                if (this.selectedProvince && this.selectedCity && this.selectedDistrict &&
                    this.regionData[this.selectedProvince] && 
                    this.regionData[this.selectedProvince][this.selectedCity] && 
                    this.regionData[this.selectedProvince][this.selectedCity][this.selectedDistrict]) {
                    this.postalCode = this.regionData[this.selectedProvince][this.selectedCity][this.selectedDistrict];
                } else {
                    this.postalCode = '';
                }

                // If user hasn't explicitly pinpointed custom GPS, auto-pan map to selected district/city
                if (!this.hasCustomPin && this.geocoderInstance && this.mapInstance) {
                    const query = `${this.selectedDistrict}, ${this.selectedCity}, ${this.selectedProvince}, Indonesia`;
                    this.geocoderInstance.geocode({ address: query }, (results, status) => {
                        if (status === 'OK' && results[0]) {
                            const loc = results[0].geometry.location;
                            this.currentLat = loc.lat();
                            this.currentLng = loc.lng();
                            this.mapInstance.panTo(loc);
                            this.mapInstance.setZoom(15);
                            this.markerInstance.setPosition(loc);
                            this.geoAddress = results[0].formatted_address;
                        }
                    });
                }
            }
        };
    }

    // Global callback for Google Maps SDK loader
    function onGoogleMapsLoaded() {
        if (window.__settingsApp) {
            window.__settingsApp.initMap();
        }
    }
</script>

<!-- Google Maps JavaScript API SDK -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=onGoogleMapsLoaded" async defer></script>
@endsection
