@extends('layouts.app')

@section('title', 'Masuk ke Akun — NusantaraMart')

@section('content')
<div class="py-4 sm:py-6 bg-[#FAF8F5] min-h-[85vh] flex items-center justify-center relative overflow-hidden"
     x-data="{ showPassword: false, suspendedModalOpen: {{ (session('suspended') || session('appeal_submitted')) ? 'true' : 'false' }}, appealMode: false }">
    
    <div class="max-w-4xl w-full mx-auto px-4 sm:px-6 relative z-10">
        
        <!-- Top Back to Home Button (Premium Circular Pill) -->
        <div class="mb-2.5">
            <a href="{{ route('home') }}" 
               class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
               title="Kembali ke Beranda">
                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>

        <!-- Main Split Auth Card -->
        <div class="bg-white rounded-3xl sm:rounded-[2rem] border border-[#EAE1D7] shadow-xl overflow-hidden grid grid-cols-1 lg:grid-cols-12">
            
            <!-- Left Side: Rich Chocolate Atmosphere (5 Cols) -->
            <div class="lg:col-span-5 bg-[#6B4226] p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Background ambient circles -->
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 space-y-6">
                    <!-- Brand Pill -->
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-[#FAF4ED] text-xs font-bold">
                        <span>🛍️</span>
                        <span>NusantaraMart Official</span>
                    </div>

                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold leading-tight text-white">
                            Belanja Pilihan & <span class="text-[#FDECD2]">Aman Terpercaya</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-[#F5EBE1] mt-2 leading-relaxed font-normal">
                            Masuk untuk menikmati ribuan produk gadget, fashion, kuliner, dan kebutuhan rumah tangga dengan diskon spesial.
                        </p>
                    </div>

                    <!-- Highlight Features Pills -->
                    <div class="space-y-2.5 pt-2">
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10">
                            <span class="text-xl">🏬</span>
                            <div class="text-xs">
                                <p class="font-bold text-white">100% Produk Original</p>
                                <p class="text-[#F5EBE1] text-[10px]">Langsung dari distributor resmi</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10">
                            <span class="text-xl">🚚</span>
                            <div class="text-xs">
                                <p class="font-bold text-white">Promo Bebas Ongkir</p>
                                <p class="text-[#F5EBE1] text-[10px]">Klaim voucher setiap transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Social Proof -->
                <div class="relative z-10 pt-8 border-t border-white/15 mt-6 flex items-center justify-between text-xs">
                    <div>
                        <div class="flex text-[#FDECD2] text-xs">★★★★★</div>
                        <p class="text-[11px] text-[#F5EBE1] font-medium mt-0.5">4.9 / 5.0 (100k+ Pembeli)</p>
                    </div>
                    <span class="text-xl">✨</span>
                </div>
            </div>

            <!-- Right Side: Login Form (7 Cols) -->
            <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-center bg-white">
                
                <div class="mb-6 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#6B4226] bg-[#FAF4ED] px-3 py-1 rounded-full border border-[#6B4226]/20 inline-block">
                        Masuk Akun
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#2D241E] tracking-tight">Selamat Datang Kembali! 👋</h2>
                    <p class="text-xs text-[#7A6C60]">Masuk dengan <span class="font-bold text-[#6B4226]">Username</span> atau <span class="font-bold text-[#6B4226]">Alamat Email</span> terdaftar kamu.</p>
                </div>

                @if(session('suspended'))
                    {{-- Compact Chocolate Notice on Form --}}
                    <div @click="suspendedModalOpen = true" 
                         class="mb-5 p-3.5 rounded-2xl bg-[#FAF4ED] border border-[#EAE1D7] flex items-center justify-between gap-3 text-xs text-[#54321B] cursor-pointer hover:bg-[#F5EBE1] transition shadow-xs group">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-[#6B4226] text-white flex items-center justify-center text-sm shrink-0 shadow-2xs">
                                🚫
                            </div>
                            <div class="min-w-0">
                                <p class="font-black text-[#6B4226]">Akses Akun Dinonaktifkan</p>
                                <p class="text-[11px] text-[#7A6C60] truncate font-medium">Klik untuk membuka rincian pemblokiran & durasi</p>
                            </div>
                        </div>
                        <span class="text-xs font-black text-[#6B4226] px-3 py-1.5 rounded-xl bg-white border border-[#EAE1D7] group-hover:border-[#6B4226]/40 shadow-2xs shrink-0">
                            Lihat Card ↗
                        </span>
                    </div>
                @endif

                @if(session('error'))

                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-init="setTimeout(() => show = false, 5000)" 
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-5"
                         x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                         class="overflow-hidden mb-5 p-3.5 bg-red-50 border border-red-200 text-red-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">⚠️</span>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-red-400 hover:text-red-700 font-bold p-1 cursor-pointer">✕</button>
                    </div>
                @endif

                @if(session('info'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-init="setTimeout(() => show = false, 5000)" 
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-5"
                         x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                         class="overflow-hidden mb-5 p-3.5 bg-sky-50 border border-sky-200 text-sky-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">ℹ️</span>
                            <span>{{ session('info') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-sky-400 hover:text-sky-700 font-bold p-1 cursor-pointer">✕</button>
                    </div>
                @endif

                @if(session('success'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-init="setTimeout(() => show = false, 5000)" 
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 scale-100 max-h-20 mb-5"
                         x-transition:leave-end="opacity-0 scale-95 max-h-0 mb-0 py-0"
                         class="overflow-hidden mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">🎉</span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-emerald-400 hover:text-emerald-700 font-bold p-1 cursor-pointer">✕</button>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Login Input (Username or Email) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider">
                                Username atau Email *
                            </label>
                            <span class="text-[10px] text-[#8A7C70]">Bebas pilih salah satu</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8A7C70]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="login" 
                                   x-ref="loginInput"
                                   value="{{ old('login') }}" 
                                   required 
                                   autofocus 
                                   placeholder="Ketik username atau email kamu"
                                   class="w-full pl-10 pr-4 py-3 text-sm bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] focus:bg-white transition @error('login') border-red-400 bg-red-50/30 @enderror">
                        </div>
                        @error('login')
                            <p class="text-xs text-red-600 font-semibold mt-1.5 flex items-center gap-1">
                                <span>•</span> <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Password Input with Toggle Visibility -->
                    <div>
                        <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider mb-1.5">
                            Password *
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8A7C70]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   name="password" 
                                   x-ref="passwordInput"
                                   required 
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-11 py-3 text-sm bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] focus:bg-white transition @error('password') border-red-400 @enderror">
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#8A7C70] hover:text-[#2D241E] cursor-pointer">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 font-semibold mt-1.5 flex items-center gap-1">
                                <span>•</span> <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Remember & Action -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 text-[#7A6C60] cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-[#6B4226] border-stone-300 rounded focus:ring-[#6B4226] cursor-pointer">
                            <span class="font-medium">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-3.5 px-6 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-2xl shadow-xs transition transform active:scale-98 text-sm flex items-center justify-center gap-2 cursor-pointer">
                        <span>Masuk ke Akun Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </form>

                <!-- Footer Switch Link -->
                <div class="border-t border-[#F2EAE0] mt-6 pt-4 text-center space-y-1.5">
                    <p class="text-xs text-[#7A6C60]">
                        Belum punya akun NusantaraMart? 
                        <a href="{{ route('register') }}" class="text-[#6B4226] font-bold hover:underline ml-1">
                            Daftar Akun Baru →
                        </a>
                    </p>
                    <p class="text-[11px] text-[#8A7C70]">
                        Akun kamu diblokir atau dinonaktifkan? 
                        <button type="button" @click="suspendedModalOpen = true; appealMode = true" class="text-[#6B4226] font-bold hover:underline cursor-pointer">
                            Ajukan Banding di Sini ⚖️
                        </button>
                    </p>
                </div>

            </div>

        </div>

    </div>

    {{-- SUSPENDED ACCOUNT & APPEAL CARD MODAL (COKLAT NUSANTARAMART) --}}
    <div x-show="suspendedModalOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6 flex min-h-screen items-center justify-center"
         @click.self="suspendedModalOpen = false"
         style="display:none">
        
        <div x-show="suspendedModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative my-auto w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-[#EAE1D7] overflow-hidden flex flex-col max-h-[92vh]">

            {{-- Card Header --}}
            <div class="shrink-0 bg-[#6B4226] px-6 py-4 flex items-center justify-between text-white border-b border-[#54321B]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-xs border border-white/20 flex items-center justify-center text-xl shadow-xs">
                        <span x-text="appealMode ? '⚖️' : '🚫'"></span>
                    </div>
                    <div>
                        <h3 class="text-sm font-black tracking-tight text-white" x-text="appealMode ? 'Formulir Pengajuan Banding' : 'Akses Akun Dinonaktifkan'"></h3>
                        <p class="text-[11px] text-[#FAF4ED] font-medium" x-text="appealMode ? 'Pusat Mediasi & Resolusi Akun' : 'Pemberitahuan Resmi Keamanan NusantaraMart'"></p>
                    </div>
                </div>
                <button type="button" @click="suspendedModalOpen = false" 
                        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-[#FAF4ED] hover:text-white transition flex items-center justify-center font-bold text-sm cursor-pointer">✕</button>
            </div>

            {{-- Card Body --}}
            <div class="overflow-y-auto p-6 space-y-4">
                
                {{-- 1. VIEW MODE: SUSPENSION DETAILS --}}
                <div x-show="!appealMode" class="space-y-4">
                    
                    {{-- Status Permohonan Banding Yang Ada --}}
                    @if(session('appeal_submitted') || session('latest_appeal_status') === 'pending')
                        <div class="p-3.5 bg-amber-50 border border-amber-300 rounded-2xl flex items-start gap-3 shadow-2xs">
                            <span class="text-xl shrink-0 mt-0.5 animate-bounce">⏳</span>
                            <div class="text-xs">
                                <p class="font-black text-amber-900 uppercase tracking-wider text-[10px]">Status: Banding Sedang Ditinjau Admin</p>
                                <p class="text-amber-950 font-medium mt-0.5 leading-relaxed">
                                    Permohonan banding Anda telah tercatat pada sistem ({{ session('latest_appeal_date') ?? 'Hari ini' }}). Tim Admin NusantaraMart akan mengevaluasi pembelaan Anda.
                                </p>
                            </div>
                        </div>
                    @elseif(session('latest_appeal_status') === 'rejected')
                        <div class="p-3.5 bg-rose-50 border border-rose-300 rounded-2xl flex items-start gap-3 shadow-2xs">
                            <span class="text-xl shrink-0 mt-0.5">❌</span>
                            <div class="text-xs">
                                <p class="font-black text-rose-900 uppercase tracking-wider text-[10px]">Status: Banding Sebelumnya Ditolak</p>
                                <p class="text-rose-950 font-medium mt-0.5 leading-relaxed">
                                    Catatan Admin: "<strong>{{ session('latest_appeal_notes') ?? 'Belum memenuhi syarat pemulihan.' }}</strong>"
                                </p>
                                <p class="text-[10px] text-rose-700 mt-1 font-semibold">Anda masih dapat mengajukan banding baru dengan penjelasan atau bukti pendukung yang lebih lengkap.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Duration Status Pill --}}
                    @if(!session('is_permanent') && session('suspended_until'))
                        <div class="p-3.5 bg-[#FAF4ED] border border-[#EAE1D7] rounded-2xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-[#F5EBE1] border border-[#EAE1D7] text-[#6B4226] flex items-center justify-center shrink-0 text-lg">
                                    ⏳
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-[#6B4226]">Tipe: Pemblokiran Sementara</p>
                                    <p class="text-xs font-bold text-[#2D241E] truncate">
                                        Berakhir: <span class="underline decoration-[#6B4226]/40 font-black">{{ session('suspended_until') }}</span>
                                    </p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl bg-[#6B4226]/10 text-[#6B4226] text-[10px] font-black shrink-0 border border-[#6B4226]/20">
                                {{ session('suspended_diff') }}
                            </span>
                        </div>
                    @else
                        <div class="p-3.5 bg-[#FAF4ED] border border-[#EAE1D7] rounded-2xl flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#F5EBE1] border border-[#EAE1D7] text-[#6B4226] flex items-center justify-center shrink-0 text-lg">
                                🛡️
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-[#6B4226]">Tipe: Pemblokiran Permanen</p>
                                <p class="text-xs font-bold text-[#2D241E]">Tidak ada batas waktu otomatis (hingga dicabut admin).</p>
                            </div>
                        </div>
                    @endif

                    {{-- Suspension Reason Box --}}
                    <div class="bg-[#FAF4ED]/50 border-l-4 border-[#6B4226] border-y border-r border-[#EAE1D7] rounded-r-2xl p-4 shadow-2xs">
                        <div class="flex items-center gap-1.5 text-[#6B4226] mb-1.5">
                            <span class="text-xs">⚠️</span>
                            <span class="text-[10px] font-black uppercase tracking-wider">Alasan Penangguhan / Pemblokiran</span>
                        </div>
                        <p class="text-xs text-[#2D241E] font-semibold leading-relaxed">
                            "{{ session('suspension_reason') ?? 'Akun atau toko melanggar pedoman komunitas dan ketentuan layanan NusantaraMart.' }}"
                        </p>
                    </div>

                    {{-- Appeal Action Row --}}
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-[#EAE1D7] text-[11px]">
                        <span class="text-[#7A6C60] text-center sm:text-left">
                            Merasa ini kekeliruan sistem?
                        </span>
                        @if(session('latest_appeal_status') === 'pending' || session('appeal_submitted'))
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-100 text-amber-900 font-bold rounded-xl border border-amber-300 text-xs">
                                <span>⏳</span>
                                <span>Banding Sedang Berjalan</span>
                            </span>
                        @else
                            <button type="button" @click="appealMode = true"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-xl transition cursor-pointer shadow-2xs text-xs active:scale-95">
                                <span>⚖️</span>
                                <span>Ajukan Banding Sekarang</span>
                            </button>
                        @endif
                    </div>

                    {{-- Dismiss button --}}
                    <div class="pt-1">
                        <button type="button" @click="suspendedModalOpen = false"
                                class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                            Tutup / Masuk dengan Akun Lain
                        </button>
                    </div>
                </div>

                {{-- 2. APPEAL FORM MODE --}}
                <div x-show="appealMode" x-cloak class="space-y-4">
                    <div class="p-3 bg-[#FAF4ED] border border-[#EAE1D7] rounded-2xl flex items-start gap-2.5">
                        <span class="text-base shrink-0 mt-0.5">ℹ️</span>
                        <p class="text-xs text-[#54321B] font-medium leading-relaxed">
                            Jelaskan argumen, kronologi, atau bukti pembelaan kamu secara jujur. Tim admin NusantaraMart akan meninjau dan mempertimbangkan pemulihan akun kamu.
                        </p>
                    </div>

                    <form action="{{ route('appeals.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                        @csrf

                        {{-- Identifier (Username / Email) --}}
                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1">
                                Username atau Email Akun Terblokir <span class="text-[#6B4226]">*</span>
                            </label>
                            <input type="text" name="identifier" required
                                   value="{{ session('suspended_identifier') ?? old('identifier') }}"
                                   placeholder="Contoh: user123 atau user@domain.com"
                                   class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] bg-slate-50 font-medium">
                        </div>

                        {{-- Nama & Email Pemohon --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-black text-slate-700 mb-1">
                                    Nama Lengkap <span class="text-[#6B4226]">*</span>
                                </label>
                                <input type="text" name="applicant_name" required
                                       value="{{ session('suspended_name') ?? old('applicant_name') }}"
                                       placeholder="Nama kamu"
                                       class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] bg-white font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-700 mb-1">
                                    Email Aktif <span class="text-[#6B4226]">*</span>
                                </label>
                                <input type="email" name="applicant_email" required
                                       value="{{ session('suspended_identifier') ?? old('applicant_email') }}"
                                       placeholder="email@kamu.com"
                                       class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] bg-white font-medium">
                            </div>
                        </div>

                        {{-- Nomor Telepon / WA --}}
                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1">
                                Nomor Telepon / WhatsApp <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="tel" name="applicant_phone"
                                   value="{{ old('applicant_phone') }}"
                                   placeholder="0812xxxxxxxx"
                                   class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] bg-white font-medium">
                        </div>

                        {{-- Alasan Banding --}}
                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1">
                                Penjelasan & Argumen Pembelaan <span class="text-[#6B4226]">*</span>
                            </label>
                            <textarea name="reason" rows="4" required minlength="15"
                                      placeholder="Jelaskan secara rinci mengapa pemblokiran ini keliru atau komitmen kamu ke depannya (minimal 15 karakter)..."
                                      class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] resize-none font-medium text-slate-800">{{ old('reason') }}</textarea>
                            <p class="text-[10px] text-slate-400 mt-1">Minimal 15 karakter. Semakin jelas pembelaan, semakin cepat diproses.</p>
                        </div>

                        {{-- Berkas Pendukung --}}
                        <div>
                            <label class="block text-xs font-black text-slate-700 mb-1">
                                Bukti Pendukung <span class="text-slate-400 font-normal">(Opsional — Gambar/PDF maks 3MB)</span>
                            </label>
                            <input type="file" name="attachment" accept="image/*,.pdf"
                                   class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#FAF4ED] file:text-[#6B4226] hover:file:bg-[#F5EBE1] cursor-pointer">
                        </div>

                        {{-- Form Buttons --}}
                        <div class="flex gap-2.5 pt-3 border-t border-slate-100">
                            <button type="button" @click="appealMode = false"
                                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                                ← Kembali
                            </button>
                            <button type="submit"
                                    class="flex-2 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5 active:scale-95">
                                <span>🚀</span>
                                <span>Kirim Pengajuan Banding</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

