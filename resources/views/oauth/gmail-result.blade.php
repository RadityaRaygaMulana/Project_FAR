@extends('layouts.app')

@section('title', 'Status Koneksi Gmail API — NusantaraMart')

@section('content')
<div class="min-h-[80vh] bg-[#FAF8F5] py-12 flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-4">
        <div>
            <a href="{{ route('home') }}" 
               class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
               title="Kembali ke Beranda">
                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-8 text-center shadow-xl space-y-6">
        
        @if($success)
            <div class="w-16 h-16 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-3xl mx-auto shadow-md">
                ✓
            </div>
            <h2 class="text-xl font-black text-[#2D241E]">Gmail API Terhubung!</h2>
            <p class="text-xs text-[#6D6D72] leading-relaxed">
                {{ $message }}
            </p>
            
            <div class="pt-2">
                <a href="{{ route('home') }}" 
                   class="inline-block w-full py-3 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl transition shadow-xs">
                    Kembali ke Beranda
                </a>
            </div>
        @else
            <div class="w-16 h-16 rounded-2xl bg-red-500 text-white flex items-center justify-center text-3xl mx-auto shadow-md">
                ✕
            </div>
            <h2 class="text-xl font-black text-[#2D241E]">Koneksi Gagal</h2>
            <p class="text-xs text-red-600 leading-relaxed font-medium">
                {{ $message }}
            </p>
            <div class="pt-2 flex flex-col gap-2">
                <a href="{{ route('oauth.gmail.connect') }}" 
                   class="w-full py-3 bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs font-bold rounded-xl transition shadow-xs">
                    Coba Hubungkan Ulang
                </a>
                <a href="{{ route('home') }}" 
                   class="w-full py-3 bg-[#FAF8F5] text-[#5A4B40] hover:bg-[#FAF4ED] text-xs font-bold rounded-xl border border-[#EAE1D7] transition">
                    Kembali ke Beranda
                </a>
            </div>
        @endif

        </div>
    </div>
</div>
@endsection
