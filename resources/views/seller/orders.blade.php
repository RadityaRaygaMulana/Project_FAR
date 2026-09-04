@extends('seller.layout')

@section('title', 'Pesanan Masuk Toko — ' . $store->name)

@section('content')
<div class="space-y-6" x-data="{ 
    copiedCode: null,
    copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        this.copiedCode = text;
        setTimeout(() => this.copiedCode = null, 2000);
    },
    shippingModalOpen: false,
    shippingOrder: null,
    shippingActionUrl: '',
    openShippingModal(order, actionUrl) {
        this.shippingOrder = order;
        this.shippingActionUrl = actionUrl;
        this.shippingModalOpen = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeShippingModal() {
        this.shippingModalOpen = false;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    },
    cancelModalOpen: false,
    cancelOrder: null,
    cancelActionUrl: '',
    openCancelModal(order, actionUrl) {
        this.cancelOrder = order;
        this.cancelActionUrl = actionUrl;
        this.cancelModalOpen = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeCancelModal() {
        this.cancelModalOpen = false;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    },
    respondCancelModal: false,
    respondCancelOrderId: null,
    respondCancelOrderCode: '',
    respondCancelActionUrl: '',
    respondCancelActionType: 'approve',
    respondCancelNote: '',
    openRespondCancelModal(id, code, url, action) {
        this.respondCancelOrderId = id;
        this.respondCancelOrderCode = code;
        this.respondCancelActionUrl = url;
        this.respondCancelActionType = action;
        this.respondCancelNote = '';
        this.respondCancelModal = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeRespondCancelModal() {
        this.respondCancelModal = false;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    },
    respondReturnModal: false,
    respondReturnOrderId: null,
    respondReturnOrderCode: '',
    respondReturnActionUrl: '',
    respondReturnActionType: 'approve',
    respondReturnNote: '',
    respondReturnProofImg: null,
    respondReturnReason: '',
    respondReturnDesc: '',
    openRespondReturnModal(id, code, url, action, proofImg = null, reason = '', desc = '') {
        this.respondReturnOrderId = id;
        this.respondReturnOrderCode = code;
        this.respondReturnActionUrl = url;
        this.respondReturnActionType = action;
        this.respondReturnNote = '';
        this.respondReturnProofImg = proofImg;
        this.respondReturnReason = reason;
        this.respondReturnDesc = desc;
        this.respondReturnModal = true;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    },
    closeRespondReturnModal() {
        this.respondReturnModal = false;
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }
}">

    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <button type="button" 
                    onclick="window.smartNav ? window.smartNav.goBack('{{ route('seller.dashboard') }}') : window.location.href='{{ route('seller.dashboard') }}'" 
                    class="w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0 shadow-2xs group"
                    title="Kembali ke Dashboard Toko">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] tracking-tight flex items-center gap-2">
                    <span>Pesanan Masuk Toko</span>
                    <span>📑</span>
                </h1>
                <p class="text-xs text-[#8A7C70]">Kelola dan proses pengiriman pesanan pelanggan secara langsung & realtime</p>
            </div>
        </div>

        <!-- QUICK SUMMARY STATS -->
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <div class="bg-amber-50 text-amber-900 border border-amber-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs">
                <span>⏳ Perlu Diproses:</span>
                <span class="font-black text-sm">{{ $countPending }}</span>
            </div>
            <div class="bg-blue-50 text-blue-900 border border-blue-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs">
                <span>📦 Dikemas:</span>
                <span class="font-black text-sm">{{ $countProcessing }}</span>
            </div>
            <div class="bg-purple-50 text-purple-900 border border-purple-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs">
                <span>🚚 Dikirim:</span>
                <span class="font-black text-sm">{{ $countShipped }}</span>
            </div>
            <div class="bg-indigo-50 text-indigo-900 border border-indigo-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs">
                <span>📍 Sampai:</span>
                <span class="font-black text-sm">{{ $countDelivered }}</span>
            </div>
            <div class="bg-emerald-50 text-emerald-900 border border-emerald-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs">
                <span>✅ Selesai:</span>
                <span class="font-black text-sm">{{ $countCompleted }}</span>
            </div>
            @if($countCancellationRequests > 0)
                <div class="bg-amber-100 text-amber-950 border border-amber-300 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs animate-pulse">
                    <span>⚠️ Pengajuan Batal:</span>
                    <span class="font-black text-sm">{{ $countCancellationRequests }}</span>
                </div>
            @endif
            @if($countReturnRequests > 0)
                <div class="bg-rose-100 text-rose-950 border border-rose-300 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs animate-pulse">
                    <span>🚨 Pengajuan Retur:</span>
                    <span class="font-black text-sm">{{ $countReturnRequests }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- FILTER TABS & SEARCH BAR (SHOPEE / TOKOPEDIA SELLER CENTER STYLE) -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] shadow-xs p-4 space-y-4">
        
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            <!-- All -->
            <a href="{{ route('seller.orders', ['status' => 'all', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'all' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#2D241E]' }}">
                <span>Semua</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'all' ? 'bg-white/20 text-white' : 'bg-[#EAE1D7] text-[#2D241E]' }}">
                    {{ $countAll }}
                </span>
            </a>

            <!-- Pending / Need Action -->
            <a href="{{ route('seller.orders', ['status' => 'pending', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'pending' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#2D241E]' }}">
                <span>Perlu Diproses</span>
                @if($countPending > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'pending' ? 'bg-amber-400 text-amber-950' : 'bg-amber-100 text-amber-900' }}">
                        {{ $countPending }}
                    </span>
                @endif
            </a>

            <!-- Processing / Packaging -->
            <a href="{{ route('seller.orders', ['status' => 'processing', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'processing' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#2D241E]' }}">
                <span>Sedang Dikemas</span>
                @if($countProcessing > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'processing' ? 'bg-blue-400 text-blue-950' : 'bg-blue-100 text-blue-900' }}">
                        {{ $countProcessing }}
                    </span>
                @endif
            </a>

            <!-- Shipped -->
            <a href="{{ route('seller.orders', ['status' => 'shipped', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'shipped' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#2D241E]' }}">
                <span>Dalam Pengiriman</span>
                @if($countShipped > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'shipped' ? 'bg-purple-400 text-purple-950' : 'bg-purple-100 text-purple-900' }}">
                        {{ $countShipped }}
                    </span>
                @endif
            </a>

            <!-- Delivered (Sampai di Tujuan) -->
            <a href="{{ route('seller.orders', ['status' => 'delivered', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'delivered' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#2D241E]' }}">
                <span>Sampai di Tujuan</span>
                @if($countDelivered > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'delivered' ? 'bg-indigo-400 text-indigo-950' : 'bg-indigo-100 text-indigo-900' }}">
                        {{ $countDelivered }}
                    </span>
                @endif
            </a>

            <!-- Completed -->
            <a href="{{ route('seller.orders', ['status' => 'completed', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'completed' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#2D241E]' }}">
                <span>Selesai</span>
                @if($countCompleted > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'completed' ? 'bg-emerald-400 text-emerald-950' : 'bg-emerald-100 text-emerald-900' }}">
                        {{ $countCompleted }}
                    </span>
                @endif
            </a>

            <!-- Cancelled -->
            <a href="{{ route('seller.orders', ['status' => 'cancelled', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'cancelled' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0] hover:text-[#2D241E]' }}">
                <span>Dibatalkan</span>
                @if($countCancelled > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'cancelled' ? 'bg-rose-400 text-rose-950' : 'bg-rose-100 text-rose-900' }}">
                        {{ $countCancelled }}
                    </span>
                @endif
            </a>

            <!-- Cancellation Requests -->
            <a href="{{ route('seller.orders', ['status' => 'cancellation_requests', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'cancellation_requests' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-amber-50 text-amber-900 hover:bg-amber-100' }}">
                <span>Pengajuan Batal</span>
                @if($countCancellationRequests > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'cancellation_requests' ? 'bg-white text-amber-900' : 'bg-amber-500 text-white animate-pulse' }}">
                        {{ $countCancellationRequests }}
                    </span>
                @endif
            </a>

            <!-- Return Requests (Pengajuan Pengembalian) -->
            <a href="{{ route('seller.orders', ['status' => 'return_requests', 'q' => $search]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 shrink-0 {{ $status === 'return_requests' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 text-rose-900 hover:bg-rose-100' }}">
                <span>Pengajuan Retur</span>
                @if($countReturnRequests > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $status === 'return_requests' ? 'bg-white text-rose-900' : 'bg-rose-500 text-white animate-pulse' }}">
                        {{ $countReturnRequests }}
                    </span>
                @endif
            </a>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('seller.orders') }}" class="flex items-center gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-[#8A7C70] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" 
                       name="q" 
                       value="{{ $search }}" 
                       placeholder="Cari berdasarkan Kode Pesanan (#SNK-...), Nama Pelanggan, atau No. Telepon..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] focus:border-[#6B4226] rounded-2xl text-xs text-[#2D241E] placeholder-[#8A7C70] outline-none transition focus:ring-2 focus:ring-[#6B4226]/20">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-2xl transition shadow-2xs cursor-pointer shrink-0">
                Cari Pesanan
            </button>
            @if($search !== '')
                <a href="{{ route('seller.orders', ['status' => $status]) }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-2xl transition shrink-0">
                    Reset
                </a>
            @endif
        </form>

    </div>

    <!-- ORDERS LIST -->
    @if($orders->count() === 0)
        <!-- EMPTY STATE -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-12 text-center space-y-4 shadow-xs">
            <div class="w-16 h-16 rounded-full bg-[#FAF4ED] text-[#6B4226] flex items-center justify-center text-3xl mx-auto shadow-inner">
                📭
            </div>
            <div>
                <h3 class="font-bold text-base text-[#2D241E]">Tidak Ada Pesanan Ditemukan</h3>
                <p class="text-xs text-[#7A6C60] max-w-md mx-auto mt-1">
                    @if($search !== '')
                        Tidak ada pesanan yang sesuai dengan kata kunci "<strong>{{ $search }}</strong>". Coba kata kunci lain.
                    @else
                        Belum ada pesanan yang masuk pada kategori ini. Ketika pelanggan melakukan pemesanan, daftarnya akan langsung tampil di sini.
                    @endif
                </p>
            </div>
            @if($search !== '')
                <a href="{{ route('seller.orders', ['status' => $status]) }}" class="px-5 py-2.5 bg-[#6B4226] text-white font-bold rounded-xl text-xs hover:bg-[#54321B] transition inline-block">
                    Hapus Pencarian
                </a>
            @else
                <a href="{{ route('seller.products.index') }}" class="px-5 py-2.5 bg-[#FAF8F5] text-[#6B4226] border border-[#EAE1D7] font-bold rounded-xl text-xs hover:bg-[#F2EAE0] transition inline-block">
                    Kelola Produk Toko
                </a>
            @endif
        </div>
    @else
        <!-- ORDER CARDS STACK -->
        <div class="space-y-4">
            @foreach($orders as $order)
                @php
                    $orderStatus = strtolower($order->status);
                    $isPending = in_array($orderStatus, ['pending', 'confirmed']);
                    $isProcessing = $orderStatus === 'processing';
                    $isShipped = $orderStatus === 'shipped';
                    $isDelivered = $orderStatus === 'delivered';
                    $isCompleted = $orderStatus === 'completed';
                    $isCancelled = in_array($orderStatus, ['cancelled', 'canceled']);
                    $isReturned = in_array($orderStatus, ['returned', 'refunded']) || $order->return_status === 'approved';

                    // Calculate items subtotal for this seller
                    $sellerSubtotal = $order->items->sum('subtotal');

                    // WhatsApp URL format
                    $cleanPhone = preg_replace('/[^0-9]/', '', $order->customer_phone ?? '');
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    $waMsg = urlencode("Halo Kak {$order->customer_name}, terima kasih telah berbelanja di {$store->name}. Terkait pesanan #{$order->order_code}: ");
                    $waUrl = $cleanPhone ? "https://wa.me/{$cleanPhone}?text={$waMsg}" : null;
                @endphp

                <div class="bg-white rounded-3xl border border-[#EAE1D7] shadow-xs overflow-hidden transition hover:shadow-sm">
                    
                    <!-- ORDER CARD HEADER -->
                    <div class="p-4 sm:px-6 bg-[#FAF8F5] border-b border-[#EAE1D7] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        
                        <!-- Left: Customer, Order Code & Date -->
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <span class="font-mono font-bold text-sm text-[#6B4226] bg-white px-2.5 py-1 rounded-lg border border-[#EAE1D7] flex items-center gap-1.5 shadow-2xs">
                                <span>#{{ $order->order_code }}</span>
                                <button type="button" 
                                        @click="copyToClipboard('{{ $order->order_code }}')" 
                                        class="text-[#8A7C70] hover:text-[#6B4226] transition cursor-pointer"
                                        title="Salin No. Pesanan">
                                    <span x-text="copiedCode === '{{ $order->order_code }}' ? '✓' : '📋'"></span>
                                </button>
                            </span>

                            <span class="text-[#8A7C70] hidden sm:inline">•</span>

                            <span class="text-[#8A7C70] flex items-center gap-1">
                                <span>📅</span>
                                <span>{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                            </span>

                            <span class="text-[#8A7C70] hidden sm:inline">•</span>

                            <!-- Payment Badge -->
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $order->payment_status === 'paid' ? 'Lunas (' . strtoupper($order->payment_method ?? 'QRIS') . ')' : ($order->payment_method === 'cod' ? 'Bayar di Tempat (COD)' : 'Menunggu Bayar (' . strtoupper($order->payment_method ?? 'COD') . ')') }}
                            </span>
                        </div>

                        <!-- Right: Status Badge -->
                        <div>
                            @if($isPending)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Perlu Diproses (Pending)</span>
                                </span>
                            @elseif($isProcessing)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                    <span>Sedang Dikemas</span>
                                </span>
                            @elseif($isShipped)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-800 border border-purple-200 flex items-center gap-1.5">
                                    <span>🚚</span>
                                    <span>Dalam Pengiriman</span>
                                </span>
                            @elseif($isDelivered)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 flex items-center gap-1.5">
                                    <span>📍</span>
                                    <span>Sampai di Tujuan</span>
                                </span>
                            @elseif($isCompleted)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                                    <span>✓</span>
                                    <span>Pesanan Selesai</span>
                                </span>
                            @elseif($isReturned)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-800 border border-purple-200 flex items-center gap-1.5">
                                    <span>↩</span>
                                    <span>Barang Dikembalikan</span>
                                </span>
                            @elseif($isCancelled)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 flex items-center gap-1.5">
                                    <span>✕</span>
                                    <span>Dibatalkan</span>
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ ucfirst($order->status) }}
                                </span>
                            @endif
                        </div>

                    </div>

                    <!-- Cancellation Alert Banner (If Buyer Requested Cancellation) -->
                    @if($order->cancellation_status === 'requested')
                        <div class="mx-4 sm:mx-6 mt-4 p-4 rounded-2xl bg-amber-50 border-2 border-amber-400 text-amber-950 text-xs shadow-xs space-y-2.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-black">
                                <div class="flex items-center gap-2 text-sm text-amber-900">
                                    <span class="text-base animate-bounce">⚠️</span>
                                    <span>PEMBELI MENGAJUKAN PEMBATALAN PESANAN!</span>
                                </div>
                                @if($order->cancellation_requested_at)
                                    <span class="px-2.5 py-1 rounded-full bg-amber-200/80 text-amber-900 text-[11px] font-bold border border-amber-300 self-start sm:self-auto">
                                        Batas Waktu Respon: {{ $order->cancellation_requested_at->addDays(3)->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                            <div class="bg-white/90 p-3 rounded-xl border border-amber-200 space-y-1">
                                <span class="text-[11px] font-bold text-amber-900 block">Alasan Pembatalan dari Pembeli:</span>
                                <p class="text-xs text-[#2D241E] italic">"{{ $order->cancellation_reason }}"</p>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1 text-[11px] text-amber-800">
                                <span class="leading-relaxed">
                                    🔒 <strong>Pengiriman Dikunci:</strong> Anda wajib menyetujui atau menolak pengajuan ini terlebih dahulu. Jika dibiarkan hingga <strong>{{ $order->cancellation_requested_at ? $order->cancellation_requested_at->addDays(3)->format('d M Y, H:i') : '' }} WIB</strong>, pesanan akan dibatalkan otomatis oleh sistem.
                                </span>
                            </div>
                            <div class="flex items-center gap-2.5 pt-1 justify-end">
                                <button type="button" 
                                        @click="openRespondCancelModal({{ $order->id }}, '{{ $order->order_code }}', '{{ route('seller.orders.cancellation', $order->id) }}', 'reject')"
                                        class="px-4 py-2 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 rounded-xl font-bold text-xs shadow-2xs transition cursor-pointer">
                                    ✕ Tolak Pembatalan
                                </button>
                                <button type="button" 
                                        @click="openRespondCancelModal({{ $order->id }}, '{{ $order->order_code }}', '{{ route('seller.orders.cancellation', $order->id) }}', 'approve')"
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-2xs transition cursor-pointer flex items-center gap-1.5">
                                    <span>✓ Setujui Pembatalan</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Return Request Alert Banner (If Buyer Requested Return/Refund) -->
                    @if($order->return_status === 'requested')
                        <div class="mx-4 sm:mx-6 mt-4 p-4 rounded-2xl bg-rose-50 border-2 border-rose-400 text-rose-950 text-xs shadow-xs space-y-2.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-black">
                                <div class="flex items-center gap-2 text-sm text-rose-900">
                                    <span class="text-base animate-bounce">🚨</span>
                                    <span>PEMBELI MENGAJUKAN PENGEMBALIAN BARANG (KOMPLAIN/RETUR)!</span>
                                </div>
                                @if($order->return_requested_at)
                                    <span class="px-2.5 py-1 rounded-full bg-rose-200/80 text-rose-900 text-[11px] font-bold border border-rose-300 self-start sm:self-auto">
                                        Diajukan: {{ $order->return_requested_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                            <div class="bg-white/95 p-3 rounded-xl border border-rose-200 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-rose-900 block">Alasan Pengembalian:</span>
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">{{ $order->return_reason }}</span>
                                </div>
                                <p class="text-xs text-[#2D241E] italic">"{{ $order->return_description }}"</p>
                                @if($order->return_proof_image)
                                    <div class="pt-1 flex items-center gap-2">
                                        <span class="text-[11px] font-bold text-rose-900">Foto Bukti Barang:</span>
                                        <a href="{{ asset('storage/' . $order->return_proof_image) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 hover:underline bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                                            <span>📷 Lihat Foto Bukti</span>
                                            <span>↗</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-2.5 pt-1 justify-end">
                                <button type="button" 
                                        @click="openRespondReturnModal({{ $order->id }}, '{{ $order->order_code }}', '{{ route('seller.orders.return.respond', $order->id) }}', 'reject', '{{ $order->return_proof_image ? asset('storage/' . $order->return_proof_image) : '' }}', '{{ addslashes($order->return_reason ?? '') }}', '{{ addslashes($order->return_description ?? '') }}')"
                                        class="px-4 py-2 bg-white hover:bg-rose-100 text-rose-800 border border-rose-300 rounded-xl font-bold text-xs shadow-2xs transition cursor-pointer">
                                    ✕ Tolak Pengembalian
                                </button>
                                <button type="button" 
                                        @click="openRespondReturnModal({{ $order->id }}, '{{ $order->order_code }}', '{{ route('seller.orders.return.respond', $order->id) }}', 'approve', '{{ $order->return_proof_image ? asset('storage/' . $order->return_proof_image) : '' }}', '{{ addslashes($order->return_reason ?? '') }}', '{{ addslashes($order->return_description ?? '') }}')"
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-2xs transition cursor-pointer flex items-center gap-1.5">
                                    <span>✓ Setujui Pengembalian (Restock)</span>
                                </button>
                            </div>
                        </div>
                    @elseif($order->return_status === 'approved')
                        <div class="mx-4 sm:mx-6 mt-4 p-3 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900 text-xs space-y-1 shadow-2xs">
                            <div class="flex items-center gap-1.5 font-bold">
                                <span>↩</span>
                                <span>Pengembalian Barang Telah Disetujui</span>
                            </div>
                            <p class="text-[11px] text-purple-800">{{ $order->return_response_note ?: 'Stok produk telah otomatis dikembalikan ke etalase toko.' }}</p>
                        </div>
                    @elseif($order->return_status === 'rejected')
                        <div class="mx-4 sm:mx-6 mt-4 p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1 shadow-2xs">
                            <div class="flex items-center gap-1.5 font-bold">
                                <span>⚠️</span>
                                <span>Pengajuan Pengembalian Ditolak Penjual</span>
                            </div>
                            @if($order->return_response_note)
                                <p class="text-[11px] text-amber-800">Alasan Penolakan: "{{ $order->return_response_note }}"</p>
                            @endif
                        </div>
                    @endif

                    <!-- ORDER CARD BODY -->
                    <div class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
                        
                        <!-- Col 1: Ordered Products (7 cols) -->
                        <div class="lg:col-span-7 space-y-3">
                            <h4 class="text-[11px] font-black uppercase tracking-wider text-[#8A7C70] flex items-center gap-1.5">
                                <span>🛍️</span>
                                <span>Produk yang Dipesan ({{ $order->items->count() }} Produk)</span>
                            </h4>

                            <div class="divide-y divide-[#F2EAE0] border border-[#F2EAE0] rounded-2xl overflow-hidden bg-white">
                                @foreach($order->items as $item)
                                    @php
                                        $productImg = $item->item_image_url;
                                    @endphp
                                    <div class="p-3.5 flex items-start gap-3.5">
                                        <!-- Product Image -->
                                        <div class="w-14 h-14 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] shrink-0 overflow-hidden flex items-center justify-center">
                                            @if($productImg)
                                                <img src="{{ $productImg }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-2xl">📦</span>
                                            @endif
                                        </div>

                                        <!-- Product Info -->
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-xs font-bold text-[#2D241E] line-clamp-2">
                                                {{ $item->product_name }}
                                            </h5>
                                            <div class="mt-1 flex items-center gap-3 text-xs">
                                                <span class="text-[#6B4226] font-bold">
                                                    @ Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                </span>
                                                <span class="text-[#8A7C70]">x {{ $item->quantity }} pcs</span>
                                            </div>
                                        </div>

                                        <!-- Item Subtotal -->
                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-black text-[#2D241E] block">
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Tracking & Shipping Info (If already shipped) -->
                            @if($order->shipping_courier || $order->tracking_number)
                                <div class="bg-[#FAF4ED] border border-[#EAE1D7] p-3 rounded-2xl flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🚚</span>
                                        <div>
                                            <span class="font-bold text-[#2D241E] block">
                                                Kurir: {{ $order->shipping_courier ?: 'Ekspedisi Reguler' }}
                                            </span>
                                            @if($order->tracking_number)
                                                <span class="text-[11px] text-[#7A6C60] font-mono">
                                                    No. Resi: <strong>{{ $order->tracking_number }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($order->tracking_number)
                                        <button type="button" 
                                                @click="copyToClipboard('{{ $order->tracking_number }}')" 
                                                class="px-2.5 py-1 bg-white hover:bg-[#F2EAE0] text-[#6B4226] border border-[#EAE1D7] text-[11px] font-bold rounded-lg transition cursor-pointer shadow-2xs shrink-0">
                                            <span x-text="copiedCode === '{{ $order->tracking_number }}' ? '✓ Disalin' : 'Salin Resi'"></span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Col 2: Buyer & Delivery Destination (5 cols) -->
                        <div class="lg:col-span-5 space-y-4 bg-[#FAF8F5] p-4 rounded-2xl border border-[#EAE1D7] flex flex-col justify-between">
                            
                            <div class="space-y-3 text-xs">
                                <div class="flex items-center justify-between gap-2 border-b border-[#EAE1D7] pb-2.5">
                                    <div>
                                        <span class="text-[10px] text-[#8A7C70] font-bold uppercase tracking-wider block">Pelanggan</span>
                                        <strong class="text-sm font-black text-[#2D241E]">{{ $order->customer_name }}</strong>
                                    </div>
                                    @if($waUrl)
                                        <a href="{{ $waUrl }}" 
                                           target="_blank" 
                                           class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] transition flex items-center gap-1 shadow-2xs"
                                           title="Kirim pesan WhatsApp ke pelanggan">
                                            <span>💬</span>
                                            <span>Chat WA</span>
                                        </a>
                                    @endif
                                </div>

                                <div>
                                    <span class="text-[10px] text-[#8A7C70] font-bold uppercase tracking-wider block">No. Telepon / WhatsApp</span>
                                    <span class="text-xs font-mono font-bold text-[#2D241E]">{{ $order->customer_phone ?: '-' }}</span>
                                </div>

                                <div>
                                    <span class="text-[10px] text-[#8A7C70] font-bold uppercase tracking-wider block">Alamat Lengkap Pengiriman</span>
                                    <p class="text-xs text-[#5A4B40] font-medium leading-relaxed mt-0.5">
                                        {{ $order->customer_address }}
                                    </p>
                                </div>

                                @if($order->customer_notes)
                                    <div class="bg-amber-50/80 border border-amber-200/80 p-2.5 rounded-xl">
                                        <span class="text-[10px] text-amber-900 font-bold block">📝 Catatan Pembeli:</span>
                                        <p class="text-[11px] text-amber-800 mt-0.5 leading-snug">
                                            {{ $order->customer_notes }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Total Summary Box -->
                            <div class="pt-3 border-t border-[#EAE1D7] space-y-1.5 text-xs">
                                <div class="flex items-center justify-between text-[#7A6C60]">
                                    <span>Subtotal Produk Toko:</span>
                                    <span class="font-bold text-[#2D241E]">Rp {{ number_format($sellerSubtotal, 0, ',', '.') }}</span>
                                </div>
                                @if($order->shipping_cost > 0)
                                    <div class="flex items-center justify-between text-[#7A6C60]">
                                        <span>Ongkos Kirim:</span>
                                        <span class="font-bold text-[#2D241E]">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between pt-1 border-t border-[#EAE1D7] text-sm">
                                    <span class="font-black text-[#2D241E]">Total Pesanan:</span>
                                    <span class="font-black text-[#6B4226]">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- ORDER CARD ACTION FOOTER -->
                    <div class="px-4 sm:px-6 py-3.5 bg-white border-t border-[#EAE1D7] flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        
                        <!-- Left Tools -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('order.detail', ['order_code' => $order->order_code]) }}" 
                               target="_blank" 
                               class="px-3 py-1.5 bg-[#FAF8F5] hover:bg-[#F2EAE0] text-[#6B4226] border border-[#EAE1D7] rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                                <span>📄</span>
                                <span>Lihat & Cetak Invoice</span>
                            </a>
                        </div>

                        <!-- Right Actions: Processing buttons -->
                        <div class="flex items-center gap-2.5 flex-wrap justify-end">
                            
                            {{-- 1. IF PENDING / CONFIRMED: Can Process or Reject --}}
                            @if($isPending)
                                @if($order->cancellation_status === 'requested')
                                    <div class="px-4 py-2 bg-amber-50 text-amber-900 border border-amber-300 rounded-xl text-xs font-bold flex items-center gap-2">
                                        <span>🔒</span>
                                        <span>Tanggapi Pengajuan Batal di Atas Terlebih Dahulu</span>
                                    </div>
                                @else
                                    <!-- Tolak Pesanan Form Modal Trigger -->
                                    <button type="button" 
                                            @click="openCancelModal({{ json_encode($order) }}, '{{ route('seller.orders.update-status', $order->id) }}')" 
                                            class="px-4 py-2 bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer shadow-2xs">
                                        Tolak Pesanan
                                    </button>

                                    <!-- Terima & Proses Pesanan Direct Form -->
                                    <form method="POST" action="{{ route('seller.orders.update-status', $order->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="processing">
                                        <button type="submit" 
                                                class="px-5 py-2 bg-[#6B4226] hover:bg-[#54321B] active:bg-[#432714] text-white rounded-xl text-xs font-black transition flex items-center gap-2 shadow-2xs cursor-pointer active:scale-95">
                                            <span>🚀</span>
                                            <span>Terima & Proses Pesanan</span>
                                        </button>
                                    </form>
                                @endif
                            @endif

                            {{-- 2. IF PROCESSING: Can Ship or Cancel --}}
                            @if($isProcessing)
                                @if($order->cancellation_status === 'requested')
                                    <div class="px-4 py-2 bg-amber-50 text-amber-900 border border-amber-300 rounded-xl text-xs font-bold flex items-center gap-2">
                                        <span>🔒</span>
                                        <span>Pengiriman Terkunci: Tanggapi Pengajuan Batal Terlebih Dahulu</span>
                                    </div>
                                @else
                                    <button type="button" 
                                            @click="openCancelModal({{ json_encode($order) }}, '{{ route('seller.orders.update-status', $order->id) }}')" 
                                            class="px-4 py-2 bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition cursor-pointer shadow-2xs">
                                        Batalkan
                                    </button>

                                    <!-- Kirim Pesanan Modal Trigger -->
                                    <button type="button" 
                                            @click="openShippingModal({{ json_encode($order) }}, '{{ route('seller.orders.update-status', $order->id) }}')" 
                                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-black transition flex items-center gap-2 shadow-2xs cursor-pointer active:scale-95">
                                        <span>🚚</span>
                                        <span>Kirim Pesanan (Input Resi)</span>
                                    </button>
                                @endif
                            @endif

                            {{-- 3. IF SHIPPED: Can Mark as Delivered (Sampai di Tujuan) --}}
                            @if($isShipped)
                                <form method="POST" action="{{ route('seller.orders.update-status', $order->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit" 
                                            onclick="return confirm('Tandai pesanan ini telah sampai di tujuan pelanggan?')" 
                                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-black transition flex items-center gap-2 shadow-2xs cursor-pointer active:scale-95">
                                        <span>📍</span>
                                        <span>Tandai Pesanan Sampai</span>
                                    </button>
                                </form>
                            @endif

                            {{-- 3b. IF DELIVERED: Notice badge --}}
                            @if($isDelivered)
                                <div class="px-3.5 py-1.5 bg-indigo-50 text-indigo-900 border border-indigo-200 rounded-xl text-xs font-bold flex items-center gap-1.5">
                                    <span>📍</span>
                                    <span>Pesanan Sampai di Tujuan (Menunggu Konfirmasi Pembeli / Selesai Otomatis 7 Hari)</span>
                                </div>
                            @endif

                            {{-- 4. IF COMPLETED: Notice badge --}}
                            @if($isCompleted)
                                <div class="px-3.5 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold flex items-center gap-1.5">
                                    <span>✓</span>
                                    <span>Pesanan telah selesai & dana masuk ke saldo toko</span>
                                </div>
                            @endif

                            {{-- 5. IF RETURNED: Notice badge --}}
                            @if($isReturned)
                                <div class="px-3.5 py-1.5 bg-purple-50 text-purple-900 border border-purple-200 rounded-xl text-xs font-bold flex items-center gap-1.5">
                                    <span>↩</span>
                                    <span>Barang telah dikembalikan & stok produk dipulihkan</span>
                                </div>
                            @endif

                            {{-- 6. IF CANCELLED: Notice badge --}}
                            @if($isCancelled)
                                <div class="px-3.5 py-1.5 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl text-xs font-bold flex items-center gap-1.5">
                                    <span>✕</span>
                                    <span>Pesanan ini telah dibatalkan</span>
                                </div>
                            @endif

                        </div>

                    </div>

                </div>
            @endforeach
        </div>

        <!-- PAGINATION -->
        <div class="bg-white rounded-2xl border border-[#EAE1D7] p-4 shadow-xs">
            {{ $orders->links() }}
        </div>
    @endif

    <!-- MODAL 1: INPUT PENGIRIMAN & NOMOR RESI -->
    <div x-show="shippingModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl border border-[#EAE1D7] max-w-md w-full p-6 space-y-5 shadow-2xl" 
             @click.outside="shippingModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-[#EAE1D7] pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center text-base font-bold">🚚</span>
                    <h3 class="font-black text-[#2D241E] text-base">Kirim Pesanan</h3>
                </div>
                <button type="button" @click="shippingModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
            </div>

            <p class="text-xs text-[#7A6C60]">
                Masukkan detail ekspedisi dan nomor resi pengiriman untuk pesanan 
                <strong class="text-[#2D241E]" x-text="'#' + (shippingOrder ? shippingOrder.order_code : '')"></strong>:
            </p>

            <form :action="shippingActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="shipped">

                <div>
                    <label class="block text-xs font-bold text-[#2D241E] mb-1">Kurir / Ekspedisi Pengiriman *</label>
                    <select name="shipping_courier" 
                            required
                            class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] focus:border-[#6B4226] rounded-xl text-xs text-[#2D241E] outline-none">
                        <option value="J&T Express">J&T Express</option>
                        <option value="JNE Express">JNE Express</option>
                        <option value="SiCepat Express">SiCepat Express</option>
                        <option value="Anteraja">Anteraja</option>
                        <option value="ID Express">ID Express</option>
                        <option value="GoSend / GrabExpress">GoSend / GrabExpress</option>
                        <option value="Kurir Toko Sendiri">Kurir Toko Sendiri</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#2D241E] mb-1">Nomor Resi / Bukti Pengiriman *</label>
                    <input type="text" 
                           name="tracking_number" 
                           required
                           placeholder="Contoh: JT1234567890 / RESI-2026-001" 
                           class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] focus:border-[#6B4226] rounded-xl text-xs text-[#2D241E] placeholder-[#8A7C70] outline-none">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="closeShippingModal()" 
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-2xs cursor-pointer">
                        Konfirmasi Kirim 🚚
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: TOLAK / BATALKAN PESANAN -->
    <div x-show="cancelModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl border border-[#EAE1D7] max-w-md w-full p-6 space-y-5 shadow-2xl" 
             @click.outside="cancelModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-[#EAE1D7] pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center text-base font-bold">⚠️</span>
                    <h3 class="font-black text-[#2D241E] text-base">Batalkan Pesanan</h3>
                </div>
                <button type="button" @click="cancelModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
            </div>

            <p class="text-xs text-[#7A6C60]">
                Apakah Anda yakin ingin membatalkan pesanan 
                <strong class="text-[#2D241E]" x-text="'#' + (cancelOrder ? cancelOrder.order_code : '')"></strong>?
            </p>

            <form :action="cancelActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">

                <div>
                    <label class="block text-xs font-bold text-[#2D241E] mb-1">Alasan Pembatalan</label>
                    <select name="cancellation_reason" 
                            class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] focus:border-rose-500 rounded-xl text-xs text-[#2D241E] outline-none">
                        <option value="Stok produk sedang habis">Stok produk sedang habis</option>
                        <option value="Permintaan pembeli untuk dibatalkan">Permintaan pembeli untuk dibatalkan</option>
                        <option value="Alamat pengiriman di luar jangkauan">Alamat pengiriman di luar jangkauan</option>
                        <option value="Toko sedang libur / tutup sementara">Toko sedang libur / tutup sementara</option>
                    </select>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="closeCancelModal()" 
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Kembali
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-2xs cursor-pointer">
                        Ya, Batalkan Pesanan ✕
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: RESPON PENGAJUAN PEMBATALAN DARI PEMBELI -->
    <div x-show="respondCancelModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl border border-[#EAE1D7] max-w-md w-full p-6 space-y-5 shadow-2xl" 
             @click.outside="closeRespondCancelModal()">
            
            <div class="flex items-center justify-between border-b border-[#EAE1D7] pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black"
                          :class="respondCancelActionType === 'approve' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                        <span x-text="respondCancelActionType === 'approve' ? '✓' : '✕'"></span>
                    </span>
                    <h3 class="font-black text-[#2D241E] text-base"
                        x-text="respondCancelActionType === 'approve' ? 'Setujui Pembatalan Pesanan' : 'Tolak Pembatalan Pesanan'"></h3>
                </div>
                <button type="button" @click="closeRespondCancelModal()" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
            </div>

            <p class="text-xs text-[#5A4B40] leading-relaxed" x-show="respondCancelActionType === 'approve'">
                Dengan menyetujui, pesanan <strong class="text-[#2D241E]" x-text="'#' + respondCancelOrderCode"></strong> akan dibatalkan resmi, dan seluruh stok produk toko Anda akan <strong>otomatis dikembalikan</strong>.
            </p>

            <p class="text-xs text-[#5A4B40] leading-relaxed" x-show="respondCancelActionType === 'reject'">
                Dengan menolak, pengajuan pembatalan untuk pesanan <strong class="text-[#2D241E]" x-text="'#' + respondCancelOrderCode"></strong> akan ditolak. Pengiriman akan <strong>dibuka kembali</strong> dan Anda dapat segera mengemas serta mengirimkan paket ke pembeli.
            </p>

            <form :action="respondCancelActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" :value="respondCancelActionType">

                <div>
                    <label class="block text-xs font-bold text-[#2D241E] mb-1">
                        Catatan untuk Pembeli (Opsional)
                    </label>
                    <textarea name="response_note" 
                              rows="3"
                              :placeholder="respondCancelActionType === 'approve' ? 'Contoh: Baik kak, pesanan dibatalkan sesuai permintaan...' : 'Contoh: Mohon maaf kak, pesanan sudah dikemas rapi dan siap dipickup kurir...'"
                              class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] focus:border-[#6B4226] rounded-xl text-xs text-[#2D241E] outline-none"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="closeRespondCancelModal()" 
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            :class="respondCancelActionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white'"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer">
                        <span x-text="respondCancelActionType === 'approve' ? 'Konfirmasi Setujui Pembatalan ✓' : 'Konfirmasi Tolak Pembatalan ✕'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: RESPON PENGAJUAN PENGEMBALIAN BARANG DARI PEMBELI -->
    <div x-show="respondReturnModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl border border-[#EAE1D7] max-w-lg w-full p-6 space-y-4 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]" 
             @click.outside="closeRespondReturnModal()">
            
            <div class="flex items-center justify-between border-b border-[#EAE1D7] pb-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black"
                          :class="respondReturnActionType === 'approve' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                        <span x-text="respondReturnActionType === 'approve' ? '✓' : '✕'"></span>
                    </span>
                    <h3 class="font-black text-[#2D241E] text-base"
                        x-text="respondReturnActionType === 'approve' ? 'Setujui Pengembalian Barang' : 'Tolak Pengembalian Barang'"></h3>
                </div>
                <button type="button" @click="closeRespondReturnModal()" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">✕</button>
            </div>

            <div class="space-y-3 overflow-y-auto flex-1 pr-1 text-xs text-[#5A4B40]">
                <!-- Return Details Preview -->
                <div class="p-3 bg-[#FAF8F5] rounded-2xl border border-[#EAE1D7] space-y-1.5">
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="font-bold text-[#6B4226]">Alasan Pengembalian:</span>
                        <span class="font-black text-[#2D241E]" x-text="respondReturnReason"></span>
                    </div>
                    <div class="text-xs text-[#2D241E]">
                        <span class="font-bold block text-[11px] text-[#7A6C60]">Keterangan Pembeli:</span>
                        <p class="italic bg-white p-2 rounded-xl border border-[#EAE1D7] mt-0.5" x-text="respondReturnDesc"></p>
                    </div>
                    <template x-if="respondReturnProofImg">
                        <div class="pt-1">
                            <span class="font-bold block text-[11px] text-[#7A6C60] mb-1">Bukti Foto dari Pembeli:</span>
                            <a :href="respondReturnProofImg" target="_blank" class="block rounded-xl overflow-hidden border border-[#EAE1D7] max-h-48 group relative">
                                <img :src="respondReturnProofImg" alt="Bukti Pengembalian" class="w-full h-48 object-cover group-hover:scale-105 transition">
                                <span class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] px-2 py-0.5 rounded-lg">Klik untuk perbesar ↗</span>
                            </a>
                        </div>
                    </template>
                </div>

                <p class="leading-relaxed" x-show="respondReturnActionType === 'approve'">
                    Dengan menyetujui, pengembalian barang pesanan <strong class="text-[#2D241E]" x-text="'#' + respondReturnOrderCode"></strong> akan disetujui. Seluruh stok produk pesanan ini akan <strong>otomatis dikembalikan ke etalase toko</strong>.
                </p>

                <p class="leading-relaxed" x-show="respondReturnActionType === 'reject'">
                    Dengan menolak, pengajuan pengembalian barang untuk pesanan <strong class="text-[#2D241E]" x-text="'#' + respondReturnOrderCode"></strong> akan ditolak. Berikan alasan penolakan yang jelas untuk pembeli.
                </p>

                <form :action="respondReturnActionUrl" method="POST" class="space-y-4 pt-1">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" :value="respondReturnActionType">

                    <div>
                        <label class="block text-xs font-bold text-[#2D241E] mb-1">
                            Catatan untuk Pembeli <span x-show="respondReturnActionType === 'reject'" class="text-rose-600">*</span>
                        </label>
                        <textarea name="response_note" 
                                  rows="3"
                                  :required="respondReturnActionType === 'reject'"
                                  :placeholder="respondReturnActionType === 'approve' ? 'Contoh: Baik kak, pengembalian disetujui, silakan kirim barang kembali...' : 'Contoh: Mohon maaf kak, barang telah sesuai dengan deskripsi dan pesanan Anda...'"
                                  class="w-full px-3.5 py-2.5 bg-[#FAF8F5] border border-[#EAE1D7] focus:border-[#6B4226] rounded-xl text-xs text-[#2D241E] outline-none"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button type="button" 
                                @click="closeRespondReturnModal()" 
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                :class="respondReturnActionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white'"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-2xs cursor-pointer">
                            <span x-text="respondReturnActionType === 'approve' ? 'Konfirmasi Setujui Pengembalian ✓' : 'Konfirmasi Tolak Pengembalian ✕'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
