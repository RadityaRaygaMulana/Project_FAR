@extends('layouts.admin')

@section('title', 'Kelola Mitra Toko — NusantaraMart Admin')

@section('content')
<div class="space-y-6" x-data="{
    showDetailModal: false,
    selectedStore: null,
    showApproveModal: false,
    showRejectModal: false,
    rejectStoreId: null,
    rejectStoreName: '',
    showKtpModal: false,
    modalKtpUrl: '',
    modalKtpTitle: '',
    storeList: {
        @foreach($stores as $st)
        {{ $st->id }}: {
            id: {{ $st->id }},
            name: @js($st->name),
            slug: @js($st->slug),
            user_name: @js($st->user->name ?? 'User Tidak Ditemukan'),
            user_email: @js($st->user->email ?? '-'),
            ktp_name: @js($st->ktp_name ?: ($st->user->name ?? '-')),
            ktp_nik: @js($st->ktp_nik ?: '-'),
            ktp_photo_url: @js($st->ktp_photo_url),
            city: @js($st->city),
            phone: @js($st->phone),
            description: @js($st->description ?? ''),
            address_detail: @js($st->address_detail ?? ''),
            status: @js($st->status),
            rejection_reason: @js($st->rejection_reason ?? ''),
            created_at_formatted: @js($st->created_at->format('d M Y, H:i')),
            approved_at_formatted: @js($st->approved_at ? $st->approved_at->format('d M Y, H:i') : null),
            approve_url: @js(route('admin.stores.approve', $st->id)),
            reject_url: @js(url('admin/stores/' . $st->id . '/reject')),
            store_url: @js(route('store.show', urlencode($st->name))),
        },
        @endforeach
    },
    openDetail(id) {
        if (this.storeList && this.storeList[id]) {
            this.selectedStore = this.storeList[id];
            this.showDetailModal = true;
        }
    },
    openRejectModalFromDetail() {
        if (!this.selectedStore) return;
        this.rejectStoreId = this.selectedStore.id;
        this.rejectStoreName = this.selectedStore.name;
        this.showDetailModal = false;
        this.showRejectModal = true;
    },
    openRejectModal(id, name) {
        this.rejectStoreId = id;
        this.rejectStoreName = name;
        this.showRejectModal = true;
    }
}"
x-effect="
    const isLocked = Boolean(showDetailModal || showApproveModal || showRejectModal || showKtpModal);
    document.documentElement.style.overflow = isLocked ? 'hidden' : '';
    document.body.style.overflow = isLocked ? 'hidden' : '';
">

    <!-- Top Command & Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#6B4226] transition">Admin Dashboard</a>
                <span>/</span>
                <span class="text-[#6B4226] font-semibold">Mitra Toko</span>
            </div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Verifikasi & Pengajuan Toko Penjual
                </h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-900 border border-amber-300">
                    <span>🏬</span>
                    <span>VERIFIKASI KTP</span>
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Verifikasi berkas identitas KTP dan moderasi pengajuan pendaftaran toko resmi mitra NusantaraMart.
            </p>
        </div>
    </div>

        @if(session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-20"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 py-0"
                 class="overflow-hidden p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold flex items-center justify-between shadow-2xs">
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
                 x-transition:leave-start="opacity-100 scale-100 max-h-20"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 py-0"
                 class="overflow-hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm font-bold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">⚠️</span>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-amber-500 hover:text-amber-800 font-bold p-1 cursor-pointer transition" title="Tutup Notifikasi">✕</button>
            </div>
        @endif

        <!-- 4 STATUS METRIC CARDS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <a href="{{ route('admin.stores', ['status' => 'all']) }}" 
               class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs hover:border-[#6B4226]/40 transition block {{ $status === 'all' ? 'ring-2 ring-[#6B4226]/20 bg-[#FAF8F5]' : '' }}">
                <span class="text-[10px] uppercase font-bold text-[#8A7C70] block">Semua Toko</span>
                <span class="text-2xl font-black text-[#2D241E]">{{ $totalCount }}</span>
            </a>

            <a href="{{ route('admin.stores', ['status' => 'pending']) }}" 
               class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs hover:border-[#6B4226]/40 transition block {{ $status === 'pending' ? 'ring-2 ring-amber-500/20 bg-amber-50/40' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase font-bold text-amber-700 block">Menunggu Review</span>
                    @if($pendingCount > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    @endif
                </div>
                <span class="text-2xl font-black text-amber-600">{{ $pendingCount }}</span>
            </a>

            <a href="{{ route('admin.stores', ['status' => 'approved']) }}" 
               class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs hover:border-[#6B4226]/40 transition block {{ $status === 'approved' ? 'ring-2 ring-emerald-500/20 bg-emerald-50/40' : '' }}">
                <span class="text-[10px] uppercase font-bold text-emerald-700 block">Disetujui (Aktif)</span>
                <span class="text-2xl font-black text-emerald-700">{{ $approvedCount }}</span>
            </a>

            <a href="{{ route('admin.stores', ['status' => 'rejected']) }}" 
               class="bg-white rounded-2xl p-4 border border-[#EAE1D7] shadow-xs hover:border-[#6B4226]/40 transition block {{ $status === 'rejected' ? 'ring-2 ring-rose-500/20 bg-rose-50/40' : '' }}">
                <span class="text-[10px] uppercase font-bold text-rose-700 block">Ditolak</span>
                <span class="text-2xl font-black text-rose-600">{{ $rejectedCount }}</span>
            </a>
        </div>

        <!-- SEARCH & FILTER TOOLBAR -->
        <div class="bg-white rounded-2xl border border-[#EAE1D7] p-4 flex flex-col md:flex-row items-center justify-between gap-3 shadow-2xs">
            <!-- Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto text-xs font-bold pb-1 md:pb-0">
                <a href="{{ route('admin.stores', ['status' => 'all', 'q' => $search]) }}" 
                   class="px-3 py-1.5 rounded-xl transition shrink-0 {{ $status === 'all' ? 'bg-[#6B4226] text-white shadow-2xs' : 'bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#F2EAE0]' }}">
                    Semua ({{ $totalCount }})
                </a>
                <a href="{{ route('admin.stores', ['status' => 'pending', 'q' => $search]) }}" 
                   class="px-3 py-1.5 rounded-xl transition shrink-0 {{ $status === 'pending' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                    ⏳ Menunggu Persetujuan ({{ $pendingCount }})
                </a>
                <a href="{{ route('admin.stores', ['status' => 'approved', 'q' => $search]) }}" 
                   class="px-3 py-1.5 rounded-xl transition shrink-0 {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    ✅ Disetujui ({{ $approvedCount }})
                </a>
                <a href="{{ route('admin.stores', ['status' => 'rejected', 'q' => $search]) }}" 
                   class="px-3 py-1.5 rounded-xl transition shrink-0 {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                    🚫 Ditolak ({{ $rejectedCount }})
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('admin.stores') }}" method="GET" class="w-full md:w-72 flex items-center gap-2">
                @if($status !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="relative w-full">
                    <input type="text" 
                           name="q" 
                           value="{{ $search }}" 
                           placeholder="Cari toko / pemilik / kota..." 
                           class="w-full pl-9 pr-3 py-2 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                    <span class="absolute left-3 top-2.5 text-xs text-[#8A7C70]">🔍</span>
                </div>
                @if($search)
                    <a href="{{ route('admin.stores', ['status' => $status]) }}" class="text-xs text-[#8A7C70] hover:text-red-600 font-bold shrink-0">
                        ✕
                    </a>
                @endif
            </form>
        </div>

        <!-- STORES TABLE CARD -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
            @if($stores->count() === 0)
                <div class="p-12 text-center space-y-3">
                    <span class="text-4xl">🏬</span>
                    <h3 class="font-bold text-sm text-slate-900">Tidak Ada Pengajuan Toko</h3>
                    <p class="text-xs text-slate-500">
                        {{ $status === 'pending' ? 'Tidak ada pengajuan toko yang sedang menunggu verifikasi saat ini.' : 'Belum ada data toko pada kategori ini.' }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/90 border-b border-slate-200/80 text-slate-500 font-extrabold uppercase tracking-wider text-[10px]">
                                <th class="py-4 px-5">Toko & Kemitraan</th>
                                <th class="py-4 px-5">Akun Pemilik</th>
                                <th class="py-4 px-5">Verifikasi KTP</th>
                                <th class="py-4 px-5">Asal & Kontak</th>
                                <th class="py-4 px-5">Status</th>
                                <th class="py-4 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($stores as $st)
                                <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                    
                                    <!-- Store Identity -->
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#6B4226] to-[#4A2E19] text-white flex items-center justify-center text-lg shrink-0 shadow-2xs font-bold overflow-hidden">
                                                @if($st->logo_url)
                                                    <img src="{{ $st->logo_url }}" alt="{{ $st->name }}" class="w-full h-full object-cover">
                                                @else
                                                    🏬
                                                @endif
                                            </div>
                                            <div class="min-w-0 max-w-xs">
                                                <div class="flex items-center gap-1.5">
                                                    <strong class="font-extrabold text-sm text-slate-900 truncate block hover:text-[#6B4226] cursor-pointer" 
                                                            @click="openDetail({{ $st->id }})"
                                                            title="{{ $st->name }}">
                                                        {{ $st->name }}
                                                    </strong>
                                                    @if($st->isApproved())
                                                        <a href="{{ route('store.show', urlencode($st->name)) }}" target="_blank" class="text-[#6B4226] hover:underline text-[11px] shrink-0" title="Buka Etalase Toko">
                                                            ↗
                                                        </a>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] text-slate-400 font-mono block truncate">/store/{{ $st->slug }}</span>
                                                @if($st->isRejected() && $st->rejection_reason)
                                                    <p class="text-[10px] text-rose-600 font-medium truncate mt-0.5" title="{{ $st->rejection_reason }}">
                                                        Alasan: {{ Str::limit($st->rejection_reason, 35) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- User / Owner -->
                                    <td class="py-4 px-5">
                                        <div>
                                            <strong class="text-slate-900 text-xs font-bold block">{{ $st->user->name ?? 'User Terhapus' }}</strong>
                                            <span class="text-[11px] text-slate-400 font-mono block">{{ $st->user->email ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <!-- KTP Info & Badge -->
                                    <td class="py-4 px-5">
                                        <div class="flex items-center gap-3">
                                            @if($st->ktp_photo_url)
                                                <div class="relative w-10 h-10 rounded-xl overflow-hidden border border-amber-300/80 bg-slate-900 shrink-0 shadow-2xs group cursor-pointer"
                                                     @click.stop="modalKtpUrl = '{{ $st->ktp_photo_url }}'; modalKtpTitle = 'KTP: {{ addslashes($st->ktp_name ?: $st->name) }} (NIK: {{ $st->ktp_nik }})'; showKtpModal = true"
                                                     title="Klik untuk pratinjau cepat foto KTP">
                                                    <img src="{{ $st->ktp_photo_url }}" alt="KTP" class="w-full h-full object-cover group-hover:scale-110 transition duration-200">
                                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] transition">
                                                        🔍
                                                    </div>
                                                </div>
                                            @else
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center text-lg shrink-0"
                                                     title="Belum ada foto KTP">
                                                    🪪
                                                </div>
                                            @endif

                                            <div class="min-w-0 max-w-[220px]">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-extrabold text-xs text-slate-900 truncate block" title="{{ $st->ktp_name ?: ($st->user->name ?? '-') }}">
                                                        {{ $st->ktp_name ?: ($st->user->name ?? '-') }}
                                                    </span>
                                                    @if($st->ktp_photo_url)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0" title="Foto KTP Asli Terlampir">
                                                            ✓ KTP
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-mono mt-0.5 whitespace-nowrap">
                                                    <span class="text-[9px] font-sans font-bold text-[#6B4226] bg-amber-50 px-1 py-0.2 rounded border border-amber-200">NIK</span>
                                                    <span class="font-semibold text-slate-700 tracking-wider select-all">{{ $st->masked_nik }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Location & Phone -->
                                    <td class="py-4 px-5">
                                        <div class="text-xs">
                                            <span class="text-slate-800 font-medium block">{{ $st->city }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono block">{{ $st->phone }}</span>
                                        </div>
                                    </td>

                                    <!-- Status Pill -->
                                    <td class="py-4 px-5">
                                        @if($st->isApproved())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-200/90">
                                                <span>✓</span> <span>Disetujui</span>
                                            </span>
                                        @elseif($st->isPending())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-800 border border-amber-200/90">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>Menunggu</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-800 border border-rose-200/90">
                                                <span>✕</span> <span>Ditolak</span>
                                            </span>
                                        @endif
                                        <span class="text-[10px] text-slate-400 block mt-1 font-mono">
                                            {{ $st->created_at->format('d/m/y H:i') }}
                                        </span>
                                    </td>

                                    <!-- Admin Action: Single Clean 'Lihat' Button -->
                                    <td class="py-4 px-5 text-right whitespace-nowrap">
                                        <button type="button" 
                                                @click="openDetail({{ $st->id }})"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-[#6B4226] text-white font-bold rounded-xl text-xs shadow-2xs hover:shadow-xs transition duration-150 active:scale-95 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat</span>
                                        </button>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($stores->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $stores->links() }}
                    </div>
                @endif
            @endif
        </div>

    <!-- STORE DETAIL INFORMATION CARD MODAL -->
    <div x-show="showDetailModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6"
         @keydown.escape.window="showDetailModal = false"
         @wheel.stop
         @touchmove.stop>
        
        <div class="bg-white rounded-3xl border border-slate-200/90 max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl overflow-hidden"
             @click.away="showDetailModal = false">
            
            <!-- Modal Header -->
            <div class="px-6 py-4.5 border-b border-slate-200/80 flex items-center justify-between gap-4 bg-slate-50/90 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#6B4226] to-[#4A2E19] text-white flex items-center justify-center text-2xl shadow-xs shrink-0">
                        🏬
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base sm:text-lg font-black text-slate-900 truncate" x-text="selectedStore ? selectedStore.name : ''"></h3>
                            <!-- Status Pill -->
                            <template x-if="selectedStore && selectedStore.status === 'approved'">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✓ Disetujui & Aktif
                                </span>
                            </template>
                            <template x-if="selectedStore && selectedStore.status === 'pending'">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                    ⏳ Menunggu Persetujuan
                                </span>
                            </template>
                            <template x-if="selectedStore && selectedStore.status === 'rejected'">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300">
                                    ✕ Ditolak
                                </span>
                            </template>
                        </div>
                        <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="selectedStore ? '/store/' + selectedStore.slug : ''"></p>
                    </div>
                </div>

                <button type="button" 
                        @click="showDetailModal = false" 
                        class="w-9 h-9 rounded-full bg-white hover:bg-slate-100 text-slate-400 hover:text-slate-700 border border-slate-200 flex items-center justify-center text-sm font-black transition cursor-pointer shrink-0"
                        title="Tutup">
                    ✕
                </button>
            </div>

            <!-- Modal Scrollable Content -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs overscroll-contain">
                
                <!-- Status Notice Banner if Rejected or Pending -->
                <template x-if="selectedStore && selectedStore.status === 'rejected' && selectedStore.rejection_reason">
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 space-y-1">
                        <span class="font-black text-[10px] uppercase tracking-wider text-rose-600 block">Pesan / Alasan Penolakan:</span>
                        <p class="font-bold text-rose-800 leading-relaxed" x-text="selectedStore.rejection_reason"></p>
                    </div>
                </template>

                <template x-if="selectedStore && selectedStore.status === 'pending'">
                    <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-center gap-2.5">
                        <span class="text-lg">📋</span>
                        <span>Pengajuan toko ini menunggu verifikasi dokumen identitas dan persetujuan dari Administrator.</span>
                    </div>
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- LEFT COLUMN: Store Profile & Owner Information -->
                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <span>👤</span> <span>Akun Pemilik Toko</span>
                            </h4>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Nama Akun Pengguna:</span>
                                    <strong class="text-slate-800 text-xs block font-bold" x-text="selectedStore ? selectedStore.user_name : ''"></strong>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Email Akun:</span>
                                    <span class="text-slate-700 font-mono text-xs block" x-text="selectedStore ? selectedStore.user_email : ''"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Nomor WhatsApp / Kontak:</span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-slate-800 font-mono font-bold text-xs" x-text="selectedStore ? selectedStore.phone : ''"></span>
                                        <template x-if="selectedStore && selectedStore.phone">
                                            <a :href="'https://wa.me/' + selectedStore.phone.replace(/[^0-9]/g, '')" 
                                               target="_blank" 
                                               class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px] hover:bg-emerald-200 transition">
                                                Chat WA ↗
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <span>📍</span> <span>Lokasi & Alamat Usaha</span>
                            </h4>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Kota Asal Pengiriman:</span>
                                    <strong class="text-slate-800 text-xs block font-bold" x-text="selectedStore ? selectedStore.city : ''"></strong>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Alamat Gudang / Tempat Usaha:</span>
                                    <p class="text-slate-700 leading-relaxed text-xs" x-text="selectedStore && selectedStore.address_detail ? selectedStore.address_detail : 'Tidak ada alamat lengkap'"></p>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Deskripsi / Slogan Toko:</span>
                                    <p class="text-slate-700 leading-relaxed italic text-xs" x-text="selectedStore && selectedStore.description ? '“' + selectedStore.description + '”' : 'Belum mengisi slogan toko'"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: KTP Verification & Photo Card -->
                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200 space-y-3">
                            <h4 class="font-extrabold text-xs uppercase tracking-wider text-amber-900 flex items-center gap-1.5">
                                <span>🪪</span> <span>Dokumen Identitas KTP</span>
                            </h4>
                            
                            <div class="space-y-2">
                                <div>
                                    <span class="text-[10px] text-amber-800/80 block">Nama Lengkap Sesuai KTP:</span>
                                    <strong class="text-slate-900 text-xs block font-bold" x-text="selectedStore ? selectedStore.ktp_name : ''"></strong>
                                </div>
                                <div>
                                    <span class="text-[10px] text-amber-800/80 block">Nomor Induk Kependudukan (NIK):</span>
                                    <span class="font-mono text-xs font-black text-[#6B4226] tracking-wider px-2.5 py-1 bg-white rounded-lg border border-amber-200 inline-block mt-0.5" 
                                          x-text="selectedStore ? selectedStore.ktp_nik : ''"></span>
                                </div>
                            </div>

                            <!-- KTP Photo Box with Preview -->
                            <div class="pt-2 border-t border-amber-200/60">
                                <span class="text-[10px] font-bold text-amber-900 block mb-1.5">Foto KTP Asli Terunggah:</span>
                                
                                <template x-if="selectedStore && selectedStore.ktp_photo_url">
                                    <div class="relative group rounded-2xl overflow-hidden border border-amber-300/80 bg-slate-900 cursor-pointer shadow-sm"
                                         @click="modalKtpUrl = selectedStore.ktp_photo_url; modalKtpTitle = 'KTP: ' + (selectedStore.ktp_name || selectedStore.name) + ' (NIK: ' + selectedStore.ktp_nik + ')'; showKtpModal = true">
                                        <img :src="selectedStore.ktp_photo_url" alt="Foto KTP" class="w-full h-44 object-cover group-hover:scale-105 transition duration-300">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-2 text-white font-bold text-xs transition">
                                            <span>🔍 Klik Perbesar Foto KTP</span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="selectedStore && !selectedStore.ktp_photo_url">
                                    <div class="h-36 rounded-2xl bg-white border border-dashed border-amber-300 flex flex-col items-center justify-center text-amber-800/60 p-4 text-center">
                                        <span class="text-2xl mb-1">📭</span>
                                        <span class="text-xs">Foto KTP belum diunggah</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Registration Timeline -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-500 space-y-1">
                            <p class="flex items-center justify-between">
                                <span>Waktu Pengajuan:</span>
                                <strong class="text-slate-700" x-text="selectedStore ? selectedStore.created_at_formatted : '-'"></strong>
                            </p>
                            <template x-if="selectedStore && selectedStore.approved_at_formatted">
                                <p class="flex items-center justify-between">
                                    <span>Waktu Disetujui:</span>
                                    <strong class="text-emerald-700" x-text="selectedStore.approved_at_formatted"></strong>
                                </p>
                            </template>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Footer Action Bar (Moderation Decisions) -->
            <div class="px-6 py-3 border-t border-slate-200 bg-slate-100/70 flex items-center justify-between gap-3 shrink-0">
                <button type="button" 
                        @click="showDetailModal = false" 
                        class="px-4 py-2 bg-white hover:bg-slate-200/70 text-slate-700 font-bold rounded-xl border border-slate-200 text-xs transition cursor-pointer">
                    Tutup
                </button>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <!-- Open Store Link if Approved -->
                    <template x-if="selectedStore && selectedStore.status === 'approved'">
                        <a :href="selectedStore.store_url" 
                           target="_blank" 
                           class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5">
                            <span>Kunjungi Toko</span>
                            <span>↗</span>
                        </a>
                    </template>

                    <!-- Reject Button -->
                    <template x-if="selectedStore && selectedStore.status !== 'rejected'">
                        <button type="button" 
                                @click="openRejectModalFromDetail()" 
                                class="px-4 py-2.5 bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-rose-200 font-bold rounded-xl text-xs transition cursor-pointer">
                            Tolak Pengajuan...
                        </button>
                    </template>
                    <template x-if="selectedStore && selectedStore.status !== 'approved'">
                        <button type="button" 
                                @click="showApproveModal = true"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                            <span>Setujui Toko Resmi</span>
                            <span>✓</span>
                        </button>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- APPROVAL CONFIRMATION MODAL (NO BROWSER DIALOG) -->
    <div x-show="showApproveModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="showApproveModal = false"
         @wheel.stop
         @touchmove.stop>
        <div class="bg-white rounded-3xl border border-slate-200 max-w-md w-full p-6 space-y-4 shadow-2xl animate-scale-in"
             @click.away="showApproveModal = false">
            
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center text-2xl font-black shrink-0 shadow-2xs">
                    ✓
                </div>
                <div>
                    <h3 class="font-black text-slate-900 text-base">Konfirmasi Persetujuan Toko</h3>
                    <p class="text-xs text-slate-500">Aktivasi Hak Penjual Resmi Mitra</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 text-xs text-emerald-950 space-y-1.5">
                <p>
                    Apakah Anda yakin ingin menyetujui pengajuan toko <strong class="font-extrabold text-slate-900" x-text="selectedStore ? selectedStore.name : ''"></strong>?
                </p>
                <p class="text-[11px] text-emerald-800 leading-relaxed">
                    Pengguna pemohon (<span class="font-bold" x-text="selectedStore ? selectedStore.user_name : ''"></span>) akan langsung aktif sebagai <strong>Penjual</strong> dan dapat segera mengunggah produk.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" 
                        @click="showApproveModal = false" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                    Batal
                </button>
                <form :action="selectedStore ? selectedStore.approve_url : '#'" method="POST">
                    @csrf
                    <button type="submit" 
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <span>Ya, Setujui Sekarang</span>
                        <span>✓</span>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- REJECTION MODAL (WITH CUSTOM MESSAGE) -->
    <div x-show="showRejectModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-[#EAE1D7] max-w-md w-full p-6 space-y-4 shadow-2xl animate-scale-in"
             @click.away="showRejectModal = false">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-rose-600 font-black text-base">
                    <span class="text-2xl">🚫</span>
                    <span>Tolak Pengajuan Toko</span>
                </div>
                <button type="button" @click="showRejectModal = false" class="text-stone-400 hover:text-stone-600 text-sm font-bold cursor-pointer">✕</button>
            </div>

            <p class="text-xs text-[#7A6C60] leading-relaxed">
                Anda akan menolak pengajuan toko <strong class="text-[#2D241E]" x-text="rejectStoreName"></strong>. Silakan masukkan pesan / alasan pembatalan agar calon penjual mengetahui hal yang perlu diperbaiki.
            </p>

            <form :action="'{{ url('admin/stores') }}/' + rejectStoreId + '/reject'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="rejection_reason" class="block text-xs font-bold text-[#2D241E] mb-1.5">
                        Pesan Pembatalan / Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="rejection_reason" 
                              id="rejection_reason" 
                              rows="4" 
                              required 
                              minlength="5" 
                              maxlength="1000"
                              placeholder="Contoh: Nama toko melanggar hak merek dagang, nomor kontak tidak aktif, atau alamat pengiriman tidak lengkap..."
                              class="w-full p-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:outline-hidden focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 leading-relaxed"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" 
                            @click="showRejectModal = false" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-[#5A4B40] font-bold rounded-xl text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl text-xs shadow-xs transition cursor-pointer">
                        Kirim Penolakan 🚫
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- KTP PHOTO PREVIEW MODAL -->
    <div x-show="showKtpModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="showKtpModal = false">
        <div class="bg-white rounded-3xl border border-[#EAE1D7] max-w-2xl w-full p-6 space-y-4 shadow-2xl"
             @click.away="showKtpModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🪪</span>
                    <div>
                        <h3 class="font-extrabold text-sm text-[#2D241E]" x-text="modalKtpTitle"></h3>
                        <p class="text-[11px] text-[#8A7C70]">Dokumen Identitas KTP Pengajuan Toko</p>
                    </div>
                </div>
                <button type="button" @click="showKtpModal = false" class="text-stone-400 hover:text-stone-600 text-base font-bold cursor-pointer px-2 py-1 rounded-lg hover:bg-slate-100">✕</button>
            </div>

            <div class="bg-slate-900 rounded-2xl p-2 flex items-center justify-center min-h-[300px] max-h-[70vh] overflow-auto">
                <img :src="modalKtpUrl" alt="Foto KTP Asli" class="max-w-full max-h-[65vh] object-contain rounded-lg shadow-md">
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-[11px] text-[#8A7C70] flex items-center gap-1">
                    <span>🔒</span> Dokumen ini bersifat rahasia dan terlindungi.
                </span>
                <div class="flex items-center gap-2">
                    <a :href="modalKtpUrl" target="_blank" class="px-4 py-2 bg-[#FAF4ED] hover:bg-[#F5EBE1] text-[#6B4226] font-bold rounded-xl text-xs transition">
                        Buka Ukuran Penuh ↗
                    </a>
                    <button type="button" @click="showKtpModal = false" class="px-4 py-2 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-xl text-xs transition">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
