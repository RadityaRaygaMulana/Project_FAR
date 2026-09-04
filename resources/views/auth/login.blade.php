@extends('layouts.app')

@section('title', 'Masuk ke Akun — NusantaraMart')

@section('content')
<div class="py-4 sm:py-6 bg-[#FAF8F5] min-h-[85vh] flex items-center justify-center relative overflow-hidden"
     x-data="{ showPassword: false }">
    
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
                <div class="border-t border-[#F2EAE0] mt-6 pt-4 text-center">
                    <p class="text-xs text-[#7A6C60]">
                        Belum punya akun NusantaraMart? 
                        <a href="{{ route('register') }}" class="text-[#6B4226] font-bold hover:underline ml-1">
                            Daftar Akun Baru →
                        </a>
                    </p>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
