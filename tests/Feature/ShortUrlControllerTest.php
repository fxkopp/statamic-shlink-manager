<?php

namespace Fxkopp\StatamicShlinkManager\Tests\Feature;

use Fxkopp\StatamicShlinkManager\ShlinkClient;
use Fxkopp\StatamicShlinkManager\Tests\TestCase;
use Inertia\Testing\AssertableInertia;
use Statamic\Facades\User;

class ShortUrlControllerTest extends TestCase
{
    private function actingAsSuper()
    {
        $user = User::make()->makeSuper()->save();

        return $this->actingAs($user);
    }

    private function mockShlinkClient(): void
    {
        $mock = $this->mock(ShlinkClient::class)->makePartial();

        $mock->shouldReceive('domain')->andReturn('s.example.com');

        $mock->shouldReceive('listShortUrls')->andReturn([
            [
                'shortCode' => 'abc123',
                'shortUrl' => 'https://s.example.com/abc123',
                'longUrl' => 'https://example.com/my-page',
                'title' => null,
                'tags' => ['artist'],
                'crawlable' => true,
                'forwardQuery' => true,
                'validSince' => null,
                'validUntil' => null,
                'maxVisits' => null,
                'visitsSummary' => ['total' => 5],
                'dateCreated' => '2026-04-24 12:00',
            ],
        ]);

        $mock->shouldReceive('getVisitsOverview')->andReturn([
            'totalVisits' => 5, 'totalBots' => 1, 'orphanVisits' => 0,
        ]);

        $mock->shouldReceive('getShortUrl')->andReturn([
            'shortCode' => 'abc123',
            'shortUrl' => 'https://s.example.com/abc123',
            'longUrl' => 'https://example.com/my-page',
            'title' => null,
            'tags' => ['artist'],
            'crawlable' => true,
            'forwardQuery' => true,
            'validSince' => null,
            'validUntil' => null,
            'maxVisits' => null,
            'visitsSummary' => ['total' => 5],
            'dateCreated' => '2026-04-24 12:00',
        ]);

        $mock->shouldReceive('getVisits')->andReturn([]);
        $mock->shouldReceive('listTags')->andReturn(['artist', 'social']);
        $mock->shouldReceive('createShortUrl')->andReturn([
            'shortCode' => 'new123',
            'shortUrl' => 'https://s.example.com/new123',
            'longUrl' => 'https://example.com',
            'title' => null,
            'tags' => [],
            'crawlable' => true,
            'forwardQuery' => true,
            'validSince' => null,
            'validUntil' => null,
            'maxVisits' => null,
            'visitsSummary' => ['total' => 0],
            'dateCreated' => '2026-04-24 13:00',
        ]);
        $mock->shouldReceive('editShortUrl');
        $mock->shouldReceive('deleteShortUrl');
    }

    public function test_index_renders(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->get(cp_route('shlink-manager.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('shlink-manager::Index'));
    }

    public function test_index_requires_authentication(): void
    {
        $this->get(cp_route('shlink-manager.index'))
            ->assertRedirect();
    }

    public function test_create_renders_with_existing_tags(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->get(cp_route('shlink-manager.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('shlink-manager::Create')
                ->has('existingTags', 2)
            );
    }

    public function test_store_validates_required_url(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->from(cp_route('shlink-manager.create'))
            ->post(cp_route('shlink-manager.store'), ['long_url' => ''])
            ->assertRedirect(cp_route('shlink-manager.create'))
            ->assertSessionHasErrors('long_url');
    }

    public function test_store_rejects_invalid_url(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->from(cp_route('shlink-manager.create'))
            ->post(cp_route('shlink-manager.store'), ['long_url' => 'not-a-url'])
            ->assertRedirect(cp_route('shlink-manager.create'))
            ->assertSessionHasErrors('long_url');
    }

    public function test_store_creates_short_url(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->post(cp_route('shlink-manager.store'), [
                'long_url' => 'https://example.com',
                'custom_slug' => 'test',
                'tags' => ['social'],
            ])
            ->assertRedirect(cp_route('shlink-manager.index'));
    }

    public function test_show_renders(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->get(cp_route('shlink-manager.show', 'abc123'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('shlink-manager::Show'));
    }

    public function test_edit_renders(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->get(cp_route('shlink-manager.edit', 'abc123'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('shlink-manager::Edit'));
    }

    public function test_update_redirects_to_show(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->patch(cp_route('shlink-manager.update', 'abc123'), [
                'long_url' => 'https://new-url.com',
                'tags' => ['updated'],
            ])
            ->assertRedirect(cp_route('shlink-manager.show', 'abc123'));
    }

    public function test_destroy_redirects_to_index(): void
    {
        $this->mockShlinkClient();

        $this->actingAsSuper()
            ->delete(cp_route('shlink-manager.destroy', 'abc123'))
            ->assertRedirect(cp_route('shlink-manager.index'));
    }
}
