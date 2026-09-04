@extends('layouts.admin')

@section('title', 'Pusat Operasi & Telemetri Sistem — NusantaraMart Admin')

@section('content')
<div class="space-y-6" x-data="{
    liveTime: '{{ now()->format('H:i:s') }}',
    init() {
        setInterval(() => {
            const d = new Date();
            this.liveTime = d.toTimeString().split(' ')[0];
        }, 1000);
    }
}">

    <!-- TOP HERO / COMMAND BAR -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Pusat Operasi & Pemeliharaan Sistem
                </h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>SISTEM NORMAL</span>
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Pemantauan kesehatan infrastruktur server, latensi query, alokasi memori RAM, audit log aktivitas, dan eksekusi pemeliharaan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.cache.clear') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="px-4 py-2.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-xs text-xs font-bold rounded-xl transition flex items-center gap-2 active:scale-95 cursor-pointer">
                    <span>🧹</span>
                    <span>Bersihkan Cache</span>
                </button>
            </form>

            <form action="{{ route('admin.database.optimize') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="px-4 py-2.5 bg-[#6B4226] hover:bg-[#54321B] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2 active:scale-95 cursor-pointer">
                    <span>💾</span>
                    <span>Optimasi DB</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 4 REAL-TIME TELEMETRY STAT CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- CARD 1: DATABASE HEALTH & LATENCY -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase text-slate-400 tracking-wider">Database Engine</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    {{ $systemInfo['database_status'] }}
                </span>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 font-mono">{{ $systemInfo['database_driver'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5 flex items-center justify-between font-mono">
                    <span>Latensi: <strong class="text-emerald-700">{{ $systemInfo['database_latency'] }}</strong></span>
                    <span>Ukuran: <strong class="text-slate-700">{{ $systemInfo['database_size'] }}</strong></span>
                </p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-mono">
                <span>{{ $systemInfo['tables_count'] }} Tabel Skema</span>
                <span>{{ number_format($registeredUsersTelemetry['total'], 0, ',', '.') }} Member Terdeteksi</span>
            </div>
        </div>

        <!-- CARD 2: RAM MEMORY ALLOCATION -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase text-slate-400 tracking-wider">Alokasi RAM Server</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 font-mono">
                    Limit {{ $systemInfo['memory_limit'] }}
                </span>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 font-mono">{{ $systemInfo['memory_usage'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5 flex items-center justify-between font-mono">
                    <span>Peak: <strong class="text-slate-800">{{ $systemInfo['memory_peak'] }}</strong></span>
                    <span>PHP: <strong class="text-slate-800">{{ $systemInfo['php_version'] }}</strong></span>
                </p>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: 15%"></div>
            </div>
        </div>

        <!-- CARD 3: AUDIT TRAIL & LOG TELEMETRY -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase text-slate-400 tracking-wider">Log & Audit Telemetri</span>
                <a href="{{ route('admin.logs') }}" class="text-[11px] font-bold text-[#6B4226] hover:underline">
                    Stream Live →
                </a>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 font-mono">{{ $recentLogCount }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Baris telemetri tercatat di file log</p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-mono">
                <span class="text-purple-700 font-bold">📜 {{ $logTelemetry['audit'] }} Audit</span>
                <span class="text-rose-600 font-bold">🔴 {{ $logTelemetry['error'] }} Error</span>
                <span class="text-blue-600 font-bold">🔵 {{ $logTelemetry['info'] }} Info</span>
            </div>
        </div>

        <!-- CARD 4: ACCOUNTS & SECURITY STATUS -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase text-slate-400 tracking-wider">Integritas Pengguna</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-900 border border-amber-200 font-mono">
                    {{ $verificationRate }}% Verified
                </span>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 font-mono">{{ $totalUsers }} <span class="text-xs text-slate-400 font-sans font-normal">Akun</span></p>
                <p class="text-xs text-slate-500 mt-0.5 flex items-center justify-between font-mono">
                    <span>Admin: <strong class="text-amber-900">{{ $totalAdmins }}</strong></span>
                    <span>Customer: <strong class="text-slate-800">{{ $totalCustomers }}</strong></span>
                </p>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-mono">
                <span class="text-emerald-700 font-bold">✓ {{ $totalVerified }} Aktif</span>
                <span class="text-slate-400">{{ $totalUnverified }} Pending</span>
            </div>
        </div>

    </div>

    <!-- SYSTEM TELEMETRY CHARTS (2 COLUMNS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- CHART 1: REGISTERED USERS ENTERING WEBSITE (2 COLS) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Statistik Pengguna Terdaftar Masuk ke Website</h2>
                    <p class="text-[11px] text-slate-500">Jumlah akun pengguna terdaftar yang masuk dan aktif mengakses web per hari (7 hari terakhir)</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto font-mono text-[11px]">
                    <span class="px-2.5 py-1 bg-amber-50 text-amber-900 border border-amber-200 rounded-lg font-bold">
                        Hari Ini: {{ $registeredUsersTelemetry['today'] }} Pengguna Terdaftar
                    </span>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg">
                        Rata-rata: {{ $registeredUsersTelemetry['avg_daily'] }} Pengguna/hari
                    </span>
                </div>
            </div>

            <div class="relative h-64 w-full">
                <canvas id="registeredUsersChart"></canvas>
            </div>
        </div>

        <!-- CHART 2: ORDER STATUS & DISTRIBUTION (1 COL) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Status & Distribusi Pesanan</h2>
                    <p class="text-[11px] text-slate-500">Proporsi status pesanan pelanggan di sistem</p>
                </div>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-lg text-[10px] font-mono font-bold">
                    Total: {{ $orderTelemetry['total'] }} Pesanan
                </span>
            </div>

            <div class="relative h-48 w-full flex items-center justify-center">
                <canvas id="orderStatusChart"></canvas>
            </div>

            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs font-mono">
                <div class="p-2 bg-amber-50 rounded-lg border border-amber-200 text-amber-900">
                    <span class="text-[10px] block text-amber-700 font-bold">Menunggu</span>
                    <strong class="text-sm">{{ $orderTelemetry['pending'] }}</strong>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg border border-blue-200 text-blue-900">
                    <span class="text-[10px] block text-blue-700 font-bold">Diproses</span>
                    <strong class="text-sm">{{ $orderTelemetry['processing'] }}</strong>
                </div>
                <div class="p-2 bg-emerald-50 rounded-lg border border-emerald-200 text-emerald-900">
                    <span class="text-[10px] block text-emerald-700 font-bold">Selesai</span>
                    <strong class="text-sm">{{ $orderTelemetry['completed'] }}</strong>
                </div>
                <div class="p-2 bg-rose-50 rounded-lg border border-rose-200 text-rose-900">
                    <span class="text-[10px] block text-rose-700 font-bold">Dibatalkan</span>
                    <strong class="text-sm">{{ $orderTelemetry['cancelled'] }}</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- UTILITY COMMANDS & RUNTIME SPECIFICATIONS (2 COLUMNS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT: ACTIONABLE OPERATIONS & TOOLKIT (2 COLS) -->
        <div class="lg:col-span-2 space-y-5">
            
            <!-- TOOLKIT 1: MAINTENANCE ACTIONS -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                <h2 class="text-sm font-extrabold text-slate-900">Operasi Pemeliharaan Server</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Pembersihan Cache Sistem</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Menghapus cache config, compiled views, dan routing tabel.</p>
                        </div>
                        <form action="{{ route('admin.cache.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg transition cursor-pointer">
                                Jalankan Pembersihan Cache
                            </button>
                        </form>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-slate-900">Optimasi & Vacuum Database</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Membersihkan halaman kosong dan menata ulang indeks basis data.</p>
                        </div>
                        <form action="{{ route('admin.database.optimize') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg transition cursor-pointer">
                                Jalankan Vacuum DB
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TOOLKIT 2: QUICK DIAGNOSTIC LINKS -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                <h2 class="text-sm font-extrabold text-slate-900">Utilitas Diagnostik & Manajemen</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a href="{{ route('admin.logs') }}" class="p-4 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 rounded-xl transition group">
                        <span class="text-xl">📋</span>
                        <h3 class="text-xs font-bold text-slate-900 mt-2">Log Viewer</h3>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $recentLogCount }} baris tercatat di file log</p>
                    </a>

                    <a href="{{ route('admin.database') }}" class="p-4 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 rounded-xl transition group">
                        <span class="text-xl">💾</span>
                        <h3 class="text-xs font-bold text-slate-900 mt-2">Tabel Database</h3>
                        <p class="text-[10px] text-slate-500 mt-0.5">Inspeksi skema & ruang penyimpanan</p>
                    </a>

                    <a href="{{ route('admin.users') }}" class="p-4 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 rounded-xl transition group">
                        <span class="text-xl">👥</span>
                        <h3 class="text-xs font-bold text-slate-900 mt-2">Kelola Pengguna</h3>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $totalUsers }} akun sistem terdaftar</p>
                    </a>
                </div>
            </div>

        </div>

        <!-- RIGHT: REAL CONFIGURATION & ENVIRONMENT (1 COL) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-900">Spesifikasi Runtime & Keamanan</h2>

            <div class="divide-y divide-slate-100 text-xs font-mono">
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 font-sans">Anti-Cache Shield</span>
                    <span class="font-bold text-emerald-700">Active</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 font-sans">Enkripsi Password</span>
                    <span class="font-bold text-slate-800">{{ $systemInfo['password_encryption'] }}</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 font-sans">CSRF Protection</span>
                    <span class="font-bold text-emerald-700">Token Verified</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 font-sans">Gmail REST API</span>
                    <span class="font-bold text-slate-800">Google OAuth 2.0</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 font-sans">Cache Driver</span>
                    <span class="font-bold text-slate-800">{{ $systemInfo['cache_driver'] }}</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 font-sans">Session Driver</span>
                    <span class="font-bold text-slate-800">{{ $systemInfo['session_driver'] }}</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 font-sans">Zona Waktu</span>
                    <span class="font-bold text-slate-800">{{ $systemInfo['timezone'] }}</span>
                </div>
                <div class="py-2.5 flex items-center justify-between">
                    <span class="text-slate-500 font-sans">Waktu Server</span>
                    <span class="font-bold text-[#6B4226]" x-text="liveTime"></span>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- CHART.JS TELEMETRY INITIALIZATION -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Real Registered Users Entering Website Area/Line Chart
    const regCtx = document.getElementById('registeredUsersChart');
    if (regCtx) {
        const regData = @json($registeredUsersTelemetry);
        const labels = regData.labels || [];
        const counts = regData.data || [];

        new Chart(regCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pengguna Terdaftar Masuk',
                        data: counts,
                        borderColor: '#6B4226',
                        backgroundColor: 'rgba(107, 66, 38, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#6B4226',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 10,
                            usePointStyle: true,
                            font: { size: 11, family: 'sans-serif', weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'monospace', size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { precision: 0, font: { family: 'monospace', size: 11 } }
                    }
                }
            }
        });
    }

    // 2. Order Status Doughnut Chart
    const orderCtx = document.getElementById('orderStatusChart');
    if (orderCtx) {
        const orderData = @json($orderTelemetry);
        const pending = orderData.pending || 0;
        const processing = orderData.processing || 0;
        const completed = orderData.completed || 0;
        const cancelled = orderData.cancelled || 0;

        const hasOrders = (pending + processing + completed + cancelled) > 0;
        const chartCounts = hasOrders ? [pending, processing, completed, cancelled] : [1];
        const chartLabels = hasOrders ? ['Menunggu', 'Diproses', 'Selesai', 'Dibatalkan'] : ['Belum Ada Pesanan'];
        const chartColors = hasOrders ? ['#f59e0b', '#3b82f6', '#10b981', '#f43f5e'] : ['#e2e8f0'];

        new Chart(orderCtx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartCounts,
                    backgroundColor: chartColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: hasOrders ? 4 : 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            font: { size: 10, family: 'sans-serif', weight: 'bold' }
                        }
                    },
                    tooltip: {
                        enabled: hasOrders
                    }
                }
            }
        });
    }
});
</script>
@endsection
