@extends('layouts.app')

@section('title', 'Riwayat Pesanan Saya — NusantaraMart')

@section('content')
@php
    $countAll = $orders->count();
    $countProcessing = $orders->filter(fn($o) => in_array(strtolower($o->status), ['pending', 'confirmed', 'processing']))->count();
    $countShipped = $orders->filter(fn($o) => in_array(strtolower($o->status), ['shipped', 'delivered']) && $o->return_status !== 'requested')->count();
    $countCompleted = $orders->filter(fn($o) => strtolower($o->status) === 'completed')->count();
    $countUnreviewed = $orders->filter(fn($o) => $o->hasUnreviewedItems())->count();
    $countCancelled = $orders->filter(fn($o) => in_array(strtolower($o->status), ['cancelled', 'canceled']))->count();
    $countReturned = $orders->filter(fn($o) => in_array(strtolower($o->status), ['returned', 'refunded', 'return']) || in_array($o->return_status, ['requested', 'approved', 'rejected']))->count();
@endphp

<div class="py-6 sm:py-10 bg-[#FAF8F5] min-h-[85vh]" x-data="{ 
    activeTab: 'all',
    copiedCode: null,
    cancelModal: false,
    cancelOrderId: null,
    cancelOrderCode: '',
    cancelActionUrl: '',
    cancelReasonOption: 'Ingin mengubah alamat pengiriman',
    cancelReasonDetail: '',
    openCancelModal(id, code, url) {
        this.cancelOrderId = id;
        this.cancelOrderCode = code;
        this.cancelActionUrl = url;
        this.cancelReasonOption = 'Ingin mengubah alamat pengiriman';
        this.cancelReasonDetail = '';
        this.cancelModal = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeCancelModal() {
        this.cancelModal = false;
        this.cancelOrderId = null;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    },
    returnModal: false,
    returnOrderId: null,
    returnOrderCode: '',
    returnActionUrl: '',
    returnReason: 'Produk Rusak / Cacat',
    returnDescription: '',
    openReturnModal(id, code, url) {
        this.returnOrderId = id;
        this.returnOrderCode = code;
        this.returnActionUrl = url;
        this.returnReason = 'Produk Rusak / Cacat';
        this.returnDescription = '';
        this.returnModal = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeReturnModal() {
        this.returnModal = false;
        this.returnOrderId = null;
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
    },
    copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        this.copiedCode = text;
        setTimeout(() => this.copiedCode = null, 2000);
    },
    matchesTab(orderStatus, returnStatus, hasUnreviewed) {
        const s = (orderStatus || '').toLowerCase();
        const ret = (returnStatus || '').toLowerCase();
        if (this.activeTab === 'all') return true;
        if (this.activeTab === 'processing') return ['pending', 'confirmed', 'processing'].includes(s);
        if (this.activeTab === 'shipped') return ['shipped', 'delivered'].includes(s) && ret !== 'requested';
        if (this.activeTab === 'completed') return s === 'completed';
        if (this.activeTab === 'unreviewed') return s === 'completed' && Boolean(hasUnreviewed);
        if (this.activeTab === 'cancelled') return ['cancelled', 'canceled'].includes(s);
        if (this.activeTab === 'returned') return ['returned', 'refunded', 'return'].includes(s) || ['requested', 'approved', 'rejected'].includes(ret);
        return false;
    }
}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
        
        <!-- 1. BREADCRUMBS & TOP HEADER -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-[#EAE1D7] shadow-xs flex items-center gap-4">
            <a href="{{ route('home') }}" 
               class="w-11 h-11 rounded-2xl bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-xs flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
               title="Kembali ke Beranda">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold text-[#8A7C70] uppercase tracking-wider">
                    <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition">Beranda</a>
                    <span>/</span>
                    <span class="text-[#6B4226]">Akun Saya</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] flex items-center gap-2 mt-0.5">
                    <span>Riwayat Pesanan</span>
                    <span class="text-xl">📦</span>
                </h1>
                <p class="text-xs text-[#7A6C60] mt-0.5">Pantau status pembayaran belanja produk dan tagihan digital kamu</p>
            </div>
        </div>

        <!-- 2. STATUS FILTER TABS BAR (SHOPEE / TOKOPEDIA STYLE) -->
        <div class="bg-white rounded-2xl border border-[#EAE1D7] shadow-xs p-1.5 flex items-center overflow-x-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            <div class="flex items-center gap-1 min-w-max w-full">
                
                <!-- Tab: Semua -->
                <button type="button" 
                        @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'bg-[#6B4226] text-white shadow-xs font-black' : 'text-[#7A6C60] hover:text-[#2D241E] hover:bg-[#FAF8F5] font-bold'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span>Semua</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                          :class="activeTab === 'all' ? 'bg-white/20 text-white' : 'bg-[#FAF4ED] text-[#6B4226]'">
                        {{ $countAll }}
                    </span>
                </button>

                <!-- Tab: Diproses -->
                <button type="button" 
                        @click="activeTab = 'processing'"
                        :class="activeTab === 'processing' ? 'bg-[#6B4226] text-white shadow-xs font-black' : 'text-[#7A6C60] hover:text-[#2D241E] hover:bg-[#FAF8F5] font-bold'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span>Diproses</span>
                    @if($countProcessing > 0)
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="activeTab === 'processing' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'">
                            {{ $countProcessing }}
                        </span>
                    @endif
                </button>

                <!-- Tab: Dikirim -->
                <button type="button" 
                        @click="activeTab = 'shipped'"
                        :class="activeTab === 'shipped' ? 'bg-[#6B4226] text-white shadow-xs font-black' : 'text-[#7A6C60] hover:text-[#2D241E] hover:bg-[#FAF8F5] font-bold'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span>Dikirim</span>
                    @if($countShipped > 0)
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="activeTab === 'shipped' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800'">
                            {{ $countShipped }}
                        </span>
                    @endif
                </button>

                <!-- Tab: Selesai -->
                <button type="button" 
                        @click="activeTab = 'completed'"
                        :class="activeTab === 'completed' ? 'bg-[#6B4226] text-white shadow-xs font-black' : 'text-[#7A6C60] hover:text-[#2D241E] hover:bg-[#FAF8F5] font-bold'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span>Selesai</span>
                    @if($countCompleted > 0)
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="activeTab === 'completed' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'">
                            {{ $countCompleted }}
                        </span>
                    @endif
                </button>

                <!-- Tab: Belum Dinilai -->
                <button type="button" 
                        @click="activeTab = 'unreviewed'"
                        :class="activeTab === 'unreviewed' ? 'bg-[#6B4226] text-white shadow-xs font-black' : 'text-[#7A6C60] hover:text-[#2D241E] hover:bg-[#FAF8F5] font-bold'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span>Belum Dinilai</span>
                    @if($countUnreviewed > 0)
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              :class="activeTab === 'unreviewed' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-900 border border-amber-300'">
                            {{ $countUnreviewed }}
                        </span>
                    @endif
                </button>

                <!-- Tab: Dibatalkan -->
                <button type="button" 
                        @click="activeTab = 'cancelled'"
                        :class="activeTab === 'cancelled' ? 'bg-[#6B4226] text-white shadow-xs font-black' : 'text-[#7A6C60] hover:text-[#2D241E] hover:bg-[#FAF8F5] font-bold'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span>Dibatalkan</span>
                    @if($countCancelled > 0)
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="activeTab === 'cancelled' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800'">
                            {{ $countCancelled }}
                        </span>
                    @endif
                </button>

                <!-- Tab: Pengembalian Barang -->
                <button type="button" 
                        @click="activeTab = 'returned'"
                        :class="activeTab === 'returned' ? 'bg-[#6B4226] text-white shadow-xs font-black' : 'text-[#7A6C60] hover:text-[#2D241E] hover:bg-[#FAF8F5] font-bold'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span>Pengembalian Barang</span>
                    @if($countReturned > 0)
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="activeTab === 'returned' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800'">
                            {{ $countReturned }}
                        </span>
                    @endif
                </button>

            </div>
        </div>

        @if($orders->isEmpty())
            <!-- GLOBAL EMPTY STATE -->
            <div class="bg-white rounded-3xl p-10 sm:p-14 text-center border border-[#EAE1D7] shadow-xs max-w-md mx-auto space-y-4">
                <div class="w-18 h-18 rounded-3xl bg-gradient-to-tr from-[#FAF4ED] to-[#F5EAE0] border border-[#EAE1D7] text-[#6B4226] flex items-center justify-center text-4xl mx-auto shadow-inner">
                    🛍️
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-black text-[#2D241E]">Belum Ada Pesanan</h3>
                    <p class="text-xs text-[#7A6C60] leading-relaxed">
                        Kamu belum pernah melakukan transaksi di NusantaraMart.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('home') }}" class="px-6 py-3 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl shadow-xs transition inline-block">
                        Mulai Belanja Sekarang
                    </a>
                </div>
            </div>
        @else
            <!-- TAB SPECIFIC EMPTY STATES -->
            <div x-show="activeTab === 'processing' && {{ $countProcessing }} === 0" x-cloak
                 class="bg-white rounded-3xl p-10 text-center border border-[#EAE1D7] shadow-xs space-y-2">
                <div class="text-4xl">⏳</div>
                <h4 class="font-bold text-sm text-[#2D241E]">Tidak Ada Pesanan yang Sedang Diproses</h4>
                <p class="text-xs text-[#7A6C60]">Pesanan kamu yang baru dibayar atau sedang diproses akan tampil di sini.</p>
            </div>

            <div x-show="activeTab === 'shipped' && {{ $countShipped }} === 0" x-cloak
                 class="bg-white rounded-3xl p-10 text-center border border-[#EAE1D7] shadow-xs space-y-2">
                <div class="text-4xl">🚚</div>
                <h4 class="font-bold text-sm text-[#2D241E]">Tidak Ada Pesanan yang Sedang Dikirim</h4>
                <p class="text-xs text-[#7A6C60]">Pesanan fisik yang sedang dalam perjalanan oleh kurir akan muncul di sini.</p>
            </div>

            <div x-show="activeTab === 'completed' && {{ $countCompleted }} === 0" x-cloak
                 class="bg-white rounded-3xl p-10 text-center border border-[#EAE1D7] shadow-xs space-y-2">
                <div class="text-4xl">📦</div>
                <h4 class="font-bold text-sm text-[#2D241E]">Belum Ada Pesanan Selesai</h4>
                <p class="text-xs text-[#7A6C60]">Riwayat transaksi yang sudah selesai & lunas akan tersimpan di sini.</p>
            </div>

            <div x-show="activeTab === 'unreviewed' && {{ $countUnreviewed }} === 0" x-cloak
                 class="bg-white rounded-3xl p-10 text-center border border-[#EAE1D7] shadow-xs space-y-2">
                <div class="text-4xl">⭐</div>
                <h4 class="font-bold text-sm text-[#2D241E]">Semua Produk Telah Dinilai</h4>
                <p class="text-xs text-[#7A6C60]">Bagus sekali! Kamu sudah memberikan ulasan untuk seluruh pesanan yang telah selesai.</p>
            </div>

            <div x-show="activeTab === 'cancelled' && {{ $countCancelled }} === 0" x-cloak
                 class="bg-white rounded-3xl p-10 text-center border border-[#EAE1D7] shadow-xs space-y-2">
                <div class="text-4xl">✕</div>
                <h4 class="font-bold text-sm text-[#2D241E]">Tidak Ada Pesanan yang Dibatalkan</h4>
                <p class="text-xs text-[#7A6C60]">Daftar transaksi yang dibatalkan oleh pembeli atau penjual akan ada di sini.</p>
            </div>

            <div x-show="activeTab === 'returned' && {{ $countReturned }} === 0" x-cloak
                 class="bg-white rounded-3xl p-10 text-center border border-[#EAE1D7] shadow-xs space-y-2">
                <div class="text-4xl">↩️</div>
                <h4 class="font-bold text-sm text-[#2D241E]">Tidak Ada Pengembalian Barang</h4>
                <p class="text-xs text-[#7A6C60]">Pengajuan komplain retur atau pengembalian dana akan tercatat di sini.</p>
            </div>

            <!-- 3. ORDER CARDS LIST (FILTERED BY TAB) -->
            <div class="space-y-4">
                @foreach($orders as $order)
                    @php
                        $isPpob = str_contains($order->order_code, 'PPOB');
                        $isPaid = $order->payment_status === 'paid';
                    @endphp
                    <div x-show="matchesTab('{{ $order->status }}', '{{ $order->return_status }}', {{ $order->hasUnreviewedItems() ? 'true' : 'false' }})"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-xs hover:border-[#6B4226]/40 hover:shadow-md transition-all space-y-4">
                        
                        <!-- Header Card: Code, Category, Badges -->
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-4 border-b border-[#F2EAE0] gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl {{ $isPpob ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-[#FAF4ED] text-[#6B4226] border-[#EAE1D7]' }} flex items-center justify-center text-xl font-bold border shadow-2xs shrink-0">
                                    {{ $isPpob ? '⚡' : '🛍️' }}
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-sm sm:text-base font-black text-[#2D241E]">{{ $order->order_code }}</span>
                                        
                                        <!-- Copy Code Button -->
                                        <button type="button" 
                                                @click="copyToClipboard('{{ $order->order_code }}')"
                                                class="text-[10px] font-bold text-[#8A7C70] hover:text-[#6B4226] bg-[#FAF8F5] hover:bg-[#FAF4ED] px-2 py-0.5 rounded-md border border-[#EAE1D7] transition flex items-center gap-1 cursor-pointer"
                                                title="Salin Kode Pesanan">
                                            <span x-text="copiedCode === '{{ $order->order_code }}' ? '✓ Tersalin' : '📋 Salin'"></span>
                                        </button>

                                        @if($isPpob)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                                Tagihan Digital
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7]">
                                                Produk Fisik
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-[#8A7C70] flex items-center gap-1.5 mt-0.5">
                                        <span>📅 {{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                                        <span>•</span>
                                        <span>Metode: <strong class="text-[#2D241E] uppercase">{{ str_replace('_', ' ', $order->payment_method ?? 'QRIS') }}</strong></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Status Pills -->
                            <div class="flex items-center gap-2 self-start sm:self-center">
                                @php
                                    $statusLabel = match(strtolower($order->status)) {
                                        'completed' => 'SELESAI',
                                        'processing', 'pending', 'confirmed' => 'DIPROSES',
                                        'shipped' => 'DIKIRIM',
                                        'delivered' => 'SAMPAI DI TUJUAN',
                                        'cancelled', 'canceled' => 'DIBATALKAN',
                                        'returned', 'refunded' => 'DIKEMBALIKAN',
                                        default => strtoupper($order->status),
                                    };
                                    $statusColor = match(strtolower($order->status)) {
                                        'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'shipped' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        'delivered' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                        'cancelled', 'canceled' => 'bg-rose-50 text-rose-800 border-rose-200',
                                        'returned', 'refunded' => 'bg-purple-50 text-purple-800 border-purple-200',
                                        default => 'bg-[#FAF4ED] text-[#6B4226] border-[#6B4226]/20',
                                    };
                                    $dotColor = match(strtolower($order->status)) {
                                        'completed' => 'bg-emerald-500',
                                        'shipped' => 'bg-blue-500',
                                        'delivered' => 'bg-indigo-500',
                                        'cancelled', 'canceled' => 'bg-rose-500',
                                        'returned', 'refunded' => 'bg-purple-500',
                                        default => 'bg-amber-500',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold tracking-wide border {{ $statusColor }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                    <span>{{ $statusLabel }}</span>
                                </span>

                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold tracking-wide {{ $isPaid ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($order->payment_method === 'cod' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-rose-50 text-rose-800 border border-rose-200') }}">
                                    <span>{{ $isPaid ? '✓ LUNAS' : ($order->payment_method === 'cod' ? '💵 BAYAR DI TEMPAT' : 'MENUNGGU') }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Delivered Status Alert Banner -->
                        @if(strtolower($order->status) === 'delivered')
                            <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-950 text-xs space-y-1.5 shadow-2xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-black text-indigo-950">
                                    <span class="flex items-center gap-1.5">
                                        <span class="text-base">📍</span>
                                        <span>Pesanan Telah Sampai di Alamat Tujuan!</span>
                                    </span>
                                    @if($order->delivered_at)
                                        <span class="text-[10px] bg-indigo-100 text-indigo-900 px-2 py-0.5 rounded-full border border-indigo-300 font-bold self-start sm:self-auto">
                                            Batas Otomatis Selesai: {{ $order->delivered_at->addDays(7)->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-indigo-800 leading-relaxed">
                                    Silakan periksa kondisi dan kelengkapan barang. Jika barang sudah sesuai, klik tombol <strong>"Pesanan Selesai"</strong>. Jika terdapat kendala, Anda dapat mengajukan <strong>Pengembalian Barang</strong> sebelum batas waktu otomatis berakhir pada <strong>{{ $order->delivered_at ? $order->delivered_at->addDays(7)->format('d M Y, H:i') : '' }} WIB</strong>.
                                </p>
                            </div>
                        @endif

                        <!-- Return Status Alert Banners -->
                        @if($order->return_status === 'requested')
                            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-300 text-rose-950 text-xs space-y-1.5 shadow-2xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-black text-rose-950">
                                    <span class="flex items-center gap-1.5">
                                        <span class="animate-pulse">⏳</span>
                                        <span>Pengajuan Pengembalian Barang Sedang Ditinjau Penjual</span>
                                    </span>
                                    @if($order->return_requested_at)
                                        <span class="text-[10px] bg-rose-100 text-rose-900 px-2 py-0.5 rounded-full border border-rose-300 font-bold self-start sm:self-auto">
                                            Diajukan: {{ $order->return_requested_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-rose-800">
                                    <strong>Alasan:</strong> {{ $order->return_reason }} &bull; <em>"{{ $order->return_description }}"</em>
                                </p>
                                @if($order->return_proof_image)
                                    <div class="pt-0.5">
                                        <a href="{{ asset('storage/' . $order->return_proof_image) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 hover:underline bg-white px-2.5 py-0.5 rounded-md border border-rose-200">
                                            <span>📷 Bukti Foto Terlampir</span>
                                            <span>↗</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif($order->return_status === 'approved')
                            <div class="p-3 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900 text-xs space-y-1 shadow-2xs">
                                <div class="flex items-center gap-1.5 font-bold text-purple-950">
                                    <span>✓</span>
                                    <span>Pengajuan Pengembalian Barang Telah Disetujui Penjual</span>
                                </div>
                                @if($order->return_response_note)
                                    <p class="text-[11px] text-purple-800">
                                        <strong>Catatan Penjual:</strong> "{{ $order->return_response_note }}"
                                    </p>
                                @endif
                            </div>
                        @elseif($order->return_status === 'rejected')
                            <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1 shadow-2xs">
                                <div class="flex items-center gap-1.5 font-bold text-amber-950">
                                    <span>⚠️</span>
                                    <span>Pengajuan Pengembalian Ditolak Penjual</span>
                                </div>
                                @if($order->return_response_note)
                                    <p class="text-[11px] text-amber-800">
                                        <strong>Alasan Penolakan:</strong> "{{ $order->return_response_note }}"
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Cancellation Status Alert Banners -->
                        @if($order->cancellation_status === 'requested')
                            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs space-y-1.5 shadow-2xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 font-black text-amber-950">
                                    <span class="flex items-center gap-1.5">
                                        <span class="animate-pulse">⏳</span>
                                        <span>Pengajuan Pembatalan Sedang Ditinjau Penjual</span>
                                    </span>
                                    @if($order->cancellation_requested_at)
                                        <span class="text-[10px] bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full border border-amber-300 font-bold self-start sm:self-auto">
                                            Batas Respon: {{ $order->cancellation_requested_at->addDays(3)->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-amber-800">
                                    <strong>Alasan Pembatalan:</strong> "{{ $order->cancellation_reason }}"
                                </p>
                                <p class="text-[10px] text-amber-700 leading-relaxed">
                                    Pesanan ini dikunci dari pengiriman kurir. Jika penjual tidak merespons hingga <strong>{{ $order->cancellation_requested_at ? $order->cancellation_requested_at->addDays(3)->format('d M Y, H:i') : '' }} WIB</strong>, pesanan akan dibatalkan otomatis oleh sistem.
                                </p>
                            </div>
                        @elseif($order->cancellation_status === 'rejected')
                            <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs space-y-1 shadow-2xs">
                                <div class="flex items-center gap-1.5 font-bold text-rose-950">
                                    <span>⚠️</span>
                                    <span>Pengajuan Pembatalan Ditolak oleh Penjual</span>
                                </div>
                                @if($order->cancellation_response_note)
                                    <p class="text-[11px] text-rose-800">
                                        <strong>Alasan Penolakan:</strong> "{{ $order->cancellation_response_note }}"
                                    </p>
                                @endif
                                <p class="text-[10px] text-rose-700">Penjual tetap memproses pesanan ini untuk dikirim ke alamat kamu.</p>
                            </div>
                        @elseif($order->cancellation_status === 'approved' || in_array(strtolower($order->status), ['cancelled', 'canceled']))
                            @if($order->cancellation_response_note)
                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 text-xs space-y-0.5 shadow-2xs">
                                    <span class="font-bold text-slate-900 block">Keterangan Pembatalan:</span>
                                    <p class="text-[11px] text-slate-600">{{ $order->cancellation_response_note }}</p>
                                </div>
                            @endif
                        @endif

                        <!-- PLN Token Highlight Card (If PLN Token Order) -->
                        @if($order->customer_notes && str_contains($order->customer_notes, 'Token PLN'))
                            @php
                                preg_match('/Token PLN:\s*([0-9\-]+)/', $order->customer_notes, $matches);
                                $tokenNumber = $matches[1] ?? null;
                            @endphp
                            <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-50 via-[#FFFDF8] to-amber-50 border border-amber-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1.5 text-xs font-black text-amber-900 uppercase tracking-wider">
                                        <span>🔑 Token Listrik PLN 16 Digit:</span>
                                    </div>
                                    @if($tokenNumber)
                                        <div class="text-base sm:text-lg font-mono font-black text-[#6B4226] tracking-wider select-all">
                                            {{ $tokenNumber }}
                                        </div>
                                    @else
                                        <span class="text-xs font-mono font-bold text-[#6B4226]">{{ $order->customer_notes }}</span>
                                    @endif
                                    <span class="text-[10px] text-amber-800 block">Ketikkan 16 digit angka di atas ke kWh meter PLN di rumah Anda lalu tekan Enter.</span>
                                </div>
                                @if($tokenNumber)
                                    <button type="button" 
                                            @click="copyToClipboard('{{ $tokenNumber }}')"
                                            class="px-3.5 py-2 bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 rounded-xl text-xs font-bold transition shadow-2xs flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                                        <span x-text="copiedCode === '{{ $tokenNumber }}' ? '✓ Tersalin!' : '📋 Salin Token'"></span>
                                    </button>
                                @endif
                            </div>
                        @endif

                        <!-- Shipping & Tracking Details (If Shipped) -->
                        @if($order->shipping_courier || $order->tracking_number)
                            <div class="p-3.5 rounded-2xl bg-sky-50 border border-sky-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-2xs">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-base">🚚</span>
                                    <div>
                                        <span class="font-bold text-sky-950 block">Dikirim dengan: {{ $order->shipping_courier ?: 'Ekspedisi Pengiriman' }}</span>
                                        @if($order->tracking_number)
                                            <span class="text-[11px] text-sky-800 font-mono">No. Resi: <strong>{{ $order->tracking_number }}</strong></span>
                                        @endif
                                    </div>
                                </div>
                                @if($order->tracking_number)
                                    <button type="button" 
                                            @click="copyToClipboard('{{ $order->tracking_number }}')"
                                            class="px-3 py-1.5 bg-white hover:bg-sky-100 text-sky-900 border border-sky-200 rounded-xl text-xs font-bold transition shadow-2xs flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                                        <span x-text="copiedCode === '{{ $order->tracking_number }}' ? '✓ Tersalin!' : '📋 Salin Resi'"></span>
                                    </button>
                                @endif
                            </div>
                        @endif

                        <!-- Items Preview List -->
                        <div class="space-y-2.5 bg-[#FAF8F5] p-3.5 rounded-2xl border border-[#EAE1D7]">
                            @foreach($order->items as $item)
                                @php
                                    $itemImg = $item->item_image_url;
                                    $isCompleted = $order->status === 'completed';
                                    $itemReview = $isCompleted && $item->product_id 
                                        ? $order->reviews->firstWhere('product_id', $item->product_id) 
                                        : null;
                                @endphp
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-xs gap-3 py-1 border-b border-[#F2EAE0] last:border-0">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-12 h-12 rounded-xl bg-white border border-[#EAE1D7] flex items-center justify-center text-sm shrink-0 overflow-hidden shadow-2xs">
                                            @if($itemImg)
                                                <img src="{{ $itemImg }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-xl">{{ $isPpob ? '📱' : '📦' }}</span>
                                            @endif
                                        </div>
                                        <div class="truncate">
                                            <span class="font-bold text-[#2D241E] truncate block text-xs sm:text-sm">{{ $item->product_name }}</span>
                                            <span class="text-[11px] text-[#8A7C70]">{{ $item->quantity }}x @ Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                                        <span class="font-bold text-sm text-[#2D241E]">{{ $item->formatted_subtotal }}</span>
                                        @if($isCompleted && $item->product)
                                            @if($itemReview)
                                                <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-[11px] font-bold flex items-center gap-1 shadow-2xs">
                                                    <span>⭐</span>
                                                    <span>Dinilai ({{ $itemReview->rating }}/5)</span>
                                                </span>
                                            @else
                                                <button type="button" 
                                                        @click="openReviewModal({{ $order->id }}, '{{ $order->order_code }}', {{ $item->product->id }}, '{{ addslashes($item->product->name) }}', '{{ $itemImg ? addslashes($itemImg) : '' }}', '{{ addslashes($item->variant_name ?? '') }}', '{{ route('product.reviews.store', $item->product->slug) }}')"
                                                        class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[11px] font-bold transition shadow-2xs flex items-center gap-1 active:scale-95 cursor-pointer">
                                                    <span>⭐</span>
                                                    <span>Beri Nilai</span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Card Footer: Total & Actions -->
                        <div class="pt-3 border-t border-[#F2EAE0] flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                            <div class="flex items-baseline gap-2">
                                <span class="text-xs text-[#7A6C60]">Total Pembayaran:</span>
                                <span class="text-lg sm:text-xl font-black text-[#6B4226]">{{ $order->formatted_grand_total }}</span>
                            </div>

                            <div class="flex items-center gap-2 self-stretch sm:self-auto justify-end flex-wrap">
                                @if($order->canBeCancelledByBuyer())
                                    <button type="button" 
                                            @click="openCancelModal({{ $order->id }}, '{{ $order->order_code }}', '{{ route('orders.cancel', $order) }}')"
                                            class="px-3.5 py-2.5 bg-white hover:bg-rose-50 text-rose-700 hover:text-rose-800 border border-rose-200 hover:border-rose-300 text-xs font-bold rounded-xl transition text-center shadow-2xs flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                                        <span>✕ Batalkan Pesanan</span>
                                    </button>
                                @elseif($order->status === 'shipped')
                                    <span class="text-[11px] text-blue-700 bg-blue-50/80 px-3 py-1.5 rounded-xl border border-blue-200 font-bold flex items-center gap-1">
                                        <span>🚚 Sedang Dikirim</span>
                                    </span>
                                @elseif($order->status === 'delivered')
                                    @if($order->canBeReturnedByBuyer())
                                        <button type="button" 
                                                @click="openReturnModal({{ $order->id }}, '{{ $order->order_code }}', '{{ route('orders.return', $order) }}')"
                                                class="px-3.5 py-2.5 bg-white hover:bg-rose-50 text-rose-700 hover:text-rose-800 border border-rose-300 text-xs font-bold rounded-xl transition text-center shadow-2xs flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                                            <span>⚠️ Ajukan Pengembalian</span>
                                        </button>
                                    @endif

                                    @if($order->canBeConfirmedCompletedByBuyer())
                                        <form action="{{ route('orders.complete', $order) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('Apakah Anda yakin telah menerima pesanan ini dengan baik? Pesanan yang telah diselesaikan tidak dapat diajukan pengembalian barang lagi.')"
                                                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl transition text-center shadow-xs flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                                                <span>✓ Pesanan Selesai</span>
                                            </button>
                                        </form>
                                    @endif
                                @elseif($order->status === 'completed' && $order->hasUnreviewedItems())
                                    @php
                                        $firstUnreviewed = $order->unreviewedItems()->first();
                                    @endphp
                                    @if($firstUnreviewed && $firstUnreviewed->product)
                                        <button type="button" 
                                                @click="openReviewModal({{ $order->id }}, '{{ $order->order_code }}', {{ $firstUnreviewed->product->id }}, '{{ addslashes($firstUnreviewed->product->name) }}', '{{ $firstUnreviewed->item_image_url ? addslashes($firstUnreviewed->item_image_url) : '' }}', '{{ addslashes($firstUnreviewed->variant_name ?? '') }}', '{{ route('product.reviews.store', $firstUnreviewed->product->slug) }}')"
                                                class="px-3.5 py-2.5 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-xs font-bold rounded-xl transition text-center shadow-xs flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer">
                                            <span>⭐ Beri Nilai</span>
                                        </button>
                                    @endif
                                @endif

                                <a href="{{ route('order.detail', ['order_code' => $order->order_code]) }}" 
                                   class="px-4 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition text-center shadow-xs flex items-center justify-center gap-1.5 active:scale-95">
                                   <span>Lihat Detail</span>
                                   <span>→</span>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <!-- 4. MODAL AJUKAN PEMBATALAN PESANAN -->
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
                        <p class="text-xs text-[#8A7C70] font-mono" x-text="'Kode: #' + cancelOrderCode"></p>
                    </div>
                </div>
                <button type="button" 
                        @click="closeCancelModal()" 
                        class="w-8 h-8 rounded-full bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#8A7C70] hover:text-[#2D241E] flex items-center justify-center text-base font-bold transition cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- Form with scrollable body and pinned footer -->
            <form :action="cancelActionUrl" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden m-0">
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

    <!-- 5. MODAL AJUKAN PENGEMBALIAN BARANG (RETUR / REFUND) -->
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
                        <p class="text-xs text-[#8A7C70] font-mono" x-text="'Kode Pesanan: #' + returnOrderCode"></p>
                    </div>
                </div>
                <button type="button" 
                        @click="closeReturnModal()" 
                        class="w-8 h-8 rounded-full bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#8A7C70] hover:text-[#2D241E] flex items-center justify-center text-base font-bold transition cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- Form with scrollable body and pinned footer -->
            <form :action="returnActionUrl" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden m-0">
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

    <!-- 6. MODAL BERI PENILAIAN / ULASAN PRODUK -->
    <div x-show="reviewModal" 
         x-cloak 
         @keydown.window.escape="closeReviewModal()"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 overflow-hidden"
         aria-labelledby="modal-review-title" role="dialog" aria-modal="true">
        
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
                        <h3 id="modal-review-title" class="font-bold text-sm sm:text-base text-[#2D241E]">Nilai Produk</h3>
                        <p class="text-xs text-[#8A7C70] font-mono" x-text="'Pesanan #' + reviewOrderCode"></p>
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
</div>

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
