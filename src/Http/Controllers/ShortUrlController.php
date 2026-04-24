<?php

namespace Fxkopp\StatamicShlinkManager\Http\Controllers;

use Fxkopp\StatamicShlinkManager\ShlinkClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Shlinkio\Shlink\SDK\Exception\ShlinkException;
use Statamic\Http\Controllers\CP\CpController;

class ShortUrlController extends CpController
{
    public function __construct(
        Request $request,
        private ShlinkClient $shlink,
    ) {
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $this->authorize('view short urls');

        $search = $request->input('search');

        try {
            $urls = $this->shlink->listShortUrls($search);
            $overview = $this->shlink->getVisitsOverview();
        } catch (ShlinkException $e) {
            $urls = [];
            $overview = ['totalVisits' => 0, 'totalBots' => 0, 'orphanVisits' => 0];
            session()->flash('error', __('shlink-manager::messages.connection_error'));
        }

        return Inertia::render('shlink-manager::Index', [
            'urls' => $urls,
            'overview' => $overview,
            'search' => $search,
            'domain' => $this->shlink->domain(),
            'canEdit' => $request->user()->can('edit short urls'),
            'isSuper' => $request->user()->isSuper(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('edit short urls');

        try {
            $existingTags = $this->shlink->listTags();
        } catch (ShlinkException) {
            $existingTags = [];
        }

        return Inertia::render('shlink-manager::Create', [
            'domain' => $this->shlink->domain(),
            'existingTags' => $existingTags,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('edit short urls');

        $validated = $request->validate([
            'long_url' => ['required', 'url'],
            'custom_slug' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'title' => ['nullable', 'string', 'max:200'],
            'valid_since' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'max_visits' => ['nullable', 'integer', 'min:1'],
            'crawlable' => ['nullable', 'boolean'],
            'forward_query' => ['nullable', 'boolean'],
        ]);

        try {
            $this->shlink->createShortUrl(
                $validated['long_url'],
                $validated['custom_slug'] ?? null,
                $validated['tags'] ?? [],
                Arr::only($validated, ['title', 'valid_since', 'valid_until', 'max_visits', 'crawlable', 'forward_query']),
            );

            Cache::forget('shlink-manager:widget');
            session()->flash('success', __('shlink-manager::messages.created'));
        } catch (ShlinkException $e) {
            session()->flash('error', __('shlink-manager::messages.connection_error'));

            return back()->withInput();
        }

        return redirect()->cpRoute('shlink-manager.index');
    }

    public function show(Request $request, string $shortCode)
    {
        $this->authorize('view short urls');

        try {
            $url = $this->shlink->getShortUrl($shortCode);
            $visits = $this->shlink->getVisits($shortCode);
        } catch (ShlinkException $e) {
            session()->flash('error', __('shlink-manager::messages.connection_error'));

            return redirect()->cpRoute('shlink-manager.index');
        }

        return Inertia::render('shlink-manager::Show', [
            'url' => $url,
            'visits' => $visits,
            'canEdit' => $request->user()->can('edit short urls'),
        ]);
    }

    public function edit(Request $request, string $shortCode)
    {
        $this->authorize('edit short urls');

        try {
            $url = $this->shlink->getShortUrl($shortCode);
            $existingTags = $this->shlink->listTags();
        } catch (ShlinkException $e) {
            session()->flash('error', __('shlink-manager::messages.connection_error'));

            return redirect()->cpRoute('shlink-manager.index');
        }

        return Inertia::render('shlink-manager::Edit', [
            'url' => $url,
            'domain' => $this->shlink->domain(),
            'existingTags' => $existingTags,
        ]);
    }

    public function update(Request $request, string $shortCode)
    {
        $this->authorize('edit short urls');

        $validated = $request->validate([
            'long_url' => ['required', 'url'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'title' => ['nullable', 'string', 'max:200'],
            'valid_since' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'max_visits' => ['nullable', 'integer', 'min:1'],
            'crawlable' => ['nullable', 'boolean'],
            'forward_query' => ['nullable', 'boolean'],
        ]);

        try {
            $this->shlink->editShortUrl($shortCode, $validated);
            Cache::forget('shlink-manager:widget');
            session()->flash('success', __('shlink-manager::messages.updated'));
        } catch (ShlinkException $e) {
            session()->flash('error', __('shlink-manager::messages.connection_error'));

            return back()->withInput();
        }

        return redirect()->cpRoute('shlink-manager.show', $shortCode);
    }

    public function serversCsv(Request $request)
    {
        abort_unless($request->user()->isSuper(), 403);

        $domain = config('shlink-manager.default_domain');
        $csv = "name,apiKey,url\n{$domain},".config('shlink-manager.api_key').','.config('shlink-manager.base_url');

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="servers.csv"',
            'Cache-Control' => 'no-store, no-cache, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, string $shortCode)
    {
        $this->authorize('edit short urls');

        try {
            $this->shlink->deleteShortUrl($shortCode);
            Cache::forget('shlink-manager:widget');
            session()->flash('success', __('shlink-manager::messages.deleted'));
        } catch (ShlinkException $e) {
            session()->flash('error', __('shlink-manager::messages.connection_error'));
        }

        return redirect()->cpRoute('shlink-manager.index');
    }
}
