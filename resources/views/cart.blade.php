@extends('layouts.app')

@section('title', 'Keranjang Belanja — NusantaraMart')

@section('content')
<script>
function cartPage() {
    return {
        items: [],
        selectAll: true,
        userOwnedProductIds: {!! json_encode((Auth::check() && Auth::user()->store) ? \App\Models\Product::where('store_id', Auth::user()->store->id)->pluck('id')->toArray() : []) !!},
        
        // Variant Modal State
        showVariantModal: false,
        currentEditingItem: null,
        modalVariants: [],
        selectedModalVariantId: null,
        isLoadingVariants: false,
        variantsCache: {},
        
        // Toast State
        showVariantToast: false,
        variantToastMsg: '',

        initCartPage() {
            this.loadCart();
            if (Array.isArray(this.items)) {
                this.items.forEach(i => {
                    if (i.selected === undefined) i.selected = true;
                });
            }
            this.updateSelection();
            this.enrichCartItems();
        },

        loadCart() {
            const authId = {{ Auth::id() ? Auth::id() : 'null' }};
            const key = authId ? ('nusantaramart_cart_user_' + authId) : 'nusantaramart_cart_guest';
            let saved = localStorage.getItem(key);
            if (!saved) {
                saved = localStorage.getItem('nusantaramart_cart_guest') || localStorage.getItem('nusantaramart_cart') || localStorage.getItem('snackaroo_cart');
            }
            if (saved) {
                try {
                    this.items = JSON.parse(saved);
                } catch(e) {
                    this.items = [];
                }
            } else if (window.snackCart && Array.isArray(window.snackCart.items)) {
                this.items = window.snackCart.items;
            } else {
                this.items = [];
            }
        },

        saveCart() {
            const authId = {{ Auth::id() ? Auth::id() : 'null' }};
            if (authId) {
                const key = 'nusantaramart_cart_user_' + authId;
                localStorage.setItem(key, JSON.stringify(this.items));
            }
            if (window.snackCart) {
                window.snackCart.items = this.items;
            }
        },

        enrichCartItems() {
            if (!Array.isArray(this.items) || this.items.length === 0) return;
            const needEnrich = this.items.filter(i => (!i.store_name || !i.store_city || !i.slug) && i.id);
            if (needEnrich.length > 0) {
                needEnrich.forEach(item => {
                    fetch(`/api/products/${item.id}/variants`)
                        .then(r => r.json())
                        .then(data => {
                            let updated = false;
                            if (data.product_slug && !item.slug) {
                                item.slug = data.product_slug;
                                updated = true;
                            }
                            if (data.is_free_shipping !== undefined) {
                                item.is_free_shipping = Boolean(data.is_free_shipping);
                                item.free_shipping_min_spend = parseInt(data.free_shipping_min_spend) || 0;
                                item.allow_vouchers = data.allow_vouchers !== undefined ? Boolean(data.allow_vouchers) : true;
                                updated = true;
                            }
                            if (data.store) {
                                if (!item.store_name) { item.store_name = data.store.name || item.brand; updated = true; }
                                if (!item.store_id && data.store.id) { item.store_id = data.store.id; updated = true; }
                                if (!item.store_slug && data.store.slug) { item.store_slug = data.store.slug; updated = true; }
                                if (!item.store_city && data.store.city) { item.store_city = data.store.city; updated = true; }
                                if (data.store.badge) { item.badge = data.store.badge; updated = true; }
                            }
                            if (data.variants && data.variants.length > 0) {
                                this.variantsCache[item.id] = data.variants;
                            }
                            if (updated) {
                                this.saveCart();
                            }
                        })
                        .catch(() => {});
                });
            }
        },

        productDetailUrl(item) {
            if (!item) return '#';
            if (item.slug) return '/product/' + item.slug;
            if (item.id) return '/product/' + item.id;
            return '#';
        },

        get storeGroups() {
            const groups = {};
            (this.items || []).forEach(item => {
                const storeName = item.store_name || item.brand || 'NusantaraMart Official';
                const storeKey = item.store_id ? ('store_' + item.store_id) : ('brand_' + storeName);
                
                if (!groups[storeKey]) {
                    groups[storeKey] = {
                        key: storeKey,
                        store_id: item.store_id || null,
                        name: storeName,
                        slug: item.store_slug || null,
                        badge: item.badge || 'Official',
                        city: item.store_city || 'Indonesia',
                        items: []
                    };
                }
                groups[storeKey].items.push(item);
            });
            return Object.values(groups);
        },

        isStoreAllSelected(group) {
            if (!group || !Array.isArray(group.items) || group.items.length === 0) return false;
            return group.items.every(i => i.selected !== false);
        },

        toggleStoreSelect(group) {
            const target = !this.isStoreAllSelected(group);
            group.items.forEach(i => {
                i.selected = target;
            });
            this.saveCart();
            this.updateSelection();
        },

        toggleSelectAll() {
            this.items.forEach(i => i.selected = this.selectAll);
            this.saveCart();
        },

        updateSelection() {
            this.selectAll = this.items.length > 0 && this.items.every(i => i.selected !== false);
            this.saveCart();
        },

        // Quantity manipulation (works without selecting)
        updateQty(key, delta) {
            const item = this.items.find(i => (i.cartKey || i.id) == key);
            if (item) {
                item.quantity = (parseInt(item.quantity) || 1) + delta;
                if (item.quantity <= 0) {
                    this.items = this.items.filter(i => (i.cartKey || i.id) != key);
                }
                this.saveCart();
                this.updateSelection();
            }
        },

        setQty(key, val) {
            const item = this.items.find(i => (i.cartKey || i.id) == key);
            if (item) {
                const num = Math.max(1, parseInt(val) || 1);
                item.quantity = num;
                this.saveCart();
                this.updateSelection();
            }
        },

        // Item deletion (works without selecting)
        removeItem(key) {
            this.items = this.items.filter(i => (i.cartKey || i.id) != key);
            this.saveCart();
            this.updateSelection();
        },

        clearAll() {
            this.items = [];
            this.saveCart();
            this.updateSelection();
        },

        get selectedItems() {
            return Array.isArray(this.items) ? this.items.filter(i => i.selected !== false) : [];
        },

        get selectedCount() {
            return this.selectedItems.reduce((sum, i) => sum + (parseInt(i.quantity) || 1), 0);
        },

        get selectedAmount() {
            return this.selectedItems.reduce((sum, i) => sum + ((parseFloat(i.price) || 0) * (parseInt(i.quantity) || 1)), 0);
        },

        removeSelected() {
            this.items = this.items.filter(i => i.selected === false);
            this.saveCart();
            this.updateSelection();
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },

        isOwnProduct(productId) {
            return Array.isArray(this.userOwnedProductIds) && this.userOwnedProductIds.includes(Number(productId));
        },

        get hasOwnProductInSelection() {
            return this.selectedItems.some(i => this.isOwnProduct(i.id));
        },

        // Variant Changing Logic
        openVariantModal(item) {
            this.currentEditingItem = item;
            this.selectedModalVariantId = item.variant_id || null;
            this.showVariantModal = true;

            if (this.variantsCache[item.id]) {
                this.modalVariants = this.variantsCache[item.id];
                this.isLoadingVariants = false;
                // If item has no variant_id selected yet and variants exist, pre-select first
                if (!this.selectedModalVariantId && this.modalVariants.length > 0) {
                    this.selectedModalVariantId = this.modalVariants[0].id;
                }
                return;
            }

            this.isLoadingVariants = true;
            this.modalVariants = [];

            fetch(`/api/products/${item.id}/variants`)
                .then(res => res.json())
                .then(data => {
                    this.modalVariants = data.variants || [];
                    this.variantsCache[item.id] = this.modalVariants;

                    if (data.product_slug && !item.slug) {
                        item.slug = data.product_slug;
                    }
                    if (data.is_free_shipping !== undefined) {
                        item.is_free_shipping = Boolean(data.is_free_shipping);
                        item.free_shipping_min_spend = parseInt(data.free_shipping_min_spend) || 0;
                        item.allow_vouchers = data.allow_vouchers !== undefined ? Boolean(data.allow_vouchers) : true;
                    }

                    if (data.store) {
                        item.store_name = data.store.name || item.brand;
                        item.store_id = data.store.id || item.store_id;
                        item.store_slug = data.store.slug || item.store_slug;
                        item.store_city = data.store.city || item.store_city;
                        item.badge = data.store.badge || item.badge;
                        this.saveCart();
                    }

                    if (!this.selectedModalVariantId && this.modalVariants.length > 0) {
                        this.selectedModalVariantId = this.modalVariants[0].id;
                    }
                })
                .catch(err => {
                    console.error('Failed to load variants:', err);
                    this.modalVariants = [];
                })
                .finally(() => {
                    this.isLoadingVariants = false;
                });
        },

        selectModalVariant(variant) {
            this.selectedModalVariantId = variant.id;
        },

        confirmVariantChange() {
            if (!this.currentEditingItem) return;
            const chosen = this.modalVariants.find(v => v.id === this.selectedModalVariantId);
            if (!chosen) {
                this.showVariantModal = false;
                return;
            }

            const oldKey = this.currentEditingItem.cartKey || this.currentEditingItem.id;
            const newKey = `${this.currentEditingItem.id}_${chosen.id}`;

            // If same variant selected
            if (oldKey === newKey && this.currentEditingItem.variant_id === chosen.id) {
                this.showVariantModal = false;
                return;
            }

            // If target variant is already another item in cart, merge quantities
            const existingOther = this.items.find(i => (i.cartKey || i.id) === newKey && i !== this.currentEditingItem);
            if (existingOther) {
                existingOther.quantity += (parseInt(this.currentEditingItem.quantity) || 1);
                this.items = this.items.filter(i => i !== this.currentEditingItem);
            } else {
                this.currentEditingItem.variant_id = chosen.id;
                this.currentEditingItem.variant_name = chosen.name;
                this.currentEditingItem.price = chosen.price;
                this.currentEditingItem.cartKey = newKey;
                if (chosen.image_url) {
                    this.currentEditingItem.image_url = chosen.image_url;
                }
            }

            this.saveCart();
            this.updateSelection();
            this.showVariantModal = false;
            this.triggerVariantToast(`Varian berhasil diubah ke "${chosen.name}"! ✨`);
        },

        triggerVariantToast(msg) {
            this.variantToastMsg = msg;
            this.showVariantToast = true;
            setTimeout(() => {
                this.showVariantToast = false;
            }, 3000);
        },

        proceedToCheckout() {
            @guest
                if (typeof window.showAuthModal === 'function') {
                    window.showAuthModal({
                        icon: '🔒',
                        title: 'Selesaikan Pembayaran Pesanan',
                        message: 'Yuk masuk ke akunmu terlebih dahulu untuk memilih alamat pengiriman, opsi pembayaran, dan menyelesaikan pesananmu.'
                    });
                } else {
                    window.location.href = '{{ route('login') }}';
                }
                return;
            @endguest

            if (this.selectedCount === 0) {
                alert('Silakan centang minimal 1 produk untuk dicheckout!');
                return;
            }

            if (this.hasOwnProductInSelection) {
                const ownItem = this.selectedItems.find(i => this.isOwnProduct(i.id));
                alert(`Kamu tidak dapat membeli produk dari tokomu sendiri ("${ownItem ? ownItem.name : 'Produk Toko'}"). Silakan hilangkan centang atau hapus produk tokomu sebelum melanjutkan.`);
                return;
            }

            window.location.href = '{{ route('checkout.show') }}';
        }
    };
}
</script>

<div class="py-8 sm:py-12 bg-[#FAF8F5] min-h-[85vh]"
     x-data="cartPage()"
     x-init="initCartPage()">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header with Back Button & Title -->
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <button type="button"
                        onclick="window.smartNav.goBack('{{ route('home') }}')" 
                        class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
                        title="Kembali">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <div class="flex items-center gap-2 text-xs font-medium text-[#8A7C70] mb-0.5">
                        <a href="{{ route('home') }}" class="hover:text-[#6B4226] transition">Beranda</a>
                        <span>/</span>
                        <span class="text-[#6B4226] font-semibold">Keranjang Belanja</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#2D241E] tracking-tight flex items-center gap-2">
                        <span>Keranjang Belanja Kamu</span>
                        <span class="text-xl">🛍️</span>
                    </h1>
                </div>
            </div>
            <a href="{{ route('home') }}#katalog" class="text-xs font-bold text-[#6B4226] hover:text-[#54321B] transition flex items-center gap-1.5 self-start sm:self-auto">
                <span>← Lanjut Belanja Produk Lain</span>
            </a>
        </div>

        @guest
            <!-- Guest Notice Banner -->
            <div class="mb-6 bg-gradient-to-r from-[#FAF4ED] to-[#F5EBE1] border border-[#EAE1D7] rounded-3xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5 text-center sm:text-left">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-[#EAE1D7] text-[#6B4226] flex items-center justify-center text-2xl shadow-2xs shrink-0 mx-auto sm:mx-0">
                        🔒
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-[#2D241E]">Kamu Belum Masuk ke Akun</h3>
                        <p class="text-xs text-[#7A6C60] mt-0.5">Silakan masuk (login) terlebih dahulu agar dapat memasukkan produk ke keranjang belanja dan bertransaksi.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0">
                    <a href="{{ route('login') }}" 
                       class="w-full sm:w-auto px-5 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold text-xs rounded-xl shadow-xs transition text-center">
                        Masuk Sekarang ⚡
                    </a>
                    <a href="{{ route('register') }}" 
                       class="w-full sm:w-auto px-5 py-2.5 bg-white hover:bg-[#FAF8F5] text-[#6B4226] border border-[#EAE1D7] font-bold text-xs rounded-xl shadow-xs transition text-center">
                        Daftar
                    </a>
                </div>
            </div>
        @endguest

        <!-- Empty Cart State -->
        <div x-show="items.length === 0" x-cloak class="bg-white rounded-3xl p-12 text-center border border-[#EAE1D7] shadow-2xs max-w-lg mx-auto my-12">
            <div class="w-24 h-24 rounded-full bg-[#FAF7F2] border border-[#EAE1D7] flex items-center justify-center text-5xl mx-auto mb-4 animate-bounce">
                🛒
            </div>
            <h2 class="text-xl font-bold text-[#2D241E] mb-1">Keranjang Belanja Masih Kosong</h2>
            <p class="text-xs text-[#7A6C60] max-w-sm mx-auto mb-6">Yuk temukan produk gadget, fashion, kuliner, dan kebutuhan rumah tangga impianmu di katalog NusantaraMart!</p>
            <a href="{{ route('home') }}#katalog" 
               class="px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-2xl text-xs sm:text-sm shadow-xs transition inline-block">
                Mulai Belanja Sekarang 🛍️
            </a>
        </div>

        <!-- Cart Container -->
        <div x-show="items.length > 0" class="space-y-4">
            
            <!-- Free Shipping Promo Banner -->
            <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-[#EAE1D7] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🚚</span>
                    <div>
                        <span class="font-bold text-[#6B4226]">Promo Bebas Ongkir NusantaraMart</span>
                        <p class="text-[#7A6C60] text-[11px]" x-text="selectedAmount >= 100000 ? '🎉 Selamat! Pesananmu memenuhi syarat Bebas Ongkir (min. 100rb)' : 'Tambah ' + formatRupiah(100000 - selectedAmount) + ' lagi untuk nikmati Bebas Ongkir!'"></p>
                    </div>
                </div>
                <div class="w-full sm:w-48 bg-[#FAF7F2] rounded-full h-2 overflow-hidden border border-[#EAE1D7]">
                    <div class="bg-[#6B4226] h-2 rounded-full transition-all duration-300" :style="'width: ' + Math.min(100, (selectedAmount / 100000) * 100) + '%'"></div>
                </div>
            </div>

            <!-- SELECTION TOOLBAR CARD -->
            <div class="bg-white p-4 sm:px-6 rounded-2xl border border-[#EAE1D7] flex items-center justify-between shadow-2xs">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" 
                           x-model="selectAll" 
                           @change="toggleSelectAll()" 
                           class="w-4.5 h-4.5 text-[#6B4226] border-stone-300 rounded-md focus:ring-[#6B4226] cursor-pointer">
                    <span class="text-xs sm:text-sm font-bold text-[#2D241E]">
                        Pilih Semua (<span x-text="items.length"></span> Produk)
                    </span>
                </label>

                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="removeSelected()" 
                            x-show="selectedCount > 0" 
                            class="text-xs text-red-600 font-bold hover:underline cursor-pointer flex items-center gap-1">
                        <span>🗑️</span>
                        <span>Hapus Pilihan (<span x-text="selectedCount"></span>)</span>
                    </button>
                    <span class="text-xs font-semibold text-[#8A7C70] hidden sm:inline" x-text="selectedCount + ' produk terpilih'"></span>
                </div>
            </div>

            <!-- SHOPEE STYLE STORE-GROUPED CART CARDS -->
            <div class="space-y-4 sm:space-y-5">
                <template x-for="group in storeGroups" :key="group.key">
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#EAE1D7] shadow-2xs overflow-hidden transition-all duration-200">
                        
                        <!-- Store Header Row -->
                        <div class="p-3.5 sm:p-4 bg-[#FCFAF7] border-b border-[#F2EAE0] flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <!-- Store Checkbox (selects all items in this store) -->
                                <input type="checkbox"
                                       :checked="isStoreAllSelected(group)"
                                       @change="toggleStoreSelect(group)"
                                       class="w-4.5 h-4.5 text-[#6B4226] border-stone-300 rounded-md focus:ring-[#6B4226] cursor-pointer"
                                       :title="'Pilih semua produk dari toko ' + group.name">
                                
                                <!-- Store Badge -->
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider text-white"
                                      :class="group.badge === 'Mall' ? 'bg-[#D9381E]' : 'bg-[#6B4226]'"
                                      x-text="group.badge === 'Mall' ? 'Mall' : (group.badge || 'Official')">
                                </span>

                                <!-- Store Name & Link -->
                                <a :href="group.slug ? ('/store/' + group.slug) : ('/store/' + encodeURIComponent(group.name))"
                                   class="font-extrabold text-sm sm:text-base text-[#2D241E] hover:text-[#6B4226] transition flex items-center gap-1.5 group">
                                    <span class="text-base">🏪</span>
                                    <span x-text="group.name"></span>
                                    <svg class="w-3.5 h-3.5 text-[#8A7C70] group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>

                                <!-- City Badge -->
                                <span class="text-[11px] font-medium text-[#8A7C70] hidden sm:inline-flex items-center gap-1 bg-white border border-[#EAE1D7] px-2 py-0.5 rounded-full">
                                    <span>📍</span>
                                    <span x-text="group.city || 'Indonesia'"></span>
                                </span>
                            </div>

                            <div class="flex items-center gap-3 text-xs text-[#8A7C70]">
                                <span x-text="group.items.length + ' Produk'"></span>
                            </div>
                        </div>

                        <!-- Store Items List -->
                        <div class="divide-y divide-[#F2EAE0]">
                            <template x-for="(item, index) in group.items" :key="item.cartKey || item.id">
                                <div class="p-4 sm:p-5 transition-all duration-150"
                                     :class="item.selected !== false ? 'bg-white' : 'bg-[#FAF8F5]/40'">
                                    
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                        
                                        <!-- Left: Checkbox, Thumbnail & Info -->
                                        <div class="flex items-start sm:items-center gap-3.5 flex-1 min-w-0">
                                            <!-- Checkbox -->
                                            <div class="pt-1 sm:pt-0 shrink-0">
                                                <input type="checkbox" 
                                                       x-model="item.selected" 
                                                       @change="updateSelection()"
                                                       class="w-5 h-5 text-[#6B4226] border-stone-300 rounded-md focus:ring-[#6B4226] cursor-pointer">
                                            </div>

                                            <!-- Product Thumbnail (Clickable Link) -->
                                            <a :href="productDetailUrl(item)" 
                                               class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-b from-[#FAF8F5] to-[#FAF4ED] border border-[#F2EAE0] flex items-center justify-center shrink-0 shadow-2xs overflow-hidden group/thumb hover:border-[#6B4226]/50 transition cursor-pointer"
                                               :title="'Lihat detail ' + item.name">
                                                <template x-if="item.image_url">
                                                    <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover group-hover/thumb:scale-105 transition-transform duration-200">
                                                </template>
                                                <template x-if="!item.image_url">
                                                    <span class="text-3xl sm:text-4xl" x-text="item.icon || '🛍️'"></span>
                                                </template>
                                            </a>

                                            <!-- Title, Brand & Variant Changer Info -->
                                            <div class="min-w-0 flex-1 space-y-1.5">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <template x-if="isOwnProduct(item.id)">
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 px-2 py-0.5 rounded-lg">
                                                            <span>🏪</span>
                                                            <span>Produk Tokomu</span>
                                                        </span>
                                                    </template>
                                                </div>

                                                <!-- Product Title (Clickable Link) -->
                                                <a :href="productDetailUrl(item)" 
                                                   class="font-bold text-sm text-[#2D241E] hover:text-[#6B4226] transition leading-snug line-clamp-2 block group/title"
                                                   :title="'Lihat detail ' + item.name">
                                                    <span class="group-hover/title:underline" x-text="item.name"></span>
                                                </a>

                                                <!-- VARIANT SELECTOR / CHANGER (SHOPEE STYLE) -->
                                                <div class="pt-0.5 flex items-center gap-2 flex-wrap">
                                                    <button type="button" 
                                                            @click="openVariantModal(item)"
                                                            class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg bg-[#FAF4ED] hover:bg-[#F2E5D5] active:bg-[#EAE0D2] text-[#6B4226] border border-[#EAE1D7] transition cursor-pointer shadow-2xs group"
                                                            title="Klik untuk ubah varian produk ini">
                                                        <span>✨</span>
                                                        <span x-text="item.variant_name ? ('Variasi: ' + item.variant_name) : 'Pilih Variasi'"></span>
                                                        <svg class="w-3.5 h-3.5 text-[#6B4226] group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="flex items-center gap-2 pt-0.5 flex-wrap">
                                                    <span class="text-xs font-bold text-[#6B4226]" x-text="formatRupiah(item.price)"></span>
                                                    
                                                    <!-- Free shipping badge based on seller settings -->
                                                    <template x-if="item.is_free_shipping">
                                                        <span class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-bold border border-emerald-200/60 flex items-center gap-1">
                                                            <span>🚚 Bebas Ongkir</span>
                                                            <span x-show="item.free_shipping_min_spend > 0" class="text-[9px] font-normal" x-text="'(Min. ' + formatRupiah(item.free_shipping_min_spend) + ')'"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!item.is_free_shipping">
                                                        <span class="text-[10px] text-[#8A7C70] bg-[#FAF8F5] px-1.5 py-0.5 rounded font-medium border border-[#EAE1D7]">
                                                            🚚 Ongkir Standar
                                                        </span>
                                                    </template>

                                                    <!-- Voucher eligibility indicator -->
                                                    <template x-if="item.allow_vouchers === false">
                                                        <span class="text-[10px] text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded font-medium border border-amber-200" title="Produk ini tidak dapat menggunakan voucher promo">
                                                            🚫 Tanpa Voucher
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right: Quantity Controller & Trash (WORKS WITHOUT NEEDING SELECTION) -->
                                        <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-5 pt-3 sm:pt-0 border-t sm:border-t-0 border-[#F2EAE0] shrink-0">
                                            
                                            <!-- Quantity Controller with Input -->
                                            <div class="flex items-center border border-[#EAE1D7] bg-white rounded-xl overflow-hidden shadow-2xs">
                                                <button type="button" 
                                                        @click="updateQty(item.cartKey || item.id, -1)" 
                                                        class="w-8 h-8 flex items-center justify-center text-[#5A4B40] hover:bg-[#FAF4ED] hover:text-[#6B4226] active:bg-[#F2E5D5] transition font-bold text-sm cursor-pointer"
                                                        title="Kurangi Jumlah">-</button>
                                                <input type="number" 
                                                       min="1" 
                                                       :value="item.quantity" 
                                                       @change="setQty(item.cartKey || item.id, $event.target.value)"
                                                       class="w-10 text-center text-xs font-mono font-bold text-[#2D241E] border-0 focus:ring-0 p-0 bg-transparent"
                                                       title="Jumlah Produk">
                                                <button type="button" 
                                                        @click="updateQty(item.cartKey || item.id, 1)" 
                                                        class="w-8 h-8 flex items-center justify-center text-[#5A4B40] hover:bg-[#FAF4ED] hover:text-[#6B4226] active:bg-[#F2E5D5] transition font-bold text-sm cursor-pointer"
                                                        title="Tambah Jumlah">+</button>
                                            </div>

                                            <!-- Subtotal Amount -->
                                            <div class="text-right min-w-[110px]">
                                                <span class="text-[10px] text-[#8A7C70] block">Subtotal:</span>
                                                <span class="text-sm sm:text-base font-black text-[#6B4226]" 
                                                      x-text="formatRupiah(item.price * item.quantity)"></span>
                                            </div>

                                            <!-- Trash Button (Works without needing selection!) -->
                                            <button type="button" 
                                                    @click="removeItem(item.cartKey || item.id)" 
                                                    class="p-2 text-[#8A7C70] hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer active:scale-95"
                                                    title="Hapus Produk dari Keranjang">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>

                                        </div>

                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>
                </template>
            </div>

            <!-- Voucher Bar Strip Card -->
            <div class="p-4 bg-white rounded-2xl border border-[#EAE1D7] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="flex items-center gap-2 text-[#5A4B40]">
                    <span class="text-lg">🎟️</span>
                    <span class="font-bold">Voucher Marketplace:</span>
                    <span class="text-[#7A6C60]">Punya kode voucher diskon? Kamu bisa memasukkannya di halaman checkout!</span>
                </div>
            </div>

            <!-- Own Product Alert Banner -->
            <template x-if="hasOwnProductInSelection">
                <div class="p-3.5 sm:p-4 bg-amber-50 border border-amber-300 rounded-2xl flex items-start sm:items-center gap-3 text-xs text-amber-900 shadow-2xs">
                    <span class="text-2xl shrink-0">⚠️</span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-amber-950">Ada produk dari tokomu sendiri yang terpilih!</p>
                        <p class="text-amber-800 text-[11px] mt-0.5">Penjual tidak diperbolehkan membeli produk dari toko sendiri. Silakan hilangkan centang pada produk bertanda <span class="font-bold bg-amber-200/70 px-1.5 py-0.5 rounded">Produk Tokomu</span> agar dapat melanjutkan ke checkout.</p>
                    </div>
                </div>
            </template>

            <!-- Sticky / Action Bottom Bar -->
            <div class="sticky bottom-4 z-30 bg-white rounded-3xl border border-[#EAE1D7] shadow-xl p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <!-- Left selection summary -->
                <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-start">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-[#5A4B40]">
                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="w-4 h-4 text-[#6B4226] border-stone-300 rounded focus:ring-[#6B4226] cursor-pointer">
                        <span>Pilih Semua (<span x-text="items.length"></span>)</span>
                    </label>
                    <button @click="clearAll()" class="text-xs text-[#8A7C70] hover:text-red-600 transition cursor-pointer">
                        Kosongkan
                    </button>
                </div>

                <!-- Right Price & Checkout Button -->
                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
                    <div class="text-right">
                        <p class="text-xs text-[#7A6C60] font-medium">Total (<span x-text="selectedCount"></span> produk):</p>
                        <p class="text-xl sm:text-2xl font-black text-[#6B4226]" x-text="formatRupiah(selectedAmount)"></p>
                    </div>

                    @guest
                        <button type="button" 
                                @click="window.showAuthModal({ icon: '🔒', title: 'Masuk untuk Melanjutkan Checkout', message: 'Yuk masuk ke akunmu terlebih dahulu untuk memilih alamat pengiriman, opsi pembayaran, dan menyelesaikan pesananmu.' })"
                                class="px-6 sm:px-8 py-3.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-2xl text-xs sm:text-sm shadow-xs transition transform active:scale-95 whitespace-nowrap cursor-pointer">
                            Masuk untuk Checkout 🔒
                        </button>
                    @else
                        <button type="button" 
                                @click="proceedToCheckout()"
                                :disabled="selectedCount === 0"
                                :class="selectedCount === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:scale-102 active:scale-95 cursor-pointer'"
                                class="px-6 sm:px-10 py-3.5 sm:py-4 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-2xl text-sm shadow-sm transition transform whitespace-nowrap flex items-center gap-2">
                            <span>Checkout (<span x-text="selectedCount"></span>)</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    @endguest
                </div>

            </div>

        </div>

    </div>

    <!-- SHOPEE STYLE VARIANT CHANGER MODAL -->
    <div x-show="showVariantModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="showVariantModal = false">
        
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-[#EAE1D7] overflow-hidden transform transition-all"
             @click.outside="showVariantModal = false">
            
            <!-- Modal Header -->
            <div class="p-5 border-b border-[#F2EAE0] flex items-center justify-between bg-[#FCFAF7]">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">✨</span>
                    <div>
                        <h3 class="font-bold text-base text-[#2D241E]">Ubah Variasi Produk</h3>
                        <p class="text-xs text-[#8A7C70] line-clamp-1" x-text="currentEditingItem ? currentEditingItem.name : ''"></p>
                    </div>
                </div>
                <button type="button" 
                        @click="showVariantModal = false" 
                        class="w-9 h-9 rounded-full hover:bg-stone-200/60 flex items-center justify-center text-stone-500 hover:text-stone-800 transition cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5 max-h-[65vh] overflow-y-auto">
                
                <!-- Loading State -->
                <template x-if="isLoadingVariants">
                    <div class="py-12 text-center space-y-3">
                        <div class="w-10 h-10 border-3 border-[#6B4226] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-xs font-semibold text-[#8A7C70]">Memuat pilihan variasi...</p>
                    </div>
                </template>

                <!-- No Variants State -->
                <template x-if="!isLoadingVariants && modalVariants.length === 0">
                    <div class="py-8 text-center space-y-2">
                        <span class="text-4xl">📦</span>
                        <h4 class="font-bold text-sm text-[#2D241E]">Tidak Ada Variasi Lain</h4>
                        <p class="text-xs text-[#8A7C70] max-w-xs mx-auto">Produk ini hanya tersedia dalam varian standar saat ini.</p>
                    </div>
                </template>

                <!-- Available Variants Grid -->
                <template x-if="!isLoadingVariants && modalVariants.length > 0">
                    <div class="space-y-3">
                        <p class="text-xs font-bold text-[#5A4B40] uppercase tracking-wider">Pilih Variasi yang Tersedia:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <template x-for="variant in modalVariants" :key="variant.id">
                                <button type="button"
                                        @click="selectModalVariant(variant)"
                                        class="p-3 rounded-2xl border text-left transition-all duration-150 flex items-center gap-3 cursor-pointer group relative"
                                        :class="selectedModalVariantId === variant.id 
                                            ? 'border-[#6B4226] bg-[#FAF4ED] ring-2 ring-[#6B4226]/30 shadow-xs' 
                                            : 'border-[#EAE1D7] bg-white hover:bg-[#FAF8F5] hover:border-[#6B4226]/40'">
                                    
                                    <!-- Variant Image Thumbnail -->
                                    <div class="w-12 h-12 rounded-xl bg-stone-100 border border-[#EAE1D7] overflow-hidden shrink-0 flex items-center justify-center">
                                        <template x-if="variant.image_url">
                                            <img :src="variant.image_url" :alt="variant.name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!variant.image_url">
                                            <span class="text-xl">🎨</span>
                                        </template>
                                    </div>

                                    <!-- Variant Info -->
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-xs text-[#2D241E] truncate group-hover:text-[#6B4226]" x-text="variant.name"></p>
                                        <p class="font-extrabold text-xs text-[#6B4226] mt-0.5" x-text="formatRupiah(variant.price)"></p>
                                        <span class="text-[10px] text-[#8A7C70]" x-text="'Stok: ' + variant.stock"></span>
                                    </div>

                                    <!-- Active Check Indicator -->
                                    <template x-if="selectedModalVariantId === variant.id">
                                        <div class="w-5 h-5 rounded-full bg-[#6B4226] text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                                            ✓
                                        </div>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 border-t border-[#F2EAE0] bg-[#FCFAF7] flex items-center justify-end gap-3">
                <button type="button" 
                        @click="showVariantModal = false" 
                        class="px-5 py-2.5 rounded-xl border border-[#EAE1D7] text-xs font-bold text-[#5A4B40] hover:bg-stone-100 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" 
                        @click="confirmVariantChange()" 
                        :disabled="isLoadingVariants || modalVariants.length === 0 || !selectedModalVariantId"
                        :class="(isLoadingVariants || modalVariants.length === 0 || !selectedModalVariantId) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#54321B] cursor-pointer active:scale-95'"
                        class="px-6 py-2.5 rounded-xl bg-[#6B4226] text-white text-xs font-bold shadow-xs transition">
                    Konfirmasi Perubahan
                </button>
            </div>

        </div>
    </div>

    <!-- FLOATING TOAST NOTIFICATION -->
    <div x-show="showVariantToast"
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-24 right-6 z-50 bg-[#2D241E] text-white text-xs font-bold px-4 py-3 rounded-2xl shadow-xl border border-[#FAF4ED]/20 flex items-center gap-2">
        <span class="text-base">✨</span>
        <span x-text="variantToastMsg"></span>
    </div>

</div>
@endsection
