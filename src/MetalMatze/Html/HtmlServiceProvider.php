<?php
namespace MetalMatze\Html;

use Illuminate\Support\ServiceProvider;
use MetalMatze\MD5\MD5;

class HtmlServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHtmlBuilder();
    }

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder()
    {
        $this->app['html'] = $this->app->share(function ($app) {
            return new HtmlBuilderCachebusting($app['url'], $app['files'], new MD5);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('html');
    }
}
