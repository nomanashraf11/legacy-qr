<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Paginator::useBootstrap();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force Living Legacy as sender name (overrides .env MAIL_FROM_NAME)
        config([
            'mail.from.address' => config('mail.from.address') ?: 'notification@livinglegacyqr.com',
            'mail.from.name' => 'Living Legacy',
        ]);

        // Suppress Carbon deprecation warnings in development
        if (app()->environment('local', 'development')) {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        }

        $isProduction = app()->environment('production');

        // Only query database if we're in production and have a database connection
        try {
            $data = $isProduction ? Admin::first() : new Admin();
            View::share('admin', $data);
        } catch (\Exception $e) {
            // Fallback to empty admin object if database is not available
            View::share('admin', new Admin());
        }
    }
}
