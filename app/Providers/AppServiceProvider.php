<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Brand;
use Illuminate\Support\Facades\URL;

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
        view()->composer('*', function ($view) {
            $view->with('allBrands', Brand::has('products')->get());
        });

        // ép laravel luôn dùng https trên render
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }   
}
