<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Memaksa semua URL dari route() menjadi HTTPS (berguna saat pakai Ngrok)
        if ($this->app->environment('production') || request()->server('HTTP_X_FORWARDED_PROTO') == 'https') {
            URL::forceScheme('https');
        }
        
        // Atau jika ingin langsung dipaksa HTTPS selamanya selama testing Ngrok:
        // URL::forceScheme('https');
    }
}