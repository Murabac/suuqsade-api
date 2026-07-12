<?php

namespace App\Support;

class SupportedProductHost
{
    public static function isAllowed(string $url): bool
    {
        $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if ($host === '' || self::isPrivateOrLocalHost($host)) {
            return false;
        }

        return self::isSheinHost($host) || self::isAmazonHost($host);
    }

    public static function detectPlatform(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if (self::isSheinHost($host)) {
            return 'shein';
        }

        if (self::isAmazonHost($host)) {
            return 'amazon';
        }

        return 'unknown';
    }

    public static function isSheinHost(string $host): bool
    {
        return $host === 'shein.com' || str_ends_with($host, '.shein.com');
    }

    public static function isAmazonHost(string $host): bool
    {
        return (bool) preg_match(
            '/^(?:[a-z0-9-]+\.)*amazon\.(?:com|ae|co\.uk|de|fr|ca|it|es|nl|se|pl|sa|sg|in|jp|com\.au|com\.mx|com\.be|com\.tr)$/',
            $host,
        );
    }

    private static function isPrivateOrLocalHost(string $host): bool
    {
        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        return ! filter_var(
            $host,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        );
    }
}
