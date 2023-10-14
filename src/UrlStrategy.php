<?php

namespace Compass;

interface UrlStrategy extends QueryStrategy, PathStrategy
{

    /**
     * @param bool $schemeState
     * @param int $authoritySate
     * @param int $relativeUrlState
     * @param array $items
     * @param array $pathItems
     * @param array $queryItems
     * @param bool $updateAbsoluteUrl
     * @param string|null $suffix
     * @return void
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
    ): void;

    /**
     * @param array $items
     * @param bool $idn
     * @return string
     */
    public function updateAuthority(array $items, bool $idn): string;

    /**
     * @param array $items
     * @param string $authority
     * @param bool $idn
     * @return string
     */
    public function updateBaseUrl(array $items, string $authority, bool $idn): string;

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
    ): string;

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
    ): string;

}