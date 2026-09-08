@extends('layouts.app')

@section('title', 'Checkout Pembayaran — NusantaraMart')

@section('content')
<script>
function checkoutPage() {
    return {
        isSubmitting: false,
        cartItems: [],
        paymentMethod: 'qris',
        selectedShippingVoucher: null,
        selectedDiscountVoucher: null,
        showVoucherModal: false,
        voucherModalTab: 'shipping',
        shippingVouchersList: {!! json_encode($allShippingVouchers ?? []) !!},
        discountVouchersList: {!! json_encode($allDiscountVouchers ?? []) !!},
        claimedVoucherCodes: {!! json_encode(isset($claimedShippingVouchers, $claimedDiscountVouchers) ? $claimedShippingVouchers->pluck('code')->merge($claimedDiscountVouchers->pluck('code'))->toArray() : []) !!},
        productPaymentMethodsMap: {!! json_encode($productPaymentMethods ?? []) !!},
        userOwnedProductIds: {!! json_encode($userOwnedProductIds ?? []) !!},
        productShippingSettings: {!! json_encode($productShippingSettings ?? []) !!},
        userMemberTier: @js($userMemberTier ?? 'silver'),
        voucherInputCode: '',
        voucherModalMsg: '',
        voucherModalMsgType: 'info',
        isClaimingModalVoucher: false,

        // Sistem Kode Redeem (Terpisah dari Voucher)
        redeemInput: '',
        appliedRedeemCode: '',
        redeemDiscountAmount: 0,
        redeemShippingDiscount: 0,
        redeemMsg: '',
        redeemMsgType: '',

        initCheckout() {
            const authId = {{ Auth::id() ? Auth::id() : 'null' }};
            const key = authId ? ('nusantaramart_cart_user_' + authId) : 'nusantaramart_cart_guest';
            let saved = localStorage.getItem(key);
            if (!saved) {
                saved = localStorage.getItem('nusantaramart_cart_guest') || localStorage.getItem('nusantaramart_cart') || localStorage.getItem('snackaroo_cart');
            }
            if (saved) {
                try {
                    this.cartItems = JSON.parse(saved);
                } catch(e) {
                    this.cartItems = [];
                }
            }
            if ((!this.cartItems || this.cartItems.length === 0) && window.snackCart && Array.isArray(window.snackCart.items) && window.snackCart.items.length > 0) {
                this.cartItems = window.snackCart.items;
            }

            if (Array.isArray(this.cartItems)) {
                this.cartItems.forEach(i => {
                    if (i.selected === undefined) {
                        i.selected = true;
                    }
                });
            }

            // Auto pre-select first claimed shipping voucher if available
            const claimedShip = this.shippingVouchersList.find(v => this.claimedVoucherCodes.includes(v.code));
            if (claimedShip) {
                this.selectedShippingVoucher = claimedShip;
            }
            // Auto pre-select first claimed discount voucher if available
            const claimedDisc = this.discountVouchersList.find(v => this.claimedVoucherCodes.includes(v.code));
            if (claimedDisc) {
                this.selectedDiscountVoucher = claimedDisc;
            }

            this.ensureValidPaymentMethod();
        },

        isPaymentMethodAvailable(method) {
            if (!this.checkoutItems || this.checkoutItems.length === 0) return true;
            return this.checkoutItems.every(item => {
                const allowed = item.allowed_payment_methods 
                    || (this.productPaymentMethodsMap && this.productPaymentMethodsMap[item.id]) 
                    || ['qris', 'cod'];
                return Array.isArray(allowed) && allowed.includes(method);
            });
        },

        ensureValidPaymentMethod() {
            const supportedMethods = ['qris', 'cod'].filter(m => this.isPaymentMethodAvailable(m));
            if (supportedMethods.length > 0 && !this.isPaymentMethodAvailable(this.paymentMethod)) {
                this.paymentMethod = supportedMethods[0];
            }
        },

        get hasOwnProductInCheckout() {
            if (!Array.isArray(this.userOwnedProductIds) || this.userOwnedProductIds.length === 0) return false;
            return this.checkoutItems.some(i => this.userOwnedProductIds.includes(Number(i.id)));
        },

        get ownProductNames() {
            if (!this.hasOwnProductInCheckout) return '';
            return this.checkoutItems
                .filter(i => this.userOwnedProductIds.includes(Number(i.id)))
                .map(i => i.name)
                .join(', ');
        },

        get checkoutItems() {
            const list = (Array.isArray(this.cartItems) && this.cartItems.length > 0) ? this.cartItems : [];
            return list.filter(i => i.selected !== false);
        },
        get checkoutCount() {
            return this.checkoutItems.reduce((sum, i) => sum + i.quantity, 0);
        },
        get checkoutAmount() {
            return this.checkoutItems.reduce((sum, i) => sum + (i.price * i.quantity), 0);
        },
        get rawShippingCost() {
            if (this.checkoutAmount >= 100000) return 0;
            if (this.checkoutItems.length > 0) {
                const allFreeShipping = this.checkoutItems.every(i => {
                    const setting = this.productShippingSettings[i.id] || {};
                    const isFree = (i.is_free_shipping !== undefined) ? i.is_free_shipping : (setting.is_free_shipping || false);
                    const minSpend = (i.free_shipping_min_spend !== undefined) ? i.free_shipping_min_spend : (setting.free_shipping_min_spend || 0);
                    return isFree && (minSpend === 0 || this.checkoutAmount >= minSpend);
                });
                if (allFreeShipping) return 0;
            }
            return 15000;
        },
        get shippingVoucherDiscount() {
            if (!this.selectedShippingVoucher) return 0;
            if (this.checkoutAmount < (this.selectedShippingVoucher.min_spend || 0)) return 0;
            const cost = this.rawShippingCost;
            if (cost === 0) return 0;

            if (this.selectedShippingVoucher.type === 'percentage') {
                let disc = Math.round(cost * (this.selectedShippingVoucher.reward_amount / 100));
                if (this.selectedShippingVoucher.max_discount) {
                    disc = Math.min(disc, this.selectedShippingVoucher.max_discount);
                }
                return Math.min(cost, disc);
            }
            return Math.min(cost, this.selectedShippingVoucher.reward_amount);
        },
        get shippingDiscount() {
            return this.shippingVoucherDiscount;
        },
        get totalShippingDiscount() {
            const cost = this.rawShippingCost;
            return Math.min(cost, this.shippingVoucherDiscount + this.redeemShippingDiscount);
        },
        get finalShippingCost() {
            return Math.max(0, this.rawShippingCost - this.totalShippingDiscount);
        },
        get voucherEligibleAmount() {
            return this.checkoutItems.reduce((sum, i) => {
                const setting = this.productShippingSettings[i.id] || {};
                const allows = (i.allow_vouchers !== undefined) ? i.allow_vouchers : (setting.allow_vouchers !== undefined ? setting.allow_vouchers : true);
                return allows ? sum + (i.price * i.quantity) : sum;
            }, 0);
        },
        get productVoucherDiscount() {
            if (!this.selectedDiscountVoucher) return 0;
            const eligibleSubtotal = this.voucherEligibleAmount;
            if (eligibleSubtotal <= 0) return 0;
            if (eligibleSubtotal < (this.selectedDiscountVoucher.min_spend || 0)) return 0;

            if (this.selectedDiscountVoucher.type === 'percentage') {
                let disc = Math.round(eligibleSubtotal * (this.selectedDiscountVoucher.reward_amount / 100));
                if (this.selectedDiscountVoucher.max_discount) {
                    disc = Math.min(disc, this.selectedDiscountVoucher.max_discount);
                }
                return Math.min(eligibleSubtotal, disc);
            }
            return Math.min(eligibleSubtotal, this.selectedDiscountVoucher.reward_amount);
        },
        get productDiscount() {
            return this.productVoucherDiscount;
        },
        get redeemDiscountTotal() {
            return this.redeemDiscountAmount + (this.redeemShippingDiscount > 0 && this.rawShippingCost > 0 ? Math.min(this.rawShippingCost, this.redeemShippingDiscount) : 0);
        },
        get grandTotal() {
            const base = this.checkoutAmount + this.finalShippingCost - this.productVoucherDiscount - this.redeemDiscountAmount;
            return Math.max(0, base);
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },

        openVoucherModal(tab = 'shipping') {
            this.voucherModalTab = tab;
            this.voucherInputCode = '';
            this.voucherModalMsg = '';
            this.showVoucherModal = true;
        },

        selectShippingVoucher(v) {
            if (this.selectedShippingVoucher && this.selectedShippingVoucher.id === v.id) {
                this.selectedShippingVoucher = null;
            } else {
                this.selectedShippingVoucher = v;
            }
        },

        selectDiscountVoucher(v) {
            if (this.selectedDiscountVoucher && this.selectedDiscountVoucher.id === v.id) {
                this.selectedDiscountVoucher = null;
            } else {
                this.selectedDiscountVoucher = v;
            }
        },

        isTierEligible(v) {
            if (!v || !v.member_tier || v.member_tier === 'all') return true;
            const hierarchy = { silver: 1, gold: 2, platinum: 3 };
            const userLevel = hierarchy[this.userMemberTier] || 1;
            const reqLevel = hierarchy[v.member_tier.toLowerCase()] || 1;
            return userLevel >= reqLevel;
        },

        claimAndSelectVoucher(v) {
            if (!this.isTierEligible(v)) {
                this.voucherModalMsg = 'Voucher ini khusus untuk member ' + (v.member_tier === 'platinum' ? 'Platinum VIP 👑' : (v.member_tier === 'gold' ? 'Gold 🥇' : 'Silver 🥈')) + '. Tingkat member kamu saat ini adalah ' + (this.userMemberTier || 'Silver').toUpperCase() + '.';
                this.voucherModalMsgType = 'error';
                return;
            }

            this.isClaimingModalVoucher = true;
            fetch(`/voucher/${v.id}/claim`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.isClaimingModalVoucher = false;
                if (data.success || data.already_claimed) {
                    if (!this.claimedVoucherCodes.includes(v.code)) {
                        this.claimedVoucherCodes.push(v.code);
                    }
                    if (v.category === 'shipping') {
                        this.selectedShippingVoucher = v;
                    } else {
                        this.selectedDiscountVoucher = v;
                    }
                    this.voucherModalMsg = 'Voucher ' + v.name + ' berhasil diklaim & dipilih!';
                    this.voucherModalMsgType = 'success';
                } else {
                    this.voucherModalMsg = data.message || 'Gagal mengklaim voucher.';
                    this.voucherModalMsgType = 'error';
                }
            })
            .catch(() => {
                this.isClaimingModalVoucher = false;
                this.voucherModalMsg = 'Gagal mengklaim voucher.';
                this.voucherModalMsgType = 'error';
            });
        },

        applyManualVoucherCode() {
            const code = this.voucherInputCode.trim().toUpperCase();
            if (!code) return;

            const shipMatch = this.shippingVouchersList.find(v => v.code === code);
            if (shipMatch) {
                this.selectedShippingVoucher = shipMatch;
                if (!this.claimedVoucherCodes.includes(code)) this.claimedVoucherCodes.push(code);
                this.voucherModalTab = 'shipping';
                this.voucherModalMsg = 'Voucher Gratis Ongkir ' + shipMatch.code + ' berhasil dipilih!';
                this.voucherModalMsgType = 'success';
                return;
            }

            const discMatch = this.discountVouchersList.find(v => v.code === code);
            if (discMatch) {
                this.selectedDiscountVoucher = discMatch;
                if (!this.claimedVoucherCodes.includes(code)) this.claimedVoucherCodes.push(code);
                this.voucherModalTab = 'discount';
                this.voucherModalMsg = 'Voucher Diskon Belanja ' + discMatch.code + ' berhasil dipilih!';
                this.voucherModalMsgType = 'success';
                return;
            }

            this.voucherModalMsg = 'Kode voucher ' + code + ' tidak ditemukan atau tidak aktif.';
            this.voucherModalMsgType = 'error';
        },

        applyRedeemCode() {
            const code = this.redeemInput.trim().toUpperCase();
            if (!code) {
                this.redeemMsg = 'Silakan masukkan kode redeem.';
                this.redeemMsgType = 'error';
                return;
            }

            // Fetch from backend (codes managed by admin)
            fetch(`/api/redeem-codes/check?code=${encodeURIComponent(code)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    this.redeemMsg = data.message || 'Kode tidak valid.';
                    this.redeemMsgType = 'error';
                    return;
                }

                const minSpend = data.min_spend || 0;
                if (minSpend > 0 && this.checkoutAmount < minSpend) {
                    this.redeemMsg = `Kode ${code} butuh minimal belanja Rp ${minSpend.toLocaleString('id-ID')}.`;
                    this.redeemMsgType = 'error';
                    return;
                }

                let discAmt = 0;
                if (data.discount_type === 'percentage') {
                    discAmt = Math.round(this.checkoutAmount * (data.discount_amount / 100));
                    if (data.max_discount) discAmt = Math.min(discAmt, data.max_discount);
                } else {
                    discAmt = data.discount_amount || 0;
                }

                this.appliedRedeemCode = data.code;
                this.redeemDiscountAmount = discAmt;
                this.redeemShippingDiscount = data.shipping_discount || 0;

                let msg = `Kode redeem ${data.code} berhasil! `;
                if (discAmt > 0) msg += `Potongan Rp ${discAmt.toLocaleString('id-ID')} `;
                if (data.shipping_discount > 0) msg += `+ subsidi ongkir Rp ${data.shipping_discount.toLocaleString('id-ID')} 🚚`;
                msg += ' 🎉';
                this.redeemMsg = msg.trim();
                this.redeemMsgType = 'success';
            })
            .catch(() => {
                this.redeemMsg = 'Gagal memeriksa kode. Coba lagi.';
                this.redeemMsgType = 'error';
            });
        },

        removeRedeemCode() {
            this.appliedRedeemCode = '';
            this.redeemDiscountAmount = 0;
            this.redeemShippingDiscount = 0;
            this.redeemMsg = '';
            this.redeemInput = '';
        }
    };
}
</script>

<div class="py-8 sm:py-12 bg-[#FAF8F5] min-h-[85vh]"
     x-data="checkoutPage()"
     x-init="initCheckout()">
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Empty Checkout State -->
        <div x-show="checkoutCount === 0" 
             x-cloak 
             class="py-16 text-center max-w-md mx-auto space-y-4">
            <div class="w-20 h-20 bg-amber-50 border border-amber-200 text-amber-600 rounded-3xl mx-auto flex items-center justify-center text-3xl shadow-2xs">
                🛒
            </div>
            <div class="space-y-1">
                <h3 class="font-black text-lg text-[#2D241E]">Belum Ada Produk untuk Dicheckout</h3>
                <p class="text-xs text-[#7A6C60] leading-relaxed">
                    Keranjang belanja kamu masih kosong atau belum ada produk yang dicentang untuk dibeli.
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <a href="{{ route('cart') }}" class="px-4 py-2.5 bg-white hover:bg-[#FAF8F5] text-[#6B4226] border border-[#EAE1D7] rounded-xl text-xs font-bold transition shadow-xs">
                    ← Lihat Keranjang
                </a>
                <a href="{{ route('home') }}" class="px-5 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white rounded-xl text-xs font-bold transition shadow-xs">
                    Mulai Belanja 🛍️
                </a>
            </div>
        </div>

        <div x-show="checkoutCount > 0">
            <!-- Breadcrumb & Step Progress with Inline Back Button -->
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#EAE1D7]">
                <div class="flex items-center gap-3">
                    <a href="{{ route('cart') }}" 
                       class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                       title="Kembali ke Keranjang">
                        <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="flex items-center gap-2 text-xs font-medium text-[#8A7C70]">
                        <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition">Beranda</a>
                        <span>/</span>
                        <a href="{{ route('cart') }}" class="hover:text-[#6B4226] transition">Keranjang</a>
                        <span>/</span>
                        <span class="text-[#6B4226] font-semibold">Checkout Pembayaran</span>
                    </div>
                </div>

                <!-- Steps -->
                <div class="flex items-center gap-2 text-xs font-medium text-[#8A7C70]">
                    <span class="text-[#8A7C70]">1. Keranjang</span>
                    <span>→</span>
                    <span class="text-[#6B4226] font-bold bg-[#FAF4ED] px-2.5 py-0.5 rounded-full border border-[#6B4226]/20">2. Checkout</span>
                    <span>→</span>
                    <span>3. Selesai</span>
                </div>
            </div>

            <!-- Checkout Main Form -->
            <form action="{{ route('checkout') }}" method="POST" @submit="if(hasOwnProductInCheckout){ $event.preventDefault(); return false; } isSubmitting = true">
                @csrf

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-bold shadow-2xs">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-base">🚫</span>
                            <span>Periksa kembali formulir pesanan:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-xs font-medium text-rose-700">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Warning if cart contains seller's own product -->
                <div x-show="hasOwnProductInCheckout" 
                     x-cloak
                     class="mb-6 p-4.5 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 text-xs sm:text-sm font-bold shadow-2xs flex items-start gap-3">
                    <span class="text-2xl mt-0.5">🏪</span>
                    <div class="space-y-1">
                        <p class="font-black text-amber-900">Perhatian: Ada Produk dari Tokomu Sendiri!</p>
                        <p class="text-xs text-amber-800 font-normal leading-relaxed">
                            Penjual tidak diperbolehkan membeli produk dari akun toko sendiri (<span class="font-bold text-amber-950" x-text="ownProductNames"></span>). Silakan kembali ke keranjang untuk menghapus produk tokomu terlebih dahulu sebelum checkout.
                        </p>
                        <div class="pt-1.5">
                            <a href="{{ route('cart') }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#6B4226] hover:underline">
                                <span>← Buka Keranjang Belanja</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Hidden items array from Alpine -->
                <template x-for="(item, index) in checkoutItems" :key="item.cartKey || item.id">
                    <div>
                        <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.id">
                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                        <input type="hidden" :name="'items[' + index + '][variant_id]'" :value="item.variant_id || ''">
                        <input type="hidden" :name="'items[' + index + '][variant_name]'" :value="item.variant_name || ''">
                    </div>
                </template>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Left Column: Address, Items & Payment Selection (7 Cols) -->
                <div class="lg:col-span-7 space-y-6">
                    
                    @php
                        $user = Auth::user();
                        $hasAddress = $user && $user->hasCompleteAddress();
                        $deliveryAddress = $user ? ($user->formatted_address ?: $user->default_address) : '';
                    @endphp

                    <!-- 1. Alamat Pengiriman Section -->
                    @if($hasAddress)
                        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs relative overflow-hidden">
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#6B4226]"></div>

                            <div class="flex items-center justify-between gap-4 mb-4 pb-3 border-b border-[#F2EAE0]">
                                <div class="flex items-center gap-2 text-[#6B4226] font-bold text-sm uppercase tracking-wider">
                                    <span class="text-lg">📍</span>
                                    <span>Alamat Pengiriman Pesanan</span>
                                </div>
                                <a href="{{ route('settings', ['view' => 'address', 'return_to' => 'checkout']) }}" 
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-[#6B4226] hover:text-[#54321B] bg-[#FAF4ED] hover:bg-[#F2EAE0] px-3 py-1.5 rounded-xl border border-[#6B4226]/20 transition cursor-pointer"
                                   title="Ubah alamat di pengaturan">
                                    <span>✏️</span>
                                    <span>Ubah Alamat</span>
                                </a>
                            </div>

                            <!-- Hidden Form Inputs for Checkout Payload -->
                            <input type="hidden" name="customer_name" value="{{ $user->recipient_name }}">
                            <input type="hidden" name="customer_phone" value="{{ $user->recipient_phone }}">
                            <input type="hidden" name="customer_address" value="{{ $deliveryAddress }}">

                            <!-- Address Info Display Card -->
                            <div class="p-4 rounded-2xl bg-[#FAF8F5] border border-[#EAE1D7] space-y-2.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md bg-[#6B4226] text-white text-[10px] font-black uppercase tracking-wider">
                                        {{ $user->address_label ?: 'Utama' }}
                                    </span>
                                    <span class="font-black text-sm text-[#2D241E]">
                                        {{ $user->recipient_name }}
                                    </span>
                                    <span class="text-xs text-[#7A6C60] font-medium">
                                        ({{ $user->recipient_phone }})
                                    </span>
                                </div>

                                <p class="text-xs text-[#5A4B40] leading-relaxed">
                                    {{ $deliveryAddress }}
                                </p>

                                @if($user->map_notes)
                                    <div class="pt-1 flex items-start gap-1.5 text-[11px] text-[#8A7C70] border-t border-[#EAE1D7]/60">
                                        <span class="text-[#6B4226] font-bold shrink-0">📍 Catatan Kurir:</span>
                                        <span>{{ $user->map_notes }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Optional Specific Order Note -->
                            <div class="mt-4">
                                <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider mb-1">
                                    Catatan Tambahan untuk Penjual / Kurir (Opsional)
                                </label>
                                <input type="text" 
                                       name="customer_notes" 
                                       value="{{ old('customer_notes', $user->map_notes) }}" 
                                       placeholder="Contoh: Tolong bungkus bubble wrap tebal, kirim sebelum jam 5 sore"
                                       class="w-full px-4 py-2.5 text-xs bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] transition">
                            </div>
                        </div>
                    @else
                        <!-- State: Belum Ada Alamat Pengiriman (Nuansa Coklat NusantaraMart yang Elegan) -->
                        <div class="bg-gradient-to-br from-[#FAF5EF] via-white to-[#FAF5EF] rounded-3xl border-2 border-[#D9C3B0] p-6 sm:p-7 shadow-[0_4px_20px_rgba(107,66,38,0.06)] relative overflow-hidden">
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#6B4226] via-[#8C5832] to-[#6B4226]"></div>

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-5">
                                <div class="w-14 h-14 rounded-2xl bg-[#FAF4ED] border border-[#D9C3B0] flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                                    <span>📍</span>
                                </div>
                                <div class="space-y-2 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#6B4226] text-white text-[10px] font-black px-2.5 py-0.5 rounded-md uppercase tracking-wider shadow-2xs">
                                            Alamat Belum Diatur
                                        </span>
                                    </div>
                                    <h3 class="font-black text-base sm:text-lg text-[#2D241E]">
                                        Alamat Pengiriman Belum Lengkap
                                    </h3>
                                    <p class="text-xs sm:text-sm text-[#7A6C60] leading-relaxed">
                                        Kamu belum mengatur alamat pengiriman di akun kamu. Silakan lengkapi nama penerima, nomor telepon, dan alamat lengkap di menu <strong class="text-[#4A2E1B]">Pengaturan</strong> agar pesanan dapat dikirimkan ke lokasi yang tepat.
                                    </p>
                                    <div class="pt-2">
                                        <a href="{{ route('settings', ['view' => 'address', 'return_to' => 'checkout']) }}" 
                                           class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white font-black text-xs sm:text-sm shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-95 cursor-pointer">
                                            <span>⚙️</span>
                                            <span>Lengkapi Alamat di Pengaturan Sekarang</span>
                                            <span>→</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 2. Review Produk yang Dipesan -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                            <div class="flex items-center gap-2 font-bold text-sm text-[#2D241E]">
                                <span>📦</span>
                                <span>Produk yang Dipesan (<span x-text="checkoutCount"></span> item)</span>
                            </div>
                            <a href="{{ route('cart') }}" class="text-xs text-[#6B4226] font-bold hover:underline">
                                Ubah Keranjang
                            </a>
                        </div>

                            <template x-for="item in checkoutItems" :key="item.cartKey || item.id">
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] flex items-center justify-center shrink-0 overflow-hidden">
                                            <template x-if="item.image_url">
                                                <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.image_url">
                                                <span class="text-2xl" x-text="item.icon || '🛍️'"></span>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-medium text-[#8A7C70] uppercase" x-text="item.brand || 'Official Store'"></span>
                                                <template x-if="Array.isArray(userOwnedProductIds) && userOwnedProductIds.includes(Number(item.id))">
                                                    <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[9px] font-black uppercase">Produk Tokomu</span>
                                                </template>
                                            </div>
                                            <h4 class="font-bold text-xs sm:text-sm text-[#2D241E]" x-text="item.name"></h4>
                                            <p x-show="item.variant_name" class="text-[11px] font-bold text-[#6B4226]" x-text="'Varian: ' + item.variant_name"></p>
                                            <p class="text-[11px] text-[#8A7C70]" x-text="item.quantity + 'x @ ' + formatRupiah(item.price)"></p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-xs sm:text-sm text-[#6B4226]" x-text="formatRupiah(item.price * item.quantity)"></span>
                                </div>
                            </template>
                    </div>

                    <!-- 3. Pilihan Metode Pembayaran -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                            <div class="flex items-center gap-2 font-bold text-sm text-[#2D241E]">
                                <span>💳</span>
                                <span>Pilih Metode Pembayaran</span>
                            </div>
                            <span class="text-[11px] text-[#8A7C70] font-medium">Ditentukan oleh penjual produk</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- QRIS -->
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border transition select-none"
                                   :class="!isPaymentMethodAvailable('qris') ? 'opacity-40 cursor-not-allowed bg-gray-50 border-gray-200' : (paymentMethod === 'qris' ? 'border-[#6B4226] bg-[#FAF4ED] ring-1 ring-[#6B4226] cursor-pointer' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] cursor-pointer')">
                                <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" :disabled="!isPaymentMethodAvailable('qris')" class="sr-only">
                                <span class="text-2xl mt-0.5">📱</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-[#2D241E]">QRIS Instan</p>
                                        <span x-show="isPaymentMethodAvailable('qris')" class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-800 shrink-0">Lunas</span>
                                        <span x-show="!isPaymentMethodAvailable('qris')" class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-gray-200 text-gray-600 shrink-0">Tidak Didukung</span>
                                    </div>
                                    <p class="text-[10px] text-[#7A6C60] leading-tight mt-0.5">BCA Mobile, Mandiri, BRI, GoPay, OVO, DANA</p>
                                </div>
                            </label>

                            <!-- COD -->
                            <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border transition select-none"
                                   :class="!isPaymentMethodAvailable('cod') ? 'opacity-40 cursor-not-allowed bg-gray-50 border-gray-200' : (paymentMethod === 'cod' ? 'border-[#6B4226] bg-[#FAF4ED] ring-1 ring-[#6B4226] cursor-pointer' : 'border-[#EAE1D7] bg-[#FAF8F5] hover:bg-[#FAF4ED] cursor-pointer')">
                                <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" :disabled="!isPaymentMethodAvailable('cod')" class="sr-only">
                                <span class="text-2xl mt-0.5">💵</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-[#2D241E]">COD</p>
                                        <span x-show="isPaymentMethodAvailable('cod')" class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-amber-100 text-amber-800 shrink-0">Bayar di Tempat</span>
                                        <span x-show="!isPaymentMethodAvailable('cod')" class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-gray-200 text-gray-600 shrink-0">Tidak Didukung</span>
                                    </div>
                                    <p class="text-[10px] text-[#7A6C60] leading-tight mt-0.5">Bayar tunai ke kurir saat barang sampai</p>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Vouchers & Order Summary (5 Cols) -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <!-- 2-Category Voucher Widget (Ongkir & Diskon) -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#F2EAE0]">
                            <span class="font-bold text-xs uppercase tracking-wider text-[#2D241E] flex items-center gap-1.5">
                                <span>🎟️</span>
                                <span>Voucher NusantaraMart</span>
                            </span>
                            <span class="text-[11px] text-[#8A7C70] font-medium">Maks. 1 per kategori</span>
                        </div>

                        <!-- Slot 1: Voucher Bebas Ongkir -->
                        <div class="p-3.5 rounded-2xl border transition"
                             :class="selectedShippingVoucher ? 'bg-emerald-50/70 border-emerald-300' : 'bg-[#FAF8F5] border-[#EAE1D7]'">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl shrink-0">
                                        🚚
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-black uppercase text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded">Ongkir</span>
                                            <span class="font-bold text-xs text-[#2D241E] truncate" x-text="selectedShippingVoucher ? selectedShippingVoucher.name : 'Voucher Gratis Ongkir'"></span>
                                        </div>
                                        <p class="text-[11px] text-[#7A6C60] truncate mt-0.5" 
                                           x-text="selectedShippingVoucher ? (shippingDiscount > 0 ? ('Hemat ' + formatRupiah(shippingDiscount)) : (checkoutAmount < (selectedShippingVoucher.min_spend || 0) ? ('Min. belanja ' + formatRupiah(selectedShippingVoucher.min_spend)) : 'Ongkir sudah Rp 0')) : 'Pilih voucher potongan ongkir'"></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <template x-if="selectedShippingVoucher">
                                        <button type="button" 
                                                @click="selectedShippingVoucher = null"
                                                class="px-2.5 py-1.5 text-xs text-red-600 hover:text-red-800 font-bold transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </template>
                                    <button type="button" 
                                            @click="openVoucherModal('shipping')"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
                                            :class="selectedShippingVoucher ? 'bg-white text-emerald-800 border border-emerald-300 hover:bg-emerald-100' : 'bg-[#6B4226] text-white hover:bg-[#54321B]'">
                                        <span x-text="selectedShippingVoucher ? 'Ganti' : 'Pilih'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Slot 2: Voucher Diskon Belanja -->
                        <div class="p-3.5 rounded-2xl border transition"
                             :class="selectedDiscountVoucher ? 'bg-[#FAF4ED] border-[#D9C3B0]' : 'bg-[#FAF8F5] border-[#EAE1D7]'">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-[#FAF4ED] text-[#6B4226] border border-[#E8DED3] flex items-center justify-center text-xl shrink-0">
                                        🏷️
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-black uppercase text-[#6B4226] bg-[#FAF4ED] border border-[#E8DED3] px-1.5 py-0.2 rounded">Diskon</span>
                                            <span class="font-bold text-xs text-[#2D241E] truncate" x-text="selectedDiscountVoucher ? selectedDiscountVoucher.name : 'Voucher Diskon Belanja'"></span>
                                        </div>
                                        <p class="text-[11px] text-[#7A6C60] truncate mt-0.5" 
                                           x-text="selectedDiscountVoucher ? (productDiscount > 0 ? ('Hemat ' + formatRupiah(productDiscount)) : ('Min. belanja ' + formatRupiah(selectedDiscountVoucher.min_spend || 0))) : 'Pilih voucher potongan harga'"></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <template x-if="selectedDiscountVoucher">
                                        <button type="button" 
                                                @click="selectedDiscountVoucher = null"
                                                class="px-2.5 py-1.5 text-xs text-red-600 hover:text-red-800 font-bold transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </template>
                                    <button type="button" 
                                            @click="openVoucherModal('discount')"
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
                                            :class="selectedDiscountVoucher ? 'bg-white text-[#6B4226] border border-[#D9C3B0] hover:bg-[#FAF4ED]' : 'bg-[#6B4226] text-white hover:bg-[#54321B]'">
                                        <span x-text="selectedDiscountVoucher ? 'Ganti' : 'Pilih'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Kode Redeem Card (Terpisah dari Sistem Voucher) -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-[#F2EAE0]">
                            <span class="font-bold text-xs uppercase tracking-wider text-[#2D241E] flex items-center gap-1.5">
                                <span>🎁</span>
                                <span>Kode Redeem Promo</span>
                            </span>
                            <span class="text-[11px] text-[#8A7C70] font-medium">Kupon & Hadiah</span>
                        </div>

                        <div class="flex gap-2">
                            <input type="text" 
                                   x-model="redeemInput" 
                                   :disabled="appliedRedeemCode !== ''"
                                   placeholder="Masukkan kode redeem (misal: SNACKSERU)..."
                                   @keyup.enter="applyRedeemCode()"
                                   class="flex-1 px-4 py-2 text-xs bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 uppercase font-mono font-bold">
                            <button type="button" 
                                    x-show="!appliedRedeemCode"
                                    @click="applyRedeemCode()" 
                                    class="px-4 py-2 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition cursor-pointer">
                                Pakai
                            </button>
                            <button type="button" 
                                    x-show="appliedRedeemCode"
                                    x-cloak
                                    @click="removeRedeemCode()" 
                                    class="px-3 py-2 bg-red-50 text-red-800 border border-red-200 text-xs font-bold rounded-xl transition cursor-pointer">
                                Hapus
                            </button>
                        </div>

                        <!-- Feedback Message -->
                        <div x-show="redeemMsg" x-cloak 
                             :class="redeemMsgType === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'"
                             class="p-2.5 rounded-xl border text-[11px] font-semibold">
                            <span x-text="redeemMsg"></span>
                        </div>
                    </div>

                    <!-- Hidden Inputs for Form Submission -->
                    <input type="hidden" name="shipping_voucher_code" :value="selectedShippingVoucher ? selectedShippingVoucher.code : ''">
                    <input type="hidden" name="discount_voucher_code" :value="selectedDiscountVoucher ? selectedDiscountVoucher.code : ''">
                    <input type="hidden" name="redeem_code" :value="appliedRedeemCode">
                    <input type="hidden" name="coupon_code" :value="appliedRedeemCode ? appliedRedeemCode : (selectedDiscountVoucher ? selectedDiscountVoucher.code : (selectedShippingVoucher ? selectedShippingVoucher.code : ''))">

                    <!-- Payment Summary Box -->
                    <div class="bg-white rounded-3xl border border-[#EAE1D7] p-6 shadow-2xs space-y-4">
                        <h3 class="font-bold text-sm text-[#2D241E] pb-3 border-b border-[#F2EAE0]">
                            Ringkasan Pembayaran
                        </h3>

                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between text-[#7A6C60]">
                                <span>Total Belanja (<span x-text="checkoutCount"></span> produk):</span>
                                <span class="font-bold text-[#2D241E]" x-text="formatRupiah(checkoutAmount)"></span>
                            </div>

                            <div class="flex justify-between text-[#7A6C60]">
                                <span>Ongkos Kirim (Flat):</span>
                                <span class="font-bold" :class="rawShippingCost === 0 ? 'text-emerald-700' : 'text-[#2D241E]'" x-text="rawShippingCost === 0 ? 'GRATIS (Promo Toko)' : formatRupiah(rawShippingCost)"></span>
                            </div>

                            <!-- Potongan Voucher Ongkir -->
                            <div x-show="shippingVoucherDiscount > 0" x-cloak class="flex justify-between text-emerald-700 font-bold">
                                <span>Potongan Voucher Ongkir (<span x-text="selectedShippingVoucher ? selectedShippingVoucher.code : ''"></span>):</span>
                                <span x-text="'- ' + formatRupiah(shippingVoucherDiscount)"></span>
                            </div>

                            <!-- Potongan Voucher Diskon Belanja -->
                            <div x-show="productVoucherDiscount > 0" x-cloak class="flex justify-between text-emerald-700 font-bold">
                                <span>Potongan Voucher Diskon (<span x-text="selectedDiscountVoucher ? selectedDiscountVoucher.code : ''"></span>):</span>
                                <span x-text="'- ' + formatRupiah(productVoucherDiscount)"></span>
                            </div>

                            <!-- Potongan Kode Redeem (Terpisah) -->
                            <div x-show="redeemDiscountTotal > 0" x-cloak class="flex justify-between text-emerald-700 font-bold">
                                <span>Potongan Kode Redeem (<span x-text="appliedRedeemCode"></span>):</span>
                                <span x-text="'- ' + formatRupiah(redeemDiscountTotal)"></span>
                            </div>

                            <div class="border-t border-dashed border-[#EAE1D7] pt-3 flex justify-between items-center text-sm">
                                <span class="font-bold text-[#2D241E]">Total Pembayaran:</span>
                                <span class="font-black text-2xl text-[#6B4226]" x-text="formatRupiah(grandTotal)"></span>
                            </div>
                        </div>

                        @if($hasAddress)
                            <button type="submit" 
                                    :disabled="isSubmitting || checkoutCount === 0 || hasOwnProductInCheckout"
                                    :class="hasOwnProductInCheckout ? 'opacity-50 cursor-not-allowed bg-stone-400 hover:bg-stone-400' : 'bg-[#6B4226] hover:bg-[#54321B]'"
                                    class="w-full py-4 px-6 disabled:opacity-60 text-white font-bold rounded-2xl shadow-xs transition transform active:scale-98 text-sm flex items-center justify-center gap-2 mt-2 cursor-pointer">
                                <span x-show="!isSubmitting && !hasOwnProductInCheckout">Pesan Sekarang 🚀</span>
                                <span x-show="!isSubmitting && hasOwnProductInCheckout">🚫 Ada Produk Tokomu Sendiri</span>
                                <span x-show="isSubmitting" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Memproses Pesanan...</span>
                                </span>
                            </button>
                        @else
                            <a href="{{ route('settings', ['view' => 'address', 'return_to' => 'checkout']) }}" 
                               class="w-full py-4 px-6 bg-gradient-to-r from-[#6B4226] to-[#54321B] hover:from-[#54321B] hover:to-[#3E2412] text-white font-bold rounded-2xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:scale-98 text-sm flex items-center justify-center gap-2 mt-2 cursor-pointer text-center group">
                                <span>📍 Lengkapi Alamat Dahulu Sebelum Bayar</span>
                                <span class="transition-transform group-hover:translate-x-1">→</span>
                            </a>
                            <p class="text-[11px] text-center text-[#8A7C70] font-medium leading-tight">
                                Tombol pemesanan akan aktif setelah alamat pengiriman dilengkapi di menu pengaturan.
                            </p>
                        @endif

                        <p class="text-[11px] text-center text-[#9E9084] leading-tight">
                            Dengan mengklik "Pesan Sekarang", kamu menyetujui syarat & ketentuan belanja di NusantaraMart.
                        </p>
                    </div>

                </div>

            </div>
        </form>
        </div>

    </div>

    <!-- Interactive Voucher Selection Modal -->
    <div x-show="showVoucherModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Backdrop -->
            <div x-show="showVoucherModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showVoucherModal = false"
                 class="fixed inset-0 bg-[#2D241E]/50 backdrop-blur-xs transition-opacity" 
                 aria-hidden="true"></div>

            <div x-show="showVoucherModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-[#EAE1D7]">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-[#FAF8F5] border-b border-[#EAE1D7] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-[#2D241E]">Pilih Voucher Belanja</h3>
                        <p class="text-[11px] text-[#7A6C60]">Gunakan voucher untuk hemat ongkir & diskon harga</p>
                    </div>
                    <button type="button" 
                            @click="showVoucherModal = false" 
                            class="w-8 h-8 rounded-full bg-white hover:bg-[#FAF4ED] text-[#7A6C60] hover:text-[#2D241E] border border-[#EAE1D7] flex items-center justify-center transition cursor-pointer text-sm">
                        ✕
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    
                    <!-- Manual Voucher Code Input -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold text-[#5A4B40] uppercase tracking-wider">
                            Punya Kode Voucher Rahasia?
                        </label>
                        <div class="flex gap-2">
                            <input type="text" 
                                   x-model="voucherInputCode" 
                                   placeholder="Contoh: ONGKIRFREE, DISKON20"
                                   @keyup.enter="applyManualVoucherCode()"
                                   class="flex-1 px-3.5 py-2 text-xs bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 uppercase font-mono font-bold">
                            <button type="button" 
                                    @click="applyManualVoucherCode()" 
                                    class="px-4 py-2 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition cursor-pointer">
                                Terapkan
                            </button>
                        </div>
                        <div x-show="voucherModalMsg" x-cloak 
                             :class="voucherModalMsgType === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200'"
                             class="p-2.5 rounded-xl border text-[11px] font-semibold mt-1">
                            <span x-text="voucherModalMsg"></span>
                        </div>
                    </div>

                    <!-- Category Tabs (Ongkir vs Diskon) -->
                    <div class="flex rounded-2xl bg-[#FAF8F5] p-1 border border-[#EAE1D7]">
                        <button type="button" 
                                @click="voucherModalTab = 'shipping'"
                                :class="voucherModalTab === 'shipping' ? 'bg-emerald-700 text-white shadow-xs' : 'text-[#5A4B40] hover:text-[#2D241E]'"
                                class="flex-1 py-2 text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5">
                            <span>🚚</span>
                            <span>Voucher Ongkir</span>
                            <span x-show="selectedShippingVoucher" class="w-2 h-2 rounded-full bg-white"></span>
                        </button>
                        <button type="button" 
                                @click="voucherModalTab = 'discount'"
                                :class="voucherModalTab === 'discount' ? 'bg-[#6B4226] text-white shadow-xs' : 'text-[#5A4B40] hover:text-[#2D241E]'"
                                class="flex-1 py-2 text-xs font-bold rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5">
                            <span>🏷️</span>
                            <span>Voucher Diskon</span>
                            <span x-show="selectedDiscountVoucher" class="w-2 h-2 rounded-full bg-white"></span>
                        </button>
                    </div>

                    <!-- Notice: Max 1 per category -->
                    <div class="px-3 py-2 rounded-xl bg-amber-50/80 border border-amber-200 text-[11px] text-amber-900 flex items-center gap-2">
                        <span>ℹ️</span>
                        <span>Kamu dapat memilih <strong>1 Voucher Ongkir</strong> dan <strong>1 Voucher Diskon</strong> sekaligus!</span>
                    </div>

                    <!-- Tab Content 1: Shipping Vouchers -->
                    <div x-show="voucherModalTab === 'shipping'" class="space-y-2.5">
                        <template x-for="v in shippingVouchersList" :key="v.id">
                            <div class="p-3.5 rounded-2xl border transition flex items-start justify-between gap-3 cursor-pointer"
                                 :class="[
                                     (selectedShippingVoucher && selectedShippingVoucher.id === v.id) ? 'bg-emerald-50 border-emerald-400 ring-1 ring-emerald-400' : 'bg-white border-[#EAE1D7] hover:border-[#6B4226]/40',
                                     (checkoutAmount < (v.min_spend || 0)) ? 'opacity-60 bg-gray-50' : ''
                                 ]"
                                 @click="(claimedVoucherCodes.includes(v.code) && checkoutAmount >= (v.min_spend || 0)) ? selectShippingVoucher(v) : null">
                                
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <!-- Radio / Check -->
                            <div class="mt-0.5">
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                             :class="(selectedShippingVoucher && selectedShippingVoucher.id === v.id) ? 'border-emerald-600 bg-emerald-600' : 'border-[#C8BCB0] bg-white'">
                                            <div x-show="selectedShippingVoucher && selectedShippingVoucher.id === v.id" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="space-y-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-mono font-bold text-xs text-[#2D241E] bg-[#FAF8F5] px-2 py-0.5 rounded border border-[#EAE1D7]" x-text="v.code"></span>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-800">Bebas Ongkir</span>
                                            <template x-if="v.member_tier && v.member_tier !== 'all'">
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-purple-100 text-purple-900 border border-purple-200"
                                                      x-text="v.member_tier === 'platinum' ? '👑 Platinum' : (v.member_tier === 'gold' ? '🥇 Gold+' : '🥈 Silver+')"></span>
                                            </template>
                                            <template x-if="v.is_weekly_recurring">
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7]">🔄 Mingguan</span>
                                            </template>
                                        </div>
                                        <h4 class="font-bold text-xs sm:text-sm text-[#2D241E]" x-text="v.name"></h4>
                                        <p class="text-[11px] text-[#7A6C60]" x-text="v.description"></p>
                                        <div class="flex items-center gap-2 text-[10px] text-[#8A7C70] pt-1">
                                            <span x-text="v.min_spend > 0 ? ('Min. Belanja ' + formatRupiah(v.min_spend)) : 'Tanpa Min. Belanja'"></span>
                                            <template x-if="checkoutAmount < (v.min_spend || 0)">
                                                <span class="text-rose-600 font-bold">• Belanjaan belum cukup</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action if not claimed yet -->
                                <div class="shrink-0" @click.stop>
                                    <template x-if="!claimedVoucherCodes.includes(v.code)">
                                        <div>
                                            <template x-if="isTierEligible(v)">
                                                <button type="button" 
                                                        @click="claimAndSelectVoucher(v)"
                                                        :disabled="isClaimingModalVoucher"
                                                        class="px-3 py-1.5 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold transition shadow-2xs cursor-pointer">
                                                    Klaim & Pakai
                                                </button>
                                            </template>
                                            <template x-if="!isTierEligible(v)">
                                                <button type="button" 
                                                        @click="claimAndSelectVoucher(v)"
                                                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-500 text-[11px] font-bold cursor-pointer hover:bg-slate-200">
                                                    🔒 Khusus Member
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="claimedVoucherCodes.includes(v.code)">
                                        <span class="text-[11px] font-bold text-emerald-700"
                                              x-text="(selectedShippingVoucher && selectedShippingVoucher.id === v.id) ? 'Terpilih ✓' : 'Tersedia'">
                                        </span>
                                    </template>
                                </div>

                            </div>
                        </template>
                    </div>

                    <!-- Tab Content 2: Discount Vouchers -->
                    <div x-show="voucherModalTab === 'discount'" class="space-y-2.5">
                        <template x-for="v in discountVouchersList" :key="v.id">
                            <div class="p-3.5 rounded-2xl border transition flex items-start justify-between gap-3 cursor-pointer"
                                 :class="[
                                     (selectedDiscountVoucher && selectedDiscountVoucher.id === v.id) ? 'bg-[#FAF4ED] border-[#6B4226] ring-1 ring-[#6B4226]' : 'bg-white border-[#EAE1D7] hover:border-[#6B4226]/40',
                                     (checkoutAmount < (v.min_spend || 0)) ? 'opacity-60 bg-gray-50' : ''
                                 ]"
                                 @click="(claimedVoucherCodes.includes(v.code) && checkoutAmount >= (v.min_spend || 0)) ? selectDiscountVoucher(v) : null">
                                
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <!-- Radio / Check -->
                                    <div class="mt-0.5">
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center"
                                             :class="(selectedDiscountVoucher && selectedDiscountVoucher.id === v.id) ? 'border-[#6B4226] bg-[#6B4226]' : 'border-[#C8BCB0] bg-white'">
                                            <div x-show="selectedDiscountVoucher && selectedDiscountVoucher.id === v.id" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="space-y-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-mono font-bold text-xs text-[#2D241E] bg-[#FAF8F5] px-2 py-0.5 rounded border border-[#EAE1D7]" x-text="v.code"></span>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7]">Diskon</span>
                                            <template x-if="v.member_tier && v.member_tier !== 'all'">
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-purple-100 text-purple-900 border border-purple-200"
                                                      x-text="v.member_tier === 'platinum' ? '👑 Platinum' : (v.member_tier === 'gold' ? '🥇 Gold+' : '🥈 Silver+')"></span>
                                            </template>
                                            <template x-if="v.is_weekly_recurring">
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7]">🔄 Mingguan</span>
                                            </template>
                                        </div>
                                        <h4 class="font-bold text-xs sm:text-sm text-[#2D241E]" x-text="v.name"></h4>
                                        <p class="text-[11px] text-[#7A6C60]" x-text="v.description"></p>
                                        <div class="flex items-center gap-2 text-[10px] text-[#8A7C70] pt-1">
                                            <span x-text="v.min_spend > 0 ? ('Min. Belanja ' + formatRupiah(v.min_spend)) : 'Tanpa Min. Belanja'"></span>
                                            <template x-if="checkoutAmount < (v.min_spend || 0)">
                                                <span class="text-rose-600 font-bold">• Belanjaan belum cukup</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action if not claimed yet -->
                                <div class="shrink-0" @click.stop>
                                    <template x-if="!claimedVoucherCodes.includes(v.code)">
                                        <div>
                                            <template x-if="isTierEligible(v)">
                                                <button type="button" 
                                                        @click="claimAndSelectVoucher(v)"
                                                        :disabled="isClaimingModalVoucher"
                                                        class="px-3 py-1.5 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold transition shadow-2xs cursor-pointer">
                                                    Klaim & Pakai
                                                </button>
                                            </template>
                                            <template x-if="!isTierEligible(v)">
                                                <button type="button" 
                                                        @click="claimAndSelectVoucher(v)"
                                                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-500 text-[11px] font-bold cursor-pointer hover:bg-slate-200">
                                                    🔒 Khusus Member
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="claimedVoucherCodes.includes(v.code)">
                                        <span class="text-[11px] font-bold text-[#6B4226]"
                                              x-text="(selectedDiscountVoucher && selectedDiscountVoucher.id === v.id) ? 'Terpilih ✓' : 'Tersedia'">
                                        </span>
                                    </template>
                                </div>

                            </div>
                        </template>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-[#FAF8F5] border-t border-[#EAE1D7] flex items-center justify-between">
                    <div class="text-xs text-[#7A6C60]">
                        <span x-show="selectedShippingVoucher || selectedDiscountVoucher" class="font-semibold text-emerald-700">
                            Voucher aktif terpilih
                        </span>
                        <span x-show="!selectedShippingVoucher && !selectedDiscountVoucher">
                            Belum ada voucher dipilih
                        </span>
                    </div>

                    <button type="button" 
                            @click="showVoucherModal = false"
                            class="px-6 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                        Gunakan Voucher Ini
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
