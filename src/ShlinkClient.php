<?php

namespace Fxkopp\StatamicShlinkManager;

use DateTimeImmutable;
use Illuminate\Support\Str;
use Shlinkio\Shlink\SDK\ShlinkClient as SdkClient;
use Shlinkio\Shlink\SDK\ShortUrls\Model\ShortUrlCreation;
use Shlinkio\Shlink\SDK\ShortUrls\Model\ShortUrlEdition;
use Shlinkio\Shlink\SDK\ShortUrls\Model\ShortUrlIdentifier;
use Shlinkio\Shlink\SDK\ShortUrls\Model\ShortUrlsFilter;
use Shlinkio\Shlink\SDK\Visits\Model\VisitsFilter;

class ShlinkClient
{
    private string $baseUrl;

    public function __construct(
        private SdkClient $sdk,
        private string $defaultDomain,
    ) {
        $this->baseUrl = Str::finish(config('shlink-manager.base_url'), '/');
    }

    public function domain(): string
    {
        return $this->defaultDomain;
    }

    public function listShortUrls(?string $search = null, int $limit = 50): array
    {
        $filter = ShortUrlsFilter::create();

        if ($search) {
            $filter = $filter->searchingBy($search);
        }

        $urls = [];
        foreach ($this->sdk->listShortUrlsWithFilter($filter) as $url) {
            $urls[] = $this->serializeShortUrl($url);
            if (count($urls) >= $limit) {
                break;
            }
        }

        return $urls;
    }

    public function createShortUrl(string $longUrl, ?string $customSlug = null, array $tags = [], array $options = []): array
    {
        $creation = ShortUrlCreation::forLongUrl($longUrl);

        if ($customSlug) {
            $creation = $creation->withCustomSlug($customSlug);
        }

        if ($tags) {
            $creation = $creation->withTags(...$tags);
        }

        $creation = $this->applyOptions($creation, $options);

        $url = $this->sdk->createShortUrl($creation);

        return $this->serializeShortUrl($url);
    }

    public function editShortUrl(string $shortCode, array $data): void
    {
        $identifier = ShortUrlIdentifier::fromShortCode($shortCode);

        $edition = ShortUrlEdition::create();

        if (isset($data['long_url'])) {
            $edition = $edition->withLongUrl($data['long_url']);
        }

        if (array_key_exists('tags', $data)) {
            $edition = $data['tags'] ? $edition->withTags(...$data['tags']) : $edition->withoutTags();
        }

        $edition = $this->applyOptions($edition, $data);

        // Handle removals for nullable fields
        if (array_key_exists('title', $data) && $data['title'] === null) {
            $edition = $edition->removingTitle();
        }

        if (array_key_exists('valid_since', $data) && $data['valid_since'] === null) {
            $edition = $edition->removingValidSince();
        }

        if (array_key_exists('valid_until', $data) && $data['valid_until'] === null) {
            $edition = $edition->removingValidUntil();
        }

        if (array_key_exists('max_visits', $data) && $data['max_visits'] === null) {
            $edition = $edition->removingMaxVisits();
        }

        $this->sdk->editShortUrl($identifier, $edition);
    }

    public function getShortUrl(string $shortCode): array
    {
        $identifier = ShortUrlIdentifier::fromShortCode($shortCode);
        $url = $this->sdk->getShortUrl($identifier);

        return $this->serializeShortUrl($url);
    }

    public function getVisits(string $shortCode, int $limit = 50): array
    {
        $identifier = ShortUrlIdentifier::fromShortCode($shortCode);
        $filter = VisitsFilter::create();

        $visits = [];
        foreach ($this->sdk->listShortUrlVisitsWithFilter($identifier, $filter) as $visit) {
            $location = $visit->location();
            $visits[] = [
                'date' => $visit->date()->format('Y-m-d H:i'),
                'referer' => $visit->referer(),
                'potentialBot' => $visit->potentialBot(),
                'location' => $location ? [
                    'countryName' => $location->countryName,
                ] : null,
            ];
            if (count($visits) >= $limit) {
                break;
            }
        }

        return $visits;
    }

    public function getVisitsOverview(): array
    {
        $overview = $this->sdk->getVisitsOverview();

        return [
            'totalVisits' => $overview->nonOrphanVisits->total,
            'totalBots' => $overview->nonOrphanVisits->bots,
            'orphanVisits' => $overview->orphanVisits->total,
        ];
    }

    public function listTags(): array
    {
        return $this->sdk->listTags();
    }

    public function deleteShortUrl(string $shortCode): void
    {
        $identifier = ShortUrlIdentifier::fromShortCode($shortCode);
        $this->sdk->deleteShortUrl($identifier);
    }

    private function applyOptions(ShortUrlCreation|ShortUrlEdition $builder, array $options): ShortUrlCreation|ShortUrlEdition
    {
        if (! empty($options['title'])) {
            $builder = $builder->withTitle($options['title']);
        }

        if (! empty($options['valid_since'])) {
            $builder = $builder->validSince(new DateTimeImmutable($options['valid_since']));
        }

        if (! empty($options['valid_until'])) {
            $builder = $builder->validUntil(new DateTimeImmutable($options['valid_until']));
        }

        if (! empty($options['max_visits'])) {
            $builder = $builder->withMaxVisits((int) $options['max_visits']);
        }

        if (isset($options['crawlable']) && $options['crawlable']) {
            $builder = $builder->crawlable();
        }

        if (isset($options['forward_query']) && ! $options['forward_query']) {
            $builder = $builder->withoutQueryForwardingOnRedirect();
        }

        return $builder;
    }

    private function serializeShortUrl(object $url): array
    {
        return [
            'shortCode' => $url->shortCode,
            'shortUrl' => $this->baseUrl.$url->shortCode,
            'longUrl' => $url->longUrl,
            'title' => $url->title,
            'tags' => $url->tags,
            'crawlable' => $url->crawlable,
            'forwardQuery' => $url->forwardQuery,
            'validSince' => $url->meta->validSince?->format('Y-m-d'),
            'validUntil' => $url->meta->validUntil?->format('Y-m-d'),
            'maxVisits' => $url->meta->maxVisits,
            'visitsSummary' => [
                'total' => $url->visitsSummary?->total ?? 0,
            ],
            'dateCreated' => $url->dateCreated?->format('Y-m-d H:i'),
        ];
    }
}
