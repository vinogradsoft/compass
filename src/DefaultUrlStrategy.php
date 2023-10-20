<?php
declare(strict_types=1);

namespace Compass;

class DefaultUrlStrategy implements UrlStrategy
{

    /**
     * @inheritDoc
     */
    public function updateAuthority(array $items, bool $idn): string
    {
        if (empty($items[Url::HOST])) {
            return '';
        }
        $usrPass = '';
        if (!empty($items[Url::USER]) && !empty($items[Url::PASSWORD])) {
            $usrPass = rawurlencode($items[Url::USER]) . ':' . rawurlencode($items[Url::PASSWORD]) . '@';
        } elseif (!empty($items[Url::USER]) && empty($items[Url::PASSWORD])) {
            $usrPass = rawurlencode($items[Url::USER]) . '@';
        }

        $result = $usrPass;
        $result .= $idn ? $this->idnToAscii($items[Url::HOST]) : $items[Url::HOST];
        $result .= !empty($items[Url::PORT]) ? ':' . $items[Url::PORT] : '';
        return $result;
    }

    /**
     * @param string $host
     * @return string
     */
    protected function idnToAscii(string $host): string
    {
        if (str_contains($host, '--')) {
            return $host;
        }
        return \idn_to_ascii($host) ?: $host;
    }

    /**
     * @inheritDoc
     */
    public function updateBaseUrl(array $items, string $authority, bool $idn): string
    {
        if (empty($authority)) {
            return '';
        }
        if (empty($items[Url::SCHEME])) {
            return '';
        }
        return $items[Url::SCHEME] . '://' . $authority;
    }

    /**
     * @inheritDoc
     */
    public function updateQuery(array $items): string
    {
        return http_build_query($items, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @inheritDoc
     */
    public function updatePath(array $items, string $separator, ?string $suffix = null): string
    {
        if (empty($items)) {
            return '';
        }
        return $suffix ? implode('/', $items) . $suffix : implode('/', $items);
    }

    /**
     * @inheritDoc
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
        $pathString = (string)$path;
        $queryString = (string)$query;
        $result = !empty($pathString) ? $pathString : '';
        $result .= !empty($queryString) ? '?' . $queryString : '';
        $result .= !empty($items[Url::FRAGMENT]) ? '#' . $items[Url::FRAGMENT] : '';
        return $result;
    }

    /**
     * @inheritDoc
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
        if (empty($baseUrl)) {
            return '';
        }
        return !empty($relativeUrl) ? $baseUrl . '/' . ltrim($relativeUrl, '/') : $baseUrl;
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
        //By default, you don't need to reset anything.
    }

}