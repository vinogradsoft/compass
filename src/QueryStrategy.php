<?php

namespace Compass;

interface QueryStrategy
{

    /**
     * @param array $items
     * @return string
     */
    public function updateQuery(array $items): string;
}