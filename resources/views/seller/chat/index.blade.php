@extends('seller.layout')

@section('title', 'Chat Pelanggan — Seller Center')

@section('content')
<div class="space-y-6">

    <!-- Top Navigation with Back Link to Dashboard -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('seller.dashboard') }}" 
               class="w-10 h-10 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 flex items-center justify-center transition active:scale-95 cursor-pointer shrink-0 shadow-2xs group"
               title="Kembali ke Dashboard Toko">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-[#2D241E] tracking-tight flex items-center gap-2">
                    <span>Chat & Pesan Pelanggan</span>
                    <span class="text-xl">💬</span>
                </h1>
                <p class="text-xs text-[#8A7C70]">Respon pertanyaan pembeli dengan cepat untuk meningkatkan konversi dan rating tokomu</p>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Respon Cepat Aktif</span>
        </div>
    </div>

    <!-- MAIN CHAT CONTAINER (SPLIT-VIEW) -->
    <div x-data="sellerChatRoom({{ $conversation ? $conversation->id : 'null' }})"
         class="bg-white rounded-3xl border border-[#EAE1D7] shadow-xs overflow-hidden flex flex-col md:flex-row h-[720px] max-h-[82vh]">

        <!-- LEFT PANEL: CUSTOMER CONVERSATION LIST -->
        <div class="w-full md:w-80 lg:w-96 border-r border-[#EAE1D7] flex flex-col shrink-0 bg-[#FCFAF8] h-full"
             :class="{ 'hidden md:flex': activeConversationId && isMobileView, 'flex': !activeConversationId || !isMobileView }">

            <!-- Search & Filter Header -->
            <div class="p-4 border-b border-[#EAE1D7] bg-white space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black text-[#2D241E] uppercase tracking-wider flex items-center gap-1.5">
                        <span>Daftar Calon Pembeli</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#FAF4ED] border border-[#EAE1D7] text-[#6B4226] text-[10px]">
                            {{ $conversations->count() }}
                        </span>
                    </h2>
                </div>

                <form action="{{ route('seller.chat.index') }}" method="GET" class="relative">
                    <input type="text"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari nama pembeli..."
                           class="w-full pl-9 pr-4 py-2 bg-[#FAF8F5] border border-[#EAE1D7] rounded-xl text-xs text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20">
                    <svg class="w-4 h-4 text-[#8A7C70] absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </form>
            </div>

            <!-- List Area -->
            <div class="flex-1 overflow-y-auto divide-y divide-[#F2EAE0]">
                @forelse($conversations as $conv)
                    @php
                        $unread = $conv->messages()->where('sender_id', $conv->user_id)->where('is_read', false)->count();
                        $isActive = $conversation && $conversation->id === $conv->id;
                        $latest = $conv->latestMessage;
                    @endphp
                    <a href="{{ route('seller.chat.show', $conv->id) }}"
                       class="block p-3.5 hover:bg-[#FAF4ED] transition cursor-pointer {{ $isActive ? 'bg-[#FAF4ED] border-l-4 border-l-[#6B4226]' : '' }}">
                        <div class="flex items-start gap-3">
                            <!-- Buyer Avatar Initial -->
                            <div class="relative shrink-0">
                                <div class="w-11 h-11 rounded-2xl bg-[#EAE1D7] text-[#6B4226] flex items-center justify-center font-black text-sm shadow-2xs">
                                    {{ strtoupper(substr($conv->user->name, 0, 1)) }}
                                </div>
                                @if($unread > 0)
                                    <span class="absolute -top-1 -right-1 w-4.5 h-4.5 bg-red-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center border-2 border-white shadow-2xs">
                                        {{ $unread }}
                                    </span>
                                @endif
                            </div>

                            <!-- Customer Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <h3 class="text-xs font-black text-[#2D241E] truncate">
                                        {{ $conv->user->name }}
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

                                @if($latest && $latest->product)
                                    <div class="mt-1 flex items-center gap-1 text-[10px] text-[#6B4226] font-medium truncate">
                                        <span>🛍️</span>
                                        <span class="truncate">{{ $latest->product->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-[#8A7C70] space-y-2">
                        <span class="text-3xl block">📭</span>
                        <p class="text-xs font-medium">Belum ada chat dari pembeli.</p>
                        <p class="text-[11px]">Pertanyaan dari calon pembeli produk tokomu akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT PANEL: ACTIVE CHAT ROOM -->
        <div class="flex-1 flex flex-col bg-[#FAF8F5] h-full"
             :class="{ 'flex': activeConversationId || !isMobileView, 'hidden md:flex': !activeConversationId && isMobileView }">

            @if($conversation)
                <!-- Chat Top Bar -->
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

                        <div class="w-10 h-10 rounded-2xl bg-[#EAE1D7] text-[#6B4226] flex items-center justify-center font-black text-sm shadow-xs shrink-0">
                            {{ strtoupper(substr($conversation->user->name, 0, 1)) }}
                        </div>

                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h2 class="text-sm font-black text-[#2D241E] leading-tight">
                                    {{ $conversation->user->name }}
                                </h2>
                                <span class="bg-stone-100 text-stone-700 text-[9px] font-bold px-1.5 py-0.5 rounded-md">
                                    Pelanggan
                                </span>
                            </div>
                            <p class="text-[11px] text-[#8A7C70] flex items-center gap-1">
                                <span>✉️ {{ $conversation->user->email }}</span>
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('seller.orders') }}"
                       class="px-3.5 py-1.5 rounded-xl border border-[#EAE1D7] bg-white hover:bg-[#FAF4ED] text-[#6B4226] text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                        <span>📑</span>
                        <span class="hidden sm:inline">Pesanan Masuk</span>
                    </a>
                </div>

                <!-- Messages Stream Area -->
                <div id="sellerMessagesContainer"
                     class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4">

                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex flex-col"
                             :class="msg.is_mine ? 'items-end' : 'items-start'">

                            <!-- Attached Product Card (e.g. from buyer question) -->
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
                                        <span class="text-[9px] text-[#8A7C70] block font-bold uppercase tracking-wider">Produk Ditanyakan</span>
                                        <h5 class="text-xs font-bold text-[#2D241E] truncate" x-text="msg.product.name"></h5>
                                        <span class="text-xs font-black text-[#6B4226] block" x-text="msg.product.price"></span>
                                        <a :href="msg.product.url" target="_blank" class="text-[10px] text-[#6B4226] hover:underline font-bold mt-0.5 inline-block">
                                            Lihat di Toko ›
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
                                        <span :title="msg.is_read ? 'Sudah dibaca pembeli' : 'Terkirim'"
                                              :class="msg.is_read ? 'text-amber-300 font-bold' : 'text-white/60'">
                                            ✓✓
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div x-show="messages.length === 0" class="text-center py-10 text-[#8A7C70] space-y-2">
                        <span class="text-3xl block">👋</span>
                        <p class="text-xs font-medium">Belum ada pesan dengan pembeli ini.</p>
                        <p class="text-[11px]">Kirim salam pembuka untuk mulai berinteraksi.</p>
                    </div>
                </div>

                <!-- Seller Quick Reply Chips -->
                <div class="px-4 py-2 bg-white border-t border-[#F2EAE0] flex items-center gap-1.5 overflow-x-auto text-[11px] shrink-0">
                    <span class="text-[#8A7C70] font-bold shrink-0">Template Cepat:</span>
                    <button type="button" @click="insertQuickText('Halo kak! Produk ini ready stock ya, silakan langsung diorder 😊')" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] transition shrink-0 whitespace-nowrap">
                        Ready silakan order
                    </button>
                    <button type="button" @click="insertQuickText('Bisa dikirim hari ini ya kak untuk order sebelum jam 15:00 WIB 📦')" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] transition shrink-0 whitespace-nowrap">
                        Kirim hari ini
                    </button>
                    <button type="button" @click="insertQuickText('Semua produk di toko kami 100% original dan bergaransi resmi kak ✨')" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] transition shrink-0 whitespace-nowrap">
                        100% Original
                    </button>
                    <button type="button" @click="insertQuickText('Terima kasih sudah menghubungi toko kami kak, ada yang bisa dibantu lagi? 🙏')" class="px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#FAF4ED] text-[#6B4226] border border-[#EAE1D7] transition shrink-0 whitespace-nowrap">
                        Terima kasih
                    </button>
                </div>

                <!-- Chat Input Composer Form -->
                <div class="p-3 sm:p-4 bg-white border-t border-[#EAE1D7] shrink-0">
                    <form @submit.prevent="submitMessage()" class="flex items-end gap-2">
                        <div class="flex-1 relative">
                            <textarea x-model="newMessage"
                                      @keydown.enter.exact.prevent="submitMessage()"
                                      rows="1"
                                      placeholder="Ketik balasan untuk pembeli... (Enter untuk kirim)"
                                      class="w-full px-4 py-3 bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl text-xs sm:text-sm text-[#2D241E] focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 resize-none max-h-28"></textarea>
                        </div>
                        <button type="submit"
                                :disabled="!newMessage.trim() || isSending"
                                class="px-5 py-3 rounded-2xl bg-[#6B4226] hover:bg-[#54321B] disabled:opacity-50 text-white font-bold text-xs sm:text-sm transition flex items-center justify-center gap-1.5 shadow-md active:scale-95 cursor-pointer shrink-0">
                            <span x-show="!isSending">Kirim Balasan</span>
                            <span x-show="isSending" class="animate-spin">⏳</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </form>
                </div>

            @else
                <!-- Empty Conversation Selected -->
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-[#8A7C70]">
                    <div class="w-20 h-20 rounded-3xl bg-white border border-[#EAE1D7] flex items-center justify-center text-4xl shadow-xs mb-4">
                        💬
                    </div>
                    <h3 class="text-base font-black text-[#2D241E] mb-1">
                        Pilih Percakapan Pembeli
                    </h3>
                    <p class="text-xs max-w-sm">
                        Pilih salah satu pembeli di panel sebelah kiri untuk merespon chat atau melihat riwayat obrolan.
                    </p>
                </div>
            @endif

        </div>

    </div>

</div>

@push('scripts')
<script>
function sellerChatRoom(conversationId) {
    return {
        activeConversationId: conversationId,
        isMobileView: window.innerWidth < 768,
        messages: [],
        newMessage: '',
        isSending: false,
        pollingTimer: null,

        init() {
            window.addEventListener('resize', () => {
                this.isMobileView = window.innerWidth < 768;
            });

            if (this.activeConversationId) {
                this.loadMessages();
                this.pollingTimer = setInterval(() => {
                    this.loadMessages(false);
                }, 3000);
            }
        },

        backToList() {
            this.activeConversationId = null;
        },

        insertQuickText(text) {
            this.newMessage = text;
        },

        loadMessages(shouldScroll = true) {
            if (!this.activeConversationId) return;

            fetch(`/seller/chat/${this.activeConversationId}/messages`, {
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
            .catch(err => console.error('Error polling messages on seller:', err));
        },

        submitMessage() {
            if (!this.newMessage.trim() || this.isSending || !this.activeConversationId) return;

            this.isSending = true;
            const messagePayload = {
                message: this.newMessage.trim()
            };

            fetch(`/seller/chat/${this.activeConversationId}/messages`, {
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
                    this.$nextTick(() => this.scrollToBottom());
                }
            })
            .catch(err => {
                this.isSending = false;
                console.error('Error sending message:', err);
            });
        },

        scrollToBottom() {
            const container = document.getElementById('sellerMessagesContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        isScrolledToBottom() {
            const container = document.getElementById('sellerMessagesContainer');
            if (!container) return true;
            return container.scrollHeight - container.scrollTop <= container.clientHeight + 50;
        }
    };
}
</script>
@endpush
@endsection
