<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Log authentication events (login, logout, register, verify, failed attempts).
     */
    public static function auth(string $action, string $description, ?User $user = null, ?Request $request = null): void
    {
        $ip = $request ? $request->ip() : request()->ip();
        $userStr = $user
            ? "Pengguna: {$user->name} (@{$user->username}, {$user->email}, Role: {$user->role})"
            : 'Tamu / Belum Login';

        $msg = "[AUDIT:AUTH] {$action} • {$description} | {$userStr} | IP: {$ip}";
        Log::info($msg);
    }

    /**
     * Log customer order & checkout transactions.
     */
    public static function order(string $action, Order $order, ?User $user = null): void
    {
        $actor = $user ?? Auth::user();
        $actorStr = $actor ? "Pemesan: {$actor->name} (@{$actor->username})" : "Pelanggan: {$order->customer_name}";
        $totalFmt = 'Rp '.number_format($order->grand_total, 0, ',', '.');
        $itemCount = $order->items()->count();
        $payment = strtoupper((string) ($order->payment_method ?? 'COD'));

        $msg = "[AUDIT:ORDER] {$action} • No. Pesanan: {$order->order_code} | {$actorStr} | Total: {$totalFmt} ({$itemCount} item) | Pembayaran: {$payment} | Status: {$order->status}";
        Log::info($msg);
    }

    /**
     * Log user profile and security updates.
     */
    public static function profile(string $action, string $description, ?User $user = null): void
    {
        $actor = $user ?? Auth::user();
        $actorStr = $actor ? "Akun: {$actor->name} (@{$actor->username})" : 'User Anonim';

        $msg = "[AUDIT:PROFILE] {$action} • {$description} | {$actorStr} | IP: ".request()->ip();
        Log::info($msg);
    }

    /**
     * Log administrative operations and system maintenance.
     */
    public static function admin(string $action, string $description, ?User $admin = null): void
    {
        $actor = $admin ?? Auth::user();
        $adminStr = $actor ? "Administrator: {$actor->name} (@{$actor->username})" : 'Sistem Otomatis';

        $msg = "[AUDIT:ADMIN] {$action} • {$description} | Dilakukan oleh: {$adminStr} | IP: ".request()->ip();
        Log::info($msg);
    }

    /**
     * Log digital goods and PPOB / bill payment transactions.
     */
    public static function digital(string $action, string $description, ?User $user = null): void
    {
        $actor = $user ?? Auth::user();
        $actorStr = $actor ? "Pengguna: {$actor->name} (@{$actor->username})" : 'Tamu';

        $msg = "[AUDIT:DIGITAL] {$action} • {$description} | {$actorStr} | IP: ".request()->ip();
        Log::info($msg);
    }

    /**
     * Generic audit logger fallback.
     */
    public static function log(string $action, ?string $modelType = null, mixed $modelId = null, ?string $details = null): void
    {
        $actor = Auth::user();
        $actorStr = $actor ? "Pengguna: {$actor->name} (@{$actor->username})" : 'Tamu';
        $desc = $details ?? "Action on {$modelType} #{$modelId}";

        $msg = "[AUDIT:GENERAL] {$action} • {$desc} | {$actorStr} | IP: ".request()->ip();
        Log::info($msg);
    }
}
