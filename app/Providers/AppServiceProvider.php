<?php

namespace App\Providers;

use App\Support\ToastMessages;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer(['layouts.app', 'layouts.guest'], function ($view): void {
            $view->with('sedToasts', ToastMessages::collect());
        });
    }
}
