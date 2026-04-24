<?php

namespace Fxkopp\StatamicShlinkManager\Widgets;

use Fxkopp\StatamicShlinkManager\ShlinkClient;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\User;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class RecentLinksWidget extends Widget
{
    protected static $handle = 'shlink_manager_recent_links';

    public function component()
    {
        if (! User::current()?->can('view short urls')) {
            return null;
        }

        $data = Cache::remember('shlink-manager:widget', 300, function () {
            try {
                $client = app(ShlinkClient::class);

                return [
                    'urls' => $client->listShortUrls(limit: 5),
                    'totalVisits' => $client->getVisitsOverview()['totalVisits'],
                ];
            } catch (\Throwable) {
                return ['urls' => [], 'totalVisits' => 0];
            }
        });

        return VueComponent::render('shlink-manager-recent-links-widget', [
            ...$data,
            'indexUrl' => cp_route('shlink-manager.index'),
            'createUrl' => cp_route('shlink-manager.create'),
        ]);
    }
}
