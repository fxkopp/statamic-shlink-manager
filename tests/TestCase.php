<?php

namespace Fxkopp\StatamicShlinkManager\Tests;

use Fxkopp\StatamicShlinkManager\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('shlink-manager.base_url', 'https://s.example.com');
        $app['config']->set('shlink-manager.api_key', 'test-api-key');
        $app['config']->set('shlink-manager.default_domain', 's.example.com');
    }
}
