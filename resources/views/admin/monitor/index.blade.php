@extends('layouts.admin')

@section('title', 'Pengawasan Toko — Admin Panel')

@section('content')
<div class="space-y-6" 
     x-data="{
        suspendModal: {
            open: false,
            type: '',
            id: null,
            name: '',
            duration: 'permanent',
            custom_date: '',
        },
        openSuspendModal(type, id, name) {
            this.suspendModal = {
                open: true,
                type: type,
                id: id,
                name: name,
                duration: 'permanent',
                custom_date: '',
            };
        }
     }">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                🔍 <span>Pengawasan Toko</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitor kesehatan & keamanan seluruh toko mitra secara real-time.</p>
        </div>
        <a href="{{ route('admin.stores') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
            <span>🏬</span> Kelola Mitra Toko
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2"><span>✅</span><span>{{ session('success') }}</span></div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-700 font-bold">✕</button>
        </div>
    @endif
    @if(session('warning'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="p-3.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2"><span>⚠️</span><span>{{ session('warning') }}</span></div>
            <button @click="show = false" class="text-amber-400 hover:text-amber-700 font-bold">✕</button>
        </div>
    @endif

    {{-- SAFETY SCORE SUMMARY CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- Aman --}}
        <a href="{{ request()->fullUrlWithQuery(['safety' => 'safe', 'page' => null]) }}"
           class="bg-white border-2 {{ $filterSafety === 'safe' ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-emerald-200 hover:border-emerald-300' }} rounded-2xl p-4 transition group cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center text-lg">🟢</div>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Aman</span>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $safeCount }}</p>
            <p class="text-[11px] text-slate-500 font-medium mt-0.5">Toko berstatus aman</p>
        </a>

        {{-- Perlu Pantau --}}
        <a href="{{ request()->fullUrlWithQuery(['safety' => 'warning', 'page' => null]) }}"
           class="bg-white border-2 {{ $filterSafety === 'warning' ? 'border-amber-400 ring-2 ring-amber-100' : 'border-amber-200 hover:border-amber-300' }} rounded-2xl p-4 transition group cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-lg">🟡</div>
                <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">Perlu Pantau</span>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $warningCount }}</p>
            <p class="text-[11px] text-slate-500 font-medium mt-0.5">Perlu perhatian lebih</p>
        </a>

        {{-- Berbahaya --}}
        <a href="{{ request()->fullUrlWithQuery(['safety' => 'danger', 'page' => null]) }}"
           class="bg-white border-2 {{ $filterSafety === 'danger' ? 'border-rose-400 ring-2 ring-rose-100' : 'border-rose-200 hover:border-rose-300' }} rounded-2xl p-4 transition group cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center text-lg">🔴</div>
                <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">Berbahaya</span>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $dangerCount }}</p>
            <p class="text-[11px] text-slate-500 font-medium mt-0.5">Risiko tinggi terdeteksi</p>
        </a>

        {{-- Diblokir --}}
        <a href="{{ request()->fullUrlWithQuery(['safety' => 'blocked', 'page' => null]) }}"
           class="bg-white border-2 {{ $filterSafety === 'blocked' ? 'border-slate-600 ring-2 ring-slate-100' : 'border-slate-300 hover:border-slate-400' }} rounded-2xl p-4 transition group cursor-pointer">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-lg">🚫</div>
                <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-300">Ditangguhkan</span>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $blockedCount }}</p>
            <p class="text-[11px] text-slate-500 font-medium mt-0.5">Toko aktif ditangguhkan</p>
        </a>
    </div>

    {{-- SEARCH & FILTER BAR --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.monitor') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">🔍</span>
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Cari nama toko, kota, atau email pemilik..."
                       class="w-full pl-9 pr-4 py-2.5 text-xs font-medium border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] bg-slate-50">
            </div>
            <input type="hidden" name="safety" value="{{ $filterSafety !== 'all' ? $filterSafety : '' }}">
            <button type="submit"
                    class="px-5 py-2.5 bg-[#6B4226] text-white text-xs font-bold rounded-xl hover:bg-[#54321B] transition shadow-xs">
                Cari
            </button>
            @if($search || $filterSafety !== 'all')
                <a href="{{ route('admin.monitor') }}"
                   class="px-4 py-2.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-200 transition text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- STORES TABLE --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
            <p class="text-xs font-bold text-slate-700">
                Menampilkan <span class="text-[#6B4226]">{{ $stores->count() }}</span> dari <span class="text-[#6B4226]">{{ $totalStores }}</span> toko
                @if($filterSafety !== 'all')
                    — Filter: <span class="capitalize font-black">{{ $filterSafety }}</span>
                @endif
            </p>
        </div>

        @if($stores->isEmpty())
            <div class="py-16 text-center">
                <p class="text-4xl mb-3">🏪</p>
                <p class="text-sm font-bold text-slate-600">Tidak ada toko ditemukan</p>
                <p class="text-xs text-slate-400 mt-1">Coba ubah filter atau kata kunci pencarian.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($stores as $store)
                    @php
                        $safetyConfig = match($store->safety_label) {
                            'safe'    => ['bg' => 'bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-400', 'icon' => '🟢', 'label' => 'Aman'],
                            'warning' => ['bg' => 'bg-amber-50',   'badge' => 'bg-amber-100 text-amber-800 border-amber-200',     'dot' => 'bg-amber-400',   'icon' => '🟡', 'label' => 'Perlu Pantau'],
                            'danger'  => ['bg' => 'bg-rose-50',    'badge' => 'bg-rose-100 text-rose-800 border-rose-200',       'dot' => 'bg-rose-400',    'icon' => '🔴', 'label' => 'Berbahaya'],
                            'blocked' => ['bg' => 'bg-slate-50',   'badge' => 'bg-slate-200 text-slate-700 border-slate-300',   'dot' => 'bg-slate-400',   'icon' => '🚫', 'label' => 'Ditangguhkan'],
                            default   => ['bg' => 'bg-white',      'badge' => 'bg-slate-100 text-slate-600 border-slate-200',   'dot' => 'bg-slate-300',   'icon' => '⚪', 'label' => 'Unknown'],
                        };
                        $activeProducts = $store->products->where('is_active', true)->count();
                    @endphp

                    <div class="px-5 py-4 {{ $safetyConfig['bg'] }} hover:bg-opacity-70 transition">
                        <div class="flex flex-col md:flex-row md:items-center gap-4">

                            {{-- Store Info --}}
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                {{-- Logo / Avatar --}}
                                <div class="shrink-0 w-11 h-11 rounded-xl overflow-hidden border border-white shadow-xs bg-white flex items-center justify-center">
                                    @if($store->logo_url)
                                        <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-sm font-black text-[#6B4226]">{{ $store->initials }}</span>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-black text-slate-900 truncate">{{ $store->name }}</p>
                                        {{-- Safety Badge --}}
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $safetyConfig['badge'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $safetyConfig['dot'] }}"></span>
                                            {{ $safetyConfig['icon'] }} {{ $safetyConfig['label'] }}
                                        </span>
                                        {{-- Status badge --}}
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border
                                            {{ $store->status === 'approved' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                               ($store->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-700 border-red-200') }}">
                                            {{ ucfirst($store->status) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                        <span class="text-[11px] text-slate-500">📍 {{ $store->city ?? '—' }}</span>
                                        <span class="text-[11px] text-slate-500">⭐ {{ number_format($store->rating ?? 0, 1) }}</span>
                                        <span class="text-[11px] text-slate-500">📦 {{ $activeProducts }} produk aktif</span>
                                        @if($store->user)
                                            <span class="text-[11px] text-slate-500">👤 {{ $store->user->name }}</span>
                                        @endif
                                    </div>
                                    @if($store->is_suspended && $store->suspension_reason)
                                        <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                                            <div class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 border border-rose-200 rounded-lg">
                                                <span class="text-[10px]">🚫</span>
                                                <span class="text-[10px] font-bold text-rose-700">Alasan: {{ Str::limit($store->suspension_reason, 80) }}</span>
                                            </div>
                                            @if($store->suspended_until)
                                                <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 border border-amber-200 rounded-md text-[10px] font-bold text-amber-900">
                                                    <span>⏳ Berakhir: {{ $store->suspended_until->translatedFormat('d M Y, H:i') }} ({{ $store->suspended_until->diffForHumans() }})</span>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 border border-slate-200 rounded-md text-[10px] font-bold text-slate-700">
                                                    🛡️ Permanen
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    @if($store->user && $store->user->is_suspended)
                                        <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 border border-rose-200 text-rose-800 text-[10px] font-bold rounded-md">
                                                <span>👤 Pemilik Diblokir</span>
                                                @if($store->user->suspended_until)
                                                    <span>(s/d {{ $store->user->suspended_until->translatedFormat('d M Y, H:i') }})</span>
                                                @else
                                                    <span>(Permanen)</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Safety Score Meter --}}
                            <div class="md:w-28 shrink-0">
                                <p class="text-[10px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Safety Score</p>
                                @php
                                    $scoreRaw = $store->safety_score;
                                    $scoreNorm = min(100, max(0, (($scoreRaw + 5) / 10) * 100));
                                    $scoreColor = $store->safety_label === 'safe' ? 'bg-emerald-400' : ($store->safety_label === 'warning' ? 'bg-amber-400' : 'bg-rose-400');
                                @endphp
                                <div class="w-full bg-slate-200 rounded-full h-1.5">
                                    <div class="{{ $scoreColor }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $scoreNorm }}%"></div>
                                </div>
                                <p class="text-[11px] font-bold text-slate-700 mt-0.5">{{ $scoreRaw > 0 ? '+' : '' }}{{ $scoreRaw }} poin</p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                @if($store->is_suspended)
                                    {{-- Unsuspend Store --}}
                                    <form method="POST" action="{{ route('admin.stores.unsuspend', $store) }}">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('Buka tangguhan toko {{ addslashes($store->name) }}?')"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg transition flex items-center gap-1 cursor-pointer">
                                            ✅ Buka Blokir
                                        </button>
                                    </form>
                                @else
                                    {{-- Suspend Store --}}
                                    <button type="button"
                                            @click="openSuspendModal('store', {{ $store->id }}, '{{ addslashes($store->name) }}')"
                                            class="px-3 py-1.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-[11px] font-bold rounded-lg transition flex items-center gap-1 cursor-pointer active:scale-95">
                                        🚫 Tangguhkan
                                    </button>
                                @endif

                                {{-- User actions --}}
                                @if($store->user)
                                    @if($store->user->is_suspended)
                                        <form method="POST" action="{{ route('admin.users.unsuspend', $store->user) }}">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Buka blokir akun {{ addslashes($store->user->name) }}?')"
                                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg transition flex items-center gap-1 cursor-pointer">
                                                👤 Buka Blokir User
                                            </button>
                                        </form>
                                    @else
                                        <button type="button"
                                                @click="openSuspendModal('user', {{ $store->user->id }}, '{{ addslashes($store->user->name) }}')"
                                                class="px-3 py-1.5 bg-slate-600 hover:bg-slate-700 text-white text-[11px] font-bold rounded-lg transition flex items-center gap-1 cursor-pointer active:scale-95">
                                            👤 Blokir User
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- SUSPEND MODAL (PERFECTLY CENTERED, NEVER CUT OFF / OFFSET, AND COKLAT NUSANTARAMART THEMED) --}}
    <div x-show="suspendModal.open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 flex min-h-screen items-center justify-center"
         @click.self="suspendModal.open = false"
         style="display:none">
        
        <div x-show="suspendModal.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative my-auto w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh]">

            {{-- Modal Header (NusantaraMart Chocolate) --}}
            <div class="shrink-0 bg-[#6B4226] px-5 py-4 flex items-center justify-between text-white border-b border-[#54321B]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-xs border border-white/20 flex items-center justify-center text-xl shadow-xs">
                        🚫
                    </div>
                    <div>
                        <p class="text-sm font-black tracking-tight" x-text="suspendModal.type === 'store' ? 'Tangguhkan Akses Toko' : 'Blokir Akun Pengguna'"></p>
                        <p class="text-[11px] text-[#FAF4ED] font-medium" x-text="'Target: ' + suspendModal.name"></p>
                    </div>
                </div>
                <button type="button" @click="suspendModal.open = false" 
                        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-[#FAF4ED] hover:text-white transition flex items-center justify-center font-bold text-sm cursor-pointer">✕</button>
            </div>

            {{-- Modal Body Container (Scrollable) --}}
            <div class="overflow-y-auto p-5 space-y-4">
                {{-- Notice Alert --}}
                <div class="p-3.5 bg-[#FAF4ED] border border-[#EAE1D7] rounded-xl flex items-start gap-2.5">
                    <span class="text-base shrink-0 mt-0.5">⚠️</span>
                    <p class="text-xs font-semibold text-[#54321B] leading-relaxed">
                        Tindakan ini akan membatasi akses <strong class="text-[#6B4226] underline font-bold" x-text="suspendModal.name"></strong>. Target tidak akan dapat beroperasi sampai masa blokir berakhir atau dicabut secara manual oleh admin.
                    </p>
                </div>

                {{-- STORE SUSPEND FORM --}}
                <form x-show="suspendModal.type === 'store'"
                      :action="'{{ url('admin/stores') }}/' + suspendModal.id + '/suspend'"
                      method="POST" class="space-y-4">
                    @csrf

                    {{-- DURATION SELECTION --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Durasi Penangguhan <span class="text-[#6B4226]">*</span></span>
                            <span class="text-[10px] text-slate-400 font-normal">Pilih batas waktu blokir</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === 'permanent' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="permanent" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">♾️</span>
                                <span class="text-[11px] mt-0.5 font-bold">Permanen</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === '1_day' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="1_day" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">⚡</span>
                                <span class="text-[11px] mt-0.5 font-bold">1 Hari</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === '3_days' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="3_days" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">⏱️</span>
                                <span class="text-[11px] mt-0.5 font-bold">3 Hari</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === '7_days' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="7_days" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">📅</span>
                                <span class="text-[11px] mt-0.5 font-bold">7 Hari</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === '30_days' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="30_days" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">🗓️</span>
                                <span class="text-[11px] mt-0.5 font-bold">30 Hari</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === 'custom' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="custom" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">🎯</span>
                                <span class="text-[11px] mt-0.5 font-bold">Kustom</span>
                            </label>
                        </div>

                        {{-- Custom Date Picker --}}
                        <div x-show="suspendModal.duration === 'custom'" x-cloak class="mt-2.5 p-3 bg-[#FAF4ED]/60 border border-[#EAE1D7] rounded-xl">
                            <label class="block text-[11px] font-bold text-[#54321B] mb-1">Pilih Tanggal & Jam Berakhir:</label>
                            <input type="datetime-local" name="custom_date"
                                   class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] bg-white font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">Alasan Penangguhan Toko <span class="text-[#6B4226]">*</span></label>
                        <textarea name="suspension_reason" rows="3" required minlength="5"
                                  placeholder="Jelaskan alasan penangguhan toko ini secara jelas..."
                                  class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] resize-none font-medium text-slate-800"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Minimal 5 karakter. Alasan ini akan ditampilkan ke pemilik toko.</p>
                    </div>

                    <div class="flex gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button" @click="suspendModal.open = false"
                                class="flex-1 py-2.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-200 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                            <span>🚫</span>
                            <span>Tangguhkan Toko</span>
                        </button>
                    </div>
                </form>

                {{-- USER SUSPEND FORM --}}
                <form x-show="suspendModal.type === 'user'"
                      :action="'{{ url('admin/users') }}/' + suspendModal.id + '/suspend'"
                      method="POST" class="space-y-4">
                    @csrf

                    {{-- DURATION SELECTION --}}
                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Durasi Pemblokiran Akun <span class="text-[#6B4226]">*</span></span>
                            <span class="text-[10px] text-slate-400 font-normal">Pilih batas waktu blokir</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === 'permanent' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="permanent" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">♾️</span>
                                <span class="text-[11px] mt-0.5 font-bold">Permanen</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === '1_day' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="1_day" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">⚡</span>
                                <span class="text-[11px] mt-0.5 font-bold">1 Hari</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === '3_days' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="3_days" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">⏱️</span>
                                <span class="text-[11px] mt-0.5 font-bold">3 Hari</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === '7_days' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="7_days" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">📅</span>
                                <span class="text-[11px] mt-0.5 font-bold">7 Hari</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === '30_days' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="30_days" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">🗓️</span>
                                <span class="text-[11px] mt-0.5 font-bold">30 Hari</span>
                            </label>
                            <label class="flex flex-col items-center justify-center p-2.5 rounded-xl border text-center cursor-pointer transition text-xs font-bold"
                                   :class="suspendModal.duration === 'custom' ? 'bg-[#FAF4ED] border-2 border-[#6B4226] text-[#6B4226] shadow-2xs' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="duration" value="custom" x-model="suspendModal.duration" class="sr-only">
                                <span class="text-base">🎯</span>
                                <span class="text-[11px] mt-0.5 font-bold">Kustom</span>
                            </label>
                        </div>

                        {{-- Custom Date Picker --}}
                        <div x-show="suspendModal.duration === 'custom'" x-cloak class="mt-2.5 p-3 bg-[#FAF4ED]/60 border border-[#EAE1D7] rounded-xl">
                            <label class="block text-[11px] font-bold text-[#54321B] mb-1">Pilih Tanggal & Jam Berakhir:</label>
                            <input type="datetime-local" name="custom_date"
                                   class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] bg-white font-medium">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 mb-1.5">Alasan Pemblokiran Akun <span class="text-[#6B4226]">*</span></label>
                        <textarea name="suspension_reason" rows="3" required minlength="5"
                                  placeholder="Jelaskan alasan pemblokiran akun ini secara jelas..."
                                  class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] resize-none font-medium text-slate-800"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Alasan ini akan ditampilkan sebagai card informatif di halaman login pengguna.</p>
                    </div>

                    <div class="flex gap-2.5 pt-2 border-t border-slate-100">
                        <button type="button" @click="suspendModal.open = false"
                                class="flex-1 py-2.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-200 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                            <span>🚫</span>
                            <span>Blokir Akun User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
