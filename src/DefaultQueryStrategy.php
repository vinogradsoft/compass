<?php
declare(strict_types=1);

namespace Compass;

class DefaultQueryStrategy implements QueryStrategy
{
    /**
     * @inheritDoc
     */
    public function updateQuery(array $items): string
    {
        return http_build_query($items, '', '&', PHP_QUERY_RFC3986);
    }
}