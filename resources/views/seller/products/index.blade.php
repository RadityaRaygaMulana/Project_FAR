@extends('seller.layout')

@section('title', 'Katalog Produk Toko — ' . $store->name)

@section('content')
<div class="space-y-6">

    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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
                <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] tracking-tight">
                    Katalog Produk Toko 📦
                </h1>
                <p class="text-xs text-[#8A7C70]">Kelola stok, harga, dan ketersediaan barang jualanmu</p>
            </div>
        </div>

        <a href="{{ route('seller.products.create') }}" 
           class="px-5 py-3 rounded-2xl bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm shadow-md transition transform active:scale-95 flex items-center justify-center gap-2 shrink-0">
            <span>➕</span>
            <span>Tambah Produk Baru</span>
        </a>
    </div>

    <!-- FILTER & SEARCH TOOLBAR -->
    <div class="bg-white rounded-2xl border border-[#EAE1D7] p-4 flex flex-col md:flex-row items-center justify-between gap-3 shadow-2xs">
        
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 text-xs font-bold">
            <a href="{{ route('seller.products.index', ['q' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl transition shrink-0 {{ empty($status) ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]' }}">
                Semua
            </a>
            <a href="{{ route('seller.products.index', ['status' => 'active', 'q' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl transition shrink-0 {{ $status === 'active' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]' }}">
                Aktif Tayang
            </a>
            <a href="{{ route('seller.products.index', ['status' => 'out_of_stock', 'q' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl transition shrink-0 {{ $status === 'out_of_stock' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]' }}">
                Stok Habis
            </a>
            <a href="{{ route('seller.products.index', ['status' => 'inactive', 'q' => $search]) }}" 
               class="px-3 py-1.5 rounded-xl transition shrink-0 {{ $status === 'inactive' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]' }}">
                Nonaktif
            </a>
        </div>

        <!-- Search Input Form -->
        <form action="{{ route('seller.products.index') }}" method="GET" class="w-full md:w-72 flex items-center gap-2">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative w-full">
                <input type="text" 
                       name="q" 
                       value="{{ $search }}" 
                       placeholder="Cari nama produk..." 
                       class="w-full pl-9 pr-3 py-2 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                <span class="absolute left-3 top-2.5 text-xs text-[#8A7C70]">🔍</span>
            </div>
            @if($search)
                <a href="{{ route('seller.products.index', ['status' => $status]) }}" class="text-xs text-[#8A7C70] hover:text-red-600 shrink-0 font-bold">
                    ✕
                </a>
            @endif
        </form>

    </div>

    <!-- PRODUCTS TABLE CARD -->
    <div class="bg-white rounded-3xl border border-[#EAE1D7] shadow-xs overflow-hidden">
        @if($products->count() === 0)
            <div class="p-12 text-center space-y-3">
                <span class="text-4xl">📦</span>
                <h3 class="font-bold text-sm text-[#2D241E]">Tidak Ada Produk Ditemukan</h3>
                <p class="text-xs text-[#7A6C60]">
                    {{ $search ? 'Coba ganti kata kunci pencarian.' : 'Mulai tambahkan produk pertamamu ke toko!' }}
                </p>
                <a href="{{ route('seller.products.create') }}" class="px-5 py-2.5 bg-[#6B4226] text-white font-bold rounded-xl text-xs shadow-xs hover:bg-[#54321B] transition inline-block">
                    + Tambah Produk Baru
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#FAF8F5] border-b border-[#EAE1D7] text-[#8A7C70] font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3.5 px-4">Nama Produk</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Harga Normal / Diskon</th>
                            <th class="py-3.5 px-4">Sisa Stok</th>
                            <th class="py-3.5 px-4">Terjual</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F2EAE0]">
                        @foreach($products as $product)
                            <tr class="hover:bg-[#FAF8F5] transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-b from-[#FAF8F5] to-[#FAF4ED] border border-[#EAE1D7] flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                                            @if($product->product_image_url)
                                                <img src="{{ $product->product_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                @php $allCount = count($product->allImageUrls()); @endphp
                                                @if($allCount > 1)
                                                    <span class="absolute bottom-1 right-1 bg-black/75 backdrop-blur-xs text-white text-[9px] font-bold px-1 rounded-sm leading-tight shadow-xs">
                                                        📷 {{ $allCount }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-2xl">{{ $product->category->icon ?? '🛍️' }}</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0 max-w-xs">
                                            <a href="{{ route('product.detail', $product->slug) }}" target="_blank" class="font-bold text-sm text-[#2D241E] hover:text-[#6B4226] transition block truncate" title="{{ $product->name }}">
                                                {{ $product->name }}
                                            </a>
                                            <span class="text-[10px] text-[#8A7C70] block">Berat: {{ $product->weight_grams }}g</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-[#5A4B40]">
                                    {{ $product->category->name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($product->hasDiscount())
                                        <span class="text-[10px] text-[#8A7C70] line-through block">{{ $product->formatted_price }}</span>
                                        <span class="font-black text-[#6B4226] text-sm block">{{ $product->formatted_effective_price }}</span>
                                    @else
                                        <span class="font-black text-[#2D241E] text-sm block">{{ $product->formatted_price }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold">
                                    <span class="{{ $product->stock <= 0 ? 'text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200' : 'text-[#2D241E]' }}">
                                        {{ $product->stock }} unit
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-[#5A4B40]">
                                    {{ $product->sold_count }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($product->is_available)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('seller.products.edit', $product->id) }}" 
                                           class="px-3 py-1.5 bg-[#FAF8F5] hover:bg-[#F2EAE0] text-[#6B4226] font-bold rounded-xl border border-[#EAE1D7] transition text-xs">
                                            Ubah
                                        </a>

                                        <form action="{{ route('seller.products.destroy', $product->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ addslashes($product->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer"
                                                    title="Hapus Produk">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="p-4 border-t border-[#EAE1D7]">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
