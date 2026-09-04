@extends('layouts.app')

@section('title', 'Daftar Akun Baru — NusantaraMart')

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
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-[#FAF4ED] text-xs font-bold">
                        <span>✨</span>
                        <span>Member NusantaraMart</span>
                    </div>

                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold leading-tight text-white">
                            Bikin Akun & Nikmati <span class="text-[#FDECD2]">Voucher Eksklusif</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-[#F5EBE1] mt-2 leading-relaxed font-normal">
                            Daftar dalam 30 detik untuk kemudahan simpan alamat, promo bebas ongkir, dan pelacakan pesanan secara praktis.
                        </p>
                    </div>

                    <div class="space-y-2.5 pt-2">
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10">
                            <span class="text-xl">⚡</span>
                            <div class="text-xs">
                                <p class="font-bold text-white">Checkout Cepat & Praktis</p>
                                <p class="text-[#F5EBE1] text-[10px]">Data pengiriman tersimpan otomatis</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10">
                            <span class="text-xl">📦</span>
                            <div class="text-xs">
                                <p class="font-bold text-white">Pantau Riwayat Belanja</p>
                                <p class="text-[#F5EBE1] text-[10px]">Akses riwayat transaksi kapan saja</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-8 border-t border-white/15 mt-6 flex items-center justify-between text-xs">
                    <p class="text-[#F5EBE1] text-[11px]">100% Data Aman & Terlindungi</p>
                    <span class="text-xl">🔒</span>
                </div>
            </div>

            <!-- Right Side: Registration Form (7 Cols) -->
            <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-center bg-white">
                
                <div class="mb-6 space-y-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#6B4226] bg-[#FAF4ED] px-3 py-1 rounded-full border border-[#6B4226]/20 inline-block">
                        Pendaftaran Member
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#2D241E] tracking-tight">Buat Akun NusantaraMart 🎉</h2>
                    <p class="text-xs text-[#7A6C60]">Lengkapi formulir singkat di bawah ini untuk memulai.</p>
                </div>

                <form action="{{ route('register') }}" method="POST" class="space-y-3.5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Rian Pratama"
                               class="w-full px-4 py-2.5 text-sm bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] focus:bg-white transition @error('name') border-red-400 @enderror">
                        @error('name')
                            <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider mb-1">Username *</label>
                            <input type="text" name="username" value="{{ old('username') }}" required placeholder="rian_user"
                                   class="w-full px-4 py-2.5 text-sm bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] focus:bg-white transition @error('username') border-red-400 @enderror">
                            @error('username')
                                <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider mb-1">Email Aktif *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="rian@gmail.com"
                                   class="w-full px-4 py-2.5 text-sm bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] focus:bg-white transition @error('email') border-red-400 @enderror">
                            @error('email')
                                <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider mb-1">Password *</label>
                            <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="Min. 6 Karakter"
                                   class="w-full px-4 py-2.5 text-sm bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] focus:bg-white transition @error('password') border-red-400 @enderror">
                            @error('password')
                                <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#5A4B40] uppercase tracking-wider mb-1">Konfirmasi Password *</label>
                            <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" required placeholder="Ulangi Password"
                                   class="w-full px-4 py-2.5 text-sm bg-[#FAF8F5] border border-[#EAE1D7] rounded-2xl focus:outline-none focus:ring-2 focus:ring-[#6B4226]/20 focus:border-[#6B4226] focus:bg-white transition">
                        </div>
                    </div>

                    <div class="flex items-center text-xs pt-1">
                        <label class="flex items-center gap-2 text-[#7A6C60] cursor-pointer select-none">
                            <input type="checkbox" @change="showPassword = !showPassword" class="w-4 h-4 text-[#6B4226] border-stone-300 rounded focus:ring-[#6B4226] cursor-pointer">
                            <span>Tampilkan Password</span>
                        </label>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-6 bg-[#6B4226] hover:bg-[#54321B] text-white font-bold rounded-2xl shadow-xs transition transform active:scale-98 text-sm flex items-center justify-center gap-2 mt-2 cursor-pointer">
                        <span>Daftar Akun Sekarang 🎉</span>
                    </button>
                </form>

                <div class="border-t border-[#F2EAE0] mt-5 pt-3 text-center">
                    <p class="text-xs text-[#7A6C60]">
                        Sudah punya akun NusantaraMart? 
                        <a href="{{ route('login') }}" class="text-[#6B4226] font-bold hover:underline ml-1">Masuk di sini →</a>
                    </p>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
