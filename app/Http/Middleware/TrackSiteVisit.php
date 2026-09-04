<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisit
{
    /**
     * Handle an incoming request and track website visits.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track real GET page visits, ignoring AJAX polling, debug routes, and assets
        if ($request->isMethod('GET') && ! $request->ajax() && ! $request->wantsJson() && ! $request->has('feed')) {
            $path = $request->path();
            if (! str_starts_with($path, '_') && ! str_starts_with($path, 'api/')) {
                try {
                    $userId = Auth::id() ?? $request->user()?->id;
                    SiteVisit::create([
                        'ip_address' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'route_name' => $request->route()?->getName() ?? $path,
                        'method' => $request->method(),
                        'user_id' => $userId,
                        'user_agent' => substr((string) $request->userAgent(), 0, 500),
                        'created_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // Silently fail if DB is unavailable to not block user browsing
                }
            }
        }

        return $response;
    }
}
