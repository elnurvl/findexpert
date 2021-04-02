<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;
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
        $this->app->singleton(Client::class, function ($app) {
            return ClientBuilder::create()
                ->addHttpConnection('default', env('NEO4J_PROTOCOL').'://'.env('NEO4J_USER').':'.env('NEO4J_PASSWORD').'@'.env('NEO4J_HOST'))
                ->setDefaultConnection('default')
                ->build();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();
    }
}
