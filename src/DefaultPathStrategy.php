<?php

namespace Compass;

class DefaultPathStrategy implements PathStrategy
{
    /**
     * @inheritDoc
     */
    public function updatePath(array $items, ?string $suffix = null): string
    {
        if (empty($items)) {
            return '';
        }
        return $suffix ? implode('/', $items) . $suffix : implode('/', $items);
    }
}