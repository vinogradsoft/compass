<?php

namespace Test;

use Test\Cases\Dummy\DummyUrlStrategy;
use Compass\DefaultQueryStrategy;
use Compass\UrlStrategy;
use Compass\Query;
use PHPUnit\Framework\TestCase;

class UrlQueryTest extends TestCase
{

    public function testConstruct()
    {
        $query = new Query($q = 'key=value');
        self::assertEquals($q, $query->getSource());
        self::assertInstanceOf(DefaultQueryStrategy::class, $query->getStrategy());

        $query = new Query($q, $strategy = new DummyUrlStrategy());
        self::assertSame($strategy, $query->getStrategy());
    }

    public function testGetSeparator()
    {
        $query = new Query('');
        self::assertEquals('&', $query->getSeparator());
    }

    /**
     * @dataProvider getData
     */
    public function testSetParam(string $source, $key, $newValue, $expected)
    {
        $updateStrategy = new DummyUrlStrategy();
        $query = new Query($source, $updateStrategy);
        $return = $query->setParam($key, $newValue);

        $query->updateSource();
        self::assertSame($return, $query);
        self::assertEquals($expected, $query->getSource());
    }

    public function getData()
    {
        return [
            ['key=value&key2=value2', 'key2', 'new value', 'key=value&key2=new+value'],
            ['key=value&key2=value2', 'key2', new class() {
                public string $r = 'new value';
            }, 'key=value&key2[r]=new+value'],
            ['key=value&key2=value2', 'key', 'new value1', 'key=new+value1&key2=value2'],
            ['name=param&name2=para2m&n[]=f&n[]=f2&n[]=f3', 'n', 'new value1', 'name=param&name2=para2m&n=new+value1'],
        ];
    }

    public function testReset()
    {
        $updateStrategy = new DummyUrlStrategy();
        $query = new Query('key=value&key2=value2', $updateStrategy);
        $query->reset();

        self::assertEmpty($query->getSource());
        self::assertEmpty($query->getAll());
    }

    public function testSetUpdateStrategy()
    {
        $updateStrategy = $this->getMockForAbstractClass(UrlStrategy::class);
        $updateStrategy2 = $this->getMockForAbstractClass(UrlStrategy::class);
        $urlQuery = new Query('', $updateStrategy);
        $reflection = new \ReflectionObject($urlQuery);
        $property = $reflection->getProperty('strategy');
        $property->setAccessible(true);
        $objectValue = $property->getValue($urlQuery);
        $urlQuery->setStrategy($updateStrategy2);

        $property = $reflection->getProperty('strategy');
        $property->setAccessible(true);
        $objectValue2 = $property->getValue($urlQuery);
        self::assertNotSame($objectValue, $objectValue2);
    }

    public function testEqualsStrategy()
    {
        $updateStrategy = $this->getMockForAbstractClass(UrlStrategy::class);
        $updateStrategy2 = $this->getMockForAbstractClass(UrlStrategy::class);
        $urlQuery = new Query('', $updateStrategy);
        self::assertFalse($urlQuery->equalsStrategy($updateStrategy2));
        self::assertTrue($urlQuery->equalsStrategy($updateStrategy));
    }

    /**
     * @dataProvider getDataByGetValueByName
     */
    public function testGetValueByName(string $source, string $key, $expected)
    {
        $updateStrategy = new DummyUrlStrategy();
        $query = new Query($source, $updateStrategy);
        $value = $query->getValueByName($key);
        self::assertEquals($expected, $value);
    }

    public function testGetValueByNameNotExists()
    {
        $updateStrategy = new DummyUrlStrategy();
        $query = new Query('key=value&key2=value2', $updateStrategy);
        $value = $query->getValueByName('no_key');
        self::assertEmpty($value);
    }

    public function getDataByGetValueByName()
    {
        return [
            ['key=value&key2=value2', 'key', 'value'],
            ['key=value&key2=value2', 'key2', 'value2'],
            ['name=param&name2=para2m&n[]=f&n[]=f2&n[]=f3', 'n', ['f', 'f2', 'f3']],
            ['name=param&name2=para2m&n[]=f&n[]=f2&n[]=f3', 'name', 'param'],
        ];
    }

    public function testUpdateSource()
    {
        $updateStrategy = new DummyUrlStrategy();
        $urlQuery = new Query('name=param', $updateStrategy);
        self::assertEquals('name=param', (string)$urlQuery);
        $urlQuery->updateSource();
        self::assertEquals('name=param', (string)$urlQuery);
    }

    public function testSetSource()
    {
        $updateStrategy = new DummyUrlStrategy();
        $urlQuery = new Query('name=param&name2=para2m&n[]=f&n[]=f2&n[]=f3', $updateStrategy);
        self::assertEquals('name=param&name2=para2m&n[0]=f&n[1]=f2&n[2]=f3', (string)$urlQuery);
        $urlQuery->setSource('name=param');
        self::assertEquals('name=param', (string)$urlQuery);
        $urlQuery->setSource('name');
        self::assertEquals('name=', (string)$urlQuery);
        $urlQuery->setSource('');
        self::assertEmpty((string)$urlQuery);
    }

    public function testCreateBlank()
    {
        $urlQuery = Query::createBlank();
        $urlQuery2 = Query::createBlank();
        self::assertNotSame($urlQuery, $urlQuery2);
        self::assertInstanceOf(DefaultQueryStrategy::class, $urlQuery->getStrategy());
        self::assertEmpty($urlQuery->getSource());
        self::assertEmpty($urlQuery->getAll());

        $urlQuery2 = Query::createBlank($updateStrategy = new DummyUrlStrategy());
        self::assertSame($updateStrategy, $urlQuery2->getStrategy());
        self::assertEmpty($urlQuery2->getSource());
        self::assertEmpty($urlQuery2->getAll());
    }
}
