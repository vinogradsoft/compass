<div align="center">
<img alt="Compass logo" src="banner.svg">
<br/><br/>
</div>
<br/><br/>
<div align="center">
<strong>Create reusable applications.</strong>
<br>
Open source library.
<br /><br />
</div>

<div align="center">

[![codecov](https://codecov.io/gh/vinogradsoft/compass/graph/badge.svg?token=S1XRZ1GEY8)](https://codecov.io/gh/vinogradsoft/compass)
<img src="https://badgen.net/static/license/MIT/green">

</div>

## What is Compass?

> 👉 Compass is a library designed to work with URLs and hard disk file paths. It includes tools to simplify manipulation
> of this data. The goal of the library is to facilitate working with URLs and data on file locations.

## General Information

Compass can be used in various PHP applications to process file paths and URLs. It includes two main components:
`Compass\Path` and `Compass\Url`. Both of these components are separate objects and offer a set of methods for simple
data manipulation. `Compass\Path` provides tools for working with directories, including finding, replacing, and
checking for directories in a path string, as well as changing their order. In turn, `Compass\Url` provides capabilities
for working with URLs, allowing you to create, retrieve, and modify various parts of a URL.

Both components operate on a similar principle, first collecting the necessary parameters and then applying them to the
result using a special `updateSource()` method.

## Install

To install with composer:

```
php composer require vinogradsoft/compass "^1.0"
```

Requires PHP 8.0 or newer.

## URL Component

### 🚀 Quick Start

```php
<?php
require_once dirname(__DIR__, 1) . '/vendor/autoload.php';
use \Compass\Url;

$url = Url::createBlank();

$url->setScheme('http')->setUser('grigor')->setPassword('password')->setHost('vinograd.soft')
    ->setPort('8080')->setPath('/path/to/resource')->setSuffix('.json')
    ->setArrayQuery(['query' => 'value',])->setFragment('fragment');

$url->updateSource();

echo '<br><br><b>Authority:</b> ', $url->getAuthority();
echo '<br><b>Base Url:</b> ', $url->getBaseUrl();
echo '<br><b>Relative Url:</b> ', $url->getRelativeUrl();
echo '<br><b>Absolute Url:</b> ', $url->getSource(), '<br>';
echo '<br><b>Scheme:</b> ', $url->getScheme();
echo '<br><b>User:</b> ', $url->getUser();
echo '<br><b>Password:</b> ', $url->getPassword();
echo '<br><b>Host:</b> ', $url->getHost();
echo '<br><b>Port:</b> ', $url->getPort();
echo '<br><b>Path:</b> ', $url->getPath();
echo '<br><b>Suffix:</b> ', $url->getSuffix();
echo '<br><b>Query:</b> ', $url->getQuery();
echo '<br><b>Fragment:</b> ', $url->getFragment();

$url->setSource('http://россия.рф');
$url->setConversionIdnToAscii(true)->updateSource();
echo '<br><br><b>new URL:</b> ',$url; #http://xn--h1alffa9f.xn--p1ai
```

### Creating An Instance Of The Compass\Url Class

The `Compass\Url` class can be instantiated by calling the static `createBlank()` method or by using the `new` operator.
The `createBlank` method is notable for its use of cloning its own prototype. This method has two optional parameters:
`$isIdnToAscii` and `$updateStrategy`, which determine whether the host should be converted to punycode and what URL
update strategy should be used.

The first parameter, `$isIdnToAscii`, determines whether the host should be converted. If it is set to `true`, the host
is converted, and if it is `false`, no conversion of the host occurs.

The second parameter, `$updateStrategy`, determines the URL update strategy. The strategy involves methods for creating
the components of the URL.

To create a new instance of the `Compass\Url` class using the `new` operator, one must pass one required parameter - the
original URL as a string.

### Methods For Generating URLs

Parameters can be set using:

- the constructor
- the `setSource` method
- the `setAll` method
- methods specific to a particular part of the URL (as shown in the quick start).

### ⚡ Examples

#### **👉 Through the constructor**

```php
$url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
```

#### **👉 Using the `setSource` method**

```php
$url->setSource('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
```

#### **👉 Using the `setAll` method**

```php
$url->setAll([
    ':host' => 'host.ru',
    ':scheme' => 'http',
    ':user' => 'user',
    ':password' => 'password',
    ':port' => '80',
    ':path' => ['path', 'to', 'resource'],
    '?' => ['key' => 'value', 'key2' => 'value2'],
    '#' => 'fragment',
    ':suffix' => '.json',
]);
```

#### **👉 Using methods responsible for a specific part of Url.**

```php
$url->setScheme('http')->setUser('grigor')->setPassword('password')->setHost('vinograd.soft')
    ->setPort('8080')->setPath('/path/to/resource')->setSuffix('.json')
    ->setArrayQuery(['query' => 'value',])->setFragment('fragment');
```

In the first two options the suffix is not recognized. In such cases, the suffix must be set using a
separate `$url->setSuffix(.json);` method, this is the only way you can manage it.

> ❗ The suffix is not parsed since it could be any string and does not have to start with a dot. If you pass a URL with a
> suffix, it becomes part of the `path`.

### Applying Changes

For the changed parameters to take effect, you must call the `$url->updateSource()` method. This method has two optional
arguments - `$updateAbsoluteUrl` and `$suffix`. The value of the `$updateAbsoluteUrl` argument determines whether the
entire URL will be updated or just a relative portion of it. By default its value is `true`, in other words, by default
it will try to update the entire URL. The `$suffix` parameter is of type `string` and allows you to set the suffix at
the time of update.

After the parameters have been applied, you can get the updated result using the `$url->getSource()` method.

### Upgrade Strategies

> 📢 An update strategy is an object that combines all inputs to create a final URL. This object must be an implementation
> of the `Compass\UrlStrategy` interface. In the system, the class that performs this function is called
> `Compass\DefaultUrlStrategy`.

The strategy was invented in order to control the URL creation process. It contains a set of methods, one for each
section of the URL where parameters are concatenated. Which methods will participate in the update are determined in the
`Compass\Url` object based on the states of the sections. There are some flags that indicate which areas need to be
recreated. To make it easier to understand, we can draw an analogy with a road divided into a certain number of sections
“A”, “B”, “C”, “D” and the road company that is responsible for it. Ideally, the road should always be smooth. When a
section of the road, such as "C", is damaged, a repair team goes to that section and repairs it. You can also imagine a
URL, where the road is a string that has logical parts - sections. Repair teams are methods in the renewal strategy. The
road company is a `Compass\Url` object.

The strategy has six methods in which parts of the URL are created:

- `updateAuthority()`
- `updateBaseUrl()`
- `updateQuery()`
- `updatePath()`
- `updateRelativeUrl()`
- `updateAbsoluteUrl()`.

By setting any parameter for a URL, the system changes the state of the section, as if damaging the section for which
the parameter was passed. After calling the `$url->updateSource();` method Appropriate strategy methods are included in
the work.

> ❗ It is important to remember that the `Compass\Url` object stores the initial parts that the user installed and the
> results of each strategy method.

The implementation of methods can be divided into three levels.

```
                                               LEVEL 3
        |--------------------------------updateAbsoluteUrl()---------------------------------|
        |                                                                                    |
        |                                      LEVEL 2                                       |
        |---------------updateBaseUrl()-----------|------------updateRelativeUrl()-----------|
        |                                         |                                          |
        |                                      LEVEL 1                                       |
        |                updateAuthority()        |     updatePath()     updateQuery()       |
        |       /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\|/‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾‾‾\         |
        |http://grigor:password@vinograd.soft:8080/path/to/resource.json?query=value#fragment|
```

The third level method `updateAbsoluteUrl()` is always executed; it combines the saved results of the second level
methods. The second level methods `updateBaseUrl()` and `updateRelativeUrl()` combine the results of the first level. At
the first level, `updateAuthority()`, `updatePath()` and `updateQuery()` glue the original parts together in their own
scope.

### State Manipulation

States are stored in several fields of the `Compass\Url` class: `$authoritySate`, `$relativeUrlState`
and `$schemeState`. `$authoritySate` and `$relativeUrlState` are of type `int`, `$schemeState` is of type `bool`.
The `$authoritySate` and `$relativeUrlState` states are controlled by bit operations. Here is a code example that shows
their default values when the state is intact:

```php
const USER_STATE = 1 << 0;
const PASSWORD_STATE = 1 << 1;
const HOST_STATE = 1 << 2;
const PORT_STATE = 1 << 3;

const PATH_STATE = 1 << 0;
const QUERY_STATE = 1 << 1;
const FRAGMENT_STATE = 1 << 2;

const AUTHORITY_WHOLE = self::USER_STATE | self::PASSWORD_STATE | self::HOST_STATE | self::PORT_STATE;
const RELATIVE_URL_WHOLE = self::PATH_STATE | self::QUERY_STATE | self::FRAGMENT_STATE;

/**
 * current states
 */
protected int $authoritySate = self::AUTHORITY_WHOLE;
protected int $relativeUrlState = self::RELATIVE_URL_WHOLE;
protected bool $schemeState = true;
```

If we want to corrupt the state of `$relativeUrlState` in the `query` region, we can use bitwise operators:

```php
$relativeUrlState &= ~Url::QUERY_STATE;
```

The remaining sections can be manipulated in a similar way, with the exception of `$schemeState`, which needs to be
assigned a boolean value.

### ⚡ An Example Of Creating Your Own Strategy

When building your URL update process, sometimes you want a method to be executed that, based on the current state, will
not be executed. In such cases, the additional method `forceUnlockMethod(...)` is used, in which you can change the
current state of a certain section, thereby forcing the system to execute the desired method.

This is better understood with an example. Let's imagine that we need to generate URLs for referral links. Let’s create
a strategy that will add a `refid` parameter equal to `40` for all URLs with the `another.site` domain.

Strategy code:

```php
<?php

namespace <your\namespace>;

use Compass\DefaultUrlStrategy;
use Compass\Url;

class ReferralUrlStrategy extends DefaultUrlStrategy
{

    private bool $isAllowInsertParam = false;

    /**
     * @inheritDoc
     */
    public function updateQuery(array $items): string
    {
        if ($this->isAllowInsertParam) {
            $items['refid'] = 40;
            $this->isAllowInsertParam = false;
        }
        return http_build_query($items, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @inheritDoc
     */
    public function forceUnlockMethod(
        bool    &$schemeState,
        int     &$authoritySate,
        int     &$relativeUrlState,
        array   $items,
        array   $pathItems,
        array   $queryItems,
        bool    $updateAbsoluteUrl,
        ?string $suffix = null
    ): void
    {
        if ($items[Url::HOST] === 'another.site') {
            $this->isAllowInsertParam = true;
        }
        $relativeUrlState &= ~Url::QUERY_STATE;
    }

}
```

Now let's set the strategy and output two URLs, one of which will be the destination, with the domain `another.site`:

```php
$url = Url::createBlank();
$url->setUpdateStrategy(new ReferralUrlStrategy());

$url->setSource('https://another.site');
$url->updateSource();
echo $url->getSource(), '<br>'; # https://another.site/?refid=40

$url->setHost('vinograd.soft');
$url->updateSource();
echo $url->getSource(); # https://vinograd.soft
```

At first glance, it might seem that the state change (`$relativeUrlState &= ~Url::QUERY_STATE;`) should have been
written inside the `if` construct, but this is not the case. After we installed the `vinograd.soft` host, calling
the `updateSource()` method would not lead to the execution of the `updateQuery` method of our strategy, since not a
single parameter was added in the normal way that could change the state of this section. As a result, the state would
remain intact, only the `baseurl` section would be updated and the saved `refid=40` parameter from the last time would
be merged with the new `baseurl` which contains the `vinograd.soft` host.

This example shows that you are not limited to just the standard methods of setting URL parts. Using strategies, you can
post-process the result, for example, when you need to escape the result for HTML attributes containing URLs (href, src
and other attributes of this type).

---

## PATH Component

👉 `Compass\Path` can be described as an object representation of a file path. It operates on the path string without
relying on the actual file system. The component, like `Compass\Url`, has an update strategy, which includes
one `updatePath()` method. It's important to note that this component is stateless.

### 🚀 Demonstration Of Methods

```php
<?php

use Compass\Path;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

$path = new Path('/__NAME__/__NAME__Scanner/__NAME2__Driver', '/');

$path->replaceAll([
    '__NAME__' => 'User',
    '__NAME2__' => 'Filesystem',
]);
$path->updateSource();

echo '<br>', $path; # /User/UserScanner/FilesystemDriver

$path->setAll(['path', 'to', 'file.txt']);
$path->updateSource();

echo '<br>', $path; # path/to/file.txt

$path->set(1, 'newTo');
$path->updateSource();

echo '<br>', $path; # path/newTo/file.txt

$path->setBy('path', 'newPath');
$path->updateSource();

echo '<br>', $path; # newPath/newTo/file.txt

echo '<br>', $path->dirname(); # newPath/newTo

$path->setSuffix('.v');
$path->updateSource();
echo '<br>', $path; # newPath/newTo/file.txt.v

$path->setSource('newPath/newTo/file');
$path->setSuffix('.v');
$path->updateSource();
echo '<br>', $path; # newPath/newTo/file.v

$path->replace('newPath','path');
$path->updateSource();
echo '<br>', $path; # path/newTo/file.v
echo '<br>', $path->getLast(); # file.v
```

## Testing

``` php composer tests ```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see License [File](LICENSE) for more information.
