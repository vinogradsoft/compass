<?php
declare(strict_types=1);

namespace Test;

use Compass\DefaultQueryStrategy;
use PHPUnit\Framework\TestCase;

class DefaultUrlQueryStrategyTest extends TestCase
{
    public function testUpdateQuery()
    {
        $strategy = new DefaultQueryStrategy();
        $result = $strategy->updateQuery([]);
        self::assertEquals('', $result);
        $result = $strategy->updateQuery(['key' => 'value']);
        self::assertEquals('key=value', $result);
    }
}
