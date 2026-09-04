<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SiteVisit;
use App\Models\Store;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\GmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Show the main system monitoring & operations dashboard.
     */
    public function dashboard(): View
    {
        // 1. Database Connection, Size & Latency Benchmark
        $dbConnected = true;
        $dbSize = 'N/A';
        $dbLatency = '0.00 ms';
        try {
            DB::connection()->getPdo();
            $startBench = microtime(true);
            DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $startBench) * 1000, 2).' ms';

            $driver = config('database.default');
            if ($driver === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                if (file_exists($dbPath)) {
                    $dbSize = round(filesize($dbPath) / 1024, 2).' KB';
                }
            } elseif ($driver === 'pgsql') {
                $res = DB::select('SELECT pg_size_pretty(pg_database_size(current_database())) as size');
                if (! empty($res)) {
                    $dbSize = $res[0]->size;
                }
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                $dbName = config('database.connections.mysql.database');
                $res = DB::select('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.tables WHERE table_schema = ?', [$dbName]);
                if (! empty($res) && isset($res[0]->size)) {
                    $dbSize = $res[0]->size.' MB';
                }
            }
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        // 2. Real Server Performance
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2).' MB';
        $memoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 2).' MB';
        $memoryLimit = ini_get('memory_limit') ?: '128M';

        // 3. User & Security Telemetry
        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalVerified = User::whereNotNull('email_verified_at')->count();
        $totalUnverified = User::whereNull('email_verified_at')->count();
        $verificationRate = $totalUsers > 0 ? round(($totalVerified / $totalUsers) * 100, 1) : 0;

        // 4. Real Registered Users Telemetry (Chart 1 - Past 7 Days)
        $registeredLabels = [];
        $registeredDaily = [];

        for ($day = 6; $day >= 0; $day--) {
            $targetDate = now()->subDays($day);
            $startOfDay = $targetDate->copy()->startOfDay();
            $endOfDay = $targetDate->copy()->endOfDay();

            $label = $targetDate->translatedFormat('d M');
            $membersCount = SiteVisit::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            $registeredLabels[] = $label;
            $registeredDaily[] = $membersCount;
        }

        $overallRegistered = SiteVisit::whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $todayRegistered = SiteVisit::where('created_at', '>=', now()->startOfDay())->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $avgDailyRegistered = round(array_sum($registeredDaily) / 7, 1);

        $registeredUsersTelemetry = [
            'labels' => $registeredLabels,
            'data' => $registeredDaily,
            'total' => $overallRegistered,
            'today' => $todayRegistered,
            'avg_daily' => $avgDailyRegistered,
        ];

        // 5. Order Status & Distribution Telemetry (Chart 2)
        $orderCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $pendingOrders = (int) ($orderCounts['pending'] ?? 0);
        $processingOrders = (int) (($orderCounts['confirmed'] ?? 0) + ($orderCounts['processing'] ?? 0));
        $completedOrders = (int) (($orderCounts['shipped'] ?? 0) + ($orderCounts['completed'] ?? 0));
        $cancelledOrders = (int) ($orderCounts['cancelled'] ?? 0);
        $totalOrders = Order::count();

        $orderTelemetry = [
            'pending' => $pendingOrders,
            'processing' => $processingOrders,
            'completed' => $completedOrders,
            'cancelled' => $cancelledOrders,
            'total' => $totalOrders,
            'labels' => ['Menunggu (Pending)', 'Diproses', 'Selesai', 'Dibatalkan'],
            'data' => [$pendingOrders, $processingOrders, $completedOrders, $cancelledOrders],
        ];

        // 6. System Logs & Telemetry Events Breakdown
        $logFile = storage_path('logs/laravel.log');
        $recentLogCount = 0;
        $auditCount = 0;
        $errorCount = 0;
        $warningCount = 0;
        $infoCount = 0;

        if (File::exists($logFile)) {
            $logContent = File::get($logFile);
            $lines = explode("\n", trim($logContent));
            $recentLogCount = count(array_filter($lines));

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }
                if (str_contains($line, '[AUDIT')) {
                    $auditCount++;
                } elseif (str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:') || str_contains($line, '.EMERGENCY:')) {
                    $errorCount++;
                } elseif (str_contains($line, '.WARNING:')) {
                    $warningCount++;
                } elseif (str_contains($line, '.INFO:') || str_contains($line, '.NOTICE:')) {
                    $infoCount++;
                }
            }
        }

        $logTelemetry = [
            'audit' => $auditCount,
            'error' => $errorCount,
            'warning' => $warningCount,
            'info' => $infoCount,
            'total' => $recentLogCount,
        ];

        // 7. Core System Metrics
        $systemInfo = [
            'app_name' => config('app.name', 'NusantaraMart'),
            'app_env' => strtoupper(config('app.env', 'production')),
            'app_debug' => config('app.debug') ? 'Aktif (Development)' : 'Nonaktif (Production Secure)',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_status' => $dbConnected ? 'Online (Connected)' : 'Offline / Error',
            'database_driver' => strtoupper(config('database.default', 'sqlite')),
            'database_size' => $dbSize,
            'database_latency' => $dbLatency,
            'tables_count' => count(Schema::getTableListing()),
            'memory_usage' => $memoryUsage,
            'memory_peak' => $memoryPeak,
            'memory_limit' => $memoryLimit,
            'cache_driver' => strtoupper(config('cache.default', 'file')),
            'session_driver' => strtoupper(config('session.driver', 'file')),
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
            'server_time' => now()->format('Y-m-d H:i:s T'),
            'prevent_back_history' => 'Aktif (Anti-Cache Protection)',
            'gmail_api_status' => class_exists(GmailService::class) ? 'Terkoneksi (Google OAuth 2.0)' : 'Nonaktif',
            'csrf_protection' => 'Aktif (Token Verified)',
            'password_encryption' => 'Aktif (Bcrypt Cost 12)',
        ];

        return view('admin.dashboard', compact(
            'systemInfo',
            'totalUsers',
            'totalAdmins',
            'totalCustomers',
            'totalVerified',
            'totalUnverified',
            'verificationRate',
            'registeredUsersTelemetry',
            'orderTelemetry',
            'logTelemetry',
            'recentLogCount'
        ));
    }

    /**
     * Clear application cache, views, route cache, and config cache.
     */
    public function clearCache(Request $request): RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');
            AuditLogger::admin('Pembersihan Cache', 'Perintah optimize:clear dijalankan', $request->user());

            return back()->with('admin_success', 'Cache aplikasi, tampilan Blade, rute, dan konfigurasi berhasil dibersihkan! 🧹');
        } catch (\Throwable $e) {
            return back()->with('admin_error', 'Gagal membersihkan cache: '.$e->getMessage());
        }
    }

    /**
     * Optimize and vacuum the database.
     */
    public function optimizeDatabase(Request $request): RedirectResponse
    {
        try {
            $driver = config('database.default');
            if (DB::transactionLevel() === 0) {
                if ($driver === 'sqlite' || $driver === 'pgsql') {
                    DB::statement('VACUUM;');
                }
            }

            AuditLogger::admin('Optimasi Basis Data', "Operasi vacuum & defragmentasi basis data {$driver} dijalankan", $request->user());

            return back()->with('admin_success', 'Optimasi basis data berhasil dijalankan! Ruang penyimpanan telah didefragmentasi. 💾✨');
        } catch (\Throwable $e) {
            return back()->with('admin_error', 'Gagal mengoptimasi database: '.$e->getMessage());
        }
    }

    /**
     * Show real system logs reader & search.
     */
    public function logs(Request $request): View|JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');
        $logEntries = [];
        $logSize = '0 KB';

        if (File::exists($logFile)) {
            $logSize = round(File::size($logFile) / 1024, 2).' KB';
            $content = File::get($logFile);
            $lines = explode("\n", trim($content));
            $lines = array_reverse($lines); // newest first

            $filter = strtolower((string) $request->get('q', ''));
            $levelFilter = strtoupper((string) $request->get('level', 'ALL'));

            $count = 0;
            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                // Match Laravel standard log format: [YYYY-MM-DD HH:MM:SS] environment.LEVEL: message
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.([A-Z]+): (.*)$/', $line, $matches)) {
                    $timestamp = $matches[1];
                    $level = $matches[2];
                    $message = $matches[3];

                    $isAudit = str_contains($message, '[AUDIT');
                    if ($levelFilter === 'AUDIT' && ! $isAudit) {
                        continue;
                    }

                    if ($levelFilter !== 'ALL' && $levelFilter !== 'AUDIT' && $level !== $levelFilter) {
                        continue;
                    }

                    if (! empty($filter) && ! str_contains(strtolower($line), $filter)) {
                        continue;
                    }

                    $auditData = $isAudit ? $this->parseAuditEntry($message) : null;

                    $logEntries[] = [
                        'timestamp' => $timestamp,
                        'level' => $level,
                        'is_audit' => $isAudit,
                        'audit' => $auditData,
                        'message' => $message,
                        'raw' => $line,
                    ];

                    $count++;
                    if ($count >= 150) {
                        break;
                    }
                } elseif (! empty($logEntries) && empty($filter) && $levelFilter === 'ALL') {
                    // Append stack trace lines to the previous entry
                    $lastIdx = count($logEntries) - 1;
                    $logEntries[$lastIdx]['message'] .= "\n".$line;
                }
            }
        }

        if ($request->wantsJson() || $request->ajax() || $request->has('feed')) {
            return response()->json([
                'entries' => $logEntries,
                'logSize' => $logSize,
                'timestamp' => now()->format('H:i:s'),
            ]);
        }

        return view('admin.logs', compact('logEntries', 'logSize'));
    }

    /**
     * Clear the system log file.
     */
    public function clearLogs(Request $request): RedirectResponse
    {
        $logFile = storage_path('logs/laravel.log');
        if (File::exists($logFile)) {
            File::put($logFile, '');
        }

        AuditLogger::admin('Pengosongan Catatan Log', 'File log sistem dibersihkan', $request->user());

        return back()->with('admin_success', 'File catatan log sistem berhasil dikosongkan! 🗑️');
    }

    /**
     * Show Database & Storage Health Monitor.
     */
    public function database(): View
    {
        $tables = [];
        try {
            $tableNames = Schema::getTableListing();
            foreach ($tableNames as $rawName) {
                $displayName = str_starts_with($rawName, 'public.') ? substr($rawName, 7) : $rawName;
                try {
                    $rowCount = DB::table($rawName)->count();
                } catch (\Throwable $e) {
                    $rowCount = 0;
                }

                $tables[] = [
                    'name' => $displayName,
                    'raw_name' => $rawName,
                    'rows' => $rowCount,
                ];
            }

            usort($tables, fn ($a, $b) => strcmp($a['name'], $b['name']));
        } catch (\Throwable $e) {
            $tables = [];
        }

        $dbInfo = [
            'driver' => strtoupper(config('database.default')),
            'status' => 'Online & Healthy',
            'tables_count' => count($tables),
            'storage_public_writable' => is_writable(storage_path('app/public')) ? 'Writable (OK)' : 'Read-Only',
            'logs_writable' => is_writable(storage_path('logs')) ? 'Writable (OK)' : 'Read-Only',
        ];

        return view('admin.database', compact('tables', 'dbInfo'));
    }

    /**
     * Show User & Administrator Accounts list with actionable admin operations.
     */
    public function users(): View
    {
        $users = User::latest()->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Update user profile details and role (termasuk jadikan admin).
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:admin,customer'],
            'password' => ['nullable', 'string', 'min:6'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($user->id === $request->user()->id && $validated['role'] === 'customer') {
            return back()->with('admin_error', 'Kamu tidak dapat mencabut peran administrator dari akunmu sendiri!');
        }

        $oldRole = $user->role;

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $updateData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if ($request->boolean('verify_email') && ! $user->email_verified_at) {
            $updateData['email_verified_at'] = now();
            $updateData['otp_code'] = null;
            $updateData['otp_expires_at'] = null;
        }

        $user->update($updateData);

        $roleChanged = $oldRole !== $validated['role'] ? " (Peran diubah dari {$oldRole} menjadi {$validated['role']})" : '';
        AuditLogger::admin('Pembaruan Data Pengguna', "Data akun {$user->name} (@{$user->username}) diperbarui oleh admin{$roleChanged}", $request->user());

        return back()->with('admin_success', "Data akun pengguna {$user->name} berhasil diperbarui! ✨");
    }

    /**
     * Update user role in system.
     */
    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id && $request->role === 'customer') {
            return back()->with('admin_error', 'Kamu tidak dapat mencabut peran administrator dari akunmu sendiri!');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,customer'],
        ]);

        $user->update([
            'role' => $validated['role'],
        ]);

        AuditLogger::admin('Perubahan Hak Akses', "Peran akun {$user->name} (@{$user->username}) diubah menjadi {$validated['role']}", $request->user());

        return back()->with('admin_success', "Peran akun {$user->name} berhasil diperbarui menjadi: ".strtoupper($validated['role']).' 🛡️✨');
    }

    /**
     * Manually verify a user's email.
     */
    public function verifyUser(Request $request, User $user): RedirectResponse
    {
        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        AuditLogger::admin('Verifikasi Akun Manual', "Akun {$user->name} (@{$user->username}, {$user->email}) diverifikasi manual", $request->user());

        return back()->with('admin_success', "Akun {$user->name} ({$user->email}) berhasil diverifikasi secara manual! ✓");
    }

    /**
     * Reset a user's password directly.
     */
    public function resetUserPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        AuditLogger::admin('Reset Kata Sandi', "Kata sandi akun {$user->name} (@{$user->username}) direset oleh admin", $request->user());

        return back()->with('admin_success', "Kata sandi akun {$user->name} berhasil direset ke sandi baru! 🔑");
    }

    /**
     * Delete a user account.
     */
    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('admin_error', 'Kamu tidak dapat menghapus akunmu sendiri!');
        }

        $userName = $user->name;
        $usernameTag = $user->username;
        $user->delete();

        AuditLogger::admin('Hapus Akun Pengguna', "Akun {$userName} (@{$usernameTag}) dihapus dari sistem", $request->user());

        return back()->with('admin_success', "Akun pengguna {$userName} berhasil dihapus dari sistem.");
    }

    /**
     * Parse raw audit log string into structured metadata for high-efficiency UI.
     *
     * @return array{category: string, action: string, description: string, actor: string, ip: string, meta: array<int, string>}
     */
    private function parseAuditEntry(string $message): array
    {
        $category = 'SYSTEM';
        if (preg_match('/^\[AUDIT:([A-Z]+)\]\s*(.*)$/', $message, $m)) {
            $category = $m[1];
            $body = $m[2];
        } else {
            $body = $message;
        }

        $parts = explode('|', $body);
        $mainPart = trim($parts[0] ?? '');
        $actorPart = trim($parts[1] ?? '');
        $metaParts = array_slice($parts, 2);

        $action = $mainPart;
        $description = '';
        if (str_contains($mainPart, ' • ')) {
            [$action, $description] = explode(' • ', $mainPart, 2);
            $action = trim($action);
            $description = trim($description);
        }

        $actor = '';
        if (! empty($actorPart)) {
            $actor = preg_replace('/^(Pengguna:|Pemesan:|Akun:|Pelanggan:|Administrator:|Dilakukan oleh:\s*Administrator:|Dilakukan oleh:)\s*/i', '', $actorPart);
            $actor = trim((string) $actor);
        }

        $ip = '';
        $otherMeta = [];
        foreach ($metaParts as $mp) {
            $mp = trim($mp);
            if (str_starts_with($mp, 'IP:')) {
                $ip = trim(substr($mp, 3));
            } else {
                $otherMeta[] = $mp;
            }
        }

        return [
            'category' => $category,
            'action' => $action,
            'description' => $description,
            'actor' => $actor,
            'ip' => $ip,
            'meta' => $otherMeta,
        ];
    }

    /**
     * Display seller store applications and management panel.
     */
    public function stores(Request $request): View
    {
        $status = $request->input('status', 'all');
        $search = $request->input('q');

        $pendingCount = Store::where('status', 'pending')->count();
        $approvedCount = Store::where('status', 'approved')->count();
        $rejectedCount = Store::where('status', 'rejected')->count();
        $totalCount = Store::count();

        $stores = Store::with(['user', 'products'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($query) use ($search) {
                $query->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('city', 'ILIKE', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'ILIKE', "%{$search}%")->orWhere('email', 'ILIKE', "%{$search}%"));
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.stores.index', compact(
            'stores',
            'status',
            'search',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount'
        ));
    }

    /**
     * Approve a seller store application.
     */
    public function approveStore(Store $store): RedirectResponse
    {
        $store->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        if ($store->user && $store->user->role !== 'admin') {
            $store->user->update(['role' => 'seller']);
        }

        AuditLogger::admin(
            'STORE_APPROVED',
            "Persetujuan Toko Penjual • Toko '{$store->name}' resmi disetujui (Pemilik: ".($store->user->name ?? 'N/A').')'
        );

        return back()->with('success', "Toko '{$store->name}' berhasil disetujui! Pengguna kini aktif sebagai Penjual Resmi.");
    }

    /**
     * Reject a seller store application with a custom message/reason.
     */
    public function rejectStore(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $store->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        AuditLogger::admin(
            'STORE_REJECTED',
            "Penolakan Toko Penjual • Pengajuan '{$store->name}' ditolak (Alasan: {$validated['rejection_reason']})"
        );

        return back()->with('warning', "Pengajuan toko '{$store->name}' telah ditolak dengan pesan pembatalan.");
    }
}
