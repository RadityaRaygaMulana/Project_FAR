<?php

use App\Http\Middleware\CheckSuspended;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsSeller;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\TrackSiteVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', PreventBackHistory::class);
        $middleware->appendToGroup('web', TrackSiteVisit::class);
        $middleware->appendToGroup('web', CheckSuspended::class);
        $middleware->alias([
            'prevent-back-history' => PreventBackHistory::class,
            'preventbackhistory' => PreventBackHistory::class,
            'admin' => EnsureUserIsAdmin::class,
            'seller' => EnsureUserIsSeller::class,
            'check-suspended' => CheckSuspended::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
