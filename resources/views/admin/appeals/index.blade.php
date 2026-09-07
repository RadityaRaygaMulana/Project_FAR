@extends('layouts.admin')

@section('title', 'Pengajuan Banding — Admin Panel')

@section('content')
<div class="space-y-6" x-data="appealsManager()">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-black uppercase tracking-wider text-[#6B4226] bg-[#FAF4ED] px-2.5 py-0.5 rounded-full border border-[#EAE1D7]">
                    Moderasi & Keadilan
                </span>
                <span class="text-slate-300">•</span>
                <span class="text-xs text-slate-500 font-medium">Banding Akun & Toko</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>⚖️</span>
                <span>Pengajuan Banding Penangguhan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Tinjau permohonan pembelaan dari pengguna atau mitra toko yang akunnya sedang dinonaktifkan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.monitor') }}"
               class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs">
                <span>🔍</span>
                <span>Pengawasan Toko</span>
            </a>
            <a href="{{ route('admin.users') }}"
               class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs">
                <span>👥</span>
                <span>Kelola Pengguna</span>
            </a>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-xs font-bold text-emerald-800 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="text-base">✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-sm font-bold cursor-pointer">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center justify-between text-xs font-bold text-rose-800 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="text-base">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-sm font-bold cursor-pointer">✕</button>
        </div>
    @endif

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.appeals.index') }}"
           class="p-4 bg-white rounded-2xl border transition shadow-2xs hover:shadow-xs {{ $status === 'all' ? 'border-[#6B4226] ring-2 ring-[#6B4226]/10' : 'border-slate-200' }}">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-lg">📋</div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">Total</span>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $counts['total'] }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5 font-medium">Semua Pengajuan</p>
        </a>

        <a href="{{ route('admin.appeals.index', ['status' => 'pending']) }}"
           class="p-4 bg-white rounded-2xl border transition shadow-2xs hover:shadow-xs {{ $status === 'pending' ? 'border-amber-400 ring-2 ring-amber-400/20' : 'border-slate-200' }}">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-lg">⏳</div>
                <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">Menunggu</span>
            </div>
            <p class="text-2xl font-black text-amber-900">{{ $counts['pending'] }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5 font-medium">Perlu Tinjauan</p>
        </a>

        <a href="{{ route('admin.appeals.index', ['status' => 'approved']) }}"
           class="p-4 bg-white rounded-2xl border transition shadow-2xs hover:shadow-xs {{ $status === 'approved' ? 'border-emerald-400 ring-2 ring-emerald-400/20' : 'border-slate-200' }}">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center text-lg">✅</div>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Disetujui</span>
            </div>
            <p class="text-2xl font-black text-emerald-900">{{ $counts['approved'] }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5 font-medium">Blokir Dicabut</p>
        </a>

        <a href="{{ route('admin.appeals.index', ['status' => 'rejected']) }}"
           class="p-4 bg-white rounded-2xl border transition shadow-2xs hover:shadow-xs {{ $status === 'rejected' ? 'border-rose-400 ring-2 ring-rose-400/20' : 'border-slate-200' }}">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center text-lg">❌</div>
                <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">Ditolak</span>
            </div>
            <p class="text-2xl font-black text-rose-900">{{ $counts['rejected'] }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5 font-medium">Tetap Ditangguhkan</p>
        </a>
    </div>

    {{-- FILTER & SEARCH BAR --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0">
            <a href="{{ route('admin.appeals.index', array_merge(request()->query(), ['status' => 'all'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'all' ? 'bg-[#6B4226] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua ({{ $counts['total'] }})
            </a>
            <a href="{{ route('admin.appeals.index', array_merge(request()->query(), ['status' => 'pending'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 {{ $status === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                <span>⏳ Menunggu</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'pending' ? 'bg-white text-amber-800' : 'bg-amber-100 text-amber-900' }}">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('admin.appeals.index', array_merge(request()->query(), ['status' => 'approved'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Disetujui ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('admin.appeals.index', array_merge(request()->query(), ['status' => 'rejected'])) }}"
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Ditolak ({{ $counts['rejected'] }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.appeals.index') }}" class="w-full sm:w-72 flex items-center gap-2">
            @if($status !== 'all')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative w-full">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari pemohon / akun..."
                       class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] bg-slate-50 font-medium">
                <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs">🔍</span>
            </div>
            <button type="submit" class="px-3 py-2 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition cursor-pointer shrink-0">
                Cari
            </button>
            @if($search)
                <a href="{{ route('admin.appeals.index', ['status' => $status]) }}" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                    ✕
                </a>
            @endif
        </form>
    </div>

    {{-- APPEALS LIST --}}
    @if($appeals->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-2xs">
            <div class="w-16 h-16 rounded-2xl bg-[#FAF4ED] text-[#6B4226] flex items-center justify-center text-3xl mx-auto mb-3 border border-[#EAE1D7]">
                ⚖️
            </div>
            <h3 class="text-sm font-black text-slate-800">Belum Ada Pengajuan Banding</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                {{ $status !== 'all' ? 'Tidak ada pengajuan banding dengan status "'.ucfirst($status).'".' : 'Saat ini belum ada pengguna atau toko yang mengajukan permohonan banding.' }}
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($appeals as $appeal)
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all duration-200 overflow-hidden">
                    
                    {{-- 1. CARD TOP HEADER --}}
                    <div class="bg-slate-50/80 px-5 sm:px-6 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            {{-- Avatar Initial --}}
                            <div class="w-10 h-10 rounded-2xl bg-linear-to-br from-[#6B4226] to-[#54321B] text-white flex items-center justify-center font-black text-sm shrink-0 shadow-2xs ring-2 ring-white">
                                {{ strtoupper(substr($appeal->applicant_name, 0, 1)) }}
                            </div>
                            
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-sm font-black text-slate-900 truncate">{{ $appeal->applicant_name }}</h2>
                                    @if($appeal->user)
                                        <span class="text-xs font-bold text-[#6B4226] bg-[#FAF4ED] px-2 py-0.5 rounded-lg border border-[#EAE1D7]">
                                            @<span>{{ $appeal->user->username }}</span>
                                        </span>
                                    @endif
                                    
                                    {{-- Type badge --}}
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $appeal->type === 'store' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-900 border border-blue-300' }}">
                                        {{ $appeal->type === 'store' ? '🏪 Toko Mitra' : '👤 Pengguna' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5 flex-wrap">
                                    <span>📧 {{ $appeal->applicant_email }}</span>
                                    @if($appeal->applicant_phone)
                                        <span class="text-slate-300">•</span>
                                        <span>📱 {{ $appeal->applicant_phone }}</span>
                                    @endif
                                    @if($appeal->store)
                                        <span class="text-slate-300">•</span>
                                        <span class="font-bold text-amber-800">🏪 Toko: {{ $appeal->store->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Right: Status & Time --}}
                        <div class="flex items-center gap-2.5 self-start sm:self-auto">
                            <span class="text-[11px] text-slate-400 font-medium hidden md:inline">
                                🕒 {{ $appeal->created_at->translatedFormat('d M Y, H:i') }} ({{ $appeal->created_at->diffForHumans() }})
                            </span>

                            @if($appeal->isPending())
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-amber-50 text-amber-800 border border-amber-300 flex items-center gap-1.5 shadow-2xs">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Menunggu Tinjauan</span>
                                </span>
                            @elseif($appeal->isApproved())
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-800 border border-emerald-300 flex items-center gap-1.5 shadow-2xs">
                                    <span>✅</span>
                                    <span>Disetujui</span>
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-rose-50 text-rose-800 border border-rose-300 flex items-center gap-1.5 shadow-2xs">
                                    <span>❌</span>
                                    <span>Ditolak</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- 2. CARD CENTER BODY: 2-COLUMN COMPARISON --}}
                    <div class="p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        
                        {{-- Left Column: Original Suspension Reason --}}
                        @php
                            $origReason = $appeal->user?->suspension_reason ?? $appeal->store?->suspension_reason;
                            $userModel = $appeal->user;
                        @endphp
                        <div class="bg-rose-50/40 border border-rose-200/70 rounded-2xl p-4 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-rose-100">
                                    <div class="flex items-center gap-1.5 text-rose-800">
                                        <span class="text-sm">🚨</span>
                                        <span class="text-[11px] font-black uppercase tracking-wider">Alasan Pemblokiran Awal</span>
                                    </div>
                                    @if($userModel && $userModel->suspended_until)
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-300 rounded-md text-[10px] font-black">
                                            ⏳ s/d {{ $userModel->suspended_until->translatedFormat('d M Y') }}
                                        </span>
                                    @elseif($userModel && $userModel->is_suspended)
                                        <span class="px-2 py-0.5 bg-rose-100 text-rose-900 border border-rose-300 rounded-md text-[10px] font-black">
                                            🛡️ Permanen
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-rose-950 font-medium leading-relaxed italic">
                                    "{{ $origReason ?? 'Pelanggaran ketentuan layanan sistem.' }}"
                                </p>
                            </div>
                            <p class="text-[10px] text-rose-500 mt-3 font-semibold">Tercatat oleh Administrator pada saat penangguhan</p>
                        </div>

                        {{-- Right Column: Applicant Defense / Appeal --}}
                        <div class="bg-[#FAF4ED]/60 border border-[#EAE1D7] rounded-2xl p-4 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-[#EAE1D7]">
                                    <div class="flex items-center gap-1.5 text-[#6B4226]">
                                        <span class="text-sm">⚖️</span>
                                        <span class="text-[11px] font-black uppercase tracking-wider">Pembelaan dari Pemohon</span>
                                    </div>
                                    @if($appeal->attachment)
                                        <a href="{{ asset('storage/'.$appeal->attachment) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-white border border-[#EAE1D7] rounded-lg text-[10px] font-black text-[#6B4226] hover:bg-[#FAF4ED] transition shadow-2xs">
                                            <span>📎</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    @endif
                                </div>
                                <p class="text-xs text-[#2D241E] font-semibold leading-relaxed whitespace-pre-line">
                                    "{{ $appeal->reason }}"
                                </p>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-[#7A6C60] mt-3 pt-2 border-t border-[#EAE1D7]/50">
                                <span>Diajukan oleh: <strong class="text-[#2D241E]">{{ $appeal->applicant_name }}</strong></span>
                                <span class="md:hidden">🕒 {{ $appeal->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                    </div>

                    {{-- 3. CARD BOTTOM ACTION & AUDIT FOOTER --}}
                    <div class="px-5 sm:px-6 py-3.5 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        @if($appeal->isPending())
                            <p class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                                <span class="text-sm">💡</span>
                                <span>Tinjau kedua alasan di atas dengan seksama sebelum mengambil keputusan.</span>
                            </p>

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <button type="button"
                                        @click="openRejectModal({{ $appeal->id }}, '{{ addslashes($appeal->applicant_name) }}')"
                                        class="flex-1 sm:flex-none py-2 px-4 bg-white hover:bg-rose-50 text-rose-700 hover:text-rose-800 text-xs font-bold rounded-xl border border-rose-200 hover:border-rose-300 transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer active:scale-95">
                                    <span>❌</span>
                                    <span>Tolak Banding</span>
                                </button>

                                <button type="button"
                                        @click="openApproveModal({{ $appeal->id }}, '{{ addslashes($appeal->applicant_name) }}')"
                                        class="flex-1 sm:flex-none py-2 px-4.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                                    <span>✅</span>
                                    <span>Terima & Pulihkan Akun</span>
                                </button>
                            </div>
                        @else
                            {{-- Already reviewed --}}
                            <div class="w-full flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <span class="font-bold">{{ $appeal->status === 'approved' ? '✅ Disetujui' : '❌ Ditolak' }} oleh: {{ $appeal->reviewer?->name ?? 'Administrator' }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-slate-400">{{ $appeal->reviewed_at ? $appeal->reviewed_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                                </div>
                                <div class="text-slate-700 font-medium bg-white px-3 py-1.5 rounded-xl border border-slate-200">
                                    Catatan Admin: <span class="italic font-semibold text-slate-900">"{{ $appeal->admin_notes ?? '-' }}"</span>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

        @if($appeals->hasPages())
            <div class="mt-4 p-4 bg-white rounded-2xl border border-slate-200 shadow-2xs">
                {{ $appeals->links() }}
            </div>
        @endif
    @endif

    {{-- 1. APPROVE APPEAL MODAL --}}
    <div x-show="approveModal.open"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 flex min-h-screen items-center justify-center"
         @click.self="approveModal.open = false"
         style="display:none">
        <div class="relative my-auto w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
            <div class="bg-emerald-600 px-5 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl">✅</div>
                    <div>
                        <h3 class="text-sm font-black">Setujui & Cabut Blokir</h3>
                        <p class="text-[11px] text-emerald-100" x-text="'Pemohon: ' + approveModal.name"></p>
                    </div>
                </div>
                <button type="button" @click="approveModal.open = false" class="text-white hover:text-emerald-200 font-bold">✕</button>
            </div>

            <form :action="'{{ url('admin/appeals') }}/' + approveModal.id + '/approve'" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-900 leading-relaxed">
                    Tindakan ini akan menyetujui permohonan banding dan <strong>otomatis mencabut status pemblokiran</strong> sehingga akun/toko dapat beroperasi kembali secara normal.
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-700 mb-1">Catatan Persetujuan (Opsional)</label>
                    <textarea name="admin_notes" rows="3"
                              placeholder="Contoh: Banding diterima setelah klarifikasi..."
                              class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 resize-none font-medium"></textarea>
                </div>

                <div class="flex gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" @click="approveModal.open = false"
                            class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                        ✅ Ya, Terima Banding
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. REJECT APPEAL MODAL --}}
    <div x-show="rejectModal.open"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 flex min-h-screen items-center justify-center"
         @click.self="rejectModal.open = false"
         style="display:none">
        <div class="relative my-auto w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
            <div class="bg-rose-600 px-5 py-4 flex items-center justify-between text-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl">❌</div>
                    <div>
                        <h3 class="text-sm font-black">Tolak Pengajuan Banding</h3>
                        <p class="text-[11px] text-rose-100" x-text="'Pemohon: ' + rejectModal.name"></p>
                    </div>
                </div>
                <button type="button" @click="rejectModal.open = false" class="text-white hover:text-rose-200 font-bold">✕</button>
            </div>

            <form :action="'{{ url('admin/appeals') }}/' + rejectModal.id + '/reject'" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-900 leading-relaxed">
                    Pengajuan banding akan ditolak dan status akun tetap diblokir. Harap berikan alasan penolakan secara jelas.
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-700 mb-1">Alasan Penolakan Banding <span class="text-rose-500">*</span></label>
                    <textarea name="admin_notes" rows="3" required minlength="5"
                              placeholder="Jelaskan alasan mengapa permohonan banding ini ditolak..."
                              class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-300 resize-none font-medium"></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Minimal 5 karakter. Alasan ini akan tercatat dalam sistem.</p>
                </div>

                <div class="flex gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" @click="rejectModal.open = false"
                            class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                        ❌ Tolak Banding
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function appealsManager() {
        return {
            approveModal: {
                open: false,
                id: null,
                name: ''
            },
            rejectModal: {
                open: false,
                id: null,
                name: ''
            },
            openApproveModal(id, name) {
                this.approveModal = {
                    open: true,
                    id: id,
                    name: name
                };
            },
            openRejectModal(id, name) {
                this.rejectModal = {
                    open: true,
                    id: id,
                    name: name
                };
            }
        };
    }
</script>
@endsection
