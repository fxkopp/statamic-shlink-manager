<?php

namespace Fxkopp\StatamicShlinkManager\Tests\Feature;

use Fxkopp\StatamicShlinkManager\Tests\TestCase;
use Statamic\Facades\User;

class ServersCsvTest extends TestCase
{
    public function test_servers_csv_requires_authentication(): void
    {
        $this->get(cp_route('shlink-manager.servers-csv'))
            ->assertRedirect();
    }

    public function test_servers_csv_returns_csv_for_super_admin(): void
    {
        $user = User::make()->makeSuper()->save();

        $response = $this->actingAs($user)
            ->get(cp_route('shlink-manager.servers-csv'));

        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('name,apiKey,url', $content);
        $this->assertStringContainsString('s.example.com', $content);
        $this->assertStringContainsString('test-api-key', $content);
    }
}
