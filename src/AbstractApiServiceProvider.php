<?php namespace Ypa\AbstractApi;

use Ypa\Core\GuzzleHttp\GuzzleClientServiceProvider;
use Illuminate\Support\ServiceProvider;

class AbstractApiServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    protected $package_name = 'ypa';

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

        $this->handleConfigs();
        $this->handleTranslations();
        $this->handleMiddleware();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind any implementations.
        $this->app->bind('ypa_abstract_api', AbstractApiFactory::class);

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [GuzzleClientServiceProvider::class];
    }

    private function handleConfigs()
    {
        if (is_laravel()) {
            $configPath = __DIR__ . '/../config/ypa-abstractapi.php';

            $this->publishes([
                $configPath => config_path('ypa-abstractapi.php')
            ], $this->package_name);

            $this->mergeConfigFrom($configPath, $this->package_name);
        }
    }

    private function handleTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', $this->package_name);
    }

    private function handleMiddleware()
    {
        if (is_laravel()) {
            $this->app['router']->pushMiddlewareToGroup('api', AbstractApiMiddleware::class);
        }
    }
}
