@extends('layouts.app')

@section('title', 'Chat Penjual — NusantaraMart')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Breadcrumb & Back -->
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('home') }}" 
           class="w-9 h-9 rounded-full bg-white hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] flex items-center justify-center transition shadow-2xs group"
           title="Kembali ke Beranda">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <div class="flex items-center gap-1.5 text-xs text-[#8A7C70]">
                <a href="{{ route('home') }}" class="hover:text-[#6B4226]">Beranda</a>
                <span>/</span>
                <span class="text-[#6B4226] font-semibold">Pesan & Obrolan</span>
            </div>
            <h1 class="text-lg sm:text-xl font-black text-[#2D241E] tracking-tight">
                Obrolan Penjual 💬
            </h1>
        </div>
    </div>

    <!-- MAIN CHAT CONTAINER (SPLIT-VIEW) -->
    <div x-data="buyerChatRoom({{ $conversation ? $conversation->id : 'null' }})"
         class="bg-white rounded-3xl border border-[#EAE1D7] shadow-sm overflow-hidden flex flex-col md:flex-row h-[720px] max-h-[82vh]">

        <!-- LEFT PANEL: CONVERSATION LIST (Stores) -->
        <div class="w-full md:w-80 lg:w-96 border-r border-[#EAE1D7] flex flex-col shrink-0 bg-[#FCFAF8] h-full"
             :class="{ 'hidden md:flex': activeConversationId && isMobileView, 'flex': !activeConversationId || !isMobileView }">
            
            <!-- Header List -->
            <div class="p-4 border-b border-[#EAE1D7] bg-white space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-black text-[#2D241E] flex items-center gap-2">
                        <span>Pesan Masuk</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#FAF4ED] border border-[#EAE1D7] text-[#6B4226] text-[10px] font-bold">
                            {{ $conversations->count() }} Toko
                        </span>
                    </h2>
                </div>

                <!-- Search Conversation Filter -->
                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari toko..."
                           class="w-full pl-9 pr-4 py-2 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                    <svg class="w-4 h-4 text-[#8A7C70] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Conversations Scrollable Area -->
            <div class="flex-1 overflow-y-auto divide-y divide-[#F2EAE0]">
                @forelse($conversations as $conv)
                    @php
                        $unread = $conv->unreadCountForUser(auth()->id());
                        $isActive = $conversation && $conversation->id === $conv->id;
                        $latest = $conv->latestMessage;
                    @endphp
                    <a href="{{ route('chat.show', $conv->id) }}"
                       x-show="matchesSearch('{{ addslashes($conv->store->name) }}')"
                       class="block p-3.5 hover:bg-[#FAF4ED] transition cursor-pointer {{ $isActive ? 'bg-[#FAF4ED] border-l-4 border-l-[#6B4226]' : '' }}">
                        <div class="flex items-start gap-3">
                            <!-- Store Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#6B4226] to-[#452713] text-white flex items-center justify-center font-bold text-sm shadow-xs overflow-hidden">
                                    @if($conv->store->logo_url)
                                        <img src="{{ $conv->store->logo_url }}" alt="{{ $conv->store->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span>🏬</span>
                                    @endif
                                </div>
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white" title="Online"></span>
                            </div>

                            <!-- Store & Snippet -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <h3 class="text-xs font-black text-[#2D241E] truncate">
                                        {{ $conv->store->name }}
                                    </h3>
                                    <span class="text-[10px] text-[#8A7C70] shrink-0">
                                        {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true, true) : '' }}
                                    </span>
                                </div>

                                <p class="text-xs truncate {{ $unread > 0 ? 'font-bold text-[#2D241E]' : 'text-[#8A7C70]' }}">
                                    @if($latest)
                                        @if($latest->sender_id === auth()->id())
                                            <span class="text-[#6B4226]">Anda: </span>
                                        @endif
                                        {{ $latest->message }}
                                    @else
                                        <span class="italic text-[#8A7C70]">Belum ada pesan</span>
                                    @endif
                                </p>

                                <div class="flex items-center justify-between mt-1.5">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-[#FAF8F5] text-[#8A7C70] border border-[#EAE1D7]">
                                        📍 {{ $conv->store->city }}
                                    </span>
                                    @if($unread > 0)
                                        <span class="w-5 h-5 rounded-full bg-[#6B4226] text-white text-[10px] font-bold flex items-center justify-center shadow-xs">
                                            {{ $unread }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-[#8A7C70] space-y-3">
                        <span class="text-4xl block">💬</span>
                        <p class="text-xs font-medium">Belum ada obrolan dengan penjual.</p>
                        <p class="text-[11px] text-[#8A7C70]">Buka salah satu produk lalu klik tombol "Chat Penjual" untuk memulai!</p>
                        <a href="{{ route('home') }}" class="inline-block px-4 py-2 rounded-xl bg-[#6B4226] text-white text-xs font-bold shadow-xs hover:bg-[#54321B]">
                            Eksplor Produk 🛍️
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT PANEL: ACTIVE CHAT ROOM -->
        <div class="flex-1 flex flex-col bg-[#FAF8F5] h-full"
             :class="{ 'flex': activeConversationId || !isMobileView, 'hidden md:flex': !activeConversationId && isMobileView }">

            @if($conversation)
                <!-- Chat Room Top Header -->
                <div class="p-3.5 sm:p-4 bg-white border-b border-[#EAE1D7] flex items-center justify-between gap-3 shrink-0 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <!-- Mobile Back Button -->
                        <button type="button"
                                @click="backToList()"
                                class="md:hidden w-8 h-8 rounded-full bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#6B4226] to-[#452713] text-white flex items-center justify-center text-lg font-bold shadow-xs shrink-0 overflow-hidden">
                            @if($conversation->store->logo_url)
                                <img src="{{ $conversation->store->logo_url }}" alt="{{ $conversation->store->name }}" class="w-full h-full object-cover">
                            @else
                                <span>🏬</span>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h2 class="text-sm font-black text-[#2D241E] leading-tight">
                                    {{ $conversation->store->name }}
                                </h2>
                                <span class="bg-blue-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase">
                                    Official
                                </span>
                            </div>
                            <p class="text-[11px] text-emerald-700 font-medium flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Online · Siap merespon pertanyaan</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('store.show', urlencode($conversation->store->name)) }}"
                           target="_blank"
                           class="px-3.5 py-1.5 rounded-xl border border-[#EAE1D7] bg-white hover:bg-[#FAF4ED] text-[#6B4226] text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                            <span>🏪</span>
                            <span class="hidden sm:inline">Kunjungi Toko</span>
                        </a>
                    </div>
                </div>

                <!-- Attached Product Card Banner (if initiated from a product inquiry) -->
                @if($activeProduct)
                    <div class="bg-white px-4 py-2.5 border-b border-[#EAE1D7] flex items-center justify-between gap-3 shrink-0">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-11 h-11 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] flex items-center justify-center overflow-hidden shrink-0">
                                @if($activeProduct->product_image_url)
                                    <img src="{{ $activeProduct->product_image_url }}" alt="{{ $activeProduct->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-lg">{{ $activeProduct->category->icon ?? '🛍️' }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-[#8A7C70] block font-medium">Sedang mendiskusikan produk:</span>
                                <h4 class="text-xs font-bold text-[#2D241E] truncate max-w-md">{{ $activeProduct->name }}</h4>
                                <span class="text-xs font-black text-[#6B4226]">{{ $activeProduct->formatted_effective_price }}</span>
                            </div>
                        </div>
                        <button type="button"
                                @click="sendProductAttachment({{ $activeProduct->id }}, '{{ addslashes($activeProduct->name) }}')"
                                class="px-3 py-1.5 rounded-xl bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold shrink-0 transition shadow-2xs">
                            Kirim Kartu Produk ↗
                        </button>
                    </div>
                @endif

                <!-- Message Stream Area -->
                <div id="messagesContainer"
                     class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4">

                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex flex-col"
                             :class="msg.is_mine ? 'items-end' : 'items-start'">

                            <!-- Attached Product Card Inside Bubble -->
                            <template x-if="msg.product">
                                <div class="mb-1.5 p-2.5 bg-white border border-[#EAE1D7] rounded-2xl shadow-2xs max-w-xs sm:max-w-sm flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#FAF8F5] border border-[#EAE1D7] flex items-center justify-center overflow-hidden shrink-0">
                                        <template x-if="msg.product.image">
                                            <img :src="msg.product.image" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!msg.product.image">
                                            <span class="text-xl">🛍️</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h5 class="text-xs font-bold text-[#2D241E] truncate" x-text="msg.product.name"></h5>
                                        <span class="text-xs font-black text-[#6B4226] block" x-text="msg.product.price"></span>
                                        <a :href="msg.product.url" target="_blank" class="text-[10px] text-[#6B4226] hover:underline font-bold mt-0.5 inline-block">
                                            Lihat Produk ›
                                        </a>
                                    </div>
                                </div>
                            </template>

                            <!-- Message Bubble -->
                            <div class="max-w-[85%] sm:max-w-md px-4 py-2.5 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-2xs"
                                 :class="msg.is_mine 
                                    ? 'bg-[#6B4226] text-white rounded-tr-xs' 
                                    : 'bg-white text-[#2D241E] border border-[#EAE1D7] rounded-tl-xs'">
                                <p class="whitespace-pre-wrap break-words" x-text="msg.message"></p>
                                <div class="flex items-center justify-end gap-1 mt-1 text-[10px]"
                                     :class="msg.is_mine ? 'text-white/70' : 'text-[#8A7C70]'">
                                    <span x-text="msg.time"></span>
                                    <template x-if="msg.is_mine">
                                        <span :title="msg.is_read ? 'Sudah dibaca' : 'Terkirim'"
                                              :class="msg.is_read ? 'text-amber-300 font-bold' : 'text-white/60'">
                                            ✓✓
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Messages in Conversation -->
                    <div x-show="messages.length === 0" class="text-center py-10 text-[#8A7C70] space-y-2">
                        <span class="text-3xl block">👋</span>
                        <p class="text-xs font-medium">Mulai percakapan dengan penjual toko ini.</p>
                        <p class="text-[11px]">Tanyakan informasi stok, pengiriman, atau spesifikasi barang.</p>
                    </div>
                </div>

                <!-- Quick Replies Chips -->
                <div class="px-4 py-2 bg-white border-t border-[#F2EAE0] flex items-center gap-1.5 overflow-x-auto text-[11px] shrink-0">
                    <span class="text-[#8A7C70] font-bold shrink-0">Cepat:</span>
                    <button type="button" @click="insertQuickText('Halo kak, apakah produk ini ready stock? 😊')" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] transition shrink-0 whitespace-nowrap">
                        Ready stock?
                    </button>
                    <button type="button" @click="insertQuickText('Bisa dikirim hari ini kak? 📦')" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] transition shrink-0 whitespace-nowrap">
                        Bisa kirim hari ini?
                    </button>
                    <button type="button" @click="insertQuickText('Apakah ada garansi resmi atau jaminan barang asli?')" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] transition shrink-0 whitespace-nowrap">
                        Garansi resmi?
                    </button>
                    <button type="button" @click="insertQuickText('Terima kasih banyak atas infonya kak! 🙏')" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] transition shrink-0 whitespace-nowrap">
                        Terima kasih!
                    </button>
                </div>

                <!-- Chat Input Composer Form -->
                <div class="p-3 sm:p-4 bg-white border-t border-[#EAE1D7] shrink-0">
                    <form @submit.prevent="submitMessage()" class="flex items-end gap-2">
                        <div class="flex-1 relative">
                            <textarea x-model="newMessage"
                                      @keydown.enter.exact.prevent="submitMessage()"
                                      rows="1"
                                      placeholder="Tulis pesan ke penjual... (Enter untuk kirim)"
                                      class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 resize-none max-h-28"></textarea>
                        </div>
                        <button type="submit"
                                :disabled="!newMessage.trim() || isSending"
                                class="px-5 py-3 rounded-2xl bg-[#6B4226] hover:bg-[#54321B] disabled:opacity-50 text-white font-bold text-xs sm:text-sm transition flex items-center justify-center gap-1.5 shadow-md active:scale-95 cursor-pointer shrink-0">
                            <span x-show="!isSending">Kirim</span>
                            <span x-show="isSending" class="animate-spin">⏳</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </form>
                </div>

            @else
                <!-- No Conversation Selected State -->
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-[#8A7C70]">
                    <div class="w-20 h-20 rounded-3xl bg-white border border-[#EAE1D7] flex items-center justify-center text-4xl shadow-xs mb-4">
                        💬
                    </div>
                    <h3 class="text-base font-black text-[#2D241E] mb-1">
                        Pilih Percakapan Toko
                    </h3>
                    <p class="text-xs max-w-sm">
                        Pilih salah satu toko di panel sebelah kiri untuk melihat pesan atau mulai mengobrol.
                    </p>
                </div>
            @endif

        </div>

    </div>

</div>

@push('scripts')
<script>
function buyerChatRoom(conversationId) {
    return {
        activeConversationId: conversationId,
        isMobileView: window.innerWidth < 768,
        searchQuery: '',
        messages: [],
        newMessage: '',
        attachedProductId: null,
        isSending: false,
        pollingTimer: null,

        init() {
            window.addEventListener('resize', () => {
                this.isMobileView = window.innerWidth < 768;
            });

            if (this.activeConversationId) {
                this.loadMessages();
                // Start polling every 3 seconds
                this.pollingTimer = setInterval(() => {
                    this.loadMessages(false);
                }, 3000);
            }
        },

        matchesSearch(storeName) {
            if (!this.searchQuery.trim()) return true;
            return storeName.toLowerCase().includes(this.searchQuery.toLowerCase());
        },

        backToList() {
            this.activeConversationId = null;
        },

        insertQuickText(text) {
            this.newMessage = text;
        },

        sendProductAttachment(productId, productName) {
            this.attachedProductId = productId;
            this.newMessage = `Halo kak, saya tertarik dengan produk ${productName} ini. Apakah masih tersedia?`;
            this.submitMessage();
        },

        loadMessages(shouldScroll = true) {
            if (!this.activeConversationId) return;

            fetch(`/chat/${this.activeConversationId}/messages`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const wasAtBottom = this.isScrolledToBottom();
                    const prevCount = this.messages.length;
                    this.messages = data.messages;

                    if (shouldScroll || (wasAtBottom && data.messages.length > prevCount)) {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                }
            })
            .catch(err => console.error('Error polling messages:', err));
        },

        submitMessage() {
            if (!this.newMessage.trim() || this.isSending || !this.activeConversationId) return;

            this.isSending = true;
            const messagePayload = {
                message: this.newMessage.trim(),
                product_id: this.attachedProductId
            };

            fetch(`/chat/${this.activeConversationId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                },
                body: JSON.stringify(messagePayload)
            })
            .then(res => res.json())
            .then(data => {
                this.isSending = false;
                if (data.status === 'success') {
                    this.messages.push(data.message);
                    this.newMessage = '';
                    this.attachedProductId = null;
                    this.$nextTick(() => this.scrollToBottom());
                }
            })
            .catch(err => {
                this.isSending = false;
                console.error('Error sending message:', err);
            });
        },

        scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        isScrolledToBottom() {
            const container = document.getElementById('messagesContainer');
            if (!container) return true;
            return container.scrollHeight - container.scrollTop <= container.clientHeight + 50;
        }
    };
}
</script>
@endpush
@endsection
