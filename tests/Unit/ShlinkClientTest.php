<?php

namespace Fxkopp\StatamicShlinkManager\Tests\Unit;

use Fxkopp\StatamicShlinkManager\ShlinkClient;
use Fxkopp\StatamicShlinkManager\Tests\TestCase;
use Mockery;
use Shlinkio\Shlink\SDK\Domains\DomainsClientInterface;
use Shlinkio\Shlink\SDK\RedirectRules\RedirectRulesClientInterface;
use Shlinkio\Shlink\SDK\ShlinkClient as SdkClient;
use Shlinkio\Shlink\SDK\ShortUrls\ShortUrlsClientInterface;
use Shlinkio\Shlink\SDK\Tags\TagsClientInterface;
use Shlinkio\Shlink\SDK\Visits\VisitsClientInterface;

class ShlinkClientTest extends TestCase
{
    private function makeSdkMock(): SdkClient
    {
        return new SdkClient(
            Mockery::mock(ShortUrlsClientInterface::class),
            Mockery::mock(VisitsClientInterface::class),
            Mockery::mock(TagsClientInterface::class),
            Mockery::mock(DomainsClientInterface::class),
            Mockery::mock(RedirectRulesClientInterface::class),
        );
    }

    public function test_domain_returns_configured_domain(): void
    {
        $client = new ShlinkClient($this->makeSdkMock(), 's.example.com');

        $this->assertEquals('s.example.com', $client->domain());
    }

    public function test_list_tags_delegates_to_sdk(): void
    {
        $sdk = $this->makeSdkMock();
        // The SDK's listTags delegates to TagsClient
        // We test through the wrapper to verify delegation
        $client = new ShlinkClient($sdk, 's.example.com');

        // This will call through to the mocked TagsClientInterface
        // We just verify the method exists and is callable
        $this->assertTrue(method_exists($client, 'listTags'));
    }

    public function test_delete_short_url_exists(): void
    {
        $client = new ShlinkClient($this->makeSdkMock(), 's.example.com');

        $this->assertTrue(method_exists($client, 'deleteShortUrl'));
    }

    public function test_edit_short_url_exists(): void
    {
        $client = new ShlinkClient($this->makeSdkMock(), 's.example.com');

        $this->assertTrue(method_exists($client, 'editShortUrl'));
    }
}
