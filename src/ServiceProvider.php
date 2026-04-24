<?php

namespace Fxkopp\StatamicShlinkManager;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Shlinkio\Shlink\SDK\Builder\ShlinkClientBuilder;
use Shlinkio\Shlink\SDK\Builder\SingletonShlinkClientBuilder;
use Shlinkio\Shlink\SDK\Config\ShlinkConfig;
use Shlinkio\Shlink\SDK\ShlinkClient as SdkClient;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
        ],
        'publicDirectory' => 'resources/dist',
        'hotFile' => __DIR__.'/../resources/dist/hot',
    ];

    protected $widgets = [
        Widgets\RecentLinksWidget::class,
    ];

    protected $config = false;

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shlink-manager.php', 'shlink-manager');

        $this->publishes([
            __DIR__.'/../config/shlink-manager.php' => config_path('shlink-manager.php'),
        ], 'shlink-manager-config');

        $this->app->singleton(ShlinkClient::class, function () {
            $httpFactory = new HttpFactory;
            $builder = new SingletonShlinkClientBuilder(
                new ShlinkClientBuilder(
                    new GuzzleClient(['timeout' => 10]),
                    $httpFactory,
                    $httpFactory,
                ),
            );

            $config = ShlinkConfig::fromBaseUrlAndApiKey(
                config('shlink-manager.base_url'),
                config('shlink-manager.api_key'),
            );

            return new ShlinkClient(
                new SdkClient(
                    $builder->buildShortUrlsClient($config),
                    $builder->buildVisitsClient($config),
                    $builder->buildTagsClient($config),
                    $builder->buildDomainsClient($config),
                    $builder->buildRedirectRulesClient($config),
                ),
                config('shlink-manager.default_domain'),
            );
        });
    }

    public function bootAddon(): void
    {
        $this->bootPermissions();
        $this->bootNav();
    }

    protected function bootPermissions(): void
    {
        Permission::group('shlink_manager', 'Short URLs', function () {
            Permission::register('view short urls', function ($permission) {
                $permission->children([
                    Permission::make('edit short urls')->label(__('shlink-manager::messages.edit_short_urls')),
                ]);
            })->label(__('shlink-manager::messages.view_short_urls'));
        });
    }

    protected function bootNav(): void
    {
        Nav::extend(function ($nav) {
            $nav->tools(__('shlink-manager::messages.title'))
                ->route('shlink-manager.index')
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.213 9.787a3.39 3.39 0 0 0-4.795 0l-3.425 3.426a3.39 3.39 0 0 0 4.795 4.794l.321-.304m-.321-4.502a3.39 3.39 0 0 0 4.795 0l3.424-3.426a3.39 3.39 0 0 0-4.794-4.795l-.321.304"/></svg>')
                ->can('view short urls');
        });
    }
}
