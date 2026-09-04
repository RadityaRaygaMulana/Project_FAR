<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->isSeller()) {
            if ($user->store && $user->store->isPending()) {
                return redirect()->route('seller.register')
                    ->with('warning', 'Pengajuan toko Anda masih dalam proses peninjauan oleh Administrator.');
            }

            if ($user->store && $user->store->isRejected()) {
                return redirect()->route('seller.register')
                    ->with('error', 'Pengajuan toko Anda belum disetujui: '.($user->store->rejection_reason ?: 'Silakan perbaiki data toko.'));
            }

            return redirect()->route('seller.register')
                ->with('info', 'Silakan daftarkan toko Anda terlebih dahulu untuk mengakses Seller Center.');
        }

        return $next($request);
    }
}
