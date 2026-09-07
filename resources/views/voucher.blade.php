@extends('layouts.app')

@section('title', 'Pusat Voucher Belanja & Bebas Ongkir — NusantaraMart')

@section('content')
<div class="min-h-screen bg-[#FAF8F5] pb-16 space-y-6"
     x-data="{
         activeTab: '{{ $category ?? 'all' }}',
         claimedIds: {{ json_encode($claimedVoucherIds ?? []) }},
         usedIds: {{ json_encode($usedVoucherIds ?? []) }},
         claimingId: null,
         toastMsg: '',
         toastType: 'success',
         showToast: false,

         triggerToast(msg, type = 'success') {
             this.toastMsg = msg;
             this.toastType = type;
             this.showToast = true;
             setTimeout(() => { this.showToast = false; }, 3500);
         },

         claimVoucher(voucherId, voucherName) {
             @guest
                 window.showAuthModal({
                     icon: '🎟️',
                     title: 'Klaim Voucher Belanja',
                     message: 'Yuk masuk ke akunmu terlebih dahulu untuk mengklaim voucher ' + (voucherName ? '"' + voucherName + '"' : 'ini') + ' dan nikmati potongan hematnya saat belanja!'
                 });
                 return;
             @endguest

             if (this.claimedIds.includes(voucherId)) {
                 this.triggerToast('Voucher ini sudah kamu klaim!', 'info');
                 return;
             }

             this.claimingId = voucherId;
             fetch(`/voucher/${voucherId}/claim`, {
                 method: 'POST',
                 headers: {
                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                     'X-Requested-With': 'XMLHttpRequest',
                     'Accept': 'application/json'
                 }
             })
             .then(res => res.json())
             .then(data => {
                 this.claimingId = null;
                 if (data.success || data.already_claimed) {
                     if (!this.claimedIds.includes(voucherId)) {
                         this.claimedIds.push(voucherId);
                     }
                     this.triggerToast(data.message, 'success');
                 } else {
                     this.triggerToast(data.message || 'Gagal mengklaim voucher.', 'error');
                 }
             })
             .catch(err => {
                 this.claimingId = null;
                 this.triggerToast('Terjadi kendala koneksi saat mengklaim voucher.', 'error');
             });
         },

         copyCode(code) {
             navigator.clipboard.writeText(code);
             this.triggerToast('Kode voucher ' + code + ' disalin!', 'success');
         }
     }">

    <!-- Toast Notification -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 max-w-sm"
         style="display: none;">
        <div class="px-4 py-3 rounded-2xl shadow-lg border flex items-center gap-3 text-xs font-bold"
             :class="toastType === 'success' ? 'bg-[#2D241E] text-white border-[#443529]' : (toastType === 'info' ? 'bg-amber-50 text-amber-900 border-amber-200' : 'bg-rose-50 text-rose-900 border-rose-200')">
            <span x-text="toastType === 'success' ? '🎉' : (toastType === 'info' ? 'ℹ️' : '⚠️')"></span>
            <span x-text="toastMsg"></span>
        </div>
    </div>

    <!-- 1. BREADCRUMBS -->
    <div class="bg-white border-b border-[#EAE1D7] py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" 
                   class="w-8 h-8 rounded-full bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0"
                   title="Kembali ke Beranda">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex items-center gap-2 text-xs text-[#8A7C70]">
                    <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition font-medium">Beranda</a>
                    <span>/</span>
                    <span class="text-[#2D241E] font-bold">Voucher Belanja</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- 2. HERO BANNER -->
        <div class="bg-[#2D241E] text-white rounded-2xl p-5 sm:p-7 border border-[#443529] shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <!-- Left: Title & Subtitle -->
                <div class="space-y-2 max-w-xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 text-amber-300 text-xs font-bold tracking-wide">
                        <span>🎟️</span>
                        <span>Pusat Voucher NusantaraMart</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-snug">
                        Klaim Voucher Gratis Ongkir & Diskon Belanja
                    </h1>
                    <p class="text-xs sm:text-sm text-[#C8BCB0] leading-relaxed">
                        Klaim voucher favoritmu dan gunakan saat checkout. Nikmati potongan ongkir hingga Rp 20.000 dan diskon harga barang spesial!
                    </p>
                </div>

                <!-- Right: Benefits -->
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4 shrink-0 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🚚</span>
                        <div>
                            <span class="font-bold block text-white">Voucher Ongkir</span>
                            <span class="text-[#9E9084] text-[11px]">Bebas ongkos kirim</span>
                        </div>
                    </div>
                    <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-base">🏷️</span>
                        <div>
                            <span class="font-bold block text-white">Voucher Diskon</span>
                            <span class="text-[#9E9084] text-[11px]">Potongan harga barang</span>
                        </div>
                    </div>
                    <div class="hidden sm:block w-px h-8 bg-white/10"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-base">⚡</span>
                        <div>
                            <span class="font-bold block text-white">Gabung 2 Voucher</span>
                            <span class="text-[#9E9084] text-[11px]">1 Ongkir + 1 Diskon</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. CATEGORY & TIER TABS -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold hide-scrollbar">
            <!-- Semua -->
            <button type="button" 
                    @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-[#2D241E] text-white shadow-xs' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                    class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
                <span>🎟️ Semua Voucher</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                      :class="activeTab === 'all' ? 'bg-white/20 text-white' : 'bg-[#FAF4ED] text-[#6B4226]'">
                    {{ $totalVouchersCount }}
                </span>
            </button>

            <!-- Kategori Ongkir -->
            <button type="button" 
                    @click="activeTab = 'shipping'"
                    :class="activeTab === 'shipping' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                    class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
                <span>🚚 Bebas Ongkir</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                      :class="activeTab === 'shipping' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'">
                    {{ $totalShippingCount }}
                </span>
            </button>

            <!-- Kategori Diskon -->
            <button type="button" 
                    @click="activeTab = 'discount'"
                    :class="activeTab === 'discount' ? 'bg-[#6B4226] text-white shadow-xs' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                    class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
                <span>🏷️ Diskon Belanja</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                      :class="activeTab === 'discount' ? 'bg-white/20 text-white' : 'bg-[#FAF4ED] text-[#6B4226] border border-[#E8DED3]'">
                    {{ $totalDiscountCount }}
                </span>
            </button>

            <!-- Khusus Tier Member -->
            <button type="button" 
                    @click="activeTab = 'tier'"
                    :class="activeTab === 'tier' ? 'bg-purple-900 text-white shadow-xs' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                    class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
                <span>👑 Khusus Tier Member</span>
            </button>

            <!-- Rutin Mingguan -->
            <button type="button" 
                    @click="activeTab = 'weekly'"
                    :class="activeTab === 'weekly' ? 'bg-amber-800 text-white shadow-xs' : 'bg-white text-[#5A4B40] hover:bg-[#FAF4ED] border border-[#EAE1D7]'"
                    class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
                <span>🔄 Rutin Mingguan</span>
            </button>
        </div>

        <!-- 4. VOUCHER CARDS GRID (TICKET-STYLE CARDS) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($vouchers as $v)
                @php
                    $isTierEligible = Auth::check() ? Auth::user()->meetsTierRequirement($v->member_tier) : true;
                @endphp
                <div x-show="activeTab === 'all' || (activeTab === '{{ $v->category }}') || (activeTab === 'tier' && '{{ $v->member_tier }}' !== 'all') || (activeTab === 'weekly' && {{ $v->is_weekly_recurring ? 'true' : 'false' }})" 
                     x-transition
                     class="bg-white rounded-2xl border border-[#EAE1D7] hover:border-[#6B4226]/40 hover:shadow-md transition duration-200 overflow-hidden flex flex-col justify-between relative group">
                    
                    <!-- Top Ribbon / Category Identifier -->
                    <div class="p-4 sm:p-5 flex items-start gap-4">
                        <!-- Left Icon Badge -->
                        <div class="w-13 h-13 rounded-2xl flex items-center justify-center text-2xl shrink-0 shadow-2xs {{ $v->category === 'shipping' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-[#FAF4ED] text-[#6B4226] border border-[#E8DED3]' }}">
                            {{ $v->category === 'shipping' ? '🚚' : '🏷️' }}
                        </div>

                        <!-- Voucher Info -->
                        <div class="space-y-1.5 min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $v->category === 'shipping' ? 'bg-emerald-100 text-emerald-800' : 'bg-[#FAF4ED] text-[#6B4226] border border-[#E8DED3]' }}">
                                    {{ $v->category_label }}
                                </span>

                                <!-- Member Tier Badges -->
                                @if($v->member_tier === 'platinum')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-purple-100 text-purple-900 border border-purple-200">
                                        👑 Platinum VIP
                                    </span>
                                @elseif($v->member_tier === 'gold')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                        🥇 Gold & Up
                                    </span>
                                @elseif($v->member_tier === 'silver')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-200">
                                        🥈 Silver+
                                    </span>
                                @endif

                                <!-- Weekly Recurring Badge -->
                                @if($v->is_weekly_recurring)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7]">
                                        🔄 Mingguan
                                    </span>
                                @endif

                                <span class="text-[10px] text-[#8A7C70]">•</span>
                                <span class="text-[10px] text-[#8A7C70] font-semibold">{{ $v->formatted_min_spend }}</span>
                            </div>

                            <h3 class="font-extrabold text-sm sm:text-base text-[#2D241E] leading-snug">
                                {{ $v->name }}
                            </h3>

                            <p class="text-[11px] text-[#7A6C60] line-clamp-2 leading-relaxed">
                                {{ $v->description }}
                            </p>
                        </div>
                    </div>

                    <!-- Ticket Perforation / Divider -->
                    <div class="relative flex items-center">
                        <div class="w-3 h-6 bg-[#FAF8F5] border-r border-[#EAE1D7] rounded-r-full -ml-px"></div>
                        <div class="flex-1 border-b border-dashed border-[#EAE1D7] mx-2"></div>
                        <div class="w-3 h-6 bg-[#FAF8F5] border-l border-[#EAE1D7] rounded-l-full -mr-px"></div>
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="p-4 bg-[#FAF8F5]/60 flex items-center justify-between gap-3">
                        <!-- Voucher Code & Copy -->
                        <button type="button" 
                                @click="copyCode('{{ $v->code }}')"
                                title="Klik untuk salin kode voucher"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white border border-[#EAE1D7] text-xs font-mono font-black text-[#2D241E] hover:border-[#6B4226] transition cursor-pointer shadow-2xs">
                            <span>{{ $v->code }}</span>
                            <span class="text-[10px] text-[#8A7C70]">📋</span>
                        </button>

                        <!-- Claim / Use Button -->
                        <div>
                            <template x-if="usedIds.includes({{ $v->id }})">
                                <span class="px-3.5 py-2 rounded-xl bg-gray-100 text-gray-500 text-xs font-bold inline-block">
                                    Sudah Digunakan
                                </span>
                            </template>

                            <template x-if="!usedIds.includes({{ $v->id }}) && claimedIds.includes({{ $v->id }})">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 rounded-md bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200">
                                        ✓ Tersedia
                                    </span>
                                    <a href="{{ route('checkout.show') }}" 
                                       class="px-3.5 py-1.5 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold transition shadow-xs cursor-pointer">
                                        Pakai
                                    </a>
                                </div>
                            </template>

                            <template x-if="!claimedIds.includes({{ $v->id }}) && !usedIds.includes({{ $v->id }})">
                                @if(Auth::check() && ! $isTierEligible)
                                    <button type="button" 
                                            @click="triggerToast('Voucher ini khusus untuk {{ $v->member_tier_label }}. Tingkatkan transaksimu untuk membuka voucher ini!', 'info')"
                                            class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
                                            title="Tingkat member kamu belum memenuhi syarat">
                                        <span>🔒</span>
                                        <span>{{ $v->member_tier_badge }}</span>
                                    </button>
                                @else
                                    <button type="button" 
                                            @click="claimVoucher({{ $v->id }}, '{{ $v->name }}')"
                                            :disabled="claimingId === {{ $v->id }}"
                                            class="px-4 py-1.5 rounded-xl bg-[#6B4226] hover:bg-[#54321B] disabled:opacity-60 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                                        <span x-show="claimingId !== {{ $v->id }}">Klaim Voucher</span>
                                        <span x-show="claimingId === {{ $v->id }}" class="inline-flex items-center gap-1">
                                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span>Mengklaim...</span>
                                        </span>
                                    </button>
                                @endif
                            </template>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- 5. HOW TO USE VOUCHERS IN CHECKOUT GUIDE -->
        <div class="bg-white rounded-2xl border border-[#EAE1D7] p-5 sm:p-6 shadow-2xs space-y-4">
            <h3 class="font-bold text-sm text-[#2D241E] flex items-center gap-2">
                <span>💡</span>
                <span>Cara Menggunakan Voucher di NusantaraMart</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0]">
                    <span class="w-6 h-6 rounded-full bg-[#6B4226] text-white font-black flex items-center justify-center text-xs shrink-0">1</span>
                    <div>
                        <h4 class="font-bold text-[#2D241E]">Klaim Voucher</h4>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5 leading-snug">Klik tombol "Klaim Voucher" di halaman ini untuk memasukkan kupon ke akun kamu.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0]">
                    <span class="w-6 h-6 rounded-full bg-[#6B4226] text-white font-black flex items-center justify-center text-xs shrink-0">2</span>
                    <div>
                        <h4 class="font-bold text-[#2D241E]">Pilih 1 Voucher Ongkir</h4>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5 leading-snug">Di halaman checkout, pilih 1 voucher ongkir untuk memotong atau menggratiskan biaya kirim.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0]">
                    <span class="w-6 h-6 rounded-full bg-[#6B4226] text-white font-black flex items-center justify-center text-xs shrink-0">3</span>
                    <div>
                        <h4 class="font-bold text-[#2D241E]">Pilih 1 Voucher Diskon</h4>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5 leading-snug">Kamu juga dapat memilih 1 voucher diskon untuk memotong harga total belanja barang.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-[#FAF8F5] border border-[#F2EAE0]">
                    <span class="w-6 h-6 rounded-full bg-[#6B4226] text-white font-black flex items-center justify-center text-xs shrink-0">4</span>
                    <div>
                        <h4 class="font-bold text-[#2D241E]">Hemat Dobel & Bayar</h4>
                        <p class="text-[11px] text-[#8A7C70] mt-0.5 leading-snug">Kedua potongan otomatis diaplikasikan secara bersamaan ke ringkasan pembayaran.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
