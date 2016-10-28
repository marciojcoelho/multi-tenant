<?php

namespace Mjc\MultiTenant;

use Cache;
use Route;
use Request;
use Illuminate\Support\ServiceProvider;

class MultiTenantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ( ! $this->app->routesAreCached()) {
            require_once __DIR__ . '/routes.php';
        }
        require_once __DIR__ . '/Helpers/helpers.php';

        $this->registerTenant();

        if($tenant = tenant()) {
            $directory = tenant_path($tenant);

            // Load views from the current tenant directory
            $this->app['view']->getFinder()->prependLocation(realpath($directory . '/views'));

            // Load languages from the current tenant directory
            if(file_exists($directory . '/lang')) {
                $this->app->singleton('translation.domain', function ($app) use ($directory){
                    $fileLoader = new \Illuminate\Translation\FileLoader($app['files'], realpath($directory . '/lang'));

                    return new Translator($fileLoader , $this->app->getLocale());
                });
            }

            // Load the tenant routes within the application controller namespace.
            if(file_exists($directory . '/routes.php')) {
                Route::group(['namespace' => 'App\Http\Controllers'], function () use ($directory) {
                    require_once $directory . '/routes.php';
                });
            }

            // Set the tenant cache driver
            Cache::setDefaultDriver('friet-nl');
        }

        // Load base views, these will be overridden by tenant views with the same name

        $paths = $this->app['view']->getFinder()->getPaths();
        if (!in_array(realpath(base_path('resources/views')), $paths)) {
            $this->app['view']->addLocation(realpath(base_path('resources/views')));
        }

        // Publish the tenant config, which will be overriden by local configuration
        $this->publishes([
            __DIR__ . '/../config/tenant.php' => config_path('tenant.php')
        ], 'multi-tenant');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tenant.php', 'tenant');
    }

    /**
     * Register the Tenant singleton.
     *
     * @return void
     */
    protected function registerTenant()
    {
        $this->app->singleton('Tenant', function ()
        {
            $tenants = config('tenant.hosts');
            $host = Request::server('HTTP_HOST');

            if (array_key_exists($host, $tenants)) {
                return $tenants[$host];
            }

            return false;
        });
    }
}
