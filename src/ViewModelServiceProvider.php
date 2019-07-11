<?php

namespace MarothyZsolt\ViewModel;

use Illuminate\Support\ServiceProvider;
use MarothyZsolt\ViewModel\Console\ViewModelComponentMakeCommand;
use MarothyZsolt\ViewModel\Console\ViewModelMakeCommand;

class ViewModelServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'wapor');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'wapor');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //$this->mergeConfigFrom(__DIR__.'/../config/viewmodel.php', 'viewmodel');

        // Register the service the package provides.
       /* $this->app->singleton('viewmodel', function ($app) {
            return new ViewModel;
        }); */

        if ($this->app->runningInConsole()) {
            $this->commands([
                ViewModelMakeCommand::class,
                ViewModelComponentMakeCommand::class
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['viewmodel'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/viewmodel.php' => config_path('viewmodel.php'),
        ], 'viewmodel.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/wapor'),
        ], 'viewmodel.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/wapor'),
        ], 'viewmodel.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/wapor'),
        ], 'viewmodel.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
