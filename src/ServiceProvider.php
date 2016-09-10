<?php

namespace Ferri\LaravelSettings;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
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
        $this->app->singleton('settings.repository', function ($app) {
            return new Repository(
                $app['db'],
                $app['config']['settings.table']
            );
        });

        $this->app->singleton('settings', function ($app) {
            $settings = new Settings($app['settings.repository']);

            $settings->setCache($app['cache.store']);

            $app['config']['settings.cache'] ? $settings->enableCache() : $settings->disableCache();

            return $settings;
        });

        $this->app->alias('settings', 'Ferri\LaravelSettings\Settings');

        $loader = \Illuminate\Foundation\AliasLoader::getInstance();

        $loader->alias('Settings', 'Ferri\LaravelSettings\Facade');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/settings.php' => config_path('settings.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'settings',
            'settings.repository',
        ];
    }
}
