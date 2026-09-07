@extends('layouts.app')

@section('title', 'Top Up Pulsa, Paket Data, Listrik PLN, BPJS & Tagihan Lengkap — NusantaraMart')

@section('content')
<div class="min-h-screen bg-[#FAF8F5] pb-16 space-y-8" x-data="topUpHub('{{ $activeTab }}')">

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
                        <span class="text-[#6B4226] font-bold">Top Up & Tagihan</span>
                    </div>
                    <h1 class="text-lg sm:text-xl font-black text-[#2D241E] tracking-tight flex items-center gap-2">
                        <span>Layanan Produk Digital & Pembayaran Tagihan Lengkap</span>
                        <span>📱⚡</span>
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL (RECEIPT NOTIFICATION) -->
    @if(session('topup_success'))
        @php $receipt = session('topup_success'); @endphp
        <div x-data="{ showReceiptModal: true }" 
             x-show="showReceiptModal" 
             x-cloak
             @click.self="showReceiptModal = false"
             class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-full h-full min-h-screen z-[9999] bg-black/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto animate-fade-in">
            
            <div @click.stop 
                 class="bg-white rounded-3xl max-w-sm sm:max-w-md w-full p-5 sm:p-6 shadow-2xl border border-[#EAE1D7] space-y-3.5 text-center relative my-auto max-h-[92vh] overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden animate-scale-up">
                
                <!-- Circular Close (X) Button -->
                <button type="button" 
                        @click="showReceiptModal = false" 
                        class="absolute top-3.5 right-3.5 w-8 h-8 rounded-full bg-[#FAF8F5] hover:bg-[#F2EAE0] active:scale-90 text-[#7A6C60] hover:text-[#2D241E] border border-[#EAE1D7] flex items-center justify-center transition shadow-2xs group cursor-pointer z-10"
                        title="Tutup Transaksi">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Status Badge with Pulsing Dot -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-bold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Transaksi Berhasil</span>
                </div>

                <!-- Modern Success Icon -->
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-lg shadow-emerald-600/25 mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <!-- Price & Service Hero Display -->
                <div class="space-y-0.5">
                    <span class="text-[11px] text-[#8A7C70] font-medium block">Total Pembayaran</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-[#2D241E] tracking-tight">
                        Rp {{ number_format($receipt['total'], 0, ',', '.') }}
                    </h2>
                    <p class="text-xs font-bold text-[#6B4226]">{{ $receipt['service'] }}</p>
                </div>

                <!-- PLN Token Display if applicable -->
                @if(!empty($receipt['token']))
                    <div class="p-3 rounded-2xl bg-amber-50/80 border border-amber-300 text-center space-y-1 shadow-xs">
                        <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider block">Nomor Token Listrik PLN:</span>
                        <div class="text-base sm:text-lg font-mono font-black text-[#6B4226] tracking-wider select-all bg-white py-1 px-3 rounded-xl border border-amber-300 shadow-inner">
                            {{ $receipt['token'] }}
                        </div>
                        <span class="text-[10px] text-amber-700 block">Masukkan 16 digit angka di atas pada kWh meter rumah Anda.</span>
                    </div>
                @endif

                <!-- Modern Ticket Receipt Card -->
                <div class="bg-[#FAF8F5] rounded-2xl p-3.5 border border-[#EAE1D7] text-left text-xs space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-[#8A7C70]">No. Pesanan:</span>
                        <span class="font-mono font-bold text-[#6B4226] text-[11px]">{{ $receipt['trx_code'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#8A7C70]">No. Tujuan:</span>
                        <span class="font-mono font-bold text-[#2D241E]">{{ $receipt['target'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#8A7C70]">Provider:</span>
                        <span class="font-bold text-[#2D241E]">{{ $receipt['provider'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#8A7C70]">Metode:</span>
                        <span class="font-bold text-[#2D241E] uppercase">{{ $receipt['method'] }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-1.5 border-t border-[#EAE1D7] text-[11px]">
                        <span class="text-[#8A7C70]">Waktu:</span>
                        <span class="text-[#5A4B40] font-medium">{{ $receipt['time'] }}</span>
                    </div>
                </div>

                <!-- Action CTA Buttons -->
                <div class="flex flex-col gap-2 pt-1">
                    @auth
                        <a href="{{ route('my.orders') }}" 
                           class="w-full py-3 px-4 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs flex items-center justify-center gap-2 transition active:scale-95 shadow-md shadow-[#6B4226]/20">
                            <span>📦 Lihat di Riwayat Pesanan Saya</span>
                            <span>→</span>
                        </a>
                    @endauth
                    <div class="flex gap-2">
                        <a href="{{ route('order.detail', ['order_code' => $receipt['trx_code']]) }}" 
                           class="flex-1 py-2.5 px-3 rounded-xl bg-white hover:bg-[#FAF8F5] text-[#6B4226] border border-[#EAE1D7] hover:border-[#6B4226]/40 font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-2xs">
                            <span>📄 Invoice Resmi</span>
                        </a>
                        <button type="button" 
                                @click="showReceiptModal = false" 
                                class="flex-1 py-2.5 px-3 rounded-xl bg-[#FAF8F5] hover:bg-[#F2EAE0] text-[#5A4B40] hover:text-[#2D241E] border border-[#EAE1D7] font-bold text-xs flex items-center justify-center gap-1 transition cursor-pointer">
                            <span>+ Transaksi Lagi</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- 2. HERO LUXURY PROMO BANNER -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#2D241E] via-[#4A2411] to-[#1E3A8A] text-white p-6 sm:p-8 lg:p-10 shadow-md">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
            <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-blue-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -bottom-10 w-48 h-48 rounded-full bg-amber-500/20 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-amber-200 text-xs font-black tracking-wider uppercase">
                        <span class="animate-pulse">⚡</span>
                        <span>Layanan Digital 24 Jam Nonstop · Otomatis & Cepat</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight">
                        Pusat Top Up Pulsa, Paket Data & Bayar Tagihan Terpercaya
                    </h2>

                    <p class="text-xs sm:text-sm text-amber-100/85 leading-relaxed max-w-xl">
                        Solusi praktis bayar listrik PLN, tagihan air PDAM, iuran BPJS, cicilan kendaraan, internet WiFi rumah, hingga voucher game dengan jaminan proses instan detik itu juga.
                    </p>

                    <!-- Trust Chips -->
                    <div class="flex items-center gap-2 sm:gap-3 flex-wrap pt-1 text-xs">
                        <span class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 text-white font-bold flex items-center gap-1.5">
                            <span>⚡</span>
                            <span>Proses Instan < 5 Detik</span>
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 text-white font-bold flex items-center gap-1.5">
                            <span>🔒</span>
                            <span>Garansi Saldo 100% Aman</span>
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 text-white font-bold flex items-center gap-1.5">
                            <span>💰</span>
                            <span>Pembayaran QRIS Praktis</span>
                        </span>
                    </div>
                </div>

                <!-- Right Visual Floating Stats Card -->
                <div class="hidden lg:flex flex-col items-center justify-center p-6 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 text-center shrink-0 min-w-[220px] shadow-inner space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-400 to-indigo-600 text-white flex items-center justify-center text-3xl shadow-md">
                        📱
                    </div>
                    <div>
                        <span class="text-xl font-black text-white block">
                            PPOB Nasional
                        </span>
                        <span class="text-[11px] text-amber-200/90 font-medium block">
                            Mitra Operator Resmi
                        </span>
                    </div>
                    <div class="pt-2 border-t border-white/15 w-full text-center">
                        <span class="text-[10px] text-emerald-300 font-bold bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-400/30">
                            ✓ Terhubung 24/7 Realtime
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TOP SERVICES TILES MATRIX (TOKOPEDIA / SHOPEE ICON GRID STYLE) -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#F2EAE0] pb-4">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-[#2D241E] flex items-center gap-2">
                        <span>Pilih Kategori Layanan Digital</span>
                        <span class="text-sm">⚡</span>
                    </h3>
                    <p class="text-xs text-[#8A7C70] mt-0.5">Klik kategori di bawah untuk langsung menuju formulir transaksi</p>
                </div>
                <span class="text-xs font-bold text-[#6B4226] bg-[#FAF4ED] px-3 py-1 rounded-xl border border-[#E8DED3]">
                    8 Kategori Utama
                </span>
            </div>

            <!-- 8 Big Feature Tiles -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 pt-2">
                <!-- 1. Pulsa & Data -->
                <button type="button" 
                        @click="selectTab('pulsa')"
                        class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-between gap-2 group cursor-pointer"
                        :class="activeTab === 'pulsa' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-sm ring-2 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white hover:border-[#6B4226]/40'">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-2xs">
                        📱
                    </div>
                    <div>
                        <span class="text-xs font-black text-[#2D241E] block leading-tight">Pulsa & Data</span>
                        <span class="text-[9px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded-md mt-1 inline-block">Diskon Promo</span>
                    </div>
                </button>

                <!-- 2. Listrik PLN -->
                <button type="button" 
                        @click="selectTab('pln')"
                        class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-between gap-2 group cursor-pointer"
                        :class="activeTab === 'pln' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-sm ring-2 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white hover:border-[#6B4226]/40'">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-2xs">
                        ⚡
                    </div>
                    <div>
                        <span class="text-xs font-black text-[#2D241E] block leading-tight">Listrik PLN</span>
                        <span class="text-[9px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded-md mt-1 inline-block">Token Instan</span>
                    </div>
                </button>

                <!-- 3. Air PDAM -->
                <button type="button" 
                        @click="selectTab('pdam')"
                        class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-between gap-2 group cursor-pointer"
                        :class="activeTab === 'pdam' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-sm ring-2 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white hover:border-[#6B4226]/40'">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-2xs">
                        💧
                    </div>
                    <div>
                        <span class="text-xs font-black text-[#2D241E] block leading-tight">Air PDAM</span>
                        <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded-md mt-1 inline-block">Nasional</span>
                    </div>
                </button>

                <!-- 4. BPJS -->
                <button type="button" 
                        @click="selectTab('bpjs')"
                        class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-between gap-2 group cursor-pointer"
                        :class="activeTab === 'bpjs' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-sm ring-2 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white hover:border-[#6B4226]/40'">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-2xs">
                        🏥
                    </div>
                    <div>
                        <span class="text-xs font-black text-[#2D241E] block leading-tight">BPJS</span>
                        <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded-md mt-1 inline-block">Keluarga</span>
                    </div>
                </button>

                <!-- 5. Internet & TV -->
                <button type="button" 
                        @click="selectTab('internet')"
                        class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-between gap-2 group cursor-pointer"
                        :class="activeTab === 'internet' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-sm ring-2 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white hover:border-[#6B4226]/40'">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-2xs">
                        🌐
                    </div>
                    <div>
                        <span class="text-xs font-black text-[#2D241E] block leading-tight">Internet & TV</span>
                        <span class="text-[9px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded-md mt-1 inline-block">Bebas Denda</span>
                    </div>
                </button>

                <!-- 6. Asuransi & Cicilan -->
                <button type="button" 
                        @click="selectTab('insurance')"
                        class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-between gap-2 group cursor-pointer"
                        :class="activeTab === 'insurance' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-sm ring-2 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white hover:border-[#6B4226]/40'">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-2xs">
                        🛡️
                    </div>
                    <div>
                        <span class="text-xs font-black text-[#2D241E] block leading-tight">Cicilan & Polis</span>
                        <span class="text-[9px] font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded-md mt-1 inline-block">Multifinance</span>
                    </div>
                </button>

                <!-- 7. E-Money -->
                <button type="button" 
                        @click="selectTab('emoney')"
                        class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-between gap-2 group cursor-pointer"
                        :class="activeTab === 'emoney' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-sm ring-2 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white hover:border-[#6B4226]/40'">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-2xs">
                        💳
                    </div>
                    <div>
                        <span class="text-xs font-black text-[#2D241E] block leading-tight">E-Money</span>
                        <span class="text-[9px] font-bold text-teal-700 bg-teal-50 px-1.5 py-0.5 rounded-md mt-1 inline-block">5 E-Wallet</span>
                    </div>
                </button>

                <!-- 8. Voucher Game -->
                <button type="button" 
                        @click="selectTab('game')"
                        class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-between gap-2 group cursor-pointer"
                        :class="activeTab === 'game' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-sm ring-2 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white hover:border-[#6B4226]/40'">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#E8DED3] flex items-center justify-center text-2xl group-hover:scale-110 transition shadow-2xs">
                        🎮
                    </div>
                    <div>
                        <span class="text-xs font-black text-[#2D241E] block leading-tight">Voucher Game</span>
                        <span class="text-[9px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded-md mt-1 inline-block">MLBB & FF</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- 4. MAIN WORKBENCH & CHECKOUT FORM -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN: WORKBENCH (8 COLS) -->
            <div class="lg:col-span-8 bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-8 shadow-xs space-y-6">
                
                <!-- ================= TAB 1: PULSA & PAKET DATA ================= -->
                <div x-show="activeTab === 'pulsa'" x-cloak class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#F2EAE0] pb-4">
                        <div>
                            <h3 class="text-base font-black text-[#2D241E] flex items-center gap-2">
                                <span>📱 Isi Pulsa & Paket Kuota Data</span>
                            </h3>
                            <p class="text-xs text-[#8A7C70] mt-0.5">Ketikkan nomor HP, operator akan otomatis terdeteksi</p>
                        </div>

                        <!-- Sub-Toggle: Pulsa vs Paket Data -->
                        <div class="flex rounded-2xl bg-[#FAF8F5] p-1 border border-[#EAE1D7] text-xs font-bold shrink-0">
                            <button type="button" 
                                    @click="pulsaSubtype = 'pulsa'; pickDenom(pulsaDenoms[1])"
                                    :class="pulsaSubtype === 'pulsa' ? 'bg-[#6B4226] text-white shadow-xs' : 'text-[#5A4B40] hover:text-[#2D241E]'"
                                    class="px-4 py-1.5 rounded-xl transition cursor-pointer">
                                Pulsa Reguler
                            </button>
                            <button type="button" 
                                    @click="pulsaSubtype = 'data'; pickDenom(dataPackages[0])"
                                    :class="pulsaSubtype === 'data' ? 'bg-[#6B4226] text-white shadow-xs' : 'text-[#5A4B40] hover:text-[#2D241E]'"
                                    class="px-4 py-1.5 rounded-xl transition cursor-pointer">
                                Paket Data Internet
                            </button>
                        </div>
                    </div>

                    <!-- Phone Input with Operator Badge & Clear Button -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-[#2D241E]">Nomor Telepon / Ponsel *</label>
                        <div class="relative">
                            <input type="tel" 
                                   x-model="phoneNumber" 
                                   @input="phoneNumber = phoneNumber.replace(/\D/g, '').slice(0, 15); detectOperator()"
                                   maxlength="15"
                                   placeholder="Contoh: 081234567890" 
                                   class="w-full pl-4 pr-36 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                            
                            <!-- Quick Clear Button -->
                            <button type="button" 
                                    x-show="phoneNumber && phoneNumber.length > 0" 
                                    @click="phoneNumber = ''; detectOperator()" 
                                    class="absolute right-36 top-3.5 w-5 h-5 rounded-full bg-[#EAE1D7] hover:bg-[#D5C7B7] text-[#5A4B40] text-[11px] font-bold flex items-center justify-center cursor-pointer transition"
                                    title="Hapus nomor">
                                ✕
                            </button>

                            <!-- Detected Operator Badge -->
                            <div class="absolute right-3 top-2.5 flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white border border-[#EAE1D7] text-xs font-black shadow-2xs"
                                 :class="operatorColor">
                                <span class="w-2 h-2 rounded-full" :class="operatorDotColor"></span>
                                <span x-text="detectedOperator"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Denomination Grid (Pulsa) -->
                    <div x-show="pulsaSubtype === 'pulsa'" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-[#2D241E]">Pilih Nominal Pulsa</label>
                            <span class="text-[11px] text-emerald-700 font-semibold">Semua nominal bergaransi masuk</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="item in pulsaDenoms" :key="item.amount">
                                <button type="button" 
                                        @click="pickDenom(item)"
                                        class="p-4 rounded-2xl border text-left transition relative cursor-pointer group flex flex-col justify-between"
                                        :class="selectedProduct.amount === item.amount ? 'border-[#6B4226] bg-[#FAF4ED] shadow-xs ring-1 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-white hover:border-[#6B4226]/40'">
                                    <div class="flex items-start justify-between">
                                        <span class="text-sm font-black text-[#2D241E]" x-text="'Rp ' + item.nominal.toLocaleString('id-ID')"></span>
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded text-rose-600 bg-rose-50 border border-rose-200" x-text="item.discount"></span>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-[#F2EAE0] flex items-center justify-between">
                                        <div>
                                            <span class="text-[10px] text-[#8A7C70] block">Harga Bayar:</span>
                                            <span class="text-xs font-black text-[#6B4226]" x-text="'Rp ' + item.amount.toLocaleString('id-ID')"></span>
                                        </div>
                                        <span x-show="selectedProduct.amount === item.amount" class="w-5 h-5 rounded-full bg-[#6B4226] text-white flex items-center justify-center text-xs font-bold">✓</span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Denomination Grid (Paket Data) -->
                    <div x-show="pulsaSubtype === 'data'" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-[#2D241E]">Pilih Paket Kuota Internet 4G / 5G</label>
                            <span class="text-[11px] text-emerald-700 font-semibold">Full 24 Jam Tanpa Pembagian</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <template x-for="pkg in dataPackages" :key="pkg.name">
                                <button type="button" 
                                        @click="pickDenom(pkg)"
                                        class="p-4 rounded-2xl border text-left transition relative cursor-pointer flex flex-col justify-between"
                                        :class="selectedProduct.name === pkg.name ? 'border-[#6B4226] bg-[#FAF4ED] shadow-xs ring-1 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-white hover:border-[#6B4226]/40'">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-xs sm:text-sm font-black text-[#2D241E]" x-text="pkg.name"></h4>
                                                <span class="text-[9px] px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 shrink-0" x-text="pkg.validity"></span>
                                            </div>
                                            <p class="text-[11px] text-[#8A7C70] mt-1" x-text="pkg.desc"></p>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-2.5 border-t border-[#F2EAE0] flex items-center justify-between">
                                        <div>
                                            <span class="text-xs font-black text-[#6B4226]" x-text="'Rp ' + pkg.amount.toLocaleString('id-ID')"></span>
                                            <span class="text-[10px] text-rose-600 line-through ml-1" x-text="'Rp ' + pkg.originalPrice.toLocaleString('id-ID')"></span>
                                        </div>
                                        <span x-show="selectedProduct.name === pkg.name" class="text-xs font-bold text-[#6B4226] flex items-center gap-1">
                                            <span>Terpilih</span> <span>✓</span>
                                        </span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 2: LISTRIK PLN ================= -->
                <div x-show="activeTab === 'pln'" x-cloak class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#F2EAE0] pb-4">
                        <div>
                            <h3 class="text-base font-black text-[#2D241E] flex items-center gap-2">
                                <span>⚡ Layanan Listrik PT PLN (Persero)</span>
                            </h3>
                            <p class="text-xs text-[#8A7C70] mt-0.5">Beli token listrik prabayar atau bayar tagihan listrik bulanan resmi</p>
                        </div>

                        <div class="flex rounded-2xl bg-[#FAF8F5] p-1 border border-[#EAE1D7] text-xs font-bold shrink-0">
                            <button type="button" 
                                    @click="plnSubtype = 'token'; setPlnProduct(plnDenoms[1])"
                                    :class="plnSubtype === 'token' ? 'bg-[#6B4226] text-white shadow-xs' : 'text-[#5A4B40] hover:text-[#2D241E]'"
                                    class="px-4 py-1.5 rounded-xl transition cursor-pointer">
                                Token Listrik
                            </button>
                            <button type="button" 
                                    @click="plnSubtype = 'bill'; setPlnBill()"
                                    :class="plnSubtype === 'bill' ? 'bg-[#6B4226] text-white shadow-xs' : 'text-[#5A4B40] hover:text-[#2D241E]'"
                                    class="px-4 py-1.5 rounded-xl transition cursor-pointer">
                                Tagihan Listrik
                            </button>
                        </div>
                    </div>

                    <!-- Input ID Pelanggan / No Meter -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-[#2D241E]" x-text="plnSubtype === 'token' ? 'No. Meter / ID Pelanggan Prabayar (11-12 Digit) *' : 'ID Pelanggan Pascabayar (12 Digit) *'"></label>
                        <div class="flex gap-2">
                            <input type="text" 
                                   x-model="plnCustomerNumber" 
                                   placeholder="Contoh: 14238592019" 
                                   class="flex-1 px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                            <button type="button" 
                                    @click="checkPlnCustomer()"
                                    class="px-5 py-3.5 rounded-2xl bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs shrink-0 transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                                <span>🔍</span>
                                <span>Cek Data Pelanggan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Inquiry Customer Card result -->
                    <div x-show="inquiryData.name" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs space-y-2">
                        <div class="flex items-center justify-between font-bold text-amber-950">
                            <span class="flex items-center gap-1.5">
                                <span>✓</span>
                                <span>Data Pelanggan PLN Terverifikasi</span>
                            </span>
                            <span class="text-[10px] bg-amber-200 text-amber-900 px-2.5 py-0.5 rounded-md font-black" x-text="inquiryData.tariff"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px] text-amber-900 pt-1">
                            <p>Nama Pelanggan: <strong class="text-amber-950" x-text="inquiryData.name"></strong></p>
                            <p>No. Meter: <strong class="font-mono text-amber-950" x-text="plnCustomerNumber"></strong></p>
                            <p>Daya Listrik: <strong class="text-amber-950" x-text="inquiryData.power"></strong></p>
                            <p>Status Meter: <strong class="text-emerald-700">Aktif Normal</strong></p>
                        </div>
                    </div>

                    <!-- Token Nominal Grid (If token) -->
                    <div x-show="plnSubtype === 'token'" class="space-y-3">
                        <label class="block text-xs font-bold text-[#2D241E]">Pilih Nominal Token Listrik</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="item in plnDenoms" :key="item.amount">
                                <button type="button" 
                                        @click="setPlnProduct(item)"
                                        class="p-4 rounded-2xl border text-left transition relative cursor-pointer"
                                        :class="selectedProduct.amount === item.amount ? 'border-[#6B4226] bg-[#FAF4ED] shadow-xs ring-1 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-white hover:border-[#6B4226]/40'">
                                    <span class="text-sm font-black text-[#2D241E] block" x-text="'Rp ' + item.nominal.toLocaleString('id-ID')"></span>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-[#F2EAE0]">
                                        <span class="text-xs font-bold text-[#6B4226]" x-text="'Rp ' + item.amount.toLocaleString('id-ID')"></span>
                                        <span x-show="selectedProduct.amount === item.amount" class="text-xs text-[#6B4226] font-bold">✓</span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 3: AIR PDAM ================= -->
                <div x-show="activeTab === 'pdam'" x-cloak class="space-y-6">
                    <div class="border-b border-[#F2EAE0] pb-4">
                        <h3 class="text-base font-black text-[#2D241E] flex items-center gap-2">
                            <span>💧 Pembayaran Tagihan Air Bersih PDAM</span>
                        </h3>
                        <p class="text-xs text-[#8A7C70] mt-0.5">Pilih wilayah PDAM dan masukkan nomor sambungan pelanggan Anda</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Wilayah / Daerah PDAM *</label>
                            <select x-model="pdamRegion" 
                                    class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 cursor-pointer">
                                <option value="PAM JAYA DKI Jakarta">PAM JAYA DKI Jakarta</option>
                                <option value="PDAM Surya Sembada Kota Surabaya">PDAM Surya Sembada Kota Surabaya</option>
                                <option value="PDAM Tirtawening Kota Bandung">PDAM Tirtawening Kota Bandung</option>
                                <option value="PDAM Tirta Moedal Kota Semarang">PDAM Tirta Moedal Kota Semarang</option>
                                <option value="PDAM Tirtanadi Kota Medan">PDAM Tirtanadi Kota Medan</option>
                                <option value="PDAM Tirta Musi Kota Palembang">PDAM Tirta Musi Kota Palembang</option>
                                <option value="PDAM Kota Denpasar Bali">PDAM Kota Denpasar Bali</option>
                                <option value="PDAM Kota Makassar">PDAM Kota Makassar</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Nomor Sambungan / Pelanggan *</label>
                            <input type="text" 
                                   x-model="pdamCustomerNumber"
                                   placeholder="Contoh: 002948102" 
                                   class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                        </div>
                    </div>

                    <button type="button" 
                            @click="checkPdamBill()"
                            class="px-5 py-3.5 rounded-2xl bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-xs cursor-pointer">
                        <span>🔍 Cek Tagihan Air PDAM</span>
                    </button>

                    <!-- PDAM Inquiry Detail Card -->
                    <div x-show="pdamInquiry.name" class="p-5 rounded-2xl bg-sky-50 border border-sky-200 text-xs space-y-3">
                        <div class="flex items-center justify-between font-bold text-sky-950">
                            <span class="flex items-center gap-1.5">
                                <span>✓</span>
                                <span>Tagihan PDAM Ditemukan</span>
                            </span>
                            <span class="text-sm font-black text-[#6B4226]" x-text="'Rp ' + pdamInquiry.amount.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sky-950 pt-2 border-t border-sky-200/60 text-[11px]">
                            <div>
                                <span class="text-sky-700 block">Nama:</span>
                                <strong x-text="pdamInquiry.name"></strong>
                            </div>
                            <div>
                                <span class="text-sky-700 block">Pemakaian:</span>
                                <strong x-text="pdamInquiry.usage + ' m³'"></strong>
                            </div>
                            <div>
                                <span class="text-sky-700 block">Periode:</span>
                                <strong>Bulan Ini</strong>
                            </div>
                            <div>
                                <span class="text-sky-700 block">Status:</span>
                                <strong class="text-amber-700">Belum Dibayar</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 4: BPJS KESEHATAN & KETENAGAKERJAAN ================= -->
                <div x-show="activeTab === 'bpjs'" x-cloak class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#F2EAE0] pb-4">
                        <div>
                            <h3 class="text-base font-black text-[#2D241E] flex items-center gap-2">
                                <span>🏥 Iuran BPJS Kesehatan & Ketenagakerjaan</span>
                            </h3>
                            <p class="text-xs text-[#8A7C70] mt-0.5">Bayar iuran perlindungan kesehatan keluarga tepat waktu</p>
                        </div>

                        <div class="flex rounded-2xl bg-[#FAF8F5] p-1 border border-[#EAE1D7] text-xs font-bold shrink-0">
                            <button type="button" 
                                    @click="bpjsType = 'kesehatan'; updateBpjsProduct()"
                                    :class="bpjsType === 'kesehatan' ? 'bg-[#6B4226] text-white shadow-xs' : 'text-[#5A4B40] hover:text-[#2D241E]'"
                                    class="px-4 py-1.5 rounded-xl transition cursor-pointer">
                                BPJS Kesehatan
                            </button>
                            <button type="button" 
                                    @click="bpjsType = 'ketenagakerjaan'; updateBpjsProduct()"
                                    :class="bpjsType === 'ketenagakerjaan' ? 'bg-[#6B4226] text-white shadow-xs' : 'text-[#5A4B40] hover:text-[#2D241E]'"
                                    class="px-4 py-1.5 rounded-xl transition cursor-pointer">
                                BPJS Ketenagakerjaan
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Nomor Peserta / Virtual Account BPJS (13-16 Digit) *</label>
                            <input type="text" 
                                   x-model="bpjsNumber"
                                   placeholder="Contoh: 8888801234567890" 
                                   class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Bayar Hingga Bulan *</label>
                            <select x-model="bpjsMonths" 
                                    @change="updateBpjsProduct()"
                                    class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 cursor-pointer">
                                <option value="1">1 Bulan (Bulan Berjalan)</option>
                                <option value="2">2 Bulan ke Depan</option>
                                <option value="3">3 Bulan ke Depan</option>
                                <option value="6">6 Bulan ke Depan</option>
                                <option value="12">1 Tahun Penuh (12 Bulan)</option>
                            </select>
                        </div>
                    </div>

                    <!-- BPJS Simulation Card -->
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-950 flex items-center gap-1.5">
                                <span>✓</span>
                                <span>Rincian Iuran Kepesertaan Keluarga</span>
                            </span>
                            <span class="text-xs font-black text-[#6B4226]" x-text="'Rp ' + (selectedProduct.amount).toLocaleString('id-ID')"></span>
                        </div>
                        <p class="text-[11px] text-emerald-800 leading-relaxed">
                            Mencakup jaminan proteksi fasilitas kesehatan tingkat pertama (Faskes 1) dan rujukan lanjutan tanpa batasan biaya tindakan kegawatdaruratan.
                        </p>
                    </div>
                </div>

                <!-- ================= TAB 5: INTERNET & TV KABEL ================= -->
                <div x-show="activeTab === 'internet'" x-cloak class="space-y-6">
                    <div class="border-b border-[#F2EAE0] pb-4">
                        <h3 class="text-base font-black text-[#2D241E] flex items-center gap-2">
                            <span>🌐 Tagihan WiFi Internet Rumah & TV Kabel</span>
                        </h3>
                        <p class="text-xs text-[#8A7C70] mt-0.5">Bayar langganan WiFi internet rumah Anda tanpa putus koneksi</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Pilih Provider WiFi / TV *</label>
                            <select x-model="internetProvider" 
                                    @change="updateInternetProduct()"
                                    class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 cursor-pointer">
                                <option value="IndiHome (Telkom)">IndiHome (Telkom)</option>
                                <option value="First Media">First Media</option>
                                <option value="Biznet Home">Biznet Home</option>
                                <option value="MyRepublic">MyRepublic</option>
                                <option value="MNC Play">MNC Play</option>
                                <option value="XL Home / SATU">XL Home / SATU</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Nomor Pelanggan / ID Langganan *</label>
                            <input type="text" 
                                   x-model="internetCustomerNumber"
                                   placeholder="Contoh: 12294819028" 
                                   class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                        </div>
                    </div>

                    <button type="button" 
                            @click="checkInternetBill()"
                            class="px-5 py-3.5 rounded-2xl bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-xs cursor-pointer">
                        <span>🔍 Cek Tagihan WiFi</span>
                    </button>
                </div>

                <!-- ================= TAB 6: ASURANSI & ANGSURAN ================= -->
                <div x-show="activeTab === 'insurance'" x-cloak class="space-y-6">
                    <div class="border-b border-[#F2EAE0] pb-4">
                        <h3 class="text-base font-black text-[#2D241E] flex items-center gap-2">
                            <span>🛡️ Premi Asuransi Jiwa & Angsuran Kredit Multifinance</span>
                        </h3>
                        <p class="text-xs text-[#8A7C70] mt-0.5">Bayar cicilan motor, mobil, dan asuransi secara aman dan bergaransi resmi</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Pilih Lembaga Finansial / Asuransi *</label>
                            <select x-model="financeCompany" 
                                    @change="updateFinanceProduct()"
                                    class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 cursor-pointer">
                                <option value="FIF Group">FIF Group (Honda Motor)</option>
                                <option value="Adira Finance">Adira Finance</option>
                                <option value="BAF (Busan Auto Finance)">BAF (Yamaha Motor)</option>
                                <option value="OTO Kredit Mobil & Motor">OTO Kredit Mobil & Motor</option>
                                <option value="Prudential Life">Prudential Life Assurance</option>
                                <option value="Allianz Indonesia">Allianz Indonesia</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Nomor Kontrak / No. Polis *</label>
                            <input type="text" 
                                   x-model="financeContractNumber"
                                   placeholder="Contoh: 1029481928" 
                                   class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 7: E-MONEY ================= -->
                <div x-show="activeTab === 'emoney'" x-cloak class="space-y-6">
                    <div class="border-b border-[#F2EAE0] pb-4">
                        <h3 class="text-base font-black text-[#2D241E] flex items-center gap-2">
                            <span>💳 Top Up Saldo Dompet Digital (E-Money)</span>
                        </h3>
                        <p class="text-xs text-[#8A7C70] mt-0.5">Isi saldo GoPay, OVO, DANA, ShopeePay langsung masuk hitungan detik</p>
                    </div>

                    <!-- E-Wallet Provider Selection Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <template x-for="ewallet in ewallets" :key="ewallet.name">
                            <button type="button" 
                                    @click="selectEwallet(ewallet.name)"
                                    class="p-3.5 rounded-2xl border text-center transition flex flex-col items-center justify-center gap-1.5 cursor-pointer"
                                    :class="selectedEwallet === ewallet.name ? 'border-[#6B4226] bg-[#FAF4ED] shadow-xs ring-1 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-white'">
                                <span class="text-2xl" x-text="ewallet.icon"></span>
                                <span class="text-xs font-black text-[#2D241E]" x-text="ewallet.name"></span>
                            </button>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#2D241E]">Nomor Ponsel Akun Dompet Digital *</label>
                        <input type="tel" 
                               x-model="ewalletNumber"
                               placeholder="Contoh: 081298765432" 
                               class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                    </div>

                    <!-- Denom E-Money Grid -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-[#2D241E]">Pilih Nominal Isi Saldo</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="val in ewalletDenoms" :key="val">
                                <button type="button" 
                                        @click="pickEwalletAmount(val)"
                                        class="p-4 rounded-2xl border text-left transition relative cursor-pointer"
                                        :class="selectedProduct.amount === val ? 'border-[#6B4226] bg-[#FAF4ED] shadow-xs ring-1 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-white hover:border-[#6B4226]/40'">
                                    <span class="text-sm font-black text-[#2D241E] block" x-text="'Rp ' + val.toLocaleString('id-ID')"></span>
                                    <span class="text-[11px] text-[#8A7C70] mt-1 block">+ Biaya Admin Rp 1.000</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 8: VOUCHER GAME ================= -->
                <div x-show="activeTab === 'game'" x-cloak class="space-y-6">
                    <div class="border-b border-[#F2EAE0] pb-4">
                        <h3 class="text-base font-black text-[#2D241E] flex items-center gap-2">
                            <span>🎮 Top Up Voucher Game Favorit</span>
                        </h3>
                        <p class="text-xs text-[#8A7C70] mt-0.5">Top up Diamonds MLBB, Free Fire, Valorant Points & Steam Wallet harga termurah</p>
                    </div>

                    <!-- Game Selector Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <template x-for="g in games" :key="g.name">
                            <button type="button" 
                                    @click="selectGame(g)"
                                    class="p-3.5 rounded-2xl border text-left transition flex items-center gap-3 cursor-pointer"
                                    :class="selectedGame.name === g.name ? 'border-[#6B4226] bg-[#FAF4ED] shadow-xs ring-1 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-white hover:border-[#6B4226]/40'">
                                <span class="text-2xl" x-text="g.icon"></span>
                                <div>
                                    <span class="text-xs font-black text-[#2D241E] block" x-text="g.name"></span>
                                    <span class="text-[10px] text-[#8A7C70]" x-text="g.currency"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">User ID Akun Game *</label>
                            <input type="text" 
                                   x-model="gameUserId"
                                   placeholder="Contoh: 12849182" 
                                   class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                        </div>

                        <div class="space-y-1.5" x-show="selectedGame.hasZone">
                            <label class="block text-xs font-bold text-[#2D241E]">Zone ID / Server *</label>
                            <input type="text" 
                                   x-model="gameZoneId"
                                   placeholder="Contoh: 2024" 
                                   class="w-full px-4 py-3.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm font-bold text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                        </div>
                    </div>

                    <!-- Game Denoms Grid -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-[#2D241E]">Pilih Nominal Top Up Game</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="item in selectedGame.packages" :key="item.name">
                                <button type="button" 
                                        @click="pickGamePackage(item)"
                                        class="p-4 rounded-2xl border text-left transition relative cursor-pointer"
                                        :class="selectedProduct.name === item.name ? 'border-[#6B4226] bg-[#FAF4ED] shadow-xs ring-1 ring-[#6B4226]/20' : 'border-[#EAE1D7] bg-white hover:border-[#6B4226]/40'">
                                    <span class="text-xs sm:text-sm font-black text-[#2D241E] block" x-text="item.name"></span>
                                    <span class="text-xs font-bold text-[#6B4226] mt-2 block" x-text="'Rp ' + item.amount.toLocaleString('id-ID')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: CHECKOUT SUMMARY & PROMO VOUCHERS (4 COLS) -->
            <div class="lg:col-span-4 sticky top-24 space-y-5">
                
                <!-- CHECKOUT SUMMARY CARD -->
                <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-sm space-y-5">
                    <div class="border-b border-[#F2EAE0] pb-3">
                        <h3 class="text-sm sm:text-base font-black text-[#2D241E] flex items-center justify-between">
                            <span>Ringkasan Transaksi</span>
                            <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md font-bold">24 Jam</span>
                        </h3>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5">Periksa kembali tujuan nomor sebelum membayar</p>
                    </div>

                    <!-- Form submission -->
                    <form x-ref="checkoutForm" action="{{ route('topup.checkout') }}" method="POST" @submit="isSubmitting = true" class="space-y-4">
                        @csrf
                        <input type="hidden" name="service_type" :value="getServiceType()">
                        <input type="hidden" name="customer_number" :value="getCustomerNumber()">
                        <input type="hidden" name="provider" :value="getProviderName()">
                        <input type="hidden" name="product_name" :value="selectedProduct.name">
                        <input type="hidden" name="amount" :value="selectedProduct.amount">
                        <input type="hidden" name="admin_fee" :value="adminFee">

                        <!-- Detail Specs -->
                        <div class="space-y-2.5 text-xs text-[#5A4B40] bg-[#FAF8F5] p-3.5 rounded-2xl border border-[#EAE1D7]">
                            <div class="flex justify-between">
                                <span class="text-[#8A7C70]">Layanan:</span>
                                <span class="font-bold text-[#2D241E] text-right truncate max-w-[180px]" x-text="selectedProduct.name"></span>
                            </div>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-[#8A7C70] shrink-0">Tujuan / No. ID:</span>
                                <span class="font-bold text-[#2D241E] font-mono truncate max-w-[170px] text-right" x-text="getCustomerNumber() || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#8A7C70]">Harga Produk:</span>
                                <span class="font-bold text-[#2D241E]" x-text="'Rp ' + selectedProduct.amount.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#8A7C70]">Biaya Admin:</span>
                                <span class="font-bold text-emerald-700" x-text="appliedCoupon === 'BEBASADMIN' ? 'GRATIS' : 'Rp ' + adminFee.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex justify-between" x-show="promoDiscount > 0">
                                <span class="text-[#8A7C70]">Diskon Voucher:</span>
                                <span class="font-bold text-rose-600" x-text="'-Rp ' + promoDiscount.toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <!-- Voucher Promo Input Section -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-[#2D241E]">Klaim Voucher Diskon</label>
                            <div class="flex gap-2">
                                <input type="text" 
                                       x-model="voucherInput" 
                                       placeholder="Kode promo (cth: KILAT5K)" 
                                       class="flex-1 px-3 py-2 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs font-bold uppercase text-[#2D241E]">
                                <button type="button" 
                                        @click="applyVoucher()"
                                        class="px-3.5 py-2 bg-[#6B4226] text-white font-bold text-xs rounded-xl hover:bg-[#54321B] transition cursor-pointer">
                                    Pakai
                                </button>
                            </div>
                            <!-- Active Voucher Banner -->
                            <div x-show="appliedCoupon" class="flex items-center justify-between p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-800 font-bold">
                                <span>🎟️ Voucher <strong x-text="appliedCoupon"></strong> Aktif!</span>
                                <button type="button" @click="removeVoucher()" class="text-rose-600 hover:underline">Hapus</button>
                            </div>
                        </div>

                        <!-- Grand Total Banner -->
                        <div class="p-3.5 rounded-2xl bg-[#6B4226] text-white flex items-center justify-between shadow-xs">
                            <span class="text-xs font-bold text-amber-200">Total Pembayaran:</span>
                            <span class="text-base sm:text-lg font-black" x-text="'Rp ' + calculateGrandTotal().toLocaleString('id-ID')"></span>
                        </div>

                        <!-- Payment Method Selector (Clean QRIS) -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-[#2D241E]">Metode Pembayaran</label>
                            <label class="p-3.5 rounded-2xl border border-[#6B4226] bg-[#FAF8F5] hover:bg-[#FAF4ED] flex items-center justify-between cursor-pointer transition shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="payment_method" value="qris" checked class="text-[#6B4226] focus:ring-[#6B4226] cursor-pointer">
                                    <div class="w-9 h-9 rounded-xl bg-white border border-[#EAE1D7] flex items-center justify-center text-base shadow-2xs shrink-0">
                                        📱
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs text-[#2D241E]">QRIS Instant</div>
                                        <p class="text-[11px] text-[#8A7C70] mt-0.5">BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay</p>
                                    </div>
                                </div>
                                <span class="w-5 h-5 rounded-full bg-[#6B4226] text-white flex items-center justify-center text-[10px] font-black shrink-0">
                                    ✓
                                </span>
                            </label>
                        </div>

                        <!-- Direct Payment Submit Button -->
                        <button type="submit" 
                                :disabled="!getCustomerNumber() || selectedProduct.amount <= 0"
                                class="w-full py-4 px-4 rounded-2xl bg-gradient-to-r from-[#6B4226] via-[#8C4F27] to-[#B86221] hover:from-[#54321B] hover:to-[#964B13] disabled:opacity-50 text-white font-black text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-98 shadow-md cursor-pointer group">
                            <span x-show="!isSubmitting" class="flex items-center gap-2">
                                <span>⚡</span>
                                <span>Bayar Sekarang</span>
                                <span class="group-hover:translate-x-1 transition-transform">›</span>
                            </span>
                            <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memproses Pembayaran...</span>
                            </span>
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
function topUpHub(defaultTab) {
    return {
        activeTab: defaultTab || 'pulsa',
        pulsaSubtype: 'pulsa',
        phoneNumber: '',
        detectedOperator: 'Operator Seluler',
        operatorColor: 'text-[#8A7C70]',
        operatorDotColor: 'bg-[#8A7C70]',
        adminFee: 1500,

        // Voucher State
        voucherInput: '',
        appliedCoupon: '',
        promoDiscount: 0,

        // Tab PLN State
        plnSubtype: 'token',
        plnCustomerNumber: '',
        inquiryData: { name: '', tariff: '', power: '' },

        // Tab PDAM State
        pdamRegion: 'PAM JAYA DKI Jakarta',
        pdamCustomerNumber: '',
        pdamInquiry: { name: '', usage: 0, amount: 0 },

        // Tab BPJS State
        bpjsType: 'kesehatan',
        bpjsNumber: '',
        bpjsMonths: '1',

        // Tab Internet
        internetProvider: 'IndiHome (Telkom)',
        internetCustomerNumber: '',

        // Tab Finance
        financeCompany: 'FIF Group',
        financeContractNumber: '',

        // Tab E-Money
        selectedEwallet: 'GoPay',
        ewalletNumber: '',

        // Tab Game
        selectedGame: {},
        gameUserId: '',
        gameZoneId: '',

        // Selected Product for Checkout
        selectedProduct: {
            name: 'Pulsa Reguler Rp 25.000',
            amount: 25000
        },

        // Modal States
        showPaymentModal: false,
        isSubmitting: false,

        // Master Data
        pulsaDenoms: [
            { nominal: 10000, amount: 10500, discount: '-5%' },
            { nominal: 25000, amount: 24850, discount: 'Promo' },
            { nominal: 50000, amount: 49500, discount: 'Best 🔥' },
            { nominal: 100000, amount: 98000, discount: 'Hemat 2rb' },
            { nominal: 150000, amount: 147500, discount: 'Hemat 2.5rb' },
            { nominal: 200000, amount: 196000, discount: 'Hemat 4rb' },
        ],

        dataPackages: [
            { name: 'Paket Internet Bulanan 15 GB', desc: '10GB Utama Nasional + 5GB Chat & Sosmed', amount: 45000, originalPrice: 55000, validity: '30 Hari' },
            { name: 'Paket Unlimited Max 35 GB', desc: '30GB Kuota Utama + 5GB Nonton Streaming', amount: 85000, originalPrice: 95000, validity: '30 Hari' },
            { name: 'Paket Harian Ngebut 5 GB', desc: 'Full 24 Jam Kuota Utama Tanpa FUP', amount: 18000, originalPrice: 22000, validity: '7 Hari' },
            { name: 'Paket Jumbo Kuota 65 GB', desc: '50GB Utama + 15GB YouTube, TikTok & Game', amount: 135000, originalPrice: 150000, validity: '30 Hari' },
        ],

        plnDenoms: [
            { nominal: 20000, amount: 21500 },
            { nominal: 50000, amount: 51500 },
            { nominal: 100000, amount: 101500 },
            { nominal: 200000, amount: 201500 },
            { nominal: 500000, amount: 501500 },
            { nominal: 1000000, amount: 1001500 },
        ],

        ewallets: [
            { name: 'GoPay', icon: '🟢' },
            { name: 'OVO', icon: '🟣' },
            { name: 'DANA', icon: '🔵' },
            { name: 'ShopeePay', icon: '🟠' },
            { name: 'LinkAja', icon: '🔴' }
        ],

        ewalletDenoms: [20000, 50000, 100000, 200000, 500000, 1000000],

        games: [
            {
                name: 'Mobile Legends',
                icon: '⚔️',
                currency: 'Diamonds',
                hasZone: true,
                packages: [
                    { name: '86 Diamonds MLBB', amount: 21500 },
                    { name: '172 Diamonds MLBB', amount: 42500 },
                    { name: '257 Diamonds MLBB', amount: 63000 },
                    { name: '706 Diamonds MLBB', amount: 168000 },
                    { name: 'Weekly Diamond Pass', amount: 28500 },
                ]
            },
            {
                name: 'Free Fire',
                icon: '🔥',
                currency: 'Diamonds',
                hasZone: false,
                packages: [
                    { name: '140 Diamonds FF', amount: 19500 },
                    { name: '355 Diamonds FF', amount: 48000 },
                    { name: '720 Diamonds FF', amount: 96000 },
                    { name: 'Membership Mingguan', amount: 29000 },
                ]
            },
            {
                name: 'Valorant',
                icon: '🎯',
                currency: 'Points (VP)',
                hasZone: false,
                packages: [
                    { name: '475 Points Valorant', amount: 52000 },
                    { name: '1000 Points Valorant', amount: 108000 },
                    { name: '2050 Points Valorant', amount: 215000 },
                ]
            },
            {
                name: 'Steam Wallet',
                icon: '🎮',
                currency: 'Voucher IDR',
                hasZone: false,
                packages: [
                    { name: 'Steam Wallet Rp 45.000', amount: 49500 },
                    { name: 'Steam Wallet Rp 90.000', amount: 99000 },
                    { name: 'Steam Wallet Rp 250.000', amount: 275000 },
                ]
            }
        ],

        init() {
            this.selectedGame = this.games[0];
            this.detectOperator();
        },

        selectTab(tab) {
            this.activeTab = tab;
            if (tab === 'pulsa') {
                this.pickDenom(this.pulsaSubtype === 'pulsa' ? this.pulsaDenoms[1] : this.dataPackages[0]);
            } else if (tab === 'pln') {
                this.setPlnProduct(this.plnDenoms[1]);
            } else if (tab === 'pdam') {
                this.checkPdamBill();
            } else if (tab === 'bpjs') {
                this.updateBpjsProduct();
            } else if (tab === 'internet') {
                this.updateInternetProduct();
            } else if (tab === 'insurance') {
                this.updateFinanceProduct();
            } else if (tab === 'emoney') {
                this.pickEwalletAmount(this.ewalletDenoms[1]);
            } else if (tab === 'game') {
                this.pickGamePackage(this.selectedGame.packages[0]);
            }
        },

        detectOperator() {
            this.phoneNumber = (this.phoneNumber || '').replace(/\D/g, '').slice(0, 15);
            const clean = this.phoneNumber;
            if (clean.startsWith('0811') || clean.startsWith('0812') || clean.startsWith('0813') || clean.startsWith('0821') || clean.startsWith('0822') || clean.startsWith('0823') || clean.startsWith('0851')) {
                this.detectedOperator = 'Telkomsel';
                this.operatorColor = 'text-red-600';
                this.operatorDotColor = 'bg-red-600';
            } else if (clean.startsWith('0814') || clean.startsWith('0815') || clean.startsWith('0816') || clean.startsWith('0855') || clean.startsWith('0856') || clean.startsWith('0857') || clean.startsWith('0858')) {
                this.detectedOperator = 'Indosat Ooredoo';
                this.operatorColor = 'text-amber-500';
                this.operatorDotColor = 'bg-amber-500';
            } else if (clean.startsWith('0817') || clean.startsWith('0818') || clean.startsWith('0819') || clean.startsWith('0859') || clean.startsWith('0877') || clean.startsWith('0878')) {
                this.detectedOperator = 'XL Axiata';
                this.operatorColor = 'text-blue-600';
                this.operatorDotColor = 'bg-blue-600';
            } else if (clean.startsWith('0895') || clean.startsWith('0896') || clean.startsWith('0897') || clean.startsWith('0898') || clean.startsWith('0899')) {
                this.detectedOperator = 'Tri (3)';
                this.operatorColor = 'text-purple-600';
                this.operatorDotColor = 'bg-purple-600';
            } else if (clean.startsWith('0881') || clean.startsWith('0882') || clean.startsWith('0883') || clean.startsWith('0888')) {
                this.detectedOperator = 'Smartfren';
                this.operatorColor = 'text-rose-500';
                this.operatorDotColor = 'bg-rose-500';
            } else if (clean.startsWith('0831') || clean.startsWith('0832') || clean.startsWith('0838')) {
                this.detectedOperator = 'Axis';
                this.operatorColor = 'text-violet-600';
                this.operatorDotColor = 'bg-violet-600';
            } else {
                this.detectedOperator = 'Operator Seluler';
                this.operatorColor = 'text-[#6B4226]';
                this.operatorDotColor = 'bg-[#6B4226]';
            }
        },

        pickDenom(item) {
            const prefix = this.pulsaSubtype === 'pulsa' ? 'Pulsa ' + this.detectedOperator : '';
            this.selectedProduct = {
                name: this.pulsaSubtype === 'pulsa' ? `${prefix} Rp ${item.nominal.toLocaleString('id-ID')}` : item.name,
                amount: item.amount
            };
        },

        setPlnProduct(item) {
            this.selectedProduct = {
                name: `Token Listrik PLN Rp ${item.nominal.toLocaleString('id-ID')}`,
                amount: item.amount
            };
        },

        setPlnBill() {
            this.selectedProduct = {
                name: 'Tagihan Listrik PLN Pascabayar',
                amount: 185000
            };
        },

        checkPlnCustomer() {
            if (!this.plnCustomerNumber) return;
            this.inquiryData = {
                name: 'BUDI PRASETYO',
                tariff: 'R1M / 900 VA',
                power: '900 Watt'
            };
        },

        checkPdamBill() {
            this.pdamInquiry = {
                name: 'HENDRA WIJAYA',
                usage: 24,
                amount: 88500
            };
            this.selectedProduct = {
                name: `Tagihan Air ${this.pdamRegion}`,
                amount: 88500
            };
        },

        updateBpjsProduct() {
            const baseRate = this.bpjsType === 'kesehatan' ? 35000 : 29000;
            const total = baseRate * parseInt(this.bpjsMonths);
            this.selectedProduct = {
                name: `Iuran ${this.bpjsType === 'kesehatan' ? 'BPJS Kesehatan' : 'BPJS Ketenagakerjaan'} (${this.bpjsMonths} Bulan)`,
                amount: total
            };
        },

        updateInternetProduct() {
            this.selectedProduct = {
                name: `Tagihan WiFi & TV ${this.internetProvider}`,
                amount: 320000
            };
        },

        checkInternetBill() {
            alert(`Tagihan ${this.internetProvider} untuk No. ${this.internetCustomerNumber} terverifikasi sebesar Rp 320.000.`);
        },

        updateFinanceProduct() {
            this.selectedProduct = {
                name: `Angsuran / Premi ${this.financeCompany}`,
                amount: 650000
            };
        },

        selectEwallet(name) {
            this.selectedEwallet = name;
            this.pickEwalletAmount(this.selectedProduct.amount || 50000);
        },

        pickEwalletAmount(amount) {
            this.selectedProduct = {
                name: `Top Up Saldo ${this.selectedEwallet} Rp ${amount.toLocaleString('id-ID')}`,
                amount: amount
            };
        },

        selectGame(g) {
            this.selectedGame = g;
            this.pickGamePackage(g.packages[0]);
        },

        pickGamePackage(pkg) {
            this.selectedProduct = {
                name: `${this.selectedGame.name} - ${pkg.name}`,
                amount: pkg.amount
            };
        },

        applyVoucher() {
            const code = this.voucherInput.trim().toUpperCase();
            if (code === 'KILAT5K') {
                this.appliedCoupon = 'KILAT5K';
                this.promoDiscount = 5000;
            } else if (code === 'BEBASADMIN') {
                this.appliedCoupon = 'BEBASADMIN';
                this.promoDiscount = this.adminFee;
            } else {
                alert('Kode voucher tidak ditemukan atau telah kedaluwarsa.');
            }
        },

        removeVoucher() {
            this.appliedCoupon = '';
            this.promoDiscount = 0;
            this.voucherInput = '';
        },

        calculateGrandTotal() {
            const effectiveAdmin = this.appliedCoupon === 'BEBASADMIN' ? 0 : this.adminFee;
            return Math.max(0, this.selectedProduct.amount + effectiveAdmin - this.promoDiscount);
        },

        getServiceType() {
            if (this.activeTab === 'pulsa') return this.pulsaSubtype;
            if (this.activeTab === 'pln') return this.plnSubtype === 'token' ? 'pln_token' : 'pln_bill';
            return this.activeTab;
        },

        getCustomerNumber() {
            if (this.activeTab === 'pulsa') return (this.phoneNumber || '').replace(/\D/g, '').slice(0, 15);
            if (this.activeTab === 'pln') return (this.plnCustomerNumber || '').replace(/\D/g, '').slice(0, 16);
            if (this.activeTab === 'pdam') return (this.pdamCustomerNumber || '').replace(/\D/g, '').slice(0, 20);
            if (this.activeTab === 'bpjs') return (this.bpjsNumber || '').replace(/\D/g, '').slice(0, 20);
            if (this.activeTab === 'internet') return (this.internetCustomerNumber || '').replace(/\D/g, '').slice(0, 20);
            if (this.activeTab === 'insurance') return (this.financeContractNumber || '').replace(/\D/g, '').slice(0, 25);
            if (this.activeTab === 'emoney') return (this.ewalletNumber || '').replace(/\D/g, '').slice(0, 15);
            if (this.activeTab === 'game') return (this.gameUserId || '') + (this.selectedGame.hasZone && this.gameZoneId ? ` (${this.gameZoneId})` : '');
            return '';
        },

        getProviderName() {
            if (this.activeTab === 'pulsa') return this.detectedOperator;
            if (this.activeTab === 'pln') return 'PT PLN (Persero)';
            if (this.activeTab === 'pdam') return this.pdamRegion;
            if (this.activeTab === 'bpjs') return this.bpjsType === 'kesehatan' ? 'BPJS Kesehatan' : 'BPJS Ketenagakerjaan';
            if (this.activeTab === 'internet') return this.internetProvider;
            if (this.activeTab === 'insurance') return this.financeCompany;
            if (this.activeTab === 'emoney') return this.selectedEwallet;
            if (this.activeTab === 'game') return this.selectedGame.name;
            return 'NusantaraMart PPOB';
        }
    };
}
</script>
@endpush
@endsection
