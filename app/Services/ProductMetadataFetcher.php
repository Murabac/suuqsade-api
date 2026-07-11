<?php

namespace App\Services;

use App\Support\ProductLinkNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProductMetadataFetcher
{
    public function detectPlatform(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if (str_contains($host, 'shein.com')) {
            return 'shein';
        }

        if (preg_match('/(^|\.)amazon\./', $host)) {
            return 'amazon';
        }

        return 'unknown';
    }

    /**
     * @return array{platform: string, title: ?string, description: ?string, images: list<string>}
     */
    public function fetch(string $url): array
    {
        $url = ProductLinkNormalizer::normalize($url);
        $html = $this->fetchHtml($url);

        if ($html === null) {
            return $this->emptyResult($url);
        }

        $title = $this->metaContent($html, 'og:title')
            ?? $this->metaContent($html, 'twitter:title')
            ?? $this->titleTag($html);

        $description = $this->metaContent($html, 'og:description')
            ?? $this->metaContent($html, 'twitter:description');

        $images = $this->collectImages($html);

        return [
            'platform' => $this->detectPlatform($url),
            'title' => $title ? Str::limit(html_entity_decode($title, ENT_QUOTES), 500) : null,
            'description' => $description ? Str::limit(html_entity_decode($description, ENT_QUOTES), 2000) : null,
            'images' => array_values(array_unique(array_slice($images, 0, 12))),
        ];
    }

    /**
     * @return array{platform: string, title: null, description: null, images: array{}}
     */
    private function emptyResult(string $url): array
    {
        return [
            'platform' => $this->detectPlatform($url),
            'title' => null,
            'description' => null,
            'images' => [],
        ];
    }

    private function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::withOptions([
                'verify' => config('services.product_fetch.verify_ssl', false),
                'allow_redirects' => true,
                'timeout' => 20,
            ])->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->get($url);

            if (! $response->successful()) {
                return null;
            }

            return $response->body();
        } catch (\Throwable) {
            return null;
        }
    }

    private function metaContent(string $html, string $property): ?string
    {
        $patterns = [
            '/<meta[^>]+(?:property|name)=["\']'.preg_quote($property, '/').'["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property|name)=["\']'.preg_quote($property, '/').'["\']/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $match)) {
                return trim($match[1]);
            }
        }

        return null;
    }

    private function titleTag(string $html): ?string
    {
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $match)) {
            return trim($match[1]);
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function collectImages(string $html): array
    {
        $images = [];

        foreach ($this->jsonLdBlocks($html) as $block) {
            $decoded = json_decode($block, true);

            if (! is_array($decoded)) {
                continue;
            }

            $images = array_merge($images, $this->imagesFromJsonLd($decoded));
        }

        preg_match_all(
            '/<meta[^>]+(?:property|name)=["\'](?:og:image|twitter:image)["\'][^>]+content=["\']([^"\']+)["\']/i',
            $html,
            $ogMatches,
        );

        foreach ($ogMatches[1] ?? [] as $image) {
            $images[] = $this->normalizeImageUrl($image);
        }

        return array_values(array_filter(array_unique($images)));
    }

    /**
     * @return list<string>
     */
    private function jsonLdBlocks(string $html): array
    {
        preg_match_all(
            '/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is',
            $html,
            $matches,
        );

        return $matches[1] ?? [];
    }

    /**
     * @return list<string>
     */
    private function imagesFromJsonLd(array $data): array
    {
        $images = [];

        if (isset($data['@graph']) && is_array($data['@graph'])) {
            foreach ($data['@graph'] as $node) {
                if (is_array($node)) {
                    $images = array_merge($images, $this->imagesFromJsonLd($node));
                }
            }
        }

        $type = $data['@type'] ?? null;
        $isProduct = $type === 'Product'
            || (is_array($type) && in_array('Product', $type, true));

        if ($isProduct && isset($data['image'])) {
            $images = array_merge($images, $this->normalizeImageList($data['image']));
        }

        return $images;
    }

    /**
     * @return list<string>
     */
    private function normalizeImageList(mixed $image): array
    {
        if (is_string($image)) {
            return [$this->normalizeImageUrl($image)];
        }

        if (! is_array($image)) {
            return [];
        }

        $urls = [];

        foreach ($image as $item) {
            if (is_string($item)) {
                $urls[] = $this->normalizeImageUrl($item);
            } elseif (is_array($item) && isset($item['url'])) {
                $urls[] = $this->normalizeImageUrl($item['url']);
            }
        }

        return $urls;
    }

    private function normalizeImageUrl(string $url): string
    {
        $url = html_entity_decode(trim($url), ENT_QUOTES);

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        return $url;
    }
}
