<?php
declare(strict_types=1);

namespace Test;

use Compass\DefaultPathStrategy;
use PHPUnit\Framework\TestCase;

class DefaultPathStrategyTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testUpdatePath(array $items, ?string $suffix, $expected)
    {
        $strategy = new DefaultPathStrategy();
        $path = $strategy->updatePath($items, '/', $suffix);
        self::assertEquals($expected, $path);
    }

    public function getData(): array
    {
        return [
            [[], null, ''],
            [[], '.json', ''],
            [['', 'path', 'to', 'resource'], null, '/path/to/resource'],
            [['', 'path', 'to', 'resource'], '.json', '/path/to/resource.json'],
            [['path', 'to', 'resource'], '.json', 'path/to/resource.json'],
            [['path/to/resource'], '.json', 'path/to/resource.json'],
            [['path'], '.json', 'path.json'],
        ];
    }
}
