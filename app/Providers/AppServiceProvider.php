<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PHPHtmlParser\Dom;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Dom::class, function ($app) {
            return new Dom;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
