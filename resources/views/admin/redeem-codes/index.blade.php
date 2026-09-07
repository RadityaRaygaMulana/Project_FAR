@extends('layouts.admin')

@section('title', 'Kelola Kode Redeem Promo — Admin Snackaroo')

@section('content')
<div x-data="{
    showCreateModal: false,
    showEditModal: false,
    showDeleteModal: false,
    editCode: {},
    deleteCode: {},
    openEdit(code) {
        this.editCode = { ...code };
        this.showEditModal = true;
    },
    openDelete(code) {
        this.deleteCode = code;
        this.showDeleteModal = true;
    }
}" x-init="
    $watch('showCreateModal || showEditModal || showDeleteModal', val => {
        document.body.style.overflow = (showCreateModal || showEditModal || showDeleteModal) ? 'hidden' : '';
    })
">

    <!-- PAGE HEADER -->
    <div class="mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900">🎁 Kelola Kode Redeem Promo</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kode yang dimasukkan manual oleh customer saat checkout — terpisah dari sistem voucher klaim.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.vouchers.index') }}"
                   class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    🎟️ Kelola Voucher
                </a>
                <button @click="showCreateModal = true"
                        class="px-5 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-black rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <span class="text-sm">＋</span> Buat Kode Baru
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-semibold text-emerald-800 flex items-center gap-2">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    <!-- TELEMETRY CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#FEF9F0] flex items-center justify-center text-xl">🎁</div>
            <div>
                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Total Kode</p>
                <p class="text-xl font-black text-slate-900">{{ number_format($totalCodes) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-xl">✅</div>
            <div>
                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Aktif</p>
                <p class="text-xl font-black text-emerald-700">{{ number_format($activeCodes) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl">📊</div>
            <div>
                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Total Dipakai</p>
                <p class="text-xl font-black text-blue-700">{{ number_format($totalUsed) }}×</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl">♾️</div>
            <div>
                <p class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Kuota Tak Terbatas</p>
                <p class="text-xl font-black text-purple-700">{{ number_format($noQuotaCodes) }}</p>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider text-[10px]">Kode & Nama</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider text-[10px]">Jenis Diskon</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider text-[10px]">Ongkir</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider text-[10px]">Min. Belanja</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider text-[10px]">Pemakaian</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider text-[10px]">Status</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600 uppercase tracking-wider text-[10px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($codes as $c)
                    <tr class="hover:bg-slate-50/50 transition">
                        <!-- Code & Name -->
                        <td class="px-4 py-3.5">
                            <div class="flex flex-col gap-0.5">
                                <span class="font-mono font-black text-[#6B4226] bg-[#FEF9F0] px-2 py-0.5 rounded-lg text-[11px] inline-block w-fit tracking-widest">
                                    {{ $c->code }}
                                </span>
                                <span class="text-slate-700 font-semibold text-[11px]">{{ $c->name }}</span>
                                @if($c->description)
                                <span class="text-slate-400 text-[10px]">{{ $c->description }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- Discount Type -->
                        <td class="px-4 py-3.5">
                            @if($c->discount_amount > 0)
                                @if($c->discount_type === 'percentage')
                                    <span class="font-bold text-blue-700">{{ $c->discount_amount }}%</span>
                                    @if($c->max_discount)
                                    <span class="text-slate-400 text-[10px] block">maks Rp {{ number_format($c->max_discount) }}</span>
                                    @endif
                                @else
                                    <span class="font-bold text-blue-700">Rp {{ number_format($c->discount_amount) }}</span>
                                @endif
                            @else
                                <span class="text-slate-400 text-[10px]">—</span>
                            @endif
                        </td>

                        <!-- Shipping Discount -->
                        <td class="px-4 py-3.5">
                            @if($c->shipping_discount > 0)
                                <span class="font-bold text-emerald-700">Rp {{ number_format($c->shipping_discount) }}</span>
                            @else
                                <span class="text-slate-400 text-[10px]">—</span>
                            @endif
                        </td>

                        <!-- Min Spend -->
                        <td class="px-4 py-3.5">
                            @if($c->min_spend > 0)
                                <span class="font-semibold text-slate-700">Rp {{ number_format($c->min_spend) }}</span>
                            @else
                                <span class="text-[10px] text-slate-400">Bebas</span>
                            @endif
                        </td>

                        <!-- Usage -->
                        <td class="px-4 py-3.5">
                            <div class="space-y-0.5">
                                <span class="font-semibold text-slate-700">{{ number_format($c->used_count) }}×
                                @if($c->quota)
                                    <span class="text-slate-400">/ {{ number_format($c->quota) }}</span>
                                @else
                                    <span class="text-purple-500 text-[10px]">/ ♾️</span>
                                @endif
                                </span>
                                @if($c->quota && $c->used_count > 0)
                                <div class="w-24 h-1 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#6B4226] rounded-full" style="width: {{ min(100, round(($c->used_count / $c->quota) * 100)) }}%"></div>
                                </div>
                                @endif
                                @if($c->used_count > 0)
                                <form action="{{ route('admin.redeem-codes.reset_used', $c) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[9px] text-[#6B4226] hover:underline font-bold">↺ Reset</button>
                                </form>
                                @endif
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3.5">
                            <form action="{{ route('admin.redeem-codes.toggle', $c) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold cursor-pointer transition {{ $c->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                    {{ $c->is_active ? '✅ Aktif' : '⏸ Nonaktif' }}
                                </button>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1.5">
                                <button @click="openEdit({{ $c->toJson() }})"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[10px] font-bold transition cursor-pointer">
                                    ✏️ Edit
                                </button>
                                <button @click="openDelete({ id: {{ $c->id }}, code: '{{ $c->code }}', delete_url: '{{ route('admin.redeem-codes.destroy', $c) }}' })"
                                        class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-[10px] font-bold transition cursor-pointer">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-xs">
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-3xl">🎁</span>
                                <p class="font-semibold">Belum ada kode redeem</p>
                                <p class="text-[11px]">Klik "Buat Kode Baru" untuk menambahkan kode promo pertama.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: BUAT KODE BARU -->
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showCreateModal = false"
             class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto hide-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden shadow-2xl border border-slate-200 flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🎁</span>
                    <h3 class="font-black text-slate-900 text-base">Buat Kode Redeem Baru</h3>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.redeem-codes.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-[11px] text-amber-800 font-semibold">
                    💡 Kode ini dimasukkan <strong>langsung oleh customer</strong> di kolom "Kode Redeem" saat checkout. Berbeda dari voucher yang diklaim terlebih dahulu.
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Redeem <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" required
                               placeholder="Misal: HEMAT20"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-black uppercase tracking-widest"
                               oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '')">
                        <p class="text-[10px] text-slate-400 mt-1">Hanya huruf kapital, angka, dan underscore (_)</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Deskriptif <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Misal: Diskon 20% Pelanggan Baru"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi <span class="text-slate-400">(opsional)</span></label>
                    <input type="text" name="description" placeholder="Catatan internal untuk admin..."
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <!-- Discount Section -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl space-y-3" x-data="{ dtype: 'fixed' }">
                    <p class="text-xs font-bold text-blue-900">💸 Diskon Harga Produk</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Diskon</label>
                            <select name="discount_type" x-model="dtype"
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="fixed">Nominal Tetap (Rp)</option>
                                <option value="percentage">Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                <span x-text="dtype === 'fixed' ? 'Nominal Potongan (Rp)' : 'Persentase (%)'"></span>
                            </label>
                            <input type="number" name="discount_amount" min="0" value="0"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs">
                        </div>
                    </div>
                    <div x-show="dtype === 'percentage'" x-cloak>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Maks. Potongan (Rp) <span class="text-slate-400">opsional</span></label>
                        <input type="number" name="max_discount" min="0" placeholder="Kosongkan jika tidak ada batas"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <!-- Shipping Discount -->
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-2">
                    <p class="text-xs font-bold text-emerald-900">🚚 Diskon Ongkos Kirim</p>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Potongan Ongkir (Rp)</label>
                        <input type="number" name="shipping_discount" min="0" value="0"
                               class="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-xs">
                        <p class="text-[10px] text-slate-400 mt-1">Set 0 jika tidak ada diskon ongkir</p>
                    </div>
                </div>

                <!-- Conditions -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Belanja (Rp)</label>
                        <input type="number" name="min_spend" min="0" value="0"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kuota Pemakaian</label>
                        <input type="number" name="quota" min="1" placeholder="Kosongkan = tak terbatas"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked id="create_is_active"
                           class="w-4 h-4 text-[#6B4226] rounded border-slate-300 focus:ring-[#6B4226]">
                    <label for="create_is_active" class="text-xs font-bold text-slate-800 cursor-pointer">Langsung Aktifkan Kode</label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-black rounded-xl shadow-xs transition cursor-pointer">
                        Simpan Kode Redeem
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT KODE -->
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showEditModal = false"
             class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto hide-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden shadow-2xl border border-slate-200 flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="font-black text-slate-900 text-base">Edit Kode <span class="font-mono text-[#6B4226]" x-text="editCode.code"></span></h3>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
            </div>

            <!-- Form -->
            <form :action="`/admin/redeem-codes/${editCode.id}`" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Redeem <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" required x-model="editCode.code"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-black uppercase tracking-widest"
                               oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '')">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Deskriptif <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required x-model="editCode.name"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                    <input type="text" name="description" x-model="editCode.description"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                </div>

                <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl space-y-3">
                    <p class="text-xs font-bold text-blue-900">💸 Diskon Harga Produk</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Jenis Diskon</label>
                            <select name="discount_type" x-model="editCode.discount_type"
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="fixed">Nominal Tetap (Rp)</option>
                                <option value="percentage">Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Nilai Diskon</label>
                            <input type="number" name="discount_amount" min="0" x-model="editCode.discount_amount"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs">
                        </div>
                    </div>
                    <div x-show="editCode.discount_type === 'percentage'" x-cloak>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Maks. Potongan (Rp)</label>
                        <input type="number" name="max_discount" min="0" x-model="editCode.max_discount"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                    <p class="text-xs font-bold text-emerald-900 mb-2">🚚 Diskon Ongkos Kirim</p>
                    <input type="number" name="shipping_discount" min="0" x-model="editCode.shipping_discount"
                           class="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-xs">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min. Belanja (Rp)</label>
                        <input type="number" name="min_spend" min="0" x-model="editCode.min_spend"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kuota Pemakaian</label>
                        <input type="number" name="quota" min="1" x-model="editCode.quota" placeholder="Kosongkan = tak terbatas"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" :checked="editCode.is_active" id="edit_is_active"
                           class="w-4 h-4 text-[#6B4226] rounded border-slate-300 focus:ring-[#6B4226]">
                    <label for="edit_is_active" class="text-xs font-bold text-slate-800 cursor-pointer">Status Aktif</label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-black rounded-xl shadow-xs transition cursor-pointer">
                        Perbarui Kode
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: HAPUS KONFIRMASI -->
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.outside="showDeleteModal = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-200 text-center">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto text-2xl">🗑️</div>
            <div>
                <h3 class="text-base font-black text-slate-900">Hapus Kode Redeem?</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Yakin ingin menghapus kode <strong class="font-mono text-slate-800" x-text="deleteCode.code"></strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <form :action="deleteCode.delete_url" method="POST" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                    Ya, Hapus Kode
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
