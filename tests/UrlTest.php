<?php
declare(strict_types=1);

namespace Test;

use Test\Cases\Dummy\DummyUrlStrategy;
use Test\Cases\Dummy\ReferralUrlStrategy;
use Compass\DefaultUrlStrategy;
use Compass\Exception\InvalidUrlException;
use Compass\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{

    private const USER_KEY = 1 << 0;
    private const PASSWORD_KEY = 1 << 1;
    private const HOST_KEY = 1 << 2;
    private const PORT_KEY = 1 << 3;

    private const PATH_KEY = 1 << 0;
    private const QUERY_KEY = 1 << 1;
    private const FRAGMENT_KEY = 1 << 2;

    private const AUTHORITY_WHOLE = self::USER_KEY | self::PASSWORD_KEY | self::HOST_KEY | self::PORT_KEY;
    private const RELATIVE_URL_WHOLE = self::PATH_KEY | self::QUERY_KEY | self::FRAGMENT_KEY;

    public function testConstruct()
    {
        $url = new Url('http://vinograd.soft');

        self::assertEquals('/', $url->getSeparator());
        self::assertFalse($url->isConversionIdnToAscii());
        $strategy = $url->getUpdateStrategy();
        self::assertInstanceOf(DefaultUrlStrategy::class, $strategy);

        $url = new Url('http://vinograd.soft', true, $dummy = new DummyUrlStrategy());
        self::assertTrue($url->isConversionIdnToAscii());
        $strategy = $url->getUpdateStrategy();
        self::assertSame($dummy, $strategy);
    }

    public function testCreateBlank()
    {
        $url = Url::createBlank();

        self::assertEquals('/', $url->getSeparator());
        self::assertFalse($url->isConversionIdnToAscii());
        $strategy = $url->getUpdateStrategy();
        self::assertInstanceOf(DefaultUrlStrategy::class, $strategy);
        self::assertEmpty($url->getAll());
        self::assertEmpty($url->getSource());

        $url = Url::createBlank(true, $dummy = new DummyUrlStrategy());

        self::assertTrue($url->isConversionIdnToAscii());
        $strategy = $url->getUpdateStrategy();
        self::assertSame($dummy, $strategy);
        self::assertEmpty($url->getAll());
        self::assertEmpty($url->getSource());
    }

    public function testSetConversionIdnToAscii()
    {
        $url = new Url('http://привет.рф', true);
        self::assertTrue($url->isConversionIdnToAscii());
        self::assertEquals('http://xn--b1agh1afp.xn--p1ai', $url->getBaseUrl());

        $url->setConversionIdnToAscii(false);
        self::assertFalse($url->isConversionIdnToAscii());
        self::assertEquals('http://xn--b1agh1afp.xn--p1ai', $url->getBaseUrl());
        $url->updateSource();
        self::assertEquals('http://привет.рф', $url->getBaseUrl());

        $url->setConversionIdnToAscii(true);
        self::assertTrue($url->isConversionIdnToAscii());
        self::assertEquals('http://привет.рф', $url->getBaseUrl());
        $url->updateSource();
        self::assertEquals('http://xn--b1agh1afp.xn--p1ai', $url->getBaseUrl());

        $url->setConversionIdnToAscii(false);
        self::assertFalse($url->isConversionIdnToAscii());
        self::assertEquals('http://xn--b1agh1afp.xn--p1ai', $url->getBaseUrl());
        $url->setConversionIdnToAscii(false);
        self::assertFalse($url->isConversionIdnToAscii());
        self::assertEquals('http://xn--b1agh1afp.xn--p1ai', $url->getBaseUrl());
        $url->updateSource();
        self::assertEquals('http://привет.рф', $url->getBaseUrl());
    }

    public function testSetUpdateStrategy()
    {
        $url = new Url('https://host.ru');
        $url->setUpdateStrategy($strategy = new DummyUrlStrategy());

        $path = $this->getValue($url, 'path');
        $urlQuery = $this->getValue($url, 'urlQuery');

        self::assertSame($strategy, $url->getUpdateStrategy());
        self::assertSame($strategy, $path->getStrategy());
        self::assertSame($strategy, $urlQuery->getStrategy());
        self::assertFalse($this->getValue($url, 'authoritySate') === self::AUTHORITY_WHOLE);
        self::assertFalse($this->getValue($url, 'relativeUrlState') === self::RELATIVE_URL_WHOLE);

        $url->updateSource();
        self::assertTrue($this->getValue($url, 'authoritySate') === self::AUTHORITY_WHOLE);
        self::assertTrue($this->getValue($url, 'relativeUrlState') === self::RELATIVE_URL_WHOLE);

        $url->setUpdateStrategy($strategy);
        self::assertTrue($this->getValue($url, 'authoritySate') === self::AUTHORITY_WHOLE);
        self::assertTrue($this->getValue($url, 'relativeUrlState') === self::RELATIVE_URL_WHOLE);

        self::assertSame($strategy, $url->getUpdateStrategy());
        self::assertSame($strategy, $path->getStrategy());
        self::assertSame($strategy, $urlQuery->getStrategy());

    }

    private function getValue($object, string $valueName)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($valueName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * @dataProvider getDataSetSource
     */
    public function testSetSource(
        $source,
        $scheme,
        $authority,
        $host,
        $user,
        $password,
        $port,
        $path,
        $query,
        $fragment,
        $absoluteUrl,
        $baseUrl,
        $relativeUrl
    )
    {
        $url = Url::createBlank();
        $url->setSource($source);

        self::assertEquals($scheme, $url->getScheme());
        self::assertEquals($authority, $url->getAuthority());
        self::assertEquals($host, $url->getHost());
        self::assertEquals($user, $url->getUser());
        self::assertEquals($password, $url->getPassword());
        self::assertEquals($port, $url->getPort());
        self::assertEquals($path, $url->getPath());
        self::assertEquals($query, $url->getQuery());
        self::assertEquals($fragment, $url->getFragment());
        self::assertEquals($absoluteUrl, $url->getSource());
        self::assertEquals($baseUrl, $url->getBaseUrl());
        self::assertEquals($relativeUrl, $url->getRelativeUrl());
    }

    public function getDataSetSource()
    {
        return $this->getData('setSource');
    }

    private function getData(string $name)
    {
        return include __DIR__ . '/data/' . $name . '.php';
    }

    /**
     * @dataProvider  getDataSetSourceInvalid
     */
    public function testSetSourceInvalid($source)
    {
        $this->expectException(InvalidUrlException::class);
        $url = Url::createBlank();
        $url->setSource($source);
    }

    public function getDataSetSourceInvalid()
    {
        return $this->getData('setSourceInvalid');
    }

    public function testReset()
    {
        $url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
        $url->reset();
        $path = $this->getValue($url, 'path');
        $urlQuery = $this->getValue($url, 'urlQuery');
        $items = $this->getValue($url, 'items');
        $baseUrl = $this->getValue($url, 'baseUrl');
        $relativeUrl = $this->getValue($url, 'relativeUrl');
        $authorityUrl = $this->getValue($url, 'authorityUrl');
        self::assertEmpty($path->getSource());
        self::assertEmpty($urlQuery->getSource());
        self::assertEmpty($items);
        self::assertEmpty($baseUrl);
        self::assertEmpty($relativeUrl);
        self::assertEmpty($authorityUrl);
    }

    public function testRepeatReset()
    {
        $url = new Url('http://vinograd.soft/path/to/resource');
        $url->setSuffix('.json');
        $url->updateSource();
        self::assertEquals('http://vinograd.soft/path/to/resource.json', $url->getSource());

        $url->reset();
        $url->setSource('http://vinograd.soft/path/to');
        $url->updateSource();
        self::assertEquals('http://vinograd.soft/path/to', $url->getSource());

        $url->reset();
        $url->setSource('ftp://vinograd.soft');
        $url->setArrayPath(['path', 'to']);
        $url->setScheme('https');
        $url->updateSource();
        self::assertEquals('https://vinograd.soft/path/to', $url->getSource());

        $url->reset();
        $url->setSource('http://vinograd.soft');
        $url->setArrayQuery(['path' => 'to']);
        $url->updateSource();
        self::assertEquals('http://vinograd.soft/?path=to', $url->getSource());

        $url->reset();
        $url->setSource('http://vinograd.ru');
        $url->setUser('grigor');
        $url->setPassword('password');
        $url->updateSource();
        self::assertEquals('http://grigor:password@vinograd.ru', $url->getSource());

        $url->reset();
        $url->setSource('http://vinograd.ru');
        $url->setPassword('password2');
        $url->updateSource();
        self::assertEquals('http://vinograd.ru', $url->getSource());

        $url->reset();
        $url->setSource('http://vinograd.ru');
        $url->setFragment('fragment');
        $url->updateSource();
        self::assertEquals('http://vinograd.ru/#fragment', $url->getSource());

        $url->reset();
        $url->setSource('http://vinograd.ru');
        $url->setPort('80');
        $url->updateSource();
        self::assertEquals('http://vinograd.ru:80', $url->getSource());
    }

    public function testGetHost()
    {
        $url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
        self::assertEquals('vinograd.soft', $url->getHost());
        $url = Url::createBlank();
        self::assertEmpty($url->getHost());
        $url->setHost('vinograd.soft');
        self::assertEquals('vinograd.soft', $url->getHost());
        $url->setHost('vinograd.ru');
        self::assertEquals('vinograd.ru', $url->getHost());
    }

    public function testGetPort()
    {
        $url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
        self::assertEquals('8080', $url->getPort());
        $url = Url::createBlank();
        self::assertEmpty($url->getPort());
        $url->setPort('80');
        self::assertEquals('80', $url->getPort());
        $url->setPort('9200');
        self::assertEquals('9200', $url->getPort());
    }

    public function testGetPath()
    {
        $url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
        self::assertEquals('/path/to/resource', $url->getPath());
        $url = Url::createBlank();
        self::assertEmpty($url->getPath());
        $url->setPath('path/to/resource');
        self::assertEquals('path/to/resource', $url->getPath());
        $url->setPath('/path/to/resource');
        self::assertEquals('/path/to/resource', $url->getPath());
        $url->setPath('new/path');
        self::assertEquals('new/path', $url->getPath());
    }

    public function testSetArrayPath()
    {
        $url = new Url('http://vinograd.soft');
        $url->setArrayPath(['', 'path', 'to', 'resource']);
        $url->updateSource();
        self::assertEquals('/path/to/resource', $url->getPath());
        $url->setArrayPath(['path', 'to', 'resource']);
        $url->updateSource();
        self::assertEquals('path/to/resource', $url->getPath());
        self::assertEquals('http://vinograd.soft/path/to/resource', $url->getSource());
    }

    /**
     * @dataProvider getDataGetAuthority
     */
    public function testGetAuthority(bool $idn, string $host, string $port, string $user, string $password, $expected)
    {
        $url = Url::createBlank($idn);
        $url->setScheme('http');  //require Scheme
        $url->setHost($host);            //require host
        $url->setPort($port);
        $url->setUser($user);
        $url->setPassword($password);
        $url->updateSource();
        self::assertEquals($expected, $url->getAuthority());
    }

    public function getDataGetAuthority()
    {
        return $this->getData('getAuthority');
    }

    /**
     * @dataProvider getDataGetAuthorityInvalid
     */
    public function testGetAuthorityInvalidWithMethod(string $port, string $user, string $password)
    {
        $this->expectException(InvalidUrlException::class);
        $url = Url::createBlank();
        $url->setScheme('http');  //require Scheme
        $url->setPort($port);
        $url->setUser($user);
        $url->setPassword($password);
        $url->updateSource();
    }

    public function getDataGetAuthorityInvalid()
    {
        return $this->getData('getAuthorityInvalid');
    }

    /**
     * @dataProvider getDataGetAuthorityInvalidSource
     */
    public function testGetAuthorityInvalidWithSource(string $source)
    {
        $this->expectException(InvalidUrlException::class);
        $url = Url::createBlank();
        $url->setSource($source);
    }

    public function getDataGetAuthorityInvalidSource()
    {
        return $this->getData('getAuthorityInvalidSource');
    }

    public function testSetQuery()
    {
        $url = Url::createBlank();
        $url->setQuery($query = 'key=value&key2=value2');
        self::assertEquals($query, $url->getQuery());
        $url->setQuery('');
        self::assertEmpty($url->getQuery());

        $url->setQuery($query);
        $url->setScheme('http')->setHost('host.ru');
        $url->updateSource();
        self::assertEquals('http://host.ru/?key=value&key2=value2', $url->getSource());
    }

    public function testSetArrayQuery()
    {
        $url = new Url('http://host.ru');
        $url->setArrayQuery([
            'key' => 'value',
            'key2' => 'value2',
            'name' => ['value', 'value2'],
        ]);
        self::assertEquals('', $url->getQuery());
        $url->updateSource();
        self::assertEquals('key=value&key2=value2&name%5B0%5D=value&name%5B1%5D=value2', $url->getQuery());
        self::assertEquals('http://host.ru/?key=value&key2=value2&name%5B0%5D=value&name%5B1%5D=value2', $url->getSource());
    }

    public function testSetFragment()
    {
        $url = new Url('http://host.ru');
        $url->setFragment('fragment');
        self::assertEquals('fragment', $url->getFragment());
        $url->updateSource();
        self::assertEquals('http://host.ru/#fragment', $url->getSource());

        $url->setFragment('fragment2');
        self::assertEquals('fragment2', $url->getFragment());
        $url->updateSource();
        self::assertEquals('http://host.ru/#fragment2', $url->getSource());
    }

    public function testSetPassword()
    {
        $url = new Url('http://user:password@host.ru:8080');
        self::assertEquals('password', $url->getPassword());
        $url->setPassword('');
        self::assertEmpty($url->getPassword());
        $url->updateSource();
        self::assertEquals('http://user@host.ru:8080', $url->getSource());
        $url->setPassword('pass');
        self::assertEquals('pass', $url->getPassword());
        $url->updateSource();
        self::assertEquals('http://user:pass@host.ru:8080', $url->getSource());
    }

    public function testGetUser()
    {
        $url = new Url('http://user@host.ru:8080');
        self::assertEquals('user', $url->getUser());
        $url->setUser('user2');
        self::assertEquals('user2', $url->getUser());
        $url->updateSource();
        self::assertEquals('http://user2@host.ru:8080', $url->getSource());
        $url->setUser('');
        self::assertEmpty($url->getUser());
        $url->updateSource();
        self::assertEquals('http://host.ru:8080', $url->getSource());
    }

    public function testGetScheme()
    {
        $url = new Url('http://host.ru');
        self::assertEquals('http', $url->getScheme());
        $url->setScheme('ftp');
        self::assertEquals('ftp', $url->getScheme());
        $url->updateSource();
        self::assertEquals('ftp://host.ru', $url->getSource());
    }

    public function testSetParameter()
    {
        $url = new Url('http://host.ru/?key=value');
        $url->setParameter('key2', 'value2');
        $url->updateSource();
        self::assertEquals('key=value&key2=value2', $url->getQuery());
        $url->setParameter('key3', ['a', 'b', 'c']);
        $url->updateSource();
        self::assertEquals('key=value&key2=value2&key3%5B0%5D=a&key3%5B1%5D=b&key3%5B2%5D=c', $url->getQuery());
        $url->setParameter('key3', 'value3');
        $url->updateSource();
        self::assertEquals('key=value&key2=value2&key3=value3', $url->getQuery());
        self::assertEquals('value', $url->getParameter('key'));
    }

    public function testGetRelativeUrl()
    {
        $url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
        self::assertEquals('/path/to/resource?query=value#fragment', $url->getRelativeUrl());
    }

    public function testGetBaseUrl()
    {
        $url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
        self::assertEquals('http://grigor:password@vinograd.soft:8080', $url->getBaseUrl());
    }

    public function testSetSuffix()
    {
        $url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
        self::assertEmpty($url->getSuffix());
        $url->setSuffix('.json');
        $url->updateSource();
        self::assertEquals('http://grigor:password@vinograd.soft:8080/path/to/resource.json?query=value#fragment',
            $url->getSource());

        $url->setSuffix('.json');
        $url->updateSource();
        self::assertEquals('http://grigor:password@vinograd.soft:8080/path/to/resource.json?query=value#fragment',
            $url->getSource());

        $url->updateSource(true, '.php');
        self::assertEquals('http://grigor:password@vinograd.soft:8080/path/to/resource.php?query=value#fragment',
            $url->getSource());
        self::assertEquals('.php', $url->getSuffix());
    }

    public function testUpdateSource()
    {
        $url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
        $url->setFragment('ffff');
        $url->updateSource();
        self::assertEquals('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#ffff',
            $url->getSource());
        $url->setFragment('DDDDD');
        $url->updateSource(false);
        self::assertEquals('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#ffff',
            $url->getSource());
        self::assertEquals('/path/to/resource?query=value#DDDDD',
            $url->getRelativeUrl());
        $url->setSource('http://grigor:password@vinograd.soft:8080');
        $url->setPath('path/to/resource');
        $url->updateSource(true, '.php');
        self::assertEquals('http://grigor:password@vinograd.soft:8080/path/to/resource.php',
            $url->getSource());
        self::assertEquals('.php', $url->getSuffix());
        $url->setPath('path/to/resource2');
        $url->updateSource();
        self::assertEquals('http://grigor:password@vinograd.soft:8080/path/to/resource2.php',
            $url->getSource());
    }

    /**
     * @dataProvider getDataInvalidUpdateSource
     */
    public function testInvalidUpdateSource(string $scheme, string $user, string $password, string $host, string $port)
    {
        $this->expectException(InvalidUrlException::class);
        $url = Url::createBlank();
        $url->setScheme($scheme);
        $url->setUser($user);
        $url->setPassword($password);
        $url->setHost($host);
        $url->setPort($port);
        $url->updateSource();
    }

    public function getDataInvalidUpdateSource()
    {
        return $this->getData('invalidUpdateSource');
    }

    public function testSetAll()
    {
        $url = Url::createBlank();
        $url->setAll([
            Url::HOST => 'host.ru',
            Url::SCHEME => 'http',
            Url::USER => 'user',
            Url::PASSWORD => 'password',
            Url::PORT => '80',
            Url::PATH => ['path', 'to', 'resource'],
            Url::QUERY => ['key' => 'value', 'key2' => 'value2'],
            Url::FRAGMENT => 'fragment',
            Url::SUFFIX => '.json',
        ]);
        $url->updateSource();
        self::assertEquals('http://user:password@host.ru:80/path/to/resource.json?key=value&key2=value2#fragment', $url->getSource());
        self::assertEquals('path/to/resource.json?key=value&key2=value2#fragment', $url->getRelativeUrl());
        self::assertEquals('http://user:password@host.ru:80', $url->getBaseUrl());
        self::assertEquals('user:password@host.ru:80', $url->getAuthority());
        self::assertEquals('80', $url->getPort());
        self::assertEquals('host.ru', $url->getHost());
        self::assertEquals('password', $url->getPassword());
        self::assertEquals('user', $url->getUser());
        self::assertEquals('http', $url->getScheme());
        self::assertEquals('.json', $url->getSuffix());

        $url->setAll([]);

        self::assertEmpty($url->getPort());
        self::assertEmpty($url->getHost());
        self::assertEmpty($url->getPassword());
        self::assertEmpty($url->getUser());
        self::assertEmpty($url->getScheme());
        self::assertEmpty($url->getSuffix());

        $url->setAll([
            Url::HOST => 'host.ru',
            Url::SCHEME => 'http',
        ]);
        $url->updateSource();
        self::assertEquals('http://host.ru', $url->getSource());
        self::assertEmpty($url->getRelativeUrl());
        self::assertEquals('http://host.ru', $url->getBaseUrl());
        self::assertEquals('host.ru', $url->getAuthority());
        self::assertEmpty($url->getPort());
        self::assertEquals('host.ru', $url->getHost());
        self::assertEmpty($url->getPassword());
        self::assertEmpty($url->getUser());
        self::assertEquals('http', $url->getScheme());
        self::assertEmpty($url->getSuffix());
    }

    /**
     * @return void
     */
    public function testBuildExternalWithStrategy()
    {
        $url = Url::createBlank();
        $url->setUpdateStrategy(new ReferralUrlStrategy());

        $url->setSource('https://another.site');
        $url->updateSource();
        self::assertEquals('https://another.site/?refid=222', $url->getSource());

        $url->setSource('https://another.site/path/to/resource');
        $url->updateSource();
        self::assertEquals('https://another.site/path/to/resource?refid=222', $url->getSource());
    }

    /**
     * @return void
     */
    public function testClearRelativeUrl()
    {
        $url = Url::createBlank();
        $url->setSource('https://another.site/path/to/resource');
        self::assertEquals('https://another.site/path/to/resource', $url->getSource());
        $url->clearRelativeUrl();
        $url->updateSource();
        self::assertEquals('https://another.site', $url->getSource());

        $url->setSource('https://another.site/path/to/resource?key=value');
        self::assertEquals('https://another.site/path/to/resource?key=value', $url->getSource());
        $url->clearRelativeUrl();
        $url->updateSource();
        self::assertEquals('https://another.site', $url->getSource());

        $url->setSource('https://another.site/?key=value');
        self::assertEquals('https://another.site/?key=value', $url->getSource());
        $url->clearRelativeUrl();
        $url->updateSource();
        self::assertEquals('https://another.site', $url->getSource());

        $url->setSource('https://another.site/#frag');
        self::assertEquals('https://another.site/#frag', $url->getSource());
        $url->clearRelativeUrl();
        $url->updateSource();
        self::assertEquals('https://another.site', $url->getSource());

        $url->setSource('https://another.site/path/to/resource?key=value#fragment');
        self::assertEquals('https://another.site/path/to/resource?key=value#fragment', $url->getSource());
        $url->clearRelativeUrl();
        $url->updateSource();
        self::assertEquals('https://another.site', $url->getSource());

        $url->setSource('https://россия.рф/path/to/resource?key=value#fragment');
        $url->setConversionIdnToAscii(true);
        $url->updateSource();
        self::assertEquals('https://xn--h1alffa9f.xn--p1ai/path/to/resource?key=value#fragment', $url->getSource());
        $url->clearRelativeUrl();
        $url->updateSource();
        self::assertEquals('https://xn--h1alffa9f.xn--p1ai', $url->getSource());

        $url->reset();
        $url->setAll([
            Url::HOST => 'россия.рф',
            Url::SCHEME => 'https',
            Url::USER => 'user',
            Url::PASSWORD => 'password',
            Url::PORT => '80',
            Url::PATH => ['path', 'to', 'resource'],
            Url::QUERY => ['key' => 'value', 'key2' => 'value2'],
            Url::FRAGMENT => 'fragment',
            Url::SUFFIX => '.json',
        ]);
        $url->setConversionIdnToAscii(true);
        $url->updateSource();
        self::assertEquals('https://user:password@xn--h1alffa9f.xn--p1ai:80/path/to/resource.json?key=value&key2=value2#fragment', $url->getSource());
        $url->clearRelativeUrl();
        $url->updateSource();
        self::assertEquals('https://user:password@xn--h1alffa9f.xn--p1ai:80', $url->getSource());
    }

}
