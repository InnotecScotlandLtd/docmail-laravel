<?php

namespace GoSoftware\Docmail;

use Illuminate\Support\ServiceProvider;

class DocmailServiceProvider extends ServiceProvider {
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'docmail');

        $this->publishes([
            __DIR__.'/config/docmail.php' => config_path('docmail.php'),
            __DIR__.'/views' => base_path('resources/views/vendor/docmail'),
        ]);
    }
}