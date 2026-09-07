@extends('layouts.admin')

@section('title', 'Kelola Voucher & Jadwal Mingguan — NusantaraMart Admin')

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    showDeleteModal: false,
    editVoucher: {
        id: null,
        code: '',
        name: '',
        category: 'discount',
        type: 'fixed',
        reward_amount: 10000,
        max_discount: null,
        min_spend: 0,
        quota: 1000,
        member_tier: 'all',
        is_weekly_recurring: false,
        weekly_day_rule: 'all',
        start_date: '',
        end_date: '',
        description: '',
        is_active: true,
        update_url: ''
    },
    deleteVoucher: {
        id: null,
        code: '',
        name: '',
        delete_url: ''
    },
    openEdit(voucherData, updateUrl) {
        this.editVoucher = Object.assign({}, voucherData, { update_url: updateUrl });
        this.showEditModal = true;
    },
    openDelete(id, code, name, deleteUrl) {
        this.deleteVoucher = {
            id: id,
            code: code,
            name: name,
            delete_url: deleteUrl
        };
        this.showDeleteModal = true;
    }
}"
x-effect="
    const isLocked = Boolean(showCreateModal || showEditModal || showDeleteModal);
    document.documentElement.style.overflow = isLocked ? 'hidden' : '';
    document.body.style.overflow = isLocked ? 'hidden' : '';
">

    <!-- Top Command & Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#6B4226] transition">Admin Dashboard</a>
                <span>/</span>
                <span class="text-[#6B4226] font-semibold">Voucher & Promosi</span>
            </div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Manajemen Voucher & Jadwal Rutin Mingguan
                </h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-900 border border-amber-300">
                    <span>🎟️</span>
                    <span>3 TIER MEMBER & WEEKLY</span>
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola voucher diskon, bebas ongkir, pengaturan hak akses tier member (Silver, Gold, Platinum), dan jadwal rutin mingguan.
            </p>
        </div>

        <button type="button" 
                @click="showCreateModal = true" 
                class="px-5 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-black rounded-xl shadow-xs transition transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer shrink-0">
            <span class="text-base">+</span>
            <span>Buat Voucher Baru</span>
        </button>
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition
             class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-bold flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="text-lg">✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="show = false" class="text-emerald-700 hover:text-emerald-900">✕</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium space-y-1 shadow-2xs">
            <div class="flex items-center gap-2 font-bold text-rose-900">
                <span>⚠️</span>
                <span>Terdapat kendala pada isian data voucher:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Telemetry Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Voucher</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalVouchers) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Tersedia di katalog</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center text-xl">
                🎟️
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rutin Mingguan</p>
                <p class="text-2xl font-black text-[#6B4226] mt-1">{{ number_format($weeklyRecurringCount) }}</p>
                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">🔄 Auto-renew mingguan</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] flex items-center justify-center text-xl">
                📅
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Voucher Aktif</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($activeCount) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Dapat diklaim pembeli</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center text-xl">
                ✨
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Klaim</p>
                <p class="text-2xl font-black text-blue-600 mt-1">{{ number_format($totalClaimed) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Klaim oleh pelanggan</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center text-xl">
                📊
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
        <form action="{{ route('admin.vouchers.index') }}" method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
                    <input type="text" 
                           name="q" 
                           value="{{ $search }}" 
                           placeholder="Cari kode atau nama..." 
                           class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-[#6B4226]/20">
                </div>

                <!-- Category Filter -->
                <select name="category" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 cursor-pointer focus:outline-hidden">
                    <option value="">Semua Kategori</option>
                    <option value="shipping" {{ $category === 'shipping' ? 'selected' : '' }}>🚚 Ongkir</option>
                    <option value="discount" {{ $category === 'discount' ? 'selected' : '' }}>🏷️ Diskon Belanja</option>
                </select>

                <!-- Member Tier Filter -->
                <select name="tier" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 cursor-pointer focus:outline-hidden">
                    <option value="">Semua Target Tier</option>
                    <option value="all" {{ $tier === 'all' ? 'selected' : '' }}>🌟 Semua Member</option>
                    <option value="silver" {{ $tier === 'silver' ? 'selected' : '' }}>🥈 Silver Member+</option>
                    <option value="gold" {{ $tier === 'gold' ? 'selected' : '' }}>🥇 Gold & Up</option>
                    <option value="platinum" {{ $tier === 'platinum' ? 'selected' : '' }}>👑 Platinum VIP</option>
                </select>

                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 cursor-pointer focus:outline-hidden">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif Tayang</option>
                    <option value="weekly" {{ $status === 'weekly' ? 'selected' : '' }}>🔄 Rutin Mingguan</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Terapkan
                </button>

                @if($search || $category || $tier || $status)
                    <a href="{{ route('admin.vouchers.index') }}" class="px-3 py-2 text-xs font-bold text-rose-600 hover:underline">
                        Reset Filter
                    </a>
                @endif
            </div>

            <div class="text-xs text-slate-500">
                Menampilkan <strong>{{ $vouchers->total() }}</strong> voucher
            </div>
        </form>
    </div>

    <!-- Voucher Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-3.5">Kode & Nama Voucher</th>
                        <th class="px-4 py-3.5">Reward / Potongan</th>
                        <th class="px-4 py-3.5">Target Member Tier</th>
                        <th class="px-4 py-3.5">Jadwal & Rutinitas</th>
                        <th class="px-4 py-3.5">Kuota Klaim</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-slate-50/75 transition">
                            <!-- Code & Name -->
                            <td class="px-4 py-3.5">
                                <div class="flex items-start gap-2.5">
                                    <span class="text-xl shrink-0 mt-0.5">
                                        {{ $v->category === 'shipping' ? '🚚' : '🏷️' }}
                                    </span>
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-mono font-black text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                                {{ $v->code }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $v->category === 'shipping' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7]' }}">
                                                {{ $v->category === 'shipping' ? 'Ongkir' : 'Diskon' }}
                                            </span>
                                        </div>
                                        <p class="font-bold text-slate-900 leading-snug">{{ $v->name }}</p>
                                        @if($v->description)
                                            <p class="text-[11px] text-slate-500 line-clamp-1">{{ $v->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Reward & Min Spend -->
                            <td class="px-4 py-3.5">
                                <div class="space-y-0.5">
                                    <p class="font-black text-[#6B4226]">{{ $v->formatted_reward }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $v->formatted_min_spend }}</p>
                                </div>
                            </td>

                            <!-- Member Tier Target -->
                            <td class="px-4 py-3.5">
                                @if($v->member_tier === 'platinum')
                                    <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-900 border border-purple-200 font-bold text-[11px] inline-flex items-center gap-1">
                                        <span>👑</span>
                                        <span>Platinum VIP</span>
                                    </span>
                                @elseif($v->member_tier === 'gold')
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-900 border border-amber-200 font-bold text-[11px] inline-flex items-center gap-1">
                                        <span>🥇</span>
                                        <span>Gold & Up</span>
                                    </span>
                                @elseif($v->member_tier === 'silver')
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 border border-slate-200 font-bold text-[11px] inline-flex items-center gap-1">
                                        <span>🥈</span>
                                        <span>Silver & Up</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-medium text-[11px] inline-flex items-center gap-1">
                                        <span>🌟</span>
                                        <span>Semua Member</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Weekly Recurring & Dates -->
                            <td class="px-4 py-3.5">
                                <div class="space-y-1">
                                    @if($v->is_weekly_recurring)
                                        <span class="px-2 py-0.5 rounded-md bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] font-bold text-[10px] inline-flex items-center gap-1">
                                            <span>🔄</span>
                                            <span>Rutin Mingguan</span>
                                        </span>
                                        <p class="text-[11px] text-slate-600 font-medium">
                                            @if($v->weekly_day_rule === 'weekdays')
                                                Hari Kerja (Senin - Jumat)
                                            @elseif($v->weekly_day_rule === 'weekends')
                                                Akhir Pekan (Sabtu - Minggu)
                                            @elseif($v->weekly_day_rule === 'monday')
                                                Setiap Hari Senin
                                            @elseif($v->weekly_day_rule === 'friday')
                                                Setiap Hari Jumat
                                            @else
                                                Setiap Hari
                                            @endif
                                        </p>
                                    @else
                                        <span class="text-[11px] text-slate-600">
                                            @if($v->start_date && $v->end_date)
                                                {{ $v->start_date->format('d M') }} - {{ $v->end_date->format('d M Y') }}
                                            @else
                                                Selamanya / Fleksibel
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Quota Progress -->
                            <td class="px-4 py-3.5">
                                <div class="w-36 space-y-1">
                                    @php
                                        $currentClaimed = $v->is_weekly_recurring ? $v->getCurrentPeriodClaimedCount() : $v->claimed_count;
                                        $percentage = $v->quota > 0 ? min(100, round(($currentClaimed / $v->quota) * 100)) : 0;
                                    @endphp
                                    <div class="flex items-center justify-between text-[10px] text-slate-500 font-semibold">
                                        <span>{{ number_format($currentClaimed) }} / {{ number_format($v->quota) }} user</span>
                                        <span>{{ $percentage }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#6B4226] rounded-full" 
                                             style="width: {{ $percentage }}%"></div>
                                    </div>
                                    @if($v->is_weekly_recurring)
                                        <p class="text-[9px] text-[#6B4226] font-bold">🔄 Maks {{ number_format($v->quota) }} user/minggu (1x/user)</p>
                                    @endif
                                    @if($v->is_weekly_recurring && $currentClaimed > 0)
                                        <form action="{{ route('admin.vouchers.reset_quota', $v) }}" method="POST" class="pt-0.5">
                                            @csrf
                                            <button type="submit" class="text-[10px] text-[#6B4226] hover:underline font-bold" title="Reset kuota klaim untuk siklus minggu ini">
                                                ↺ Reset Kuota Minggu Ini
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>

                            <!-- Status Toggle -->
                            <td class="px-4 py-3.5">
                                <form action="{{ route('admin.vouchers.toggle', $v) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold cursor-pointer transition {{ $v->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200' }}"
                                            title="Klik untuk mengubah status aktif/nonaktif">
                                        <span class="w-2 h-2 rounded-full {{ $v->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        <span>{{ $v->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            @click="openEdit({
                                                id: {{ $v->id }},
                                                code: @js($v->code),
                                                name: @js($v->name),
                                                category: @js($v->category),
                                                type: @js($v->type),
                                                reward_amount: {{ (int) $v->reward_amount }},
                                                max_discount: {{ $v->max_discount ? (int) $v->max_discount : 'null' }},
                                                min_spend: {{ (int) $v->min_spend }},
                                                quota: {{ (int) $v->quota }},
                                                member_tier: @js($v->member_tier ?? 'all'),
                                                is_weekly_recurring: {{ $v->is_weekly_recurring ? 'true' : 'false' }},
                                                weekly_day_rule: @js($v->weekly_day_rule ?? 'all'),
                                                start_date: @js($v->start_date ? $v->start_date->format('Y-m-d') : ''),
                                                end_date: @js($v->end_date ? $v->end_date->format('Y-m-d') : ''),
                                                description: @js($v->description ?? ''),
                                                is_active: {{ $v->is_active ? 'true' : 'false' }}
                                            }, @js(route('admin.vouchers.update', $v)))"
                                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-[#FAF4ED] text-slate-700 hover:text-[#6B4226] rounded-lg text-xs font-bold transition">
                                        Edit ✏️
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            @click="openDelete({{ $v->id }}, @js($v->code), @js($v->name), @js(route('admin.vouchers.destroy', $v)))"
                                            class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition">
                                        Hapus 🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <div class="space-y-2">
                                    <span class="text-3xl block">🎟️</span>
                                    <p class="font-bold text-slate-700 text-sm">Belum Ada Voucher</p>
                                    <p class="text-xs">Klik tombol "Buat Voucher Baru" di atas untuk menambahkan voucher baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: TAMBAH VOUCHER BARU -->
    <div x-show="showCreateModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showCreateModal = false"
             class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto hide-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden shadow-2xl border border-slate-200 flex flex-col">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🎟️</span>
                    <h3 class="font-black text-slate-900 text-base">Buat Voucher Promo Baru</h3>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.vouchers.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Kode Voucher -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Kode Voucher <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="code" 
                               required 
                               placeholder="MISAL: DISKONEMAS" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold uppercase focus:ring-2 focus:ring-[#6B4226]/20">
                    </div>

                    <!-- Nama Voucher -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nama Voucher <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               required 
                               placeholder="Nama promo yang menarik..." 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#6B4226]/20">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Kategori -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Voucher</label>
                        <select name="category" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800">
                            <option value="discount">🏷️ Diskon Belanja</option>
                            <option value="shipping">🚚 Potongan / Bebas Ongkir</option>
                        </select>
                    </div>

                    <!-- Tipe Reward -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Potongan</label>
                        <select name="type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800">
                            <option value="fixed">Nominal Tetap (Rp)</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Nilai Reward -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Potongan <span class="text-rose-500">*</span></label>
                        <input type="number" name="reward_amount" required min="1" placeholder="10000 atau 20" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold">
                    </div>

                    <!-- Maksimal Potongan (Rp) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maks. Potongan (Rp)</label>
                        <input type="number" name="max_discount" min="0" placeholder="Opsional (persentase)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>

                    <!-- Min Belanja (Rp) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Belanja (Rp)</label>
                        <input type="number" name="min_spend" min="0" value="0" placeholder="0 = tanpa syarat" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <!-- TARGET TIER MEMBER (3 TIPE MEMBER) -->
                <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-2xl space-y-2">
                    <label class="block text-xs font-black text-amber-950 flex items-center gap-1.5">
                        <span>👑</span>
                        <span>Target Tingkatan Member (Hak Akses Voucher)</span>
                    </label>
                    <p class="text-[11px] text-amber-900/80 leading-relaxed">
                        Tentukan tingkatan member yang berhak mengklaim dan menggunakan voucher ini:
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                        <label class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center gap-2 cursor-pointer hover:border-[#6B4226]">
                            <input type="radio" name="member_tier" value="all" checked class="text-[#6B4226] focus:ring-[#6B4226]">
                            <span class="text-xs font-bold text-slate-800">🌟 Semua</span>
                        </label>
                        <label class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center gap-2 cursor-pointer hover:border-[#6B4226]">
                            <input type="radio" name="member_tier" value="silver" class="text-[#6B4226] focus:ring-[#6B4226]">
                            <span class="text-xs font-bold text-slate-800">🥈 Silver+</span>
                        </label>
                        <label class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center gap-2 cursor-pointer hover:border-[#6B4226]">
                            <input type="radio" name="member_tier" value="gold" class="text-[#6B4226] focus:ring-[#6B4226]">
                            <span class="text-xs font-bold text-slate-800">🥇 Gold+</span>
                        </label>
                        <label class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center gap-2 cursor-pointer hover:border-[#6B4226]">
                            <input type="radio" name="member_tier" value="platinum" class="text-[#6B4226] focus:ring-[#6B4226]">
                            <span class="text-xs font-bold text-purple-900">👑 Platinum</span>
                        </label>
                    </div>
                </div>

                <!-- JADWAL RUTIN MINGGUAN (WEEKLY RECURRING) -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3" x-data="{ isWeekly: false }">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="is_weekly_recurring" value="1" x-model="isWeekly" class="w-4 h-4 text-[#6B4226] border-slate-300 rounded focus:ring-[#6B4226]">
                        <span class="text-xs font-bold text-slate-900">🔄 Aktifkan sebagai Voucher Rutin Mingguan</span>
                    </label>
                    <p class="text-[11px] text-slate-500">
                        Voucher rutin mingguan otomatis berlaku berulang setiap minggu tanpa perlu membuat ulang secara manual.
                    </p>

                    <div x-show="isWeekly" x-cloak class="pt-2 border-t border-slate-200 space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Pilihan Hari Aktif Mingguan:</label>
                        <select name="weekly_day_rule" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800">
                            <option value="all">Setiap Hari dalam Seminggu</option>
                            <option value="weekdays">Hari Kerja Saja (Senin - Jumat)</option>
                            <option value="weekends">Akhir Pekan Saja (Sabtu - Minggu)</option>
                            <option value="monday">Hanya Hari Senin (Senin Ceria)</option>
                            <option value="friday">Hanya Hari Jumat (Jumat Berkah)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Kuota -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Batas Kuota User (Maks User yang Bisa Klaim) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="quota" value="10000" min="1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <p class="text-[10px] text-slate-500 mt-1">
                            Untuk voucher mingguan: batas kuota total user yang dapat mengklaim per minggu (setiap user dibatasi 1x klaim/minggu).
                        </p>
                    </div>

                    <!-- Status Aktif -->
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-[#6B4226] border-slate-300 rounded focus:ring-[#6B4226]">
                            <span class="text-xs font-bold text-slate-800">Langsung Aktifkan Voucher</span>
                        </label>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi & Catatan Voucher</label>
                    <textarea name="description" rows="2" placeholder="Keterangan singkat tentang promo ini..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-black rounded-xl shadow-xs transition">
                        Simpan & Terbitkan Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT VOUCHER -->
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showEditModal = false"
             class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto hide-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden shadow-2xl border border-slate-200 flex flex-col">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="font-black text-slate-900 text-base">Edit Voucher <span class="font-mono text-[#6B4226]" x-text="editVoucher.code"></span></h3>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
            </div>

            <!-- Form -->
            <form :action="editVoucher.update_url" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Kode Voucher -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Voucher</label>
                        <input type="text" name="code" required x-model="editVoucher.code" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold uppercase">
                    </div>

                    <!-- Nama Voucher -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Voucher</label>
                        <input type="text" name="name" required x-model="editVoucher.name" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Voucher</label>
                        <select name="category" x-model="editVoucher.category" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="discount">🏷️ Diskon Belanja</option>
                            <option value="shipping">🚚 Potongan / Bebas Ongkir</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Potongan</label>
                        <select name="type" x-model="editVoucher.type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="fixed">Nominal Tetap (Rp)</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Potongan</label>
                        <input type="number" name="reward_amount" required min="1" x-model="editVoucher.reward_amount" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maks. Potongan (Rp)</label>
                        <input type="number" name="max_discount" min="0" x-model="editVoucher.max_discount" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Belanja (Rp)</label>
                        <input type="number" name="min_spend" min="0" x-model="editVoucher.min_spend" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <!-- Target Tier Member -->
                <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-2xl space-y-2">
                    <label class="block text-xs font-black text-amber-950">Target Tingkatan Member</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                        <label class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="member_tier" value="all" x-model="editVoucher.member_tier" class="text-[#6B4226]">
                            <span class="text-xs font-bold text-slate-800">🌟 Semua</span>
                        </label>
                        <label class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="member_tier" value="silver" x-model="editVoucher.member_tier" class="text-[#6B4226]">
                            <span class="text-xs font-bold text-slate-800">🥈 Silver+</span>
                        </label>
                        <label class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="member_tier" value="gold" x-model="editVoucher.member_tier" class="text-[#6B4226]">
                            <span class="text-xs font-bold text-slate-800">🥇 Gold+</span>
                        </label>
                        <label class="p-2.5 bg-white border border-amber-200 rounded-xl flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="member_tier" value="platinum" x-model="editVoucher.member_tier" class="text-[#6B4226]">
                            <span class="text-xs font-bold text-purple-900">👑 Platinum</span>
                        </label>
                    </div>
                </div>

                <!-- Jadwal Rutin Mingguan -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="is_weekly_recurring" value="1" x-model="editVoucher.is_weekly_recurring" class="w-4 h-4 text-[#6B4226] rounded">
                        <span class="text-xs font-bold text-slate-900">🔄 Aktifkan sebagai Voucher Rutin Mingguan</span>
                    </label>
                    <div x-show="editVoucher.is_weekly_recurring" class="pt-2 border-t border-slate-200 space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Pilihan Hari Aktif Mingguan:</label>
                        <select name="weekly_day_rule" x-model="editVoucher.weekly_day_rule" class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="all">Setiap Hari dalam Seminggu</option>
                            <option value="weekdays">Hari Kerja Saja (Senin - Jumat)</option>
                            <option value="weekends">Akhir Pekan Saja (Sabtu - Minggu)</option>
                            <option value="monday">Hanya Hari Senin (Senin Ceria)</option>
                            <option value="friday">Hanya Hari Jumat (Jumat Berkah)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Batas Kuota User (Maks User yang Bisa Klaim) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="quota" min="1" x-model="editVoucher.quota" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <p class="text-[10px] text-slate-500 mt-1">
                            Untuk voucher mingguan: batas kuota total user yang dapat mengklaim per minggu (setiap user dibatasi 1x klaim/minggu).
                        </p>
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" :checked="editVoucher.is_active" class="w-4 h-4 text-[#6B4226] rounded">
                            <span class="text-xs font-bold text-slate-800">Status Aktif Tayang</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" x-model="editVoucher.description" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-black rounded-xl shadow-xs transition">
                        Perbarui Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: KONFIRMASI HAPUS -->
    <div x-show="showDeleteModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showDeleteModal = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-200 text-center">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto text-2xl">
                🗑️
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">Hapus Voucher?</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Apakah kamu yakin ingin menghapus voucher <strong class="font-mono text-slate-800" x-text="deleteVoucher.code"></strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <form :action="deleteVoucher.delete_url" method="POST" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Ya, Hapus Voucher
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
