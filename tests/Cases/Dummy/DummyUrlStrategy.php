<?php

namespace Test\Cases\Dummy;

use Compass\UrlStrategy;
use Compass\Path;
use Compass\Query;

class DummyUrlStrategy implements UrlStrategy
{

    /**
     * @param array $items
     * @param Path $path
     * @param Query $query
     * @param bool $updateAbsoluteUrl
     * @param bool $idn
     * @param string|null $suffix
     * @return string
     */
    public function updateRelativeUrl(
        array   $items,
        Path    $path,
        Query   $query,
        bool    $updateAbsoluteUrl,
        bool    $idn,
        ?string $suffix = null
    ): string
    {
        return '';
    }

    /**
     * @param array $items
     * @return string
     */
    public function updateQuery(array $items): string
    {
        return rawurldecode(http_build_query($items, "", "&", PHP_QUERY_RFC1738));
    }

    /**
     * @param array $items
     * @param string|null $suffix
     * @return string
     */
    public function updatePath(array $items, ?string $suffix = null): string
    {
        return implode('/', $items);
    }

    /**
     * @param array $items
     * @param bool $idn
     * @return string
     */
    public function updateAuthority(array $items, bool $idn = false): string
    {
        return '';
    }

    /**
     * @param array $items
     * @param string $authority
     * @param bool $idn
     * @return string
     */
    public function updateBaseUrl(array $items, string $authority, bool $idn = false): string
    {
        return '';
    }

    /**
     * @param array $items
     * @param string $relativeUrl
     * @param string $baseUrl
     * @param Path $path
     * @param Query $query
     * @param bool $idn
     * @param string|null $suffix
     * @return string
     */
    public function updateAbsoluteUrl(
        array   $items,
        string  $relativeUrl,
        string  $baseUrl,
        Path    $path,
        Query   $query,
        bool    $idn,
        ?string $suffix = null
    ): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function forceUnlockMethod(
        bool    &$schemeState,
        int     &$authoritySate,
        int     &$relativeUrlState,
        array   $items,
        array   $pathItems,
        array   $queryItems,
        bool    $updateAbsoluteUrl,
        ?string $suffix = null
    ): void
    {

    }

}