<?php

namespace Compass;

class DefaultPathStrategy implements PathStrategy
{
    /**
     * @inheritDoc
     */
    public function updatePath(array $items, string $separator, ?string $suffix = null): string
    {
        if (empty($items)) {
            return '';
        }
        return $suffix ? implode($separator, $items) . $suffix : implode($separator, $items);
    }
}