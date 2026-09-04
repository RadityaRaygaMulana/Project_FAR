@extends('layouts.admin')

@section('title', 'Catatan Log Sistem & Audit Trail Real-Time — NusantaraMart Admin')

@section('content')
<div class="space-y-6"
     x-data="{
        logSearch: '',
        selectedLevel: '{{ request('level', 'ALL') }}',
        isStreaming: true,
        pollingTimer: null,
        confirmClearModalOpen: false,
        logSize: '{{ $logSize }}',
        lastUpdated: '{{ now()->format('H:i:s') }}',
        entries: [
            @foreach($logEntries as $idx => $entry)
            {
                id: {{ $idx }},
                level: {{ Js::from($entry['level']) }},
                isAudit: {{ $entry['is_audit'] ? 'true' : 'false' }},
                audit: {{ Js::from($entry['audit'] ?? null) }},
                timestamp: {{ Js::from($entry['timestamp']) }},
                message: {{ Js::from($entry['message']) }},
                showRaw: false
            },
            @endforeach
        ],
        parseAudit(msg) {
            let category = 'SYSTEM';
            let m = msg.match(/^\[AUDIT:([A-Z]+)\]\s*(.*)$/);
            let body = m ? m[2] : msg;
            if (m) category = m[1];

            let parts = body.split('|').map(p => p.trim());
            let mainPart = parts[0] || '';
            let actorPart = parts[1] || '';
            let metaParts = parts.slice(2);

            let action = mainPart;
            let description = '';
            if (mainPart.includes(' • ')) {
                let s = mainPart.split(' • ');
                action = s[0].trim();
                description = s[1].trim();
            }

            let actor = actorPart.replace(/^(Pengguna:|Pemesan:|Akun:|Pelanggan:|Administrator:|Dilakukan oleh:\s*Administrator:|Dilakukan oleh:)\s*/i, '').trim();

            let ip = '';
            let extra = [];
            metaParts.forEach(mp => {
                if (mp.startsWith('IP:')) {
                    ip = mp.substring(3).trim();
                } else if (mp) {
                    extra.push(mp);
                }
            });

            return { category, action, description, actor, ip, meta: extra };
        },
        getAuditInfo(entry) {
            return entry.audit || this.parseAudit(entry.message);
        },
        matches(e) {
            if (this.selectedLevel === 'AUDIT' && !e.isAudit) return false;
            if (this.selectedLevel !== 'ALL' && this.selectedLevel !== 'AUDIT' && e.level !== this.selectedLevel) return false;
            if (!this.logSearch.trim()) return true;
            let q = this.logSearch.toLowerCase().trim();
            return (e.message && e.message.toLowerCase().includes(q)) || 
                   (e.timestamp && e.timestamp.includes(q)) || 
                   (e.level && e.level.toLowerCase().includes(q));
        },
        get visibleCount() {
            return this.entries.filter(e => this.matches(e)).length;
        },
        fetchLatestLogs() {
            if (!this.isStreaming) return;
            fetch('{{ route('admin.logs.feed') }}?feed=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.entries) {
                    this.entries = data.entries.map((entry, idx) => ({
                        id: idx,
                        level: entry.level,
                        isAudit: entry.is_audit || (entry.message && entry.message.includes('[AUDIT')),
                        audit: entry.audit || null,
                        timestamp: entry.timestamp,
                        message: entry.message,
                        showRaw: false
                    }));
                    this.logSize = data.logSize || this.logSize;
                    this.lastUpdated = data.timestamp || this.lastUpdated;
                }
            })
            .catch(() => {});
        },
        toggleStream() {
            this.isStreaming = !this.isStreaming;
            if (this.isStreaming) {
                this.fetchLatestLogs();
            }
        },
        init() {
            this.pollingTimer = setInterval(() => {
                this.fetchLatestLogs();
            }, 2000);
        }
     }">

    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" 
               class="w-11 h-11 rounded-full bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-xs flex items-center justify-center transition active:scale-95 shrink-0"
               title="Kembali ke Dashboard">
                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">
                        Catatan Log Sistem & Audit Trail
                    </h1>
                    <!-- Real-Time Pulse Indicator -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold border shadow-2xs"
                          :class="isStreaming ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-800 border-amber-300'">
                        <span class="w-2 h-2 rounded-full" :class="isStreaming ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                        <span x-text="isStreaming ? 'LIVE STREAMING' : 'STREAM DIJEDA'"></span>
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pelacakan aktivitas sistem terstruktur & real-time • Ukuran Log: <span class="font-bold text-slate-700 font-mono" x-text="logSize"></span> • Terakhir Dicek: <span class="font-mono text-slate-700 font-bold" x-text="lastUpdated"></span>
                </p>
            </div>
        </div>

        <!-- STREAM CONTROLS & CLEAR -->
        <div class="flex items-center gap-2">
            <!-- Pause/Resume Toggle -->
            <button type="button" 
                    @click="toggleStream()" 
                    :class="isStreaming ? 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white border-transparent'"
                    class="px-3.5 py-2.5 border shadow-xs text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                <span x-text="isStreaming ? '⏸ Jeda Stream' : '▶ Lanjutkan Stream'"></span>
            </button>

            <!-- Manual Trigger -->
            <button type="button" 
                    @click="fetchLatestLogs()" 
                    class="px-3.5 py-2.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-xs text-xs font-bold rounded-xl transition cursor-pointer"
                    title="Muat data terkini sekarang">
                ↻ Tarik Baru
            </button>

            <!-- Trigger Clear Log Modal -->
            <button type="button" 
                    @click="confirmClearModalOpen = true"
                    class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl transition cursor-pointer active:scale-95">
                Kosongkan Log
            </button>
        </div>
    </div>

    <!-- FILTER & REAL-TIME SEARCH BAR -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            
            <!-- LEVEL SELECTOR -->
            <div class="w-full sm:w-64 shrink-0">
                <select x-model="selectedLevel" 
                        class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226] cursor-pointer">
                    <option value="ALL">Semua Catatan</option>
                    <option value="AUDIT">📜 Audit Trail (Aktivitas Pengguna & Admin)</option>
                    <option value="ERROR">🔴 ERROR (Galat)</option>
                    <option value="WARNING">🟡 WARNING (Peringatan)</option>
                    <option value="INFO">🔵 INFO (Informasi)</option>
                </select>
            </div>

            <!-- REAL-TIME SEARCH INPUT -->
            <div class="relative w-full">
                <input type="text" 
                       x-model="logSearch" 
                       placeholder="Cari aktivitas, username, aksi, IP address, kata kunci..." 
                       class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition font-mono">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                <button type="button" 
                        x-show="logSearch.length > 0" 
                        x-cloak 
                        @click="logSearch = ''" 
                        class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold w-5 h-5 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center transition cursor-pointer">
                    ✕
                </button>
            </div>

        </div>
    </div>

    <!-- LOG & AUDIT ENTRIES LIST -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-500 font-medium">
                Menampilkan <span class="font-bold text-slate-900" x-text="visibleCount"></span> entri
            </span>
            <div class="flex items-center gap-2 text-[11px] font-mono text-slate-400">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping" x-show="isStreaming"></span>
                <span>Auto-Poll (2s) • storage/logs/laravel.log</span>
            </div>
        </div>

        <template x-if="entries.length === 0">
            <div class="p-12 text-center space-y-2">
                <span class="text-3xl">✓</span>
                <p class="text-sm font-bold text-slate-800">Catatan log masih kosong</p>
                <p class="text-xs text-slate-500">Aktivitas baru yang terjadi akan otomatis muncul di sini secara real-time.</p>
            </div>
        </template>

        <template x-if="entries.length > 0">
            <div class="divide-y divide-slate-100 text-xs max-h-[580px] overflow-y-auto overscroll-contain scroll-smooth [scrollbar-width:thin] [scrollbar-color:#cbd5e1_transparent]" id="logs-container">
                <template x-for="entry in entries" :key="entry.id + '-' + entry.timestamp">
                    <div x-show="matches(entry)"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="p-4 hover:bg-slate-50/80 transition">

                        <!-- 1. STRUCTURED AUDIT TRAIL CARD -->
                        <template x-if="entry.isAudit">
                            <div class="space-y-2.5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        
                                        <!-- Category Pill -->
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border flex items-center gap-1.5 shadow-2xs"
                                              :class="{
                                                  'bg-blue-50 text-blue-700 border-blue-200': getAuditInfo(entry).category === 'AUTH',
                                                  'bg-emerald-50 text-emerald-700 border-emerald-200': getAuditInfo(entry).category === 'ORDER',
                                                  'bg-purple-50 text-purple-700 border-purple-200': getAuditInfo(entry).category === 'PROFILE',
                                                  'bg-amber-50 text-amber-800 border-amber-200': getAuditInfo(entry).category === 'ADMIN',
                                                  'bg-slate-100 text-slate-700 border-slate-200': !['AUTH', 'ORDER', 'PROFILE', 'ADMIN'].includes(getAuditInfo(entry).category)
                                              }">
                                            <span x-text="{
                                                'AUTH': '🔑 AUTENTIKASI',
                                                'ORDER': '🛍️ PESANAN',
                                                'PROFILE': '👤 PROFIL',
                                                'ADMIN': '⚙️ OPERASI ADMIN'
                                            }[getAuditInfo(entry).category] || ('📜 ' + getAuditInfo(entry).category)"></span>
                                        </span>

                                        <!-- Action Title -->
                                        <h3 class="font-extrabold text-sm text-slate-900 tracking-tight"
                                            x-text="getAuditInfo(entry).action">
                                        </h3>

                                    </div>

                                    <!-- Timestamp & Toggle Raw -->
                                    <div class="flex items-center gap-3 text-slate-400 font-mono text-[11px] self-start sm:self-auto">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span x-text="entry.timestamp"></span>
                                        </span>
                                        <button type="button" 
                                                @click="entry.showRaw = !entry.showRaw"
                                                class="text-slate-400 hover:text-slate-600 underline text-[10px] cursor-pointer">
                                            <span x-text="entry.showRaw ? 'Tutup Raw' : 'Raw Log'"></span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Description & Meta Info -->
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 space-y-2">
                                    
                                    <!-- Main Description -->
                                    <p class="text-xs text-slate-700 leading-relaxed font-medium"
                                       x-text="getAuditInfo(entry).description || entry.message">
                                    </p>

                                    <!-- Bottom Meta Badges -->
                                    <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-slate-200/60 text-[11px] font-mono">
                                        
                                        <!-- Actor Badge -->
                                        <template x-if="getAuditInfo(entry).actor">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-800 font-bold shadow-2xs font-sans">
                                                <span>👤</span>
                                                <span x-text="getAuditInfo(entry).actor"></span>
                                            </span>
                                        </template>

                                        <!-- IP Badge -->
                                        <template x-if="getAuditInfo(entry).ip">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-200/70 text-slate-700 rounded-md">
                                                <span class="text-[9px] text-slate-400">IP:</span>
                                                <span x-text="getAuditInfo(entry).ip"></span>
                                            </span>
                                        </template>

                                        <!-- Extra Details (e.g. Order Total, Payment, Status) -->
                                        <template x-for="item in (getAuditInfo(entry).meta || [])" :key="item">
                                            <span class="inline-flex items-center px-2 py-0.5 bg-white border border-slate-200 rounded-md text-slate-600"
                                                  x-text="item">
                                            </span>
                                        </template>

                                    </div>
                                </div>

                                <!-- Collapsible Raw Log -->
                                <div x-show="entry.showRaw" x-cloak class="mt-2 p-2.5 bg-slate-900 text-slate-200 rounded-xl text-[11px] font-mono overflow-x-auto whitespace-pre-wrap select-all">
                                    <p x-text="entry.message"></p>
                                </div>
                            </div>
                        </template>

                        <!-- 2. STANDARD DEVELOPER LOG (ERRORS, STACK TRACES, INFO) -->
                        <template x-if="!entry.isAudit">
                            <div class="space-y-2 font-mono">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase border font-sans"
                                              :class="{
                                                  'bg-rose-50 text-rose-700 border-rose-200': ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR'].includes(entry.level.toUpperCase()),
                                                  'bg-amber-50 text-amber-800 border-amber-200': entry.level.toUpperCase() === 'WARNING',
                                                  'bg-blue-50 text-blue-700 border-blue-200': ['INFO', 'NOTICE'].includes(entry.level.toUpperCase()),
                                                  'bg-slate-50 text-slate-700 border-slate-200': !['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'INFO', 'NOTICE'].includes(entry.level.toUpperCase())
                                              }"
                                              x-text="entry.level">
                                        </span>
                                        <span class="text-slate-400 text-[11px]" x-text="entry.timestamp"></span>
                                    </div>
                                </div>
                                <div class="p-3 bg-slate-900 text-slate-100 rounded-xl overflow-x-auto whitespace-pre-wrap text-[11px] leading-relaxed select-all"
                                     x-text="entry.message">
                                </div>
                            </div>
                        </template>

                    </div>
                </template>

                <!-- EMPTY STATE ON SEARCH -->
                <div x-show="visibleCount === 0" x-cloak class="p-12 text-center space-y-2">
                    <p class="text-sm font-bold text-slate-700">Tidak ada catatan log yang cocok</p>
                    <p class="text-xs text-slate-400">Silakan ubah filter level atau kata kunci pencarian.</p>
                </div>

                <!-- Hidden Server-Side Rendered Fallback for Crawlers & Tests -->
                <div class="hidden">
                    @foreach($logEntries as $e)
                        <div>{{ $e['message'] }}</div>
                    @endforeach
                </div>
            </div>
        </template>
    </div>

    <!-- BEAUTIFUL CONFIRMATION POPUP MODAL (REPLACES BROWSER 'THIS PAGE SAYS') -->
    <div x-show="confirmClearModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 border border-slate-200 shadow-2xl text-center space-y-4"
             @click.away="confirmClearModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center mx-auto shadow-xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>

            <div>
                <h3 class="text-base font-black text-slate-900 tracking-tight">Kosongkan Riwayat Log?</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Seluruh catatan log sistem saat ini di <span class="font-mono font-bold text-slate-700">storage/logs/laravel.log</span> akan dibersihkan.
                </p>
                <p class="text-[11px] text-rose-600 font-semibold mt-1">Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <form action="{{ route('admin.logs.clear') }}" method="POST" @submit="entries = []; logSize = '0 KB'; confirmClearModalOpen = false;" class="pt-2 flex items-center justify-center gap-2">
                @csrf
                <button type="button" 
                        @click="confirmClearModalOpen = false" 
                        class="w-1/2 py-2.5 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        class="w-1/2 py-2.5 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                    Ya, Bersihkan
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
