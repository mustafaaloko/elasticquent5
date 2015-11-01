<?php

namespace Aloko\Elasticquent;

use Illuminate\Support\ServiceProvider;

class ElasticquentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the config file to the config path
        $this->publishes([
            __DIR__.'/../config/elasticquent.php' => config_path('elasticquent.php')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
