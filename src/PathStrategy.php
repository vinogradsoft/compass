<?php
declare(strict_types=1);

namespace Compass;

interface PathStrategy
{

    /**
     * @param array $items
     * @param string $separator
     * @param string|null $suffix
     * @return string
     */
    public function updatePath(array $items, string $separator, ?string $suffix = null): string;
}