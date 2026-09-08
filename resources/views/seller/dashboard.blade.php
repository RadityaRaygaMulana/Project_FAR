@extends('seller.layout')

@section('title', 'Dashboard Seller Center — ' . $store->name)

@section('content')
<div class="space-y-6">

    <!-- TOP NAVIGATION & BACK BUTTON (DIRECT KE HALAMAN UTAMA) -->
    <div class="flex items-center gap-3">
        <a href="{{ route('home') }}" 
           class="w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0 shadow-2xs group"
           title="Kembali ke Halaman Utama">
            <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-[#8A7C70] mb-0.5">
                <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition">Halaman Utama</a>
                <span>/</span>
                <span class="text-[#6B4226] font-semibold">Seller Center</span>
            </div>
            <p class="text-xs font-bold text-[#2D241E]">Panel Manajemen Toko Resmi</p>
        </div>
    </div>

    <!-- HERO STORE BANNER -->
    <div class="bg-gradient-to-r from-[#2D241E] via-[#452713] to-[#6B4226] rounded-3xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-3xl sm:text-4xl shadow-inner shrink-0 overflow-hidden">
                    @if($store->logo_url)
                        <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="w-full h-full object-cover">
                    @else
                        <span>🏬</span>
                    @endif
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="bg-emerald-500 text-white text-[10px] font-black px-2 py-0.5 rounded-md uppercase">
                            Mitra Terverifikasi ✓
                        </span>
                        <span class="text-xs text-amber-200/80">• Asal {{ $store->city }}</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Selamat Datang di Toko {{ $store->name }}! 🎉
                    </h1>
                    <p class="text-xs text-amber-100/70 max-w-xl">
                        Pantau inventaris barang, pesanan pelanggan, dan kelola etalase tokomu dengan mudah di Seller Center.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                @if(!$store->isSuspended())
                    <form action="{{ route('seller.store.toggle_status') }}" method="POST" class="inline">
                        @csrf
                        @if($store->isClosed())
                            <button type="submit" 
                                    class="px-4 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-amber-950 font-black text-xs shadow-md transition transform active:scale-95 flex items-center gap-2 cursor-pointer"
                                    title="Toko sedang libur. Klik untuk buka kembali!">
                                <span>▶️</span>
                                <span>Buka Toko Sekarang</span>
                            </button>
                        @else
                            <button type="submit" 
                                    onclick="return confirm('Apakah kamu yakin ingin menutup toko sementara (mode libur)? Pelanggan tidak akan dapat membuat pesanan baru hingga toko dibuka kembali.')"
                                    class="px-4 py-3 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs border border-white/30 shadow-md transition transform active:scale-95 flex items-center gap-2 cursor-pointer"
                                    title="Klik untuk mengubah status toko ke tutup sementara (mode libur)">
                                <span>⏸️</span>
                                <span>Tutup Toko Sementara</span>
                            </button>
                        @endif
                    </form>
                @endif

                <a href="{{ route('seller.products.create') }}" 
                   class="px-5 py-3 rounded-xl bg-white text-[#6B4226] hover:bg-[#FAF4ED] font-black text-xs shadow-md transition transform active:scale-95 flex items-center gap-2">
                    <span>➕</span>
                    <span>Tambah Produk Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- OPERATIONAL STATUS CARD -->
    <div class="rounded-2xl p-4 sm:p-5 border transition-all shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $store->isClosed() ? 'bg-amber-50/80 border-amber-200' : ($store->isSuspended() ? 'bg-red-50/80 border-red-200' : 'bg-emerald-50/70 border-emerald-200') }}">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl shrink-0 {{ $store->isClosed() ? 'bg-amber-100' : ($store->isSuspended() ? 'bg-red-100' : 'bg-emerald-100') }}">
                @if($store->isSuspended())
                    🚫
                @elseif($store->isClosed())
                    ⏸️
                @else
                    🟢
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-extrabold text-[#2D241E]">
                        Status Operasional: 
                        <span class="{{ $store->isClosed() ? 'text-amber-800' : ($store->isSuspended() ? 'text-red-700' : 'text-emerald-700') }}">
                            {{ $store->status_label }}
                        </span>
                    </h3>
                    @if($store->isClosed())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-200 text-amber-900">Mode Libur</span>
                    @elseif($store->isOpen())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-200 text-emerald-900">Melayani Pesanan</span>
                    @endif
                </div>
                <p class="text-xs text-[#7A6C60] mt-0.5">
                    @if($store->isSuspended())
                        Toko kamu sedang dinonaktifkan sementara oleh administrator.
                    @elseif($store->isClosed())
                        Toko sedang tidak beroperasi sementara. Pembeli tetap dapat melihat etalase tokomu, namun transaksi pembelian dinonaktifkan hingga toko dibuka kembali.
                    @else
                        Toko aktif dan siap melayani transaksi pesanan pembeli di NusantaraMart.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if(!$store->isSuspended())
                <form action="{{ route('seller.store.toggle_status') }}" method="POST">
                    @csrf
                    @if($store->isClosed())
                        <button type="submit" 
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-xs transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                            <span>▶️</span>
                            <span>Buka Toko Kembali</span>
                        </button>
                    @else
                        <button type="submit" 
                                onclick="return confirm('Apakah kamu ingin menutup toko sementara (mode libur)? Pembeli tidak akan dapat melakukan checkout hingga toko dibuka kembali.')"
                                class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs rounded-xl shadow-xs transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                            <span>⏸️</span>
                            <span>Tutup Sementara (Mode Libur)</span>
                        </button>
                    @endif
                </form>
            @endif
            <a href="{{ route('seller.settings') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] text-xs font-bold transition shadow-2xs">
                ⚙️ Pengaturan Toko
            </a>
        </div>
    </div>

    <!-- 6 METRICS CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">

        <!-- Total Products -->
        <div class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs space-y-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#8A7C70] uppercase tracking-wider">Katalog</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">📦</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#2D241E]">{{ $totalProducts }}</p>
                <p class="text-[10px] text-emerald-700 font-semibold mt-0.5">{{ $activeProducts }} aktif tayang</p>
            </div>
        </div>

        <!-- Out of Stock -->
        <div class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs space-y-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#8A7C70] uppercase tracking-wider">Stok Habis</span>
                <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm">⚠️</span>
            </div>
            <div>
                <p class="text-2xl font-black {{ $outOfStock > 0 ? 'text-rose-600' : 'text-[#2D241E]' }}">{{ $outOfStock }}</p>
                <p class="text-[10px] text-[#8A7C70] mt-0.5">{{ $outOfStock > 0 ? 'Perlu restock' : 'Semua aman' }}</p>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs space-y-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#8A7C70] uppercase tracking-wider">Pesanan</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">📑</span>
            </div>
            <div>
                <p class="text-2xl font-black text-[#2D241E]">{{ $totalOrdersCount }}</p>
                <p class="text-[10px] text-[#8A7C70] mt-0.5">Total dari pembeli</p>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs space-y-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#8A7C70] uppercase tracking-wider">Total Omzet</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">💰</span>
            </div>
            <div>
                <p class="text-lg font-black text-[#2D241E] leading-tight">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <p class="text-[10px] text-[#8A7C70] mt-0.5">Keseluruhan</p>
            </div>
        </div>

        <!-- This Month Revenue -->
        <div class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs space-y-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#8A7C70] uppercase tracking-wider">Bulan Ini</span>
                <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm">📅</span>
            </div>
            <div>
                <p class="text-base font-black text-[#2D241E] leading-tight">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</p>
                <p class="text-[10px] font-semibold mt-0.5 {{ $revenueGrowth >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $revenueGrowth >= 0 ? '↑' : '↓' }} {{ abs($revenueGrowth) }}% vs bln lalu
                </p>
            </div>
        </div>

        <!-- Store Rating -->
        <div class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs space-y-2 col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#8A7C70] uppercase tracking-wider">Rating Toko</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">⭐</span>
            </div>
            <div>
                <p class="text-2xl font-black text-amber-600">★ {{ number_format($store->rating, 1) }}</p>
                <p class="text-[10px] text-[#8A7C70] mt-0.5">{{ $thisMonthOrders }} pesanan bln ini</p>
            </div>
        </div>

    </div>

    <!-- CHARTS ROW -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Revenue & Orders Area Chart (spans 2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-xs">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h2 class="text-sm font-black text-[#2D241E]">Grafik Penjualan — 30 Hari Terakhir</h2>
                    <p class="text-[11px] text-[#8A7C70] mt-0.5">Omzet harian dari pesanan yang masuk ke toko</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] font-semibold">
                    <span class="flex items-center gap-1.5 text-[#6B4226]">
                        <span class="w-3 h-1.5 rounded-full bg-[#6B4226] inline-block"></span>Omzet
                    </span>
                    <span class="flex items-center gap-1.5 text-emerald-600">
                        <span class="w-3 h-1.5 rounded-full bg-emerald-500 inline-block"></span>Pesanan
                    </span>
                </div>
            </div>
            <div class="relative h-56">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Top Products Bar Chart -->
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-xs flex flex-col">
            <div class="mb-5">
                <h2 class="text-sm font-black text-[#2D241E]">Produk Terlaris 🏆</h2>
                <p class="text-[11px] text-[#8A7C70] mt-0.5">Berdasarkan jumlah unit terjual</p>
            </div>

            @if($topProducts->count() > 0)
                <div class="space-y-3 flex-1">
                    @foreach($topProducts as $i => $tp)
                        @php
                            $maxSold = $topProducts->first()->total_sold;
                            $pct = $maxSold > 0 ? round(($tp->total_sold / $maxSold) * 100) : 0;
                            $colors = ['bg-[#6B4226]', 'bg-amber-500', 'bg-orange-400', 'bg-yellow-400', 'bg-stone-400'];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-bold text-[#2D241E] truncate max-w-[70%]" title="{{ $tp->product_name }}">
                                    {{ $i + 1 }}. {{ Str::limit($tp->product_name, 22) }}
                                </span>
                                <span class="text-[11px] font-black text-[#6B4226]">{{ $tp->total_sold }}x</span>
                            </div>
                            <div class="w-full bg-[#F2EAE0] rounded-full h-2">
                                <div class="{{ $colors[$i] ?? 'bg-stone-400' }} h-2 rounded-full transition-all duration-700"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-center py-6 text-[#8A7C70]">
                    <span class="text-3xl mb-2">📊</span>
                    <p class="text-xs">Belum ada penjualan yang tercatat untuk ditampilkan.</p>
                </div>
            @endif
        </div>

    </div>

    <!-- RECENT PRODUCTS TABLE -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base sm:text-lg font-black text-[#2D241E]">
                    Produk Terbaru Toko
                </h2>
                <p class="text-xs text-[#8A7C70]">5 produk terakhir yang ditambahkan ke etalase toko Anda</p>
            </div>
            <a href="{{ route('seller.products.index') }}" class="text-xs font-bold text-[#6B4226] hover:underline flex items-center gap-1">
                <span>Lihat Semua Produk</span>
                <span>→</span>
            </a>
        </div>

        @if($recentProducts->count() === 0)
            <div class="p-8 text-center rounded-2xl bg-[#FAF8F5] border border-dashed border-[#EAE1D7] space-y-3">
                <span class="text-4xl">🛍️</span>
                <p class="text-xs text-[#7A6C60]">Belum ada produk yang dijual di tokomu.</p>
                <a href="{{ route('seller.products.create') }}" class="px-5 py-2.5 bg-[#6B4226] text-white font-bold rounded-xl text-xs shadow-xs hover:bg-[#54321B] transition inline-block">
                    Mulai Tambah Produk Pertama 🚀
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#F2EAE0] text-[#8A7C70] font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Produk</th>
                            <th class="py-3 px-3">Kategori</th>
                            <th class="py-3 px-3">Harga</th>
                            <th class="py-3 px-3">Stok</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F2EAE0]">
                        @foreach($recentProducts as $item)
                            <tr class="hover:bg-[#FAF8F5] transition">
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] flex items-center justify-center shrink-0 overflow-hidden">
                                            @if($item->product_image_url)
                                                <img src="{{ $item->product_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-xl">{{ $item->category->icon ?? '🛍️' }}</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('product.detail', $item->slug) }}" target="_blank" class="font-bold text-[#2D241E] hover:text-[#6B4226] transition truncate block max-w-xs" title="{{ $item->name }}">
                                                {{ $item->name }}
                                            </a>
                                            <span class="text-[10px] text-[#8A7C70]">{{ $item->sold_count }} terjual</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-[#5A4B40] font-medium">
                                    {{ $item->category->name ?? '-' }}
                                </td>
                                <td class="py-3 px-3 font-bold text-[#6B4226]">
                                    {{ $item->formatted_effective_price }}
                                </td>
                                <td class="py-3 px-3 font-mono font-bold {{ $item->stock <= 0 ? 'text-rose-600' : 'text-[#2D241E]' }}">
                                    {{ $item->stock }} unit
                                </td>
                                <td class="py-3 px-3">
                                    @if($item->is_available)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif Tayang
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('seller.products.edit', $item->id) }}" class="px-3 py-1 bg-[#FAF8F5] hover:bg-[#F2EAE0] text-[#6B4226] font-bold rounded-lg border border-[#EAE1D7] transition inline-block">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const dates   = @json($chartDates);
    const revenue = @json($chartRevenue);
    const orders  = @json($chartOrders);

    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    const primaryColor = '#6B4226';
    const emeraldColor = '#10b981';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Omzet (Rp)',
                    data: revenue,
                    borderColor: primaryColor,
                    backgroundColor: 'rgba(107,66,38,0.08)',
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: primaryColor,
                    fill: true,
                    tension: 0.42,
                    yAxisID: 'yRevenue',
                },
                {
                    label: 'Pesanan',
                    data: orders,
                    borderColor: emeraldColor,
                    backgroundColor: 'rgba(16,185,129,0.07)',
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: emeraldColor,
                    fill: true,
                    tension: 0.42,
                    yAxisID: 'yOrders',
                    borderDash: [5, 3],
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2D241E',
                    titleFont: { size: 11, weight: 'bold' },
                    bodyFont: { size: 11 },
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: (ctx) => {
                            if (ctx.datasetIndex === 0) {
                                return '  Omzet: Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                            }
                            return '  Pesanan: ' + ctx.parsed.y + ' item';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#8A7C70',
                        font: { size: 10 },
                        maxTicksLimit: 10,
                        maxRotation: 0,
                    }
                },
                yRevenue: {
                    position: 'left',
                    grid: { color: '#F2EAE0', lineWidth: 1 },
                    border: { display: false, dash: [3, 3] },
                    ticks: {
                        color: '#8A7C70',
                        font: { size: 10 },
                        callback: (v) => v === 0 ? '0' : 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v/1000).toFixed(0)+'rb'),
                        maxTicksLimit: 6,
                    }
                },
                yOrders: {
                    position: 'right',
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#10b981',
                        font: { size: 10 },
                        maxTicksLimit: 6,
                        callback: (v) => Number.isInteger(v) ? v : '',
                    }
                }
            }
        }
    });
})();
</script>
@endpush
@endsection
