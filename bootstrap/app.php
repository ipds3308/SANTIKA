<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Menambahkan alias middleware untuk mencegah cache tombol Back browser
        $middleware->alias([
            'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // Menangani error 419 Page Expired (CSRF Token Mismatch) secara mutlak
        $exceptions->render(function (TokenMismatchException $e, $request) {
            // Langsung arahkan (redirect) kembali ke halaman login dengan pesan peringatan
            return redirect()
                ->route('login')
                ->with('error', 'Sesi Anda telah kedaluwarsa atau Anda telah keluar dari tab lain. Silakan login kembali.');
        });
        
    })->create();