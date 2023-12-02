<?php
declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use Compass\Exception\InvalidPathException;
use Compass\Path;

class PathTest extends TestCase
{

    public function testGet()
    {
        $path = new Path('/src/Scanner/Driver/File/');
        self::assertEquals($path->get(0), '');
        self::assertEquals($path->get(1), 'src');
        self::assertEquals($path->get(2), 'Scanner');
        self::assertEquals($path->get(3), 'Driver');
        self::assertEquals($path->get(4), 'File');
        self::assertCount(5, $path->getAll());
    }

    public function testGetAssertion1()
    {
        $this->expectException(InvalidPathException::class);
        $path = new Path('/src/Scanner/Driver/File/');
        $path->get(5);
    }

    public function testGetAssertionNegative()
    {
        $this->expectException(InvalidPathException::class);
        $path = new Path('/src/Scanner/Driver/File/');
        $path->get(-1);
    }

    /**
     * @dataProvider getCasesGet
     */
    public function testGetLast($source, $withSuffix, $expect)
    {
        $path = new Path($source);
        self::assertEquals($expect, $path->getLast($withSuffix));
    }

    /**
     * @return array
     */
    public function getCasesGet(): array
    {
        return [
            'standard' => ['/src/Scanner/Driver/File/index.php', true, 'index.php'],
            'dot' => ['/src/Scanner/Driver/File/.php', true, '.php'],
            'no dot' => ['/src/Scanner/Driver/File/name', true, 'name'],
            'empty' => ['/', true, null],
        ];
    }

    /**
     * @dataProvider getCasesGetWithSuffix
     */
    public function testGetLastWithSuffix($source, $withSuffix, $suffix, $expect)
    {
        $path = new Path($source);
        $path->setSuffix($suffix);
        self::assertEquals($expect, $path->getLast($withSuffix));
    }

    /**
     * @return array
     */
    public function getCasesGetWithSuffix(): array
    {
        return [
            'standard' => ['/src/Scanner/Driver/File/index.php', true, '.old', 'index.php.old'],
            'dot' => ['/src/Scanner/Driver/File/.php', true, '.old', '.php.old'],
            'no dot' => ['/src/Scanner/Driver/File/name', true, '.old', 'name.old'],
            'empty' => ['/', true, '.old', null],
        ];
    }

    public function testReplaceAll()
    {
        $path = new Path('/s__NAME____NAME__rc/__NAME__Scanner/Driver__NAME__/Fi__NAME2__le/');

        $path->replaceAll([
            '__NAME__' => 'User',
            '__NAME2__' => 'User2',
        ]);
        $path->updateSource();
        self::assertEquals('/sUserUserrc/UserScanner/DriverUser/FiUser2le', $path->getSource());
        self::assertEquals('UserScanner', $path->get(2));

        $path = new Path('\\s__NAME____NAME__rc\\__NAME__Scanner\\Driver__NAME__\\Fi__NAME2__le\\', '\\');

        $path->replaceAll([
            '__NAME__' => 'User',
            '__NAME2__' => 'User2',
        ]);
        $path->updateSource();
        self::assertEquals('\\sUserUserrc\\UserScanner\\DriverUser\\FiUser2le', $path->getSource());
        self::assertEquals('UserScanner', $path->get(2));
    }

    /**
     * @dataProvider getCasesDirname
     */
    public function testDirname($source, $expect)
    {
        $path = new Path($source);
        self::assertEquals($expect, $path->dirname());
    }

    /**
     * @return array
     */
    public function getCasesDirname(): array
    {
        return [
            'standard' => ['/src/Scanner/Driver/File/index.php', '/src/Scanner/Driver/File'],
            'dot' => ['/src/Scanner/Driver/.File2/.php', '/src/Scanner/Driver/.File2'],
            'no dot' => ['/src/Scanner/Driver/.File/name', '/src/Scanner/Driver/.File'],
        ];
    }

    /**
     * @dataProvider getCasesSetBy
     * @param $source
     * @param $currentValue
     * @param $newValue
     * @param $separator
     * @param $expect
     */
    public function testSetBy($source, $currentValue, $newValue, $separator, $expect)
    {
        $path = new Path($source, $separator);
        $path->setBy($currentValue, $newValue);
        $path->updateSource();
        self::assertEquals($expect, $path->getSource());
    }

    /**
     * @return array
     */
    public function getCasesSetBy(): array
    {
        return [
            ['src/Scanner/Driver/File/index.php', 'src', 'test', '/', 'test/Scanner/Driver/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 'src', 'test', '/', '/test/Scanner/Driver/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 'Scanner', 'test', '/', '/src/test/Driver/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 'Driver', 'test', '/', '/src/Scanner/test/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 'File', 'test', '/', '/src/Scanner/Driver/test/index.php'],
            ['/src/Scanner/Driver/File/index.php', 'index.php', 'test', '/', '/src/Scanner/Driver/File/test'],
            ['c:\\src\\Scanner\\Driver\\File\\index.php', 'index.php', 'test', '\\', 'c:\\src\\Scanner\\Driver\\File\\test'],
            ['c:\\src\\Scanner\\Driver\\File\\index.php', 'Driver', 'test', '\\', 'c:\\src\\Scanner\\test\\File\\index.php'],
        ];
    }

    public function testSetByAssertion()
    {
        $this->expectException(InvalidPathException::class);
        $path = new Path('/src/Scanner/Driver/File/index.php');
        $path->setBy('bad', 'newValue');
    }

    /**
     * @dataProvider getCasesSetAll
     */
    public function testSetAll($source, $newValue, $separator, $expect)
    {
        $path = new Path($source, $separator);
        $path->setAll($newValue);
        $path->updateSource();
        self::assertEquals($expect, $path->getSource());
    }

    public function getCasesSetAll(): array
    {
        return [
            ['/src/Scanner/Driver/File/index.php', ['c:', 'method', 'getSeparator',], '/', 'c:/method/getSeparator'],
            ['/src/Scanner/Driver/File/index.php', ['', 'index.php', 'File',], '/', '/index.php/File'],
            ['src\\Scanner\\Driver\\File\\index.php', ['c:', 'method', 'getSeparator',], '\\', 'c:\method\getSeparator'],
        ];
    }

    /**
     * @dataProvider getCasesSet
     */
    public function testSet($source, $index, $newValue, $separator, $expect)
    {
        $path = new Path($source, $separator);
        $path->set($index, $newValue);
        $path->updateSource();
        self::assertEquals($expect, $path->getSource());
    }

    public function getCasesSet(): array
    {
        return [
            ['src/Scanner/Driver/File/index.php', 0, 'test', '/', 'test/Scanner/Driver/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 0, 'test', '/', 'test/src/Scanner/Driver/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 1, 'test', '/', '/test/Scanner/Driver/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 2, 'test', '/', '/src/test/Driver/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 3, 'test', '/', '/src/Scanner/test/File/index.php'],
            ['/src/Scanner/Driver/File/index.php', 4, 'test', '/', '/src/Scanner/Driver/test/index.php'],
            ['/src/Scanner/Driver/File/index.php', 5, 'test', '/', '/src/Scanner/Driver/File/test'],
            ['\\src\\Scanner\\Driver\\File\\index.php', 0, 'test', '\\', 'test\\src\\Scanner\\Driver\\File\\index.php'],
            ['\\src\\Scanner\\Driver\\File\\index.php', 1, 'test', '\\', '\\test\\Scanner\\Driver\\File\\index.php'],
            ['\\src\\Scanner\\Driver\\File\\index.php', 2, 'test', '\\', '\\src\\test\\Driver\\File\\index.php'],
            ['\\src\\Scanner\\Driver\\File\\index.php', 3, 'test', '\\', '\\src\\Scanner\\test\\File\\index.php'],
            ['\\src\\Scanner\\Driver\\File\\index.php', 4, 'test', '\\', '\\src\\Scanner\\Driver\\test\\index.php'],
            ['\\src\\Scanner\\Driver\\File\\index.php', 5, 'test', '\\', '\\src\\Scanner\\Driver\\File\\test'],
            ['src\\Scanner\\Driver\\File\\index.php', 4, 'test', '\\', 'src\\Scanner\\Driver\\File\\test'],
        ];
    }

    /**
     * @dataProvider getCasesSetAssertion
     */
    public function testSetAssertion($source, $index)
    {
        $this->expectException(InvalidPathException::class);
        $path = new Path($source);
        $path->set($index, 'test');
    }

    public function getCasesSetAssertion(): array
    {
        return [
            ['/src/Scanner/Driver/File/index.php', -1],
            ['/src/Scanner/Driver/File/index.php', -2],
            ['/src/Scanner/Driver/File/index.php', 6],
            ['/src/Scanner/Driver/File/index.php', 7],
        ];
    }

    public function test__toString()
    {
        self::assertEquals('/src/Scanner/Driver/File/index.php',
            new Path('/src/Scanner/Driver/File/index.php'));
    }

    public function testReset()
    {
        $path = new Path('/src/Scanner/Driver/File/index.php');
        $path->reset();

        self::assertEmpty($path->getSource());
        self::assertEmpty($path->getAll());
    }

}
