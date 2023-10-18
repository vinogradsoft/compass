<?php

namespace Test;

use Compass\DefaultUrlStrategy;
use PHPUnit\Framework\TestCase;
use Compass\Url;
use Compass\Path;
use Compass\Query;

class DefaultUrlStrategyTest extends TestCase
{
    /**
     * @dataProvider getItems
     */
    public function testUpdateAuthority(array $items, string $expected, bool $idn)
    {
        $updateStrategy = new DefaultUrlStrategy();
        $result = $updateStrategy->updateAuthority($items, $idn);

        self::assertEquals($expected, $result);
    }

    public function getItems(): array
    {
        return [
            [[], '', false],
            [[Url::USER => 'user', Url::PASSWORD => 'password', Url::HOST => 'host.ru', Url::PORT => '8080'],
                'user:password@host.ru:8080', false],
            [[Url::USER => 'user', Url::PASSWORD => 'password', Url::HOST => 'host.ru'],
                'user:password@host.ru', false],
            [[Url::USER => 'user', Url::HOST => 'host.ru'],
                'user@host.ru', false],
            [[Url::PASSWORD => 'password', Url::HOST => 'host.ru', Url::PORT => '8080'],
                'host.ru:8080', false],
            [[Url::HOST => 'host.ru', Url::PORT => '8080'],
                'host.ru:8080', false],
            [[Url::HOST => 'host.ru'],
                'host.ru', false],

            [[Url::USER => 'user', Url::PASSWORD => 'password', Url::HOST => 'привет.рф', Url::PORT => '8080'],
                'user:password@xn--b1agh1afp.xn--p1ai:8080', true],
            [[Url::USER => 'user', Url::PASSWORD => 'password', Url::HOST => 'привет.рф'],
                'user:password@xn--b1agh1afp.xn--p1ai', true],
            [[Url::USER => 'user', Url::HOST => 'привет.рф'],
                'user@xn--b1agh1afp.xn--p1ai', true],
            [[Url::PASSWORD => 'password', Url::HOST => 'привет.рф', Url::PORT => '8080'],
                'xn--b1agh1afp.xn--p1ai:8080', true],
            [[Url::HOST => 'привет.рф', Url::PORT => '8080'],
                'xn--b1agh1afp.xn--p1ai:8080', true],
            [[Url::HOST => 'привет.рф'],
                'xn--b1agh1afp.xn--p1ai', true],

            [[Url::USER => 'user', Url::PASSWORD => 'password', Url::HOST => 'привет.рф', Url::PORT => '8080'],
                'user:password@привет.рф:8080', false],
            [[Url::USER => 'user', Url::PASSWORD => 'password', Url::HOST => 'привет.рф'],
                'user:password@привет.рф', false],
            [[Url::USER => 'user', Url::HOST => 'привет.рф'],
                'user@привет.рф', false],
            [[Url::PASSWORD => 'password', Url::HOST => 'привет.рф', Url::PORT => '8080'],
                'привет.рф:8080', false],
            [[Url::HOST => 'привет.рф', Url::PORT => '8080'],
                'привет.рф:8080', false],
            [[Url::HOST => 'привет.рф'],
                'привет.рф', false],

            [[Url::HOST => 'xn--b1agh1afp.xn--p1ai'],
                'xn--b1agh1afp.xn--p1ai', true],

            [[Url::USER => 'user', Url::PASSWORD => 'password', Url::PORT => '8080'],
                '', false],
            [[Url::USER => 'user', Url::PASSWORD => 'password'],
                '', false],
            [[Url::USER => 'user',],
                '', false],
            [[Url::PORT => '8080'],
                '', false],
            [[Url::PASSWORD => 'password', Url::PORT => '8080'],
                '', false],
        ];
    }

    /**
     * @dataProvider getDataBaseUrl
     */
    public function testUpdateBaseUrl(array $items, string $authority, string $expected)
    {
        $result = (new DefaultUrlStrategy())->updateBaseUrl($items, $authority, true);
        self::assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public function getDataBaseUrl(): array
    {
        return [
            [[], '', ''],
            [[Url::SCHEME => 'http'], '', ''],
            [[], 'host.ru', ''],
            [[Url::SCHEME => 'http'], 'host.ru', 'http://host.ru'],
            [[Url::SCHEME => 'http'], 'user:password@host.ru:8080', 'http://user:password@host.ru:8080'],
        ];
    }

    /**
     * @dataProvider getDataRelativeUrl
     */
    public function testUpdateRelativeUrl(array $items, string $pathString, string $queryString, string $expected)
    {
        $updateStrategy = new DefaultUrlStrategy();
        $path = Path::createBlank('/', $updateStrategy);
        if (!empty($pathString)) {
            $path->setSource($pathString);
        }
        $query = Query::createBlank($updateStrategy);
        if (!empty($queryString)) {
            $query->setSource($queryString);
        }

        $result = $updateStrategy->updateRelativeUrl($items, $path, $query, true, true, '');

        self::assertEquals($expected, $result);
    }

    public function getDataRelativeUrl(): array
    {
        return [
            [[], '', '', ''],
            [[Url::FRAGMENT => 'fragment'], '', '', '#fragment'],
            [[Url::FRAGMENT => 'fragment'], '/', '', '#fragment'],
            [[Url::FRAGMENT => 'fragment'], '/path/to/resource', 'key=value&key2=value2', '/path/to/resource?key=value&key2=value2#fragment'],
            [[Url::FRAGMENT => 'fragment'], '/path/to/resource', '', '/path/to/resource#fragment'],
        ];
    }

    /**
     * @dataProvider getDataAbsoluteUrl
     */
    public function testUpdateAbsoluteUrl(string $baseUrl, string $relativeUrl, string $expected)
    {
        $updateStrategy = new DefaultUrlStrategy();

        $path = Path::createBlank('/', $updateStrategy);
        $query = Query::createBlank($updateStrategy);

        $result = $updateStrategy->updateAbsoluteUrl(
            [],
            $relativeUrl,
            $baseUrl,
            $path,
            $query,
            false
        );
        self::assertEquals($expected, $result);
    }

    public function getDataAbsoluteUrl(): array
    {
        return [
            ['', '', ''],
            ['http://host.ru', 'path/to/resource', 'http://host.ru/path/to/resource'],
            ['', 'path/to/resource', ''],
            ['http://host.ru', '', 'http://host.ru'],
            ['http://host.ru', '/', 'http://host.ru/'],
        ];
    }

    /**
     * @dataProvider getDataUpdatePath
     */
    public function testUpdatePath(array $items, ?string $suffix, string $expected)
    {
        $updateStrategy = new DefaultUrlStrategy();
        $result = $updateStrategy->updatePath($items, '/', $suffix);
        self::assertEquals($expected, $result);
    }

    public function getDataUpdatePath(): array
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

    public function testUpdateQuery()
    {
        $updateStrategy = new DefaultUrlStrategy();
        $result = $updateStrategy->updateQuery(['name' => [1, 2, 3], 'vl' => 'test']);
        self::assertEquals('name%5B0%5D=1&name%5B1%5D=2&name%5B2%5D=3&vl=test', $result);
    }
}
