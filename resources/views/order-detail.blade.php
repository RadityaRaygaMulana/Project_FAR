@extends('layouts.app')

@section('title', 'Invoice Resmi ' . $order->order_code . ' — NusantaraMart')

@section('content')
<div class="py-6 sm:py-10 bg-[#FAF8F5] min-h-screen" x-data="{
    copiedText: null,
    copy(text) {
        navigator.clipboard.writeText(text);
        this.copiedText = text;
        setTimeout(() => this.copiedText = null, 2000);
    },
    cancelModal: false,
    cancelOrderId: {{ $order->id }},
    cancelOrderCode: '{{ $order->order_code }}',
    cancelActionUrl: '{{ route('orders.cancel', $order->id) }}',
    cancelReasonOption: 'Ingin mengubah alamat pengiriman',
    cancelReasonDetail: '',
    openCancelModal() {
        this.cancelReasonOption = 'Ingin mengubah alamat pengiriman';
        this.cancelReasonDetail = '';
        this.cancelModal = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeCancelModal() {
        this.cancelModal = false;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    },
    returnModal: false,
    returnReason: 'Produk Rusak / Cacat',
    returnDescription: '',
    openReturnModal() {
        this.returnReason = 'Produk Rusak / Cacat';
        this.returnDescription = '';
        this.returnModal = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeReturnModal() {
        this.returnModal = false;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    },
    reviewModal: false,
    reviewOrderId: null,
    reviewOrderCode: '',
    reviewProductId: null,
    reviewProductName: '',
    reviewProductImage: '',
    reviewVariantName: '',
    reviewActionUrl: '',
    reviewRating: 5,
    reviewHoverRating: 5,
    reviewText: '',
    openReviewModal(orderId, orderCode, productId, productName, productImage, variantName, actionUrl) {
        this.reviewOrderId = orderId;
        this.reviewOrderCode = orderCode;
        this.reviewProductId = productId;
        this.reviewProductName = productName;
        this.reviewProductImage = productImage;
        this.reviewVariantName = variantName;
        this.reviewActionUrl = actionUrl;
        this.reviewRating = 5;
        this.reviewHoverRating = 5;
        this.reviewText = '';
        this.reviewModal = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeReviewModal() {
        this.reviewModal = false;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }
}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- 1. TOP BAR NAVIGATION & PRINT ACTION (Hidden on Print) -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 print:hidden">
            <div class="flex items-center gap-3">
                <a href="{{ route('my.orders') }}" 
                   class="w-11 h-11 rounded-2xl bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-xs flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                   title="Kembali">
                    <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2 text-[11px] font-bold text-[#8A7C70] uppercase tracking-wider">
                        <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition">Beranda</a>
                        <span>/</span>
                        <a href="{{ route('my.orders') }}" class="hover:text-[#6B4226] transition">Pesanan Saya</a>
                        <span>/</span>
                        <span class="text-[#6B4226]">Invoice</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] flex items-center gap-2">
                        <span>Rincian Invoice Transaksi</span>
                        <span class="text-xl">📄</span>
                    </h1>
                </div>
            </div>

            <!-- Print & Action CTA -->
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <button type="button" 
                        onclick="window.print()" 
                        class="px-4 py-2.5 bg-white hover:bg-[#FAF8F5] text-[#2D241E] border border-[#EAE1D7] hover:border-[#6B4226]/40 text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-2xs cursor-pointer active:scale-95">
                    <svg class="w-4 h-4 text-[#6B4226]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak / PDF</span>
                </button>

                @auth
                    <a href="{{ route('my.orders') }}" 
                       class="px-4 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 active:scale-95">
                        <span>📦 Semua Pesanan</span>
                    </a>
                @endauth
            </div>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-24"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 py-0"
                 class="overflow-hidden p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-emerald-900 text-xs sm:text-sm shadow-2xs print:hidden animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🎉</span>
                    <div>
                        <p class="font-black">{{ session('success') }}</p>
                        <p class="text-xs text-emerald-700 mt-0.5">Transaksi kamu telah resmi tercatat di sistem NusantaraMart.</p>
                    </div>
                </div>
                <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-800 font-bold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @php
            $isPpob = str_contains($order->order_code, 'PPOB');
            $isPaid = $order->payment_status === 'paid';
        @endphp

        <!-- Cancellation Status Alert Banners -->
        @if($order->cancellation_status === 'requested')
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs sm:text-sm space-y-1.5 shadow-2xs print:hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-black text-amber-950">
                    <span class="flex items-center gap-2">
                        <span class="text-base animate-pulse">⏳</span>
                        <span>Pengajuan Pembatalan Sedang Ditinjau Penjual</span>
                    </span>
                    @if($order->cancellation_requested_at)
                        <span class="text-xs bg-amber-100 text-amber-900 px-3 py-1 rounded-full border border-amber-300 font-bold self-start sm:self-auto">
                            Batas Respon: {{ $order->cancellation_requested_at->addDays(3)->diffForHumans() }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-amber-800">
                    <strong>Alasan Pembatalan:</strong> "{{ $order->cancellation_reason }}"
                </p>
                <p class="text-[11px] text-amber-700 leading-relaxed">
                    Pengiriman pesanan ini dikunci selama proses pengajuan. Jika penjual tidak merespons hingga <strong>{{ $order->cancellation_requested_at ? $order->cancellation_requested_at->addDays(3)->format('d M Y, H:i') : '' }} WIB</strong>, pesanan akan dibatalkan otomatis oleh sistem.
                </p>
            </div>
        @elseif($order->cancellation_status === 'rejected')
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm space-y-1 shadow-2xs print:hidden">
                <div class="flex items-center gap-2 font-bold text-rose-950">
                    <span class="text-base">⚠️</span>
                    <span>Pengajuan Pembatalan Ditolak oleh Penjual</span>
                </div>
                @if($order->cancellation_response_note)
                    <p class="text-xs text-rose-800">
                        <strong>Alasan Penolakan:</strong> "{{ $order->cancellation_response_note }}"
                    </p>
                @endif
                <p class="text-[11px] text-rose-700">Penjual tetap memproses pesanan ini untuk dikirimkan ke alamat Anda.</p>
            </div>
        @elseif($order->cancellation_status === 'approved' || in_array(strtolower($order->status), ['cancelled', 'canceled']))
            @if($order->cancellation_response_note)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 text-xs sm:text-sm space-y-1 shadow-2xs print:hidden">
                    <span class="font-bold text-slate-900 block">Keterangan Pembatalan:</span>
                    <p class="text-xs text-slate-600">{{ $order->cancellation_response_note }}</p>
                </div>
            @endif
        @endif

        <!-- 2. TRANSACTION PROGRESS TIMELINE (Hidden on Print) -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-xs print:hidden">
            <h3 class="text-xs font-black uppercase tracking-wider text-[#8A7C70] mb-4">Status & Alur Transaksi</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 relative">
                <!-- Step 1: Dibuat -->
                <div class="flex flex-col items-center text-center space-y-1.5 relative">
                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 border-2 border-emerald-500 flex items-center justify-center text-sm font-bold shadow-2xs">
                        ✓
                    </div>
                    <span class="text-xs font-bold text-[#2D241E]">Pesanan Dibuat</span>
                    <span class="text-[10px] text-[#8A7C70]">{{ $order->created_at->format('d M, H:i') }}</span>
                </div>

                <!-- Step 2: Pembayaran -->
                <div class="flex flex-col items-center text-center space-y-1.5 relative">
                    <div class="w-9 h-9 rounded-full {{ $isPaid ? 'bg-emerald-100 text-emerald-700 border-2 border-emerald-500' : 'bg-amber-100 text-amber-800 border-2 border-amber-400' }} flex items-center justify-center text-sm font-bold shadow-2xs">
                        {{ $isPaid ? '✓' : '⏱️' }}
                    </div>
                    <span class="text-xs font-bold text-[#2D241E]">Pembayaran</span>
                    <span class="text-[10px] {{ $isPaid ? 'text-emerald-700 font-bold' : 'text-amber-700 font-bold' }}">
                        {{ $isPaid ? 'Terverifikasi Lunas' : ($order->payment_method === 'cod' ? 'Bayar di Tempat (COD)' : 'Menunggu Bayar') }}
                    </span>
                </div>

                <!-- Step 3: Proses Operator / Penjual -->
                <div class="flex flex-col items-center text-center space-y-1.5 relative">
                    <div class="w-9 h-9 rounded-full {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700 border-2 border-emerald-500' : 'bg-blue-100 text-blue-700 border-2 border-blue-400' }} flex items-center justify-center text-sm font-bold shadow-2xs">
                        ✓
                    </div>
                    <span class="text-xs font-bold text-[#2D241E]">{{ $isPpob ? 'Diproses Operator' : 'Diproses Penjual' }}</span>
                    <span class="text-[10px] text-[#8A7C70]">Otomatis & Realtime</span>
                </div>

                <!-- Step 4: Selesai -->
                <div class="flex flex-col items-center text-center space-y-1.5 relative">
                    <div class="w-9 h-9 rounded-full {{ $order->status === 'completed' ? 'bg-emerald-600 text-white shadow-md ring-4 ring-emerald-100' : 'bg-[#FAF8F5] text-[#8A7C70] border-2 border-[#EAE1D7]' }} flex items-center justify-center text-sm font-black">
                        ✓
                    </div>
                    <span class="text-xs font-bold text-[#2D241E]">Transaksi Berhasil</span>
                    <span class="text-[10px] text-emerald-700 font-bold">Selesai 100%</span>
                </div>
            </div>
        </div>

        <!-- 3. OFFICIAL INVOICE SHEET CARD (PRINT-OPTIMIZED) -->
        <div id="invoice-printable" class="bg-white rounded-3xl border border-[#EAE1D7] shadow-md overflow-hidden relative">
            
            <!-- Watermark / Security Hologram Banner -->
            <div class="bg-gradient-to-r from-[#6B4226] via-[#8C4F27] to-[#B86221] text-white px-6 sm:px-8 py-3 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-black tracking-widest text-amber-200 uppercase">NUSANTARAMART</span>
                    <span class="text-white/60">•</span>
                    <span class="text-[11px] font-medium text-[#FAF4ED]">Dokumen Pembayaran Sah & Resmi</span>
                </div>
                <span class="text-[10px] font-mono font-bold bg-white/15 px-2 py-0.5 rounded border border-white/20">
                    STATUS: {{ $isPaid ? 'PAID / LUNAS' : ($order->payment_method === 'cod' ? 'COD (BAYAR DI TEMPAT)' : 'PENDING') }}
                </span>
            </div>

            <!-- Invoice Header: Logo, Title, Code & Paid Seal -->
            <div class="p-6 sm:p-8 bg-[#FAF7F2] border-b border-[#EAE1D7] flex flex-col sm:flex-row justify-between sm:items-center gap-6">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-[#6B4226] text-white flex items-center justify-center text-xl shadow-xs">
                            🛍️
                        </div>
                        <div>
                            <span class="text-lg font-black text-[#2D241E]">Nusantara<span class="text-[#6B4226]">Mart</span></span>
                            <span class="text-[9px] block uppercase tracking-wider text-[#8A7C70] font-bold">Official Marketplace Indonesia</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <span class="text-[11px] font-bold text-[#8A7C70] uppercase tracking-wider block">Nomor Invoice:</span>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl sm:text-2xl font-black text-[#2D241E] font-mono">{{ $order->order_code }}</h2>
                            <button type="button" 
                                    @click="copy('{{ $order->order_code }}')"
                                    class="text-[10px] font-bold text-[#8A7C70] hover:text-[#6B4226] bg-white px-2 py-1 rounded-md border border-[#EAE1D7] transition cursor-pointer print:hidden"
                                    title="Salin No Invoice">
                                <span x-text="copiedText === '{{ $order->order_code }}' ? '✓ Tersalin!' : '📋 Salin'"></span>
                            </button>
                        </div>
                        <p class="text-xs text-[#8A7C70]">Waktu: {{ $order->created_at->format('d F Y, H:i') }} WIB</p>
                    </div>
                </div>

                <!-- Official Paid Stamp / Seal -->
                <div class="flex flex-col sm:items-end gap-2 shrink-0">
                    @if($isPaid)
                        <div class="border-2 border-emerald-600 bg-emerald-50 text-emerald-800 px-4 py-2 rounded-2xl text-center shadow-xs rotate-[-2deg] sm:rotate-0">
                            <span class="text-[10px] font-black tracking-widest uppercase block text-emerald-600">Terverifikasi Otomatis</span>
                            <span class="text-lg font-black tracking-wider block">✓ LUNAS</span>
                            <span class="text-[9px] font-mono text-emerald-700 block">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @else
                        <div class="border-2 border-amber-500 bg-amber-50 text-amber-900 px-4 py-2 rounded-2xl text-center shadow-xs">
                            <span class="text-[10px] font-black tracking-widest uppercase block text-amber-600">Menunggu Pelunasan</span>
                            <span class="text-base font-black tracking-wider block">BELUM DIBAYAR</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Customer & Service Target Strip -->
            <div class="p-6 sm:p-8 border-b border-[#F2EAE0] grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs bg-[#FAF8F5]">
                <div class="space-y-1.5">
                    <span class="font-black uppercase tracking-wider text-[#8A7C70] block text-[10px]">Informasi Pemesan:</span>
                    <p class="text-sm font-bold text-[#2D241E]">{{ $order->customer_name }}</p>
                    <p class="text-[#5A4B40]">
                        <span class="text-[#8A7C70]">Kontak / WA:</span> 
                        <strong class="font-mono text-[#2D241E]">{{ $order->customer_phone }}</strong>
                    </p>
                    <p class="text-[#5A4B40]">
                        <span class="text-[#8A7C70]">Metode Bayar:</span> 
                        <span class="font-bold uppercase text-[#6B4226] bg-[#FAF4ED] px-2 py-0.5 rounded border border-[#EAE1D7]">
                            {{ str_replace('_', ' ', $order->payment_method ?? 'QRIS Instant') }}
                        </span>
                    </p>
                </div>

                <div class="space-y-1.5">
                    <span class="font-black uppercase tracking-wider text-[#8A7C70] block text-[10px]">
                        {{ $isPpob ? 'Tujuan Layanan Digital:' : 'Alamat Pengiriman:' }}
                    </span>
                    <p class="text-sm font-bold text-[#2D241E] leading-relaxed">
                        {{ $order->customer_address }}
                    </p>
                    @if($order->customer_notes && !str_contains($order->customer_notes, 'Token PLN'))
                        <p class="text-[11px] text-[#7A6C60] bg-white p-2 rounded-xl border border-[#EAE1D7]">
                            <span class="font-bold">Keterangan:</span> {{ $order->customer_notes }}
                        </p>
                    @endif

                    @if($order->shipping_courier || $order->tracking_number)
                        <div class="mt-2 p-2.5 rounded-xl bg-sky-50 border border-sky-200 text-xs text-sky-950 flex items-center justify-between">
                            <div>
                                <span class="font-bold block">🚚 Ekspedisi: {{ $order->shipping_courier ?: 'Reguler' }}</span>
                                @if($order->tracking_number)
                                    <span class="text-[11px] font-mono text-sky-800">No. Resi: <strong>{{ $order->tracking_number }}</strong></span>
                                @endif
                            </div>
                            @if($order->tracking_number)
                                <button type="button" 
                                        @click="copy('{{ $order->tracking_number }}')"
                                        class="px-2.5 py-1 bg-white text-sky-900 border border-sky-200 rounded-lg text-[10px] font-bold shadow-2xs hover:bg-sky-100 transition print:hidden">
                                    <span x-text="copiedText === '{{ $order->tracking_number }}' ? '✓ Tersalin' : '📋 Salin Resi'"></span>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- PLN TOKEN CERTIFICATE SPOTLIGHT (If Order is PLN Token) -->
            @if($order->customer_notes && str_contains($order->customer_notes, 'Token PLN'))
                @php
                    preg_match('/Token PLN:\s*([0-9\-]+)/', $order->customer_notes, $matches);
                    $tokenNumber = $matches[1] ?? null;
                @endphp
                <div class="mx-6 sm:mx-8 my-6 p-5 sm:p-6 rounded-3xl bg-gradient-to-r from-amber-50 via-[#FFFDF5] to-amber-50 border-2 border-amber-300 shadow-sm space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-amber-400 text-amber-950 flex items-center justify-center font-bold text-lg">⚡</span>
                            <div>
                                <h4 class="text-xs sm:text-sm font-black text-amber-950 uppercase tracking-wider">Nomor Token Listrik PLN Resmi</h4>
                                <span class="text-[10px] text-amber-800">Diterbitkan langsung oleh PT PLN (Persero)</span>
                            </div>
                        </div>
                        @if($tokenNumber)
                            <button type="button" 
                                    @click="copy('{{ $tokenNumber }}')"
                                    class="px-3.5 py-1.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center gap-1.5 cursor-pointer shrink-0 print:hidden">
                                <span x-text="copiedText === '{{ $tokenNumber }}' ? '✓ Tersalin!' : '📋 Salin Token'"></span>
                            </button>
                        @endif
                    </div>

                    <div class="bg-white p-3 sm:p-4 rounded-2xl border border-amber-300 text-center shadow-inner">
                        <div class="text-xl sm:text-3xl font-mono font-black text-[#6B4226] tracking-widest select-all">
                            {{ $tokenNumber ?: $order->customer_notes }}
                        </div>
                    </div>

                    <div class="text-[11px] text-amber-900 bg-amber-100/60 p-2.5 rounded-xl border border-amber-200">
                        <span class="font-bold">Cara Penggunaan:</span> Masukkan 16 digit nomor token di atas ke kWh meter PLN di tempat Anda, lalu tekan tombol <strong>ENTER</strong> (tombol merah/hijau). Saldo kWh listrik Anda akan langsung bertambah seketika.
                    </div>
                </div>
            @endif

            <!-- Rincian Produk / Layanan Table -->
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#8A7C70] mb-4">
                    {{ $isPpob ? 'Rincian Layanan Digital' : 'Daftar Barang Belanjaan' }}
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#EAE1D7] text-[#8A7C70] font-black uppercase tracking-wider text-[10px]">
                                <th class="pb-3">Item / Layanan</th>
                                <th class="pb-3 text-center">Jumlah</th>
                                <th class="pb-3 text-right">Harga Satuan</th>
                                <th class="pb-3 text-right">Total Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2EAE0]">
                            @foreach($order->items as $item)
                                @php
                                    $itemImg = $item->item_image_url;
                                    $isCompleted = $order->status === 'completed';
                                    $itemReview = $isCompleted && $item->product_id 
                                        ? $order->reviews->firstWhere('product_id', $item->product_id) 
                                        : null;
                                @endphp
                                <tr class="py-3">
                                    <td class="py-3.5 pr-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-xl bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                                                @if($itemImg)
                                                    <img src="{{ $itemImg }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-base">{{ $isPpob ? '📱' : '🛍️' }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-bold text-sm text-[#2D241E] block">{{ $item->product_name }}</span>
                                                @if($isPpob)
                                                    <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-1.5 py-0.2 rounded">Realtime 24 Jam</span>
                                                @endif
                                                @if($isCompleted && $item->product)
                                                    <div class="mt-1 print:hidden">
                                                        @if($itemReview)
                                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200">
                                                                <span>⭐</span>
                                                                <span>Dinilai ({{ $itemReview->rating }}/5)</span>
                                                            </span>
                                                        @else
                                                            <button type="button" 
                                                                    @click="openReviewModal({{ $order->id }}, '{{ $order->order_code }}', {{ $item->product->id }}, '{{ addslashes($item->product->name) }}', '{{ $itemImg ? addslashes($itemImg) : '' }}', '{{ addslashes($item->variant_name ?? '') }}', '{{ route('product.reviews.store', $item->product->slug) }}')"
                                                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-white bg-amber-500 hover:bg-amber-600 px-2.5 py-1 rounded-lg transition shadow-2xs active:scale-95 cursor-pointer">
                                                                <span>⭐</span>
                                                                <span>Beri Nilai Produk</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 text-center font-semibold text-[#5A4B40]">{{ $item->quantity }}</td>
                                    <td class="py-3.5 text-right font-medium text-[#5A4B40]">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="py-3.5 text-right font-black text-sm text-[#2D241E]">{{ $item->formatted_subtotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Price Totals Breakdown Box -->
                <div class="mt-6 pt-4 border-t-2 border-dashed border-[#EAE1D7] space-y-2.5 text-xs">
                    <div class="flex justify-between text-[#7A6C60]">
                        <span>Subtotal Produk / Layanan:</span>
                        <span class="font-bold text-[#2D241E]">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-[#7A6C60]">
                        <span>{{ $isPpob ? 'Biaya Admin Layanan Digital:' : 'Ongkos Kirim:' }}</span>
                        <span class="font-bold {{ $order->shipping_cost === 0 ? 'text-emerald-700' : 'text-[#2D241E]' }}">
                            {{ $order->shipping_cost === 0 ? 'GRATIS' : 'Rp ' . number_format($order->shipping_cost, 0, ',', '.') }}
                        </span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-rose-600 font-bold">
                            <span>Potongan Kupon Promo ({{ $order->coupon_code }}):</span>
                            <span>- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <!-- Grand Total Banner -->
                    <div class="pt-3 border-t border-[#EAE1D7] flex justify-between items-center bg-[#FAF7F2] -mx-6 sm:-mx-8 px-6 sm:px-8 py-4 mt-4">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-[#8A7C70] block">Total Pembayaran Resmi</span>
                            <span class="text-[10px] text-[#8A7C70]">Sudah termasuk PPN & biaya layanan</span>
                        </div>
                        <span class="text-2xl sm:text-3xl font-black text-[#6B4226] font-mono">
                            {{ $order->formatted_grand_total }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer: Security Seal, Reference ID & BUMN Gateway -->
            <div class="bg-[#FAF8F5] p-6 sm:p-8 border-t border-[#EAE1D7] flex flex-col sm:flex-row justify-between sm:items-center gap-4 text-xs text-[#8A7C70]">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-[11px] font-bold text-[#2D241E]">
                        <span>🛡️ NusantaraMart Safe Payment Switch</span>
                    </div>
                    <p class="text-[10px] leading-relaxed">
                        Dokumen digital ini diterbitkan secara otomatis oleh server NusantaraMart dan sah digunakan sebagai bukti pembayaran resmi, klaim garansi, reimbursement kantor, serta pembukuan keuangan.
                    </p>
                </div>
                <div class="text-left sm:text-right shrink-0">
                    <span class="text-[10px] font-mono block">REF ID: NM-PAY-{{ strtoupper(substr(md5($order->order_code), 0, 10)) }}</span>
                    <span class="text-[10px] {{ $isPaid ? 'text-emerald-700' : 'text-amber-700' }} font-bold block">
                        {{ $isPaid ? '✓ Status: LUNAS & RESMI' : ($order->payment_method === 'cod' ? '💵 Status: BAYAR DI TEMPAT (COD)' : '⏱️ Status: MENUNGGU PEMBAYARAN') }}
                    </span>
                </div>
            </div>

        </div>

        <!-- 3. BOTTOM ACTIONS: ORDER CANCELLATION (Hidden on print) -->
        <div class="print:hidden space-y-4">
            @if($order->canBeCancelledByBuyer())
                <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center text-lg font-black shrink-0 shadow-2xs">
                            ✕
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-sm font-black text-[#2D241E]">Ingin Membatalkan Pesanan Ini?</h4>
                            <p class="text-xs text-[#7A6C60] leading-relaxed">
                                Pesanan saat ini masih berstatus <strong>{{ $order->status === 'processing' ? 'Sedang Dikemas' : 'Menunggu Konfirmasi' }}</strong> dan belum dikirim oleh penjual. Anda masih dapat mengajukan pembatalan sebelum barang dikirim.
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="openCancelModal()"
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer shrink-0 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Batalkan Pesanan</span>
                    </button>
                </div>
            @elseif($order->cancellation_status === 'requested')
                <div class="bg-amber-50 rounded-3xl border border-amber-300 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-amber-900">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-100 border border-amber-300 text-amber-800 flex items-center justify-center text-lg font-black shrink-0 animate-pulse shadow-2xs">
                            ⏳
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-sm font-black text-amber-950">Pengajuan Pembatalan Sedang Ditinjau Penjual</h4>
                            <p class="text-xs text-amber-800 leading-relaxed">
                                Alasan: <em>"{{ $order->cancellation_reason }}"</em>. Penjual tidak dapat mengirim pesanan selama pengajuan ini berjalan.
                            </p>
                        </div>
                    </div>
                    @if($order->cancellation_requested_at)
                        <span class="text-xs font-bold text-amber-900 bg-amber-100/80 px-3.5 py-1.5 rounded-full border border-amber-300 shrink-0">
                            Batas Respon: {{ $order->cancellation_requested_at->addDays(3)->diffForHumans() }}
                        </span>
                    @endif
                </div>
            @elseif($order->status === 'shipped')
                <div class="bg-blue-50/80 rounded-3xl border border-blue-200 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-blue-950">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-100 border border-blue-300 text-blue-700 flex items-center justify-center text-lg font-black shrink-0 shadow-2xs">
                            🚚
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-sm font-black text-blue-950">Pesanan Sedang Dalam Pengiriman</h4>
                            <p class="text-xs text-blue-800 leading-relaxed">
                                Produk sudah diserahkan ke pihak ekspedisi ({{ $order->shipping_courier ?? 'Kurir' }}{{ $order->tracking_number ? ' • Resi: ' . $order->tracking_number : '' }}) dan <strong>sudah tidak dapat dibatalkan</strong>. Pembatalan hanya dapat dilakukan saat pesanan masih dalam status dikemas.
                            </p>
                        </div>
                    </div>
                    <div class="px-3.5 py-2 bg-blue-100 text-blue-800 border border-blue-200 rounded-xl text-xs font-bold shrink-0 flex items-center gap-1.5">
                        <span>🔒</span>
                        <span>Tidak Dapat Dibatalkan</span>
                    </div>
                </div>
            @elseif($order->status === 'delivered')
                <div class="bg-indigo-50 border border-indigo-200 rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-indigo-950">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-100 border border-indigo-300 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 shadow-2xs">
                            📍
                        </div>
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-black text-indigo-950">Pesanan Telah Sampai di Alamat Tujuan</h4>
                                @if($order->delivered_at)
                                    <span class="text-[10px] bg-indigo-200/80 text-indigo-900 px-2.5 py-0.5 rounded-full font-bold border border-indigo-300">
                                        Batas Otomatis Selesai: {{ $order->delivered_at->addDays(7)->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-indigo-800 leading-relaxed">
                                Silakan periksa kelengkapan & kondisi fisik barang. Jika sudah sesuai, klik <strong>"Pesanan Selesai"</strong>. Jika terdapat kendala, Anda dapat mengajukan <strong>Pengembalian Barang</strong> sebelum <strong>{{ $order->delivered_at ? $order->delivered_at->addDays(7)->format('d M Y, H:i') : '' }} WIB</strong>.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-center flex-wrap">
                        @if($order->canBeReturnedByBuyer())
                            <button type="button" 
                                    @click="openReturnModal()"
                                    class="px-4 py-2.5 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 text-xs font-bold rounded-xl shadow-xs transition cursor-pointer active:scale-95">
                                <span>⚠️ Ajukan Pengembalian</span>
                            </button>
                        @endif
                        @if($order->canBeConfirmedCompletedByBuyer())
                            <form action="{{ route('orders.complete', $order) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Apakah Anda yakin pesanan telah diterima dengan baik dan lengkap? Pesanan yang telah diselesaikan tidak dapat diajukan pengembalian barang lagi.')"
                                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                                    <span>✓ Konfirmasi Selesai</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            @if($order->return_status === 'requested')
                <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-rose-950">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-rose-100 border border-rose-300 text-rose-800 flex items-center justify-center text-lg font-black shrink-0 animate-pulse shadow-2xs">
                            ⏳
                        </div>
                        <div class="space-y-0.5">
                            <h4 class="text-sm font-black text-rose-950">Pengajuan Pengembalian Barang Sedang Ditinjau Penjual</h4>
                            <p class="text-xs text-rose-800 leading-relaxed">
                                Alasan: <strong>{{ $order->return_reason }}</strong> &bull; <em>"{{ $order->return_description }}"</em>
                            </p>
                        </div>
                    </div>
                    @if($order->return_proof_image)
                        <a href="{{ asset('storage/' . $order->return_proof_image) }}" target="_blank" class="px-3.5 py-2 bg-white text-blue-700 border border-rose-200 rounded-xl text-xs font-bold shrink-0 hover:underline">
                            📷 Lihat Bukti Foto
                        </a>
                    @endif
                </div>
            @elseif($order->return_status === 'approved')
                <div class="bg-purple-50 border border-purple-200 rounded-3xl p-5 sm:p-6 shadow-xs flex items-center gap-3.5 text-purple-950">
                    <div class="w-10 h-10 rounded-2xl bg-purple-100 border border-purple-300 text-purple-800 flex items-center justify-center text-lg font-black shrink-0 shadow-2xs">
                        ✓
                    </div>
                    <div class="space-y-0.5">
                        <h4 class="text-sm font-black text-purple-950">Pengembalian Barang Telah Disetujui Penjual</h4>
                        <p class="text-xs text-purple-800 leading-relaxed">
                            {{ $order->return_response_note ?: 'Pengembalian barang disetujui oleh penjual dan stok telah dipulihkan.' }}
                        </p>
                    </div>
                </div>
            @elseif($order->return_status === 'rejected')
                <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 sm:p-6 shadow-xs flex items-center gap-3.5 text-amber-950">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 border border-amber-300 text-amber-800 flex items-center justify-center text-lg font-black shrink-0 shadow-2xs">
                        ⚠️
                    </div>
                    <div class="space-y-0.5">
                        <h4 class="text-sm font-black text-amber-950">Pengajuan Pengembalian Ditolak Penjual</h4>
                        @if($order->return_response_note)
                            <p class="text-xs text-amber-800 leading-relaxed">
                                Catatan Penolakan: "{{ $order->return_response_note }}"
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            @if($order->status === 'completed')
                <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-5 sm:p-6 shadow-xs flex items-center gap-3.5 text-emerald-950">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 border border-emerald-300 text-emerald-800 flex items-center justify-center text-lg font-black shrink-0 shadow-2xs">
                        ✓
                    </div>
                    <div class="space-y-0.5">
                        <h4 class="text-sm font-black text-emerald-950">Pesanan Telah Selesai</h4>
                        <p class="text-xs text-emerald-800 leading-relaxed">
                            Pesanan ini telah resmi diselesaikan{{ $order->completed_at ? ' pada ' . $order->completed_at->format('d M Y, H:i') . ' WIB' : '' }}. Terima kasih telah berbelanja di NusantaraMart!
                        </p>
                    </div>
                </div>
            @elseif($order->status === 'cancelled')
                <div class="bg-rose-50 rounded-3xl border border-rose-200 p-5 sm:p-6 shadow-xs flex items-center gap-3.5 text-rose-900">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 border border-rose-300 text-rose-700 flex items-center justify-center text-lg font-black shrink-0 shadow-2xs">
                        ✕
                    </div>
                    <div class="space-y-0.5">
                        <h4 class="text-sm font-black text-rose-950">Pesanan Telah Dibatalkan</h4>
                        <p class="text-xs text-rose-800 leading-relaxed">
                            {{ $order->cancellation_response_note ?: 'Pesanan ini telah resmi dibatalkan.' }}
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- MODAL AJUKAN PENGEMBALIAN BARANG -->
        <div x-show="returnModal" 
             x-cloak 
             @keydown.window.escape="closeReturnModal()"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 overflow-hidden"
             aria-labelledby="modal-return-title" role="dialog" aria-modal="true">
            
            <!-- Backdrop with blur -->
            <div x-show="returnModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeReturnModal()"
                 class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"></div>

            <!-- Modal Box: flex flex-col, max-h-[88vh] -->
            <div x-show="returnModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-[#EAE1D7] text-left flex flex-col max-h-[88vh] overflow-hidden z-10">
                
                <!-- Modal Header (Fixed) -->
                <div class="p-5 sm:p-6 border-b border-[#F2EAE0] flex items-center justify-between shrink-0 bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center text-lg font-black shadow-2xs">
                            ⚠️
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-[#2D241E]" id="modal-return-title">
                                Ajukan Pengembalian Barang
                            </h3>
                            <p class="text-xs text-[#8A7C70] font-mono">Kode: #{{ $order->order_code }}</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="closeReturnModal()" 
                            class="w-8 h-8 rounded-full bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#8A7C70] hover:text-[#2D241E] flex items-center justify-center text-base font-bold transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Form with scrollable body and pinned footer -->
                <form action="{{ route('orders.return', $order) }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden m-0">
                    @csrf
                    
                    <!-- Scrollable Body Content -->
                    <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 overscroll-contain">
                        <div>
                            <label class="block text-xs font-bold text-[#2D241E] mb-1.5">
                                Pilih Alasan Pengembalian <span class="text-rose-500">*</span>
                            </label>
                            <select name="return_reason" 
                                    x-model="returnReason"
                                    required
                                    class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] transition font-medium">
                                <option value="Produk Rusak / Cacat">Produk Rusak / Cacat saat Diterima</option>
                                <option value="Produk Tidak Sesuai Deskripsi / Salah Kirim">Produk Tidak Sesuai Deskripsi / Salah Kirim Barang</option>
                                <option value="Jumlah / Komponen Produk Kurang">Jumlah Barang Kurang / Komponen Tidak Lengkap</option>
                                <option value="Produk Kadaluarsa / Basi">Produk Sudah Kadaluarsa / Basi</option>
                                <option value="Produk Tidak Berfungsi Normal">Produk Tidak Berfungsi / Malfungsi</option>
                                <option value="Lainnya">Alasan Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#2D241E] mb-1.5">
                                Rincian Kendala & Deskripsi Masalah <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="return_description" 
                                      x-model="returnDescription" 
                                      required
                                      minlength="5"
                                      placeholder="Ceritakan kendala pada barang secara jelas (contoh: kemasan robek, barang yang datang varian merah padahal pesan biru)..." 
                                      rows="3"
                                      class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#2D241E] mb-1.5">
                                Unggah Foto Bukti Barang Bermasalah (Opsional)
                            </label>
                            <input type="file" 
                                   name="return_proof_image" 
                                   accept="image/png, image/jpeg, image/jpg, image/webp"
                                   class="w-full text-xs text-[#5A4B40] file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FAF4ED] file:text-[#6B4226] hover:file:bg-[#F2EAE0] file:cursor-pointer cursor-pointer border border-[#EAE1D7] rounded-xl p-1 bg-[#FAF8F5]">
                            <span class="text-[10px] text-[#8A7C70] block mt-1">Format: JPG, PNG, WEBP (Maksimal 5MB). Lampirkan foto kondisi paket atau barang rusak agar pengajuan cepat disetujui.</span>
                        </div>

                        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-[11px] text-amber-900 space-y-1">
                            <p class="font-bold flex items-center gap-1 text-amber-950">
                                <span>ℹ️ Ketentuan Pengajuan Pengembalian:</span>
                            </p>
                            <p class="leading-relaxed text-amber-800">
                                Penjual akan meninjau kendala Anda. Harap simpan fisik barang dan kardus paket dengan baik. Jika disetujui, stok produk otomatis dipulihkan dan pengembalian diproses.
                            </p>
                        </div>
                    </div>

                    <!-- Modal Footer (Pinned / Always visible) -->
                    <div class="p-4 sm:p-5 border-t border-[#F2EAE0] bg-[#FAF8F5] flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" 
                                @click="closeReturnModal()"
                                class="px-4 py-2.5 bg-white hover:bg-[#FAF8F5] text-[#5A4B40] border border-[#EAE1D7] text-xs font-bold rounded-xl transition cursor-pointer active:scale-95">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                            <span>Kirim Pengajuan Retur</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <!-- MODAL AJUKAN PEMBATALAN PESANAN -->
    <div x-show="cancelModal" 
         x-cloak 
         @keydown.window.escape="closeCancelModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 overflow-hidden"
         aria-labelledby="modal-cancel-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop with blur -->
        <div x-show="cancelModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeCancelModal()"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"></div>

        <!-- Modal Box: flex flex-col, max-h-[88vh] -->
        <div x-show="cancelModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-[#EAE1D7] text-left flex flex-col max-h-[88vh] overflow-hidden z-10">
            
            <!-- Modal Header (Fixed) -->
            <div class="p-5 sm:p-6 border-b border-[#F2EAE0] flex items-center justify-between shrink-0 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center justify-center text-lg font-black shadow-2xs">
                        ✕
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-[#2D241E]" id="modal-cancel-title">
                            Ajukan Pembatalan Pesanan
                        </h3>
                        <p class="text-xs text-[#8A7C70] font-mono">Kode: #{{ $order->order_code }}</p>
                    </div>
                </div>
                <button type="button" 
                        @click="closeCancelModal()" 
                        class="w-8 h-8 rounded-full bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#8A7C70] hover:text-[#2D241E] flex items-center justify-center text-base font-bold transition cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- Form with scrollable body and pinned footer -->
            <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden m-0">
                @csrf
                
                <!-- Scrollable Body Content -->
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 overscroll-contain">
                    <div>
                        <label class="block text-xs font-bold text-[#2D241E] mb-2">
                            Pilih Alasan Pembatalan <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-2 text-xs">
                            <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition"
                                   :class="cancelReasonOption === 'Ingin mengubah alamat pengiriman' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-2xs' : 'border-[#EAE1D7] hover:bg-[#FAF8F5]'">
                                <input type="radio" name="reason_choice" value="Ingin mengubah alamat pengiriman" x-model="cancelReasonOption" class="text-[#6B4226] focus:ring-[#6B4226]">
                                <span class="font-medium text-[#2D241E]">Ingin mengubah alamat pengiriman</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition"
                                   :class="cancelReasonOption === 'Ingin mengubah variasi atau rincian produk' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-2xs' : 'border-[#EAE1D7] hover:bg-[#FAF8F5]'">
                                <input type="radio" name="reason_choice" value="Ingin mengubah variasi atau rincian produk" x-model="cancelReasonOption" class="text-[#6B4226] focus:ring-[#6B4226]">
                                <span class="font-medium text-[#2D241E]">Ingin mengubah variasi atau rincian produk</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition"
                                   :class="cancelReasonOption === 'Menemukan harga lebih murah di toko lain' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-2xs' : 'border-[#EAE1D7] hover:bg-[#FAF8F5]'">
                                <input type="radio" name="reason_choice" value="Menemukan harga lebih murah di toko lain" x-model="cancelReasonOption" class="text-[#6B4226] focus:ring-[#6B4226]">
                                <span class="font-medium text-[#2D241E]">Menemukan harga lebih murah di toko lain</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition"
                                   :class="cancelReasonOption === 'Lainnya' ? 'border-[#6B4226] bg-[#FAF4ED] shadow-2xs' : 'border-[#EAE1D7] hover:bg-[#FAF8F5]'">
                                <input type="radio" name="reason_choice" value="Lainnya" x-model="cancelReasonOption" class="text-[#6B4226] focus:ring-[#6B4226]">
                                <span class="font-medium text-[#2D241E]">Alasan Lainnya</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#2D241E] mb-1.5">
                            Keterangan Tambahan (Opsional)
                        </label>
                        <textarea x-model="cancelReasonDetail" 
                                  placeholder="Jelaskan alasan pembatalan secara singkat..." 
                                  rows="3"
                                  class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] transition"></textarea>
                    </div>

                    <!-- Hidden combined cancellation_reason field -->
                    <input type="hidden" name="cancellation_reason" 
                           :value="cancelReasonOption === 'Lainnya' ? (cancelReasonDetail || 'Alasan Lainnya') : (cancelReasonOption + (cancelReasonDetail ? ' (' + cancelReasonDetail + ')' : ''))">

                    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-[11px] text-amber-900 space-y-1">
                        <p class="font-bold flex items-center gap-1 text-amber-950">
                            <span>ℹ️ Ketentuan Pembatalan:</span>
                        </p>
                        <p class="leading-relaxed text-amber-800">
                            Pengajuan akan dikirim ke penjual untuk disetujui. Jika penjual tidak merespons dalam <strong>3 hari (72 jam)</strong>, pesanan akan <strong>otomatis dibatalkan</strong> oleh sistem dan penjual tidak dapat mengirim barang selama masa pengajuan.
                        </p>
                    </div>
                </div>

                <!-- Modal Footer (Pinned / Always visible) -->
                <div class="p-4 sm:p-5 border-t border-[#F2EAE0] bg-[#FAF8F5] flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" 
                            @click="closeCancelModal()"
                            class="px-4 py-2.5 bg-white hover:bg-[#FAF8F5] text-[#5A4B40] border border-[#EAE1D7] text-xs font-bold rounded-xl transition cursor-pointer active:scale-95">
                        Kembali
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <span>Kirim Pengajuan Pembatalan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL BERI PENILAIAN / ULASAN PRODUK -->
    <div x-show="reviewModal" 
         x-cloak 
         @keydown.window.escape="closeReviewModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 overflow-hidden print:hidden"
         aria-labelledby="modal-detail-review-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop with blur -->
        <div x-show="reviewModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeReviewModal()"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"></div>

        <!-- Modal Box -->
        <div x-show="reviewModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-3xl max-w-lg w-full border border-[#EAE1D7] shadow-2xl overflow-hidden z-10 flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="p-5 sm:p-6 border-b border-[#F2EAE0] flex items-center justify-between bg-[#FAF8F5] shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-lg font-bold shrink-0">
                        ⭐
                    </div>
                    <div>
                        <h3 id="modal-detail-review-title" class="font-bold text-sm sm:text-base text-[#2D241E]">Nilai Produk</h3>
                        <p class="text-xs text-[#8A7C70] font-mono" x-text="'Invoice #' + reviewOrderCode"></p>
                    </div>
                </div>
                <button type="button" 
                        @click="closeReviewModal()" 
                        class="w-8 h-8 rounded-full bg-white hover:bg-[#FAF4ED] text-[#8A7C70] hover:text-[#2D241E] flex items-center justify-center text-base font-bold transition cursor-pointer border border-[#EAE1D7]">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form :action="reviewActionUrl" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden m-0">
                @csrf
                <input type="hidden" name="order_id" :value="reviewOrderId">
                <input type="hidden" name="variant_name" :value="reviewVariantName">
                <input type="hidden" name="rating" :value="reviewRating">
                
                <!-- Scrollable Body Content -->
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 overscroll-contain">
                    <!-- Product Info Card -->
                    <div class="flex items-center gap-3 p-3 bg-[#FAF8F5] rounded-2xl border border-[#EAE1D7]">
                        <div class="w-12 h-12 rounded-xl bg-white border border-[#EAE1D7] flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                            <template x-if="reviewProductImage">
                                <img :src="reviewProductImage" :alt="reviewProductName" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!reviewProductImage">
                                <span class="text-xl">📦</span>
                            </template>
                        </div>
                        <div class="truncate">
                            <h4 class="font-bold text-xs sm:text-sm text-[#2D241E] truncate" x-text="reviewProductName"></h4>
                            <p class="text-[11px] text-[#8A7C70]" x-show="reviewVariantName" x-text="'Varian: ' + reviewVariantName"></p>
                        </div>
                    </div>

                    <!-- Interactive Star Rating -->
                    <div class="text-center py-2 bg-[#FCFAF7] rounded-2xl border border-[#EAE1D7]/60 space-y-2">
                        <label class="block text-xs font-bold text-[#2D241E]">
                            Kualitas Produk <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center justify-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                <button type="button"
                                        @click="reviewRating = star"
                                        @mouseenter="reviewHoverRating = star"
                                        @mouseleave="reviewHoverRating = reviewRating"
                                        class="p-1 text-2xl sm:text-3xl transition-transform hover:scale-125 cursor-pointer focus:outline-none"
                                        :title="star + ' Bintang'">
                                    <span :class="star <= (reviewHoverRating || reviewRating) ? 'text-amber-400' : 'text-slate-200'">★</span>
                                </button>
                            </template>
                        </div>
                        <p class="text-xs font-bold text-amber-700" x-text="['', 'Sangat Buruk 😞', 'Buruk 🙁', 'Cukup 😐', 'Puas 🙂', 'Sangat Puas! ⭐🤩'][reviewHoverRating || reviewRating] || ''"></p>
                    </div>

                    <!-- Review Textarea -->
                    <div>
                        <label class="block text-xs font-bold text-[#2D241E] mb-1.5">
                            Tulis Ulasan Kamu
                        </label>
                        <textarea name="review" 
                                  x-model="reviewText"
                                  rows="3"
                                  maxlength="1000"
                                  placeholder="Ceritakan kepuasan kamu tentang kualitas barang, kecepatan pengiriman, dan pelayanan toko..." 
                                  class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] transition"></textarea>
                        <div class="flex justify-between items-center text-[10px] text-[#8A7C70] mt-1">
                            <span>Bantu pembeli lain menentukan pilihan terbaik</span>
                            <span x-text="(reviewText.length || 0) + '/1000'"></span>
                        </div>
                    </div>

                    <!-- Photo Upload -->
                    <div>
                        <label class="block text-xs font-bold text-[#2D241E] mb-1.5">
                            Tambahkan Foto Produk (Opsional)
                        </label>
                        <input type="file" 
                               name="photo" 
                               accept="image/png, image/jpeg, image/jpg, image/webp"
                               class="w-full text-xs text-[#5A4B40] file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FAF4ED] file:text-[#6B4226] hover:file:bg-[#F2EAE0] file:cursor-pointer cursor-pointer border border-[#EAE1D7] rounded-xl p-1 bg-[#FAF8F5]">
                        <span class="text-[10px] text-[#8A7C70] block mt-1">Format: JPG, PNG, WEBP (Maksimal 3MB). Ulasan berfoto akan mendapatkan sorotan khusus.</span>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 sm:p-5 border-t border-[#F2EAE0] bg-[#FAF8F5] flex items-center justify-end gap-2.5 shrink-0">
                    <button type="button" 
                            @click="closeReviewModal()"
                            class="px-4 py-2.5 bg-white hover:bg-[#FAF8F5] text-[#5A4B40] border border-[#EAE1D7] text-xs font-bold rounded-xl transition cursor-pointer active:scale-95">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <span>⭐ Kirim Penilaian</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    body {
        background-color: white !important;
        color: black !important;
    }
    header, footer, nav, .print\:hidden {
        display: none !important;
    }
    #invoice-printable {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

@if(session('just_ordered'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            if (window.snackCart && Array.isArray(window.snackCart.items)) {
                window.snackCart.items = window.snackCart.items.filter(function(i) { return i.selected === false; });
                window.snackCart.saveCart();
            } else {
                const authId = {{ Auth::id() ? Auth::id() : 'null' }};
                const key = authId ? ('nusantaramart_cart_user_' + authId) : 'nusantaramart_cart_guest';
                const raw = localStorage.getItem(key);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed)) {
                        const remaining = parsed.filter(function(i) { return i.selected === false; });
                        localStorage.setItem(key, JSON.stringify(remaining));
                    }
                }
            }
        } catch(e) {
            console.error('Error clearing checked out items:', e);
        }
    });
</script>
@endif
@endsection
