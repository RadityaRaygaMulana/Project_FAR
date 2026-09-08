<?php

namespace App\Http\Middleware;

use App\Models\SuspensionAppeal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspended
{
    /**
     * Handle an incoming request.
     *
     * Jika pengguna yang sedang login berstatus suspended, mereka akan di-logout
     * dan diarahkan ke halaman login dengan flash message card alasan pemblokiran.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isSuspended()) {
            $user = Auth::user();
            $reason = $user->suspension_reason ?? 'Melanggar ketentuan layanan.';
            $until = $user->suspended_until;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $existingAppeal = SuspensionAppeal::where('user_id', $user->id)->latest()->first();

            return redirect()->route('login')
                ->with('suspended', true)
                ->with('suspension_reason', $reason)
                ->with('suspended_until', $until ? $until->translatedFormat('d F Y, H:i') : null)
                ->with('suspended_diff', $until ? $until->diffForHumans() : null)
                ->with('is_permanent', $until === null)
                ->with('suspended_identifier', $user->email)
                ->with('suspended_name', $user->name)
                ->with('latest_appeal_status', $existingAppeal?->status)
                ->with('latest_appeal_notes', $existingAppeal?->admin_notes)
                ->with('latest_appeal_date', $existingAppeal?->created_at?->translatedFormat('d M Y, H:i'));
        }

        return $next($request);
    }
}
