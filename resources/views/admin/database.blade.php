@extends('layouts.admin')

@section('title', 'Kesehatan Database & Storage — NusantaraMart Admin')

@section('content')
<div class="space-y-6" x-data="{ tableSearch: '', optimizeModalOpen: false }">

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
                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Kesehatan Database & Penyimpanan File
                </h1>
                <p class="text-xs text-slate-500">
                    Inspeksi koneksi PDO, driver penyimpanan, tabel skema aktif, dan integritas data sistem.
                </p>
            </div>
        </div>

        <button type="button" 
                @click="optimizeModalOpen = true"
                class="px-4 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl shadow-xs transition active:scale-95 flex items-center gap-2 cursor-pointer">
            <span>💾</span>
            <span>Optimasi & Vacuum DB</span>
        </button>
    </div>

    <!-- DB STATUS METRICS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-1">
            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Status Koneksi</p>
            <p class="text-lg font-black text-emerald-700 font-mono flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ $dbInfo['status'] }}</span>
            </p>
            <p class="text-[11px] text-slate-500 font-mono">Driver: <span class="font-bold text-slate-700">{{ $dbInfo['driver'] }}</span></p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-1">
            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Tabel Skema Aktif</p>
            <p class="text-lg font-black text-slate-900 font-mono">{{ $dbInfo['tables_count'] }} Tabel</p>
            <p class="text-[11px] text-slate-500">Struktur relasional terdeteksi</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-1">
            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Izin Tulis Disk Storage</p>
            <p class="text-lg font-black text-slate-900 font-mono">{{ $dbInfo['storage_public_writable'] }}</p>
            <p class="text-[11px] text-slate-500 font-mono">Logs: {{ $dbInfo['logs_writable'] }}</p>
        </div>
    </div>

    <!-- TABLES INSPECTOR LIST CONTAINER -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- SEARCH TABLE BAR -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900">Inspeksi Tabel Skema Database</h2>
                <p class="text-[11px] text-slate-500">Daftar entitas dan jumlah baris data yang tersimpan</p>
            </div>

            <div class="relative w-full sm:w-64">
                <input type="text" 
                       x-model="tableSearch" 
                       placeholder="Cari nama tabel..." 
                       class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] font-mono">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button type="button" 
                        x-show="tableSearch.length > 0" 
                        x-cloak 
                        @click="tableSearch = ''" 
                        class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-[10px] font-bold w-4 h-4 rounded-full bg-slate-200 flex items-center justify-center cursor-pointer">
                    ✕
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 font-bold border-b border-slate-200 text-[11px] uppercase tracking-wider font-sans">
                        <th class="py-3.5 px-4 pl-6">Nama Tabel Skema</th>
                        <th class="py-3.5 px-4">Jumlah Baris Data (Record Count)</th>
                        <th class="py-3.5 px-4 pr-6 text-right font-sans">Status Sinkronisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($tables as $t)
                        <tr x-show="tableSearch === '' || '{{ strtolower($t['name']) }}'.includes(tableSearch.toLowerCase().trim())"
                            class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 pl-6 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400">🗄️</span>
                                    <span>{{ $t['name'] }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <span class="font-bold text-slate-900">{{ number_format($t['rows'], 0, ',', '.') }}</span> records
                            </td>
                            <td class="py-3.5 px-4 pr-6 text-right font-sans">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-50 text-emerald-800 rounded-md font-bold text-[10px] border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Active</span>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- BEAUTIFUL CONFIRMATION POPUP MODAL (REPLACES BROWSER 'THIS PAGE SAYS') -->
    <div x-show="optimizeModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 border border-slate-200 shadow-2xl text-center space-y-4"
             @click.away="optimizeModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center mx-auto shadow-xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
            </div>

            <div>
                <h3 class="text-base font-black text-slate-900 tracking-tight">Optimasi & Vacuum Basis Data?</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Sistem akan membersihkan halaman kosong (*dead tuples*) dan melakukan defragmentasi indeks penyimpanan database.
                </p>
            </div>

            <form action="{{ route('admin.database.optimize') }}" method="POST" class="pt-2 flex items-center justify-center gap-2">
                @csrf
                <button type="button" 
                        @click="optimizeModalOpen = false" 
                        class="w-1/2 py-2.5 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        class="w-1/2 py-2.5 text-xs font-bold bg-[#6B4226] hover:bg-[#54321B] text-white rounded-xl shadow-xs transition cursor-pointer">
                    Ya, Jalankan
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
